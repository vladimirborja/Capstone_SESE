<?php
session_start();
include_once __DIR__ . '/../db_config.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id > 0 && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        
        echo json_encode(['success' => true, 'count' => $stmt->rowCount()]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Not logged in or PDO connection missing']);
}
exit;