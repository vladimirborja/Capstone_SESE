<?php
include 'db_config.php';

// Force the database to report errors instead of failing silently
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and grab inputs
    $post_id = !empty($_POST['post_id']) ? trim($_POST['post_id']) : null;
    $user_id = !empty($_POST['user_id']) ? trim($_POST['user_id']) : null; // The person reporting
    $report_type = !empty($_POST['report_type']) ? trim($_POST['report_type']) : '';
    $description = !empty($_POST['description']) ? trim($_POST['description']) : '';

    // Validation
    if (!$post_id || !$user_id || !$report_type) {
        echo json_encode(['success' => false, 'error' => 'Missing Post ID, User ID, or Reason.']);
        exit;
    }

    try {
        // Prepare the statement exactly matching your phpMyAdmin columns
        $stmt = $pdo->prepare("INSERT INTO post_reports (post_id, user_id, report_type, description, created_at) 
                               VALUES (:post_id, :user_id, :report_type, :description, NOW())");
        
        $result = $stmt->execute([
            ':post_id'     => $post_id,
            ':user_id'     => $user_id,
            ':report_type' => $report_type,
            ':description' => $description
        ]);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database rejected the insert.']);
        }

    } catch (PDOException $e) {
        // This will show in your browser console if a Foreign Key error happens
        echo json_encode(['success' => false, 'error' => 'SQL Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid Request Method']);
}
?>