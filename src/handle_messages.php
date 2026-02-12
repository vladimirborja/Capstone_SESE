<?php
include 'db_config.php';

header('Content-Type: application/json');

if (isset($_POST['delete_msg_id'])) {
    $msg_id = $_POST['delete_msg_id'];

    try {
        $pdo->beginTransaction();

        // 1. FIXED: Changed 'contact' to 'id' to match your database screenshot
        $stmt_fetch = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
        $stmt_fetch->execute([$msg_id]);
        $msg_data = $stmt_fetch->fetch(PDO::FETCH_ASSOC);

        if ($msg_data) {
            // 2. Insert into archives table
            $stmt_archive = $pdo->prepare("INSERT INTO archives (original_id, type, sender_name, content) 
                                          VALUES (?, 'Message', ?, ?)");
            $stmt_archive->execute([
                $msg_id, 
                $msg_data['name'], // FIXED: Changed 'full_name' to 'name' based on your table
                $msg_data['message']
            ]);

            // 3. FIXED: Changed 'contact' to 'id'
            $stmt_delete = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt_delete->execute([$msg_id]);
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Message not found']);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No Message ID provided']);
}
exit;
?>