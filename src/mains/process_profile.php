<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) { exit(); }
$user_id = $_SESSION['user_id'];

// HANDLE IMAGE UPLOAD
if (isset($_POST['upload_photo'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $target_dir = "../uploads/profile_pics/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = $_FILES["profile_image"]["name"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = array("jpg", "jpeg", "png", "gif");

        if (in_array($file_ext, $allowed_ext)) {
            // Create a unique name: user_1_1712345678.jpg
            $new_filename = "user_" . $user_id . "_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                // Update database with the file path
                // Note: using the path relative to the root or where profile.php can access it
                $db_path = "../uploads/profile_pics/" . $new_filename;
                $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
                $stmt->bind_param("si", $db_path, $user_id);
                
                if ($stmt->execute()) {
                    header("Location: profile.php?status=img_success");
                } else {
                    header("Location: profile.php?status=error");
                }
            } else {
                header("Location: profile.php?status=img_error");
            }
        } else {
            header("Location: profile.php?status=invalid_file");
        }
    } else {
        header("Location: profile.php?status=img_error");
    }
    exit();
}

// HANDLE BASIC INFO UPDATE
if (isset($_POST['update_info'])) {
    // Combine first and last name as sent from the profile.php form
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $full_name = $first_name . " " . $last_name;
    
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);

    // Security Check: No numbers, only letters, spaces, and dots for full name
    if (!preg_match("/^[a-zA-Z\s.]+$/", $full_name)) {
        header("Location: profile.php?tab=settings&status=invalid_name");
        exit();
    }

    // Security Check: No numbers, no spaces, only letters and dots for username
    if (!preg_match("/^[a-zA-Z.]+$/", $username)) {
        header("Location: profile.php?tab=settings&status=invalid_username");
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, username = ? WHERE user_id = ?");
    
    if ($stmt === false) {
        die("Critical Error: Column 'username' not found. Ensure you ran the ALTER TABLE SQL command.");
    }

    $stmt->bind_param("sssi", $full_name, $email, $username, $user_id);

    if ($stmt->execute()) {
        header("Location: profile.php?tab=settings&status=updated");
    } else {
        header("Location: profile.php?tab=settings&status=error");
    }
    exit();
}

// HANDLE BIO UPDATE
if (isset($_POST['save_bio'])) {
    $bio = $_POST['bio'];
    $stmt = $conn->prepare("UPDATE users SET bio = ? WHERE user_id = ?");
    $stmt->bind_param("si", $bio, $user_id);
    $stmt->execute() ? header("Location: profile.php?tab=about&status=success") : header("Location: profile.php?tab=about&status=error");
    exit();
}

// HANDLE PASSWORD CHANGE
if (isset($_POST['change_pwd'])) {
    $new_pwd = $_POST['new_password'];
    if ($new_pwd === $_POST['confirm_password']) {
        $hashed = password_hash($new_pwd, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        $stmt->execute();
        header("Location: profile.php?tab=settings&status=pwd_success");
    } else {
        header("Location: profile.php?tab=settings&status=pwd_mismatch");
    }
    exit();
}

// HANDLE DELETE
if (isset($_POST['delete_account'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        session_destroy();
        header("Location: ../index.php");
    }
    exit();
}
?>