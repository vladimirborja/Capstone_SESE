<?php
// FIX: Files are in the same folder, removed '../'
include 'db_config.php';

header('Content-Type: application/json');

if (isset($_POST['delete_post_id'])) {
    $post_id = $_POST['delete_post_id'];

    try {
        $pdo->beginTransaction();

        // FIX: Changed 'posts' and 'post_id' to 'pets' and 'pet_id' to match your dashboard query
        $stmt2 = $pdo->prepare("DELETE FROM pets WHERE pet_id = ?");
        $stmt2->execute([$post_id]);
        
        // Note: We keep the records in post_reports for admin history 
        // if your database uses ON DELETE CASCADE, they will disappear automatically.

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
}
exit;
?>