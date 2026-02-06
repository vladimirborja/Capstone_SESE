<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "capstone_db");

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed']));
}

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Please login first']));
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];
$postId = isset($data['post_id']) ? intval($data['post_id']) : 0;
$action = isset($data['action']) ? $data['action'] : '';

if ($action === 'like') {
    // Check if already liked
    $stmt = $conn->prepare("SELECT like_id FROM post_likes WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $status = "";
    if ($result->num_rows > 0) {
        // Unlike
        $stmt = $conn->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $postId, $userId);
        $stmt->execute();
        $status = 'unliked';
    } else {
        // Like
        $stmt = $conn->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $postId, $userId);
        $stmt->execute();
        $status = 'liked';
    }

    // --- NEW LOGIC: Get updated count to send back to main.php ---
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM post_likes WHERE post_id = ?");
    $countStmt->bind_param("i", $postId);
    $countStmt->execute();
    $countRes = $countStmt->get_result();
    $countData = $countRes->fetch_assoc();
    $newCount = $countData['total'];

    echo json_encode([
        'status' => $status,
        'new_count' => $newCount
    ]);
}

if ($action === 'comment') {
    $text = trim($data['text']);
    if (!empty($text)) {
        $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $postId, $userId, $text);
        $stmt->execute();
        echo json_encode(['status' => 'commented']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']);
    }
}

$conn->close();
?>