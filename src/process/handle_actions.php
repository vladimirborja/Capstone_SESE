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
    }

    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM post_likes WHERE post_id = ?");
    $countStmt->bind_param("i", $postId);
    $countStmt->execute();
    $newCount = $countStmt->get_result()->fetch_assoc()['total'];

    echo json_encode([
        'status' => $status,
        'new_count' => $newCount
    ]);
    exit();
}

// --- COMMENT LOGIC ---
if ($action === 'comment') {
    $text = isset($data['text']) ? trim($data['text']) : '';
    
    if (!empty($text)) {
        // Insert the comment
        $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $postId, $userId, $text);
        
        if ($stmt->execute()) {
            // Fetch the user's name so the JS can display it immediately
            $userStmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $userData = $userStmt->get_result()->fetch_assoc();
            
            echo json_encode([
                'status' => 'success',
                'user_name' => $userData['full_name'],
                'comment' => $text
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']);
    }
    exit();
}

$conn->close();
?>