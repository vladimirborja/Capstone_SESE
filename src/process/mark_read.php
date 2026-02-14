<?php
session_start();
// Include the config from the parent directory
include_once __DIR__ . '/../db_config.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id > 0 && $pdo) {
    try {
        // Update all unread notifications for this user to 'read'
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Not logged in or DB error']);
}
exit;