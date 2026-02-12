<?php
session_start();
// 1. Database Connection
$conn = new mysqli("localhost", "root", "", "capstone_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ======================== HANDLE PROFILE PICTURE UPLOAD ========================
if (isset($_POST['update_avatar'])) {
    if (isset($_FILES['profile_pix']) && $_FILES['profile_pix']['error'] === 0) {
        $file_name = $_FILES['profile_pix']['name'];
        $file_tmp  = $_FILES['profile_pix']['tmp_name'];
        
        // Create unique name to avoid overwriting
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = "avatar_" . $user_id . "_" . time() . "." . $file_ext;
        $upload_path = "uploads/" . $new_file_name;

        // Ensure directory exists
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if (move_uploaded_file($file_tmp, $upload_path)) {
            // Update database with the new path
            $stmt = $conn->prepare("UPDATE users SET profile_pix = ? WHERE user_id = ?");
            $stmt->bind_param("si", $upload_path, $user_id);
            $stmt->execute();
            
            header("Location: profile.php?status=success");
        } else {
            header("Location: profile.php?status=error");
        }
    } else {
        header("Location: profile.php?status=error");
    }
    exit();
}

// ======================== HANDLE BIO UPDATE ========================
if (isset($_POST['save_bio'])) {
    $bio = $_POST['bio'];

    $stmt = $conn->prepare("UPDATE users SET bio = ? WHERE user_id = ?");
    $stmt->bind_param("si", $bio, $user_id);

    if ($stmt->execute()) {
        header("Location: profile.php?tab=about&status=success");
    } else {
        header("Location: profile.php?tab=about&status=error");
    }
    exit();
}

// ======================== HANDLE BASIC INFO UPDATE ========================
if (isset($_POST['update_info'])) {
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $fname, $lname, $email, $user_id);

    if ($stmt->execute()) {
        header("Location: profile.php?tab=settings&status=updated");
    } else {
        header("Location: profile.php?tab=settings&status=error");
    }
    exit();
}

// ======================== HANDLE PASSWORD CHANGE ========================
if (isset($_POST['change_pwd'])) {
    $new_pwd = $_POST['new_password'];
    $confirm_pwd = $_POST['confirm_password'];

    if ($new_pwd === $confirm_pwd) {
        $hashed_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_pwd, $user_id);
        $stmt->execute();
        
        header("Location: profile.php?tab=settings&status=pwd_success");
    } else {
        header("Location: profile.php?tab=settings&status=pwd_mismatch");
    }
    exit();
}
?>