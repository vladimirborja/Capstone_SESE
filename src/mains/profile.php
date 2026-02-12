<?php
session_start();
// Database connection
$conn = new mysqli("localhost", "root", "", "capstone_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Determine which tab to show
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'about';

// Fetch User Data from Database
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Split full_name for the First/Last Name fields
$name_parts = explode(" ", $user['full_name'], 2);
$first_name = $name_parts[0] ?? '';
$last_name = $name_parts[1] ?? '';

// Display logic
$username_val = $user['username'] ?? 'user';
// Image logic: use database image if exists, else default
$profile_img = !empty($user['profile_image']) ? $user['profile_image'] : '../images/homeImages/profile icon.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | <?php echo ucfirst($current_tab); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .profile-container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .profile-header { display: flex; align-items: center; justify-content: space-between; }
        .profile-left { display: flex; align-items: center; }
        
        /* Avatar Container Fixes */
        .avatar-wrapper {
            position: relative;
            width: 100px;
            height: 100px;
            margin-right: 20px;
        }
        .avatar {
            width: 100px; height: 100px; border-radius: 50%; border: 3px solid #1e90ff;
            display: flex; align-items: center; justify-content: center; overflow: hidden;
            background: #fff;
        }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }

        /* Camera Button Styling */
        .upload-overlay {
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: #1e90ff;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
            transition: 0.3s;
            z-index: 5;
        }
        .upload-overlay:hover { background: #007bff; transform: scale(1.1); }
        #imageUpload { display: none; }

        .profile-info h2 { margin: 0; font-size: 20px; font-weight: bold; }
        .username-display { color: #666; font-size: 14px; }
        .tabs { margin-top: 20px; border-bottom: 1px solid #ddd; }
        .tabs a { margin-right: 20px; padding-bottom: 10px; display: inline-block; text-decoration: none; color: #333; font-weight: bold; }
        .tabs a.active { border-bottom: 3px solid #1e90ff; color: #1e90ff; }
        
        .form-control { background-color: #f0f4f8; border: 1px solid #ced4da; }
        .save-btn { margin-top: 20px; background: #4da3ff; color: white; border: none; padding: 10px 25px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .save-btn:hover { background: #1a8cff; }
        .delete-btn { margin-top: 10px; background: #ff4d4d; color: white; border: none; padding: 8px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .delete-btn:hover { background: #e60000; }
        h4 { margin-top: 25px; color: #333; font-weight: bold; }
    </style>
</head>
<body class="bg-light">
    
    <?php include 'header.php'; ?>

    <div class="container">
        <form id="imageForm" action="process_profile.php" method="POST" enctype="multipart/form-data" style="display:none;">
            <input type="file" name="profile_image" id="imageUpload" accept="image/*" onchange="submitImage()">
            <input type="hidden" name="upload_photo" value="1">
        </form>

        <div class="profile-container shadow-sm">
            <div class="profile-header">
                <div class="profile-left">
                    <div class="avatar-wrapper">
                        <div class="avatar">
                            <img src="<?php echo htmlspecialchars($profile_img); ?>" id="profilePreview" alt="Avatar">
                        </div>
                        <label for="imageUpload" class="upload-overlay">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>

                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <div class="username-display">@<?php echo htmlspecialchars($username_val); ?></div>
                    </div>
                </div>
            </div>

            <nav class="tabs">
                <a href="profile.php?tab=about" class="<?php echo $current_tab == 'about' ? 'active' : ''; ?>">About</a>
                <a href="profile.php?tab=settings" class="<?php echo $current_tab == 'settings' ? 'active' : ''; ?>">Settings</a>
            </nav>

            <?php if ($current_tab == 'about'): ?>
                <form action="process_profile.php" method="POST" class="mt-4">
                    <div class="mb-3">
                        <label class="fw-bold">User’s Bio</label>
                        <input type="text" name="bio" class="form-control mt-2" value="<?php echo htmlspecialchars($user['bio'] ?? ''); ?>" placeholder="Tell us about yourself...">
                    </div>
                    <button type="submit" name="save_bio" class="save-btn">Save</button>
                </form>

            <?php else: ?>
                <div class="settings-content mt-4">
                    <form action="process_profile.php" method="POST">
                        <h4>Basic Information</h4>
                        
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="input-group ">
                                <span class="input-group-text">@</span>
                                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>

                        <button type="submit" name="update_info" class="save-btn">Save Changes</button>
                    </form>

                    <hr class="my-5">

                    <form action="process_profile.php" method="POST">
                        <h4>Set New Password</h4>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" name="change_pwd" class="save-btn">Update Password</button>
                    </form>

                    <hr class="my-5">

                    <div class="delete-section">
                        <h4 class="text-danger">Delete Account</h4>
                        <p class="text-muted small">This action cannot be undone. All your data will be wiped.</p>
                        
                        <form id="deleteAccountForm" action="process_profile.php" method="POST" style="display:none;">
                            <input type="hidden" name="delete_account" value="1">
                        </form>
                        
                        <button type="button" onclick="confirmDelete()" class="delete-btn">Delete Account</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Automatically submit the form once a user selects a file
    function submitImage() {
        document.getElementById('imageForm').submit();
    }

    function confirmDelete() {
        Swal.fire({
            title: 'Are you sure?',
            text: "Your account will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4d4d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteAccountForm').submit();
            }
        });
    }

    // Status handling
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    if (status) {
        const config = {
            success: { title: 'Saved!', text: 'Information updated.', icon: 'success' },
            updated: { title: 'Updated!', text: 'Profile info updated.', icon: 'success' },
            img_success: { title: 'Success!', text: 'Profile photo updated.', icon: 'success' },
            pwd_success: { title: 'Security Updated!', text: 'Password changed.', icon: 'success' },
            error: { title: 'Error!', text: 'Something went wrong.', icon: 'error' },
            img_error: { title: 'Upload Failed!', text: 'Check file size or format.', icon: 'error' },
            pwd_mismatch: { title: 'Mismatch!', text: 'Passwords do not match.', icon: 'warning' }
        };
        if (config[status]) {
            Swal.fire({ ...config[status], confirmButtonColor: '#1e90ff' });
        }
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    </script>
</body>
</html>