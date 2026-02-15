<?php
session_start();
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "capstone_db");

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];
$postId = isset($data['post_id']) ? intval($data['post_id']) : 0;
$action = isset($data['action']) ? $data['action'] : '';

// UPDATED: Helper function to include post_id
function createNotification($conn, $targetUserId, $postId, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, post_id, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->bind_param("iis", $targetUserId, $postId, $message);
    $stmt->execute();
}

// --- LIKE LOGIC ---
if ($action === 'like') {
    $stmt = $conn->prepare("SELECT like_id FROM post_likes WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $postId, $userId);
        $stmt->execute();
        $status = 'unliked';
    } else {
        $stmt = $conn->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $postId, $userId);
        $stmt->execute();
        $status = 'liked';

        // Notify Owner
        $ownerStmt = $conn->prepare("SELECT user_id FROM posts WHERE post_id = ?");
        $ownerStmt->bind_param("i", $postId);
        $ownerStmt->execute();
        $ownerId = $ownerStmt->get_result()->fetch_assoc()['user_id'];

        if ($ownerId && $ownerId != $userId) {
            $userStmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $likerName = $userStmt->get_result()->fetch_assoc()['full_name'];
            
            $msg = $likerName . " liked your post.";
            createNotification($conn, $ownerId, $postId, $msg);
        }
    }

    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM post_likes WHERE post_id = ?");
    $countStmt->bind_param("i", $postId);
    $countStmt->execute();
    $newCount = $countStmt->get_result()->fetch_assoc()['total'];

    echo json_encode(['status' => $status, 'new_count' => $newCount]);
    exit();
}

// --- COMMENT LOGIC ---
if ($action === 'comment') {
    $text = isset($data['text']) ? trim($data['text']) : '';
    if (!empty($text)) {
        $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $postId, $userId, $text);
        
        if ($stmt->execute()) {
            $userStmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $userData = $userStmt->get_result()->fetch_assoc();

            $ownerStmt = $conn->prepare("SELECT user_id FROM posts WHERE post_id = ?");
            $ownerStmt->bind_param("i", $postId);
            $ownerStmt->execute();
            $ownerId = $ownerStmt->get_result()->fetch_assoc()['user_id'];

            if ($ownerId && $ownerId != $userId) {
                $msg = $userData['full_name'] . " commented: \"" . substr($text, 0, 20) . "...\"";
                createNotification($conn, $ownerId, $postId, $msg);
            }
            
            echo json_encode(['status' => 'success', 'user_name' => $userData['full_name'], 'comment' => $text]);
        }
    }
    exit();
}
$conn->close();
?>