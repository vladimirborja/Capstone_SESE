<?php
require_once 'db_config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE verification_code = ? AND is_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $update = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE user_id = ?");
        $update->execute([$user['user_id']]);
        
        // Redirect to login with success message
        header("Location: signIn.php?status=verified");
        exit;
    } else {
        echo "Invalid or expired token.";
    }
}