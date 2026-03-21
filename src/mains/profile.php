<?php
session_start();

require_once '../config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$viewer_user_id = (int)$_SESSION['user_id'];
$profile_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $viewer_user_id;
if ($profile_user_id <= 0) {
    $profile_user_id = $viewer_user_id;
}
$is_own_profile = $profile_user_id === $viewer_user_id;

// Determine which tab to show
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'about';
if (!$is_own_profile && $current_tab === 'settings') {
    $current_tab = 'about';
}

// Fetch User Data from Database
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    header("Location: profile.php");
    exit();
}

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
    $petStmt->bind_param("i", $profile_user_id);
    $petStmt->execute();
    $myPetSubmissions = $petStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$ownedEstablishments = [];
$ownerBadgeLabel = '';
$ownerBadgeClass = '';
try {
    $ownedStmt = $conn->prepare("SELECT name, COALESCE(verified_by, 'self') AS verified_by
                                 FROM establishments
                                 WHERE COALESCE(owner_id, user_id) = ? AND COALESCE(owner_verified, 0) = 1
                                 ORDER BY created_at DESC");
    if ($ownedStmt) {
        $ownedStmt->bind_param("i", $profile_user_id);
        $ownedStmt->execute();
        $ownedEstablishments = $ownedStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if (!empty($ownedEstablishments)) {
            $verifiedByValues = array_map(static fn($row) => strtolower((string)($row['verified_by'] ?? 'self')), $ownedEstablishments);
            $isAdminVerified = in_array('admin', $verifiedByValues, true) || in_array('super_admin', $verifiedByValues, true);
            $ownerBadgeLabel = $isAdminVerified ? '✓ Admin-Verified Establishment Owner' : '✓ Verified Establishment Owner';
            $ownerBadgeClass = $isAdminVerified ? 'owner-verified-chip-admin' : 'owner-verified-chip-self';
        }
    }
} catch (Exception $e) {
    $ownedEstablishments = [];
}

$roleKey = strtolower(trim((string)($user['role'] ?? 'user')));
$roleMeta = [
    'user' => ['label' => 'User', 'class' => 'role-user', 'icon' => 'fa-user'],
    'admin' => ['label' => 'Admin', 'class' => 'role-admin', 'icon' => 'fa-user-shield'],
    'super_admin' => ['label' => 'Super Admin', 'class' => 'role-super-admin', 'icon' => 'fa-crown'],
    'business_owner' => ['label' => 'Business Owner', 'class' => 'role-business-owner', 'icon' => 'fa-store'],
    'veterinarian' => ['label' => 'Veterinarian', 'class' => 'role-veterinarian', 'icon' => 'fa-stethoscope'],
    'salon_owner' => ['label' => 'Salon Owner', 'class' => 'role-salon-owner', 'icon' => 'fa-scissors']
];
$currentRole = $roleMeta[$roleKey] ?? ['label' => 'User', 'class' => 'role-user', 'icon' => 'fa-user'];
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
    <style>
        .role-badge-card {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            margin-top: 8px;
            border: 1px solid transparent;
        }
        .role-badge-card i { font-size: 0.9rem; }
        .role-user { background: #eef2f7; color: #334155; border-color: #d8e0ea; }
        .role-admin { background: #e0f2fe; color: #075985; border-color: #bae6fd; }
        .role-super-admin { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .role-business-owner { background: #ede9fe; color: #5b21b6; border-color: #ddd6fe; }
        .role-veterinarian { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .role-salon-owner { background: #ffe4e6; color: #9f1239; border-color: #fecdd3; }
        .owner-verified-chip {
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            font-size: 0.82rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .owner-verified-chip-self {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            color: #2e7d32;
        }
        .owner-verified-chip-admin {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            color: #1565c0;
        }
        .owner-verified-chip .owner-sub {
            font-weight: 600;
            color: #166534;
            display: block;
            width: 100%;
        }
    </style>
</head>
<body class="bg-light">
    
    <?php include 'header.php'; ?>

    <div class="container">
        <?php if ($is_own_profile): ?>
            <form id="imageForm" action="process_profile.php" method="POST" enctype="multipart/form-data" style="display:none;">
                <input type="file" name="profile_image" id="imageUpload" accept="image/*" onchange="submitImage()">
                <input type="hidden" name="upload_photo" value="1">
            </form>
        <?php endif; ?>

        <div class="profile-container shadow-sm ">
            <div class="profile-header ">
                <div class="profile-left">
                    <div class="avatar-wrapper">
                        <div class="avatar ">
                            <img src="<?php echo htmlspecialchars($profile_img); ?>" id="profilePreview" alt="Avatar">
                        </div>
                        <?php if ($is_own_profile): ?>
                            <label for="imageUpload" class="upload-overlay">
                                <i class="fas fa-camera"></i>
                            </label>
                        <?php endif; ?>
                    </div>

                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <div class="username-display">@<?php echo htmlspecialchars($username_val); ?></div>
                        <div class="role-badge-card <?php echo htmlspecialchars($currentRole['class']); ?>">
                            <i class="fas <?php echo htmlspecialchars($currentRole['icon']); ?>"></i>
                            <?php echo htmlspecialchars($currentRole['label']); ?>
                        </div>
                        <?php if (!empty($ownedEstablishments)): ?>
                            <div class="owner-verified-chip <?php echo htmlspecialchars($ownerBadgeClass); ?>">
                                <i class="fas fa-shield-alt"></i>
                                <?php echo htmlspecialchars($ownerBadgeLabel); ?>
                                <span class="owner-sub">
                                    Owner of: <?php echo htmlspecialchars(implode(', ', array_map(static fn($r) => (string)($r['name'] ?? ''), $ownedEstablishments))); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <nav class="tabs">
                <a href="profile.php?tab=about&user_id=<?php echo (int)$profile_user_id; ?>" class="<?php echo $current_tab == 'about' ? 'active' : ''; ?>">About</a>
                <?php if ($is_own_profile): ?>
                    <a href="profile.php?tab=settings&user_id=<?php echo (int)$profile_user_id; ?>" class="<?php echo $current_tab == 'settings' ? 'active' : ''; ?>">Settings</a>
                <?php endif; ?>
            </nav>

            <?php if ($current_tab == 'about'): ?>
                <?php if ($is_own_profile): ?>
                    <form action="process_profile.php" method="POST" class="mt-4">
                        <div class="mb-3">
                            <label class="fw-bold">User’s Bio</label>
                            <input type="text" name="bio" class="form-control mt-2" value="<?php echo htmlspecialchars($user['bio'] ?? ''); ?>" placeholder="Tell us about yourself...">
                        </div>
                        <button type="submit" name="save_bio" class="save-btn">Save</button>
                    </form>
                <?php else: ?>
                    <div class="mt-4">
                        <label class="fw-bold">User’s Bio</label>
                        <p class="text-muted mt-2 mb-0"><?php echo htmlspecialchars($user['bio'] ?? 'No bio added yet.'); ?></p>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <h5 class="fw-bold text-primary"><?php echo $is_own_profile ? 'My Lost &amp; Found Submissions' : 'Lost &amp; Found Submissions'; ?></h5>
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

            <?php elseif ($is_own_profile): ?>
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
            <?php else: ?>
                <div class="mt-4">
                    <p class="text-muted mb-0">Settings are only available on your own profile.</p>
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