<?php
include 'db_config.php';

header('Content-Type: application/json');

if (isset($_POST['delete_post_id'])) {
    $post_id = $_POST['delete_post_id'];

    try {
        $pdo->beginTransaction();

        // 1. Fetch post details for archiving before we destroy it
        $stmt_fetch = $pdo->prepare("SELECT p.*, u.full_name FROM posts p JOIN users u ON p.user_id = u.user_id WHERE p.post_id = ?");
        $stmt_fetch->execute([$post_id]);
        $post_data = $stmt_fetch->fetch(PDO::FETCH_ASSOC);

        if ($post_data) {
            // 2. Insert into archives table so the admin has a record of why it was deleted
            $stmt_archive = $pdo->prepare("INSERT INTO archives (original_id, type, sender_name, content) 
                                          VALUES (?, 'Reported Post', ?, ?)");
            $stmt_archive->execute([
                $post_id, 
                $post_data['full_name'], 
                $post_data['content']
            ]);

            // 3. Delete related notifications FIRST (this is what's causing your error)
            $stmt_del_notifications = $pdo->prepare("DELETE FROM notifications WHERE post_id = ?");
            $stmt_del_notifications->execute([$post_id]);

            // 4. Delete related comments
            $stmt_del_comments = $pdo->prepare("DELETE FROM post_comments WHERE post_id = ?");
            $stmt_del_comments->execute([$post_id]);

            // 5. Delete related likes
            $stmt_del_likes = $pdo->prepare("DELETE FROM post_likes WHERE post_id = ?");
            $stmt_del_likes->execute([$post_id]);

            // 6. Delete any reports associated with this post
            $stmt_del_reports = $pdo->prepare("DELETE FROM post_reports WHERE post_id = ?");
            $stmt_del_reports->execute([$post_id]);

            // 7. DELETE FROM THE ACTUAL POSTS TABLE 
            // This is the step that removes it from your main.php feed
            $stmt_del_post = $pdo->prepare("DELETE FROM posts WHERE post_id = ?");
            $stmt_del_post->execute([$post_id]);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Post not found in database']);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No Post ID provided']);
}
exit;
?>