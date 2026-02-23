<?php
require_once 'google_api.php';
require_once 'db_config.php';
session_start();

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        
        // Get user profile info
        $google_oauth = new Google\Service\Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $email = $google_account_info->email;
        $full_name = $google_account_info->name;
        $google_id = $google_account_info->id;

        // 1. Check if user exists in your DB
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Log them in
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
        } else {
            // 2. Register user if they don't exist
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, is_verified, role, google_id) VALUES (?, ?, 1, 'user', ?)");
            $stmt->execute([$full_name, $email, $google_id]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['role'] = 'user';
            $_SESSION['full_name'] = $full_name;
        }

        if ($_SESSION['role'] === 'admin') {
            header('Location: admin_reports.php');
        } else {
            header('Location: mains/main.php');
        }
        exit;
    }
}

// If something goes wrong
header('Location: signIn.php?error=google_failed');
exit;