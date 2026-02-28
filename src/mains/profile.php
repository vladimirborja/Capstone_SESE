<?php
session_start();

require_once '../config.php';

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

$myPetSubmissions = [];
$petStmt = $conn->prepare("SELECT pet_id, pet_name, category, requested_category, verification_reason, created_at
                           FROM pets
                           WHERE user_id = ?
                           ORDER BY created_at DESC");
if ($petStmt) {
    $petStmt->bind_param("i", $user_id);
    $petStmt->execute();
    $myPetSubmissions = $petStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | <?php echo ucfirst($current_tab); ?></title>
    <link rel="icon" type="image/png" href="../favicon.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    
    <?php include 'header.php'; ?>

    <div class="container">
        <form id="imageForm" action="process_profile.php" method="POST" enctype="multipart/form-data" style="display:none;">
            <input type="file" name="profile_image" id="imageUpload" accept="image/*" onchange="submitImage()">
            <input type="hidden" name="upload_photo" value="1">
        </form>

        <div class="profile-container shadow-sm ">
            <div class="profile-header ">
                <div class="profile-left">
                    <div class="avatar-wrapper">
                        <div class="avatar ">
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

                <div class="mt-4">
                    <h5 class="fw-bold text-primary">My Lost &amp; Found Submissions</h5>
                    <?php if (empty($myPetSubmissions)): ?>
                        <p class="text-muted small mb-0">No pet submissions yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Pet</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myPetSubmissions as $p): ?>
                                        <?php
                                            $key = strtolower(trim((string)($p['category'] ?? '')));
                                            $label = strtoupper($key);
                                            $badge = 'bg-secondary';
                                            if ($key === 'waiting_approval') { $label = 'WAITING FOR APPROVAL'; $badge = 'bg-danger'; }
                                            elseif ($key === 'rejected') { $label = 'REJECTED'; $badge = 'bg-secondary'; }
                                            elseif ($key === 'lost') { $label = 'LOST'; $badge = 'bg-danger'; }
                                            elseif ($key === 'found') { $label = 'FOUND'; $badge = 'bg-success'; }
                                            elseif ($key === 'for_adoption') { $label = 'FOR ADOPTION'; $badge = 'bg-primary'; }
                                            elseif ($key === 'adopted') { $label = 'ADOPTED'; $badge = 'bg-success'; }
                                            elseif ($key === 'pending') { $label = 'PENDING'; $badge = 'bg-warning text-dark'; }
                                            elseif ($key === 'resolved') { $label = 'RESOLVED'; $badge = 'bg-secondary'; }
                                            $requested = strtoupper(str_replace('_', ' ', (string)($p['requested_category'] ?? '')));
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($p['pet_name'] ?? 'N/A') ?></td>
                                            <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($label) ?></span></td>
                                            <td><?= !empty($p['created_at']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($p['created_at']))) : 'N/A' ?></td>
                                            <td class="small">
                                                <?php if ($key === 'waiting_approval'): ?>
                                                    <span class="text-danger fw-bold">Under admin review</span>
                                                    <?php if ($requested !== ''): ?><div class="text-muted">Pending type: <?= htmlspecialchars($requested) ?></div><?php endif; ?>
                                                <?php elseif ($key === 'rejected'): ?>
                                                    <span class="text-secondary fw-bold"><?= htmlspecialchars($p['verification_reason'] ?? 'Rejected by admin.') ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">Visible according to current status.</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

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