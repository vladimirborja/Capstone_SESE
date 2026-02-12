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

// Logic for display name
$full_name = trim(($user['first_name'] ?? '') . " " . ($user['last_name'] ?? ''));
if(empty($full_name)) $full_name = $user['full_name']; // Fallback to full_name column
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_GET['status'])): ?>
<script>
    const status = "<?php echo $_GET['status']; ?>";
    
    if (status === "success") {
        Swal.fire({
            title: 'Saved!',
            text: 'Your bio has been updated successfully.',
            icon: 'success',
            confirmButtonColor: '#1e90ff'
        });
    } else if (status === "updated") {
        Swal.fire({
            title: 'Updated!',
            text: 'Profile information updated.',
            icon: 'success',
            confirmButtonColor: '#1e90ff'
        });
    } else if (status === "pwd_success") {
        Swal.fire({
            title: 'Security Updated!',
            text: 'Your password has been changed.',
            icon: 'success',
            confirmButtonColor: '#1e90ff'
        });
    } else if (status === "error") {
        Swal.fire({
            title: 'Error!',
            text: 'Something went wrong. Please try again.',
            icon: 'error',
            confirmButtonColor: '#ff4d4d'
        });
    } else if (status === "pwd_mismatch") {
        Swal.fire({
            title: 'Mismatch!',
            text: 'Passwords do not match.',
            icon: 'warning',
            confirmButtonColor: '#ff4d4d'
        });
    }

    // Clean up the URL so the alert doesn't pop up again on refresh
    window.history.replaceState({}, document.title, window.location.pathname + window.location.search.split('&status=')[0]);
</script>
<?php endif; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | <?php echo ucfirst($current_tab); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <style>
        .profile-container {
    max-width: 900px;
    margin: 30px auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    padding: 20px;
}

.profile-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.profile-left {
    display: flex;
    align-items: center;
}

.avatar {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 3px solid #1e90ff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    overflow: hidden;
}

.avatar img {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-info h2 {
    margin: 0;
    font-size: 20px;
    font-weight: bold;
}

.username {
    color: #666;
    font-size: 14px;
}

.location {
    color: #888;
    font-size: 13px;
}

.tabs {
    margin-top: 20px;
    border-bottom: 1px solid #ddd;
}

.tabs a {
    margin-right: 20px;
    padding-bottom: 10px;
    display: inline-block;
    text-decoration: none;
    color: #333;
    font-weight: bold;
}

.tabs a.active {
    border-bottom: 3px solid #1e90ff;
    color: #1e90ff;
}

.bio-section {
    margin-top: 20px;
}

.bio-section label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    color: #333;
}

.bio-section input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}

.save-btn {
    margin-top: 10px;
    background: #4da3ff;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}

.save-btn:hover {
    background: #1a8cff;
}

.delete-btn {
    margin-top: 10px;
    background: #ff4d4d;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}

.delete-btn:hover {
    background: #e60000;
}

.full {
    margin-top: 15px;
}

h4 {
    margin-top: 25px;
    color: #333;
    font-weight: bold;
}
    </style>
    
   <?php include 'header.php'; ?>


    <div class="container">
        <div class="profile-container shadow-sm">
            <div class="profile-header">
                <div class="profile-left">
                    <div class="avatar">
                        <img src="../images/homeImages/profile icon.png" alt="Avatar">
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($full_name); ?></h2>
                        <div class="username">@<?php echo htmlspecialchars($user['username'] ?? 'user'); ?></div>
                        <div class="location"><?php echo htmlspecialchars($user['location'] ?? 'No Location'); ?></div>
                    </div>
                </div>
               
            </div>

            <nav class="tabs">
                <a href="profile.php?tab=about" class="<?php echo $current_tab == 'about' ? 'active' : ''; ?>">About</a>
                <a href="profile.php?tab=settings" class="<?php echo $current_tab == 'settings' ? 'active' : ''; ?>">Settings</a>
            </nav>

            <?php if ($current_tab == 'about'): ?>
                <form action="process_profile.php" method="POST" class="mt-4">
                    <div class="bio-section">
                        <label for="bio">User’s Bio</label>
                        <input type="text" name="bio" id="bio" value="<?php echo htmlspecialchars($user['bio'] ?? ''); ?>" placeholder="Tell us about yourself...">
                    </div>
                    <button type="submit" name="save_bio" class="save-btn">Save</button>
                </form>

            <?php else: ?>
                <div class="settings-content mt-4">
                    <form action="process_profile.php" method="POST">
                        <h4>Basic Information</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="full mt-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                        </div>

                        <button type="submit" name="update_info" class="save-btn mt-3">Save Changes</button>
                    </form>

                    <hr class="my-4">

                    <form action="process_profile.php" method="POST">
                        <h4>Set New Password</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" name="change_pwd" class="save-btn mt-3">Update Password</button>
                    </form>

                    <hr class="my-4">

                    <div class="delete-section">
                        <h4 class="text-danger">Delete Account</h4>
                        <small class="text-muted">(This action cannot be undone)</small><br>
                        <form action="process_profile.php" method="POST" onsubmit="return confirm('Are you sure you want to delete your account?');">
                            <button type="submit" name="delete_account" class="delete-btn">Delete Account</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>