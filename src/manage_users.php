<?php
session_start();
require_once 'check_session.php';
require_once 'db_config.php';

// Only admins can access
requireRole(['admin']);

$user = getLoggedInUser();

// Fetch admin profile image from DB
$adminStmt = $pdo->prepare("SELECT profile_image FROM users WHERE user_id = ?");
$adminStmt->execute([$user['id']]);
$adminData = $adminStmt->fetch();
$adminProfileImage = (!empty($adminData['profile_image']))  ? str_replace('../', '', $adminData['profile_image']) : 'images/homeImages/profile icon.png';
// Handle role update
$successMessage = ""; 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_role'])) {
        $userId = $_POST['user_id'];
        $newRole = $_POST['role'];

        if (in_array($newRole, ['user', 'admin'])) {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
            $stmt->execute([$newRole, $userId]);
            $successMessage = "User role updated successfully!";
        }
    }

    if (isset($_POST['toggle_status'])) {
        $userId = $_POST['user_id'];
        $newStatus = $_POST['is_active'];
        $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE user_id = ?");
        $stmt->execute([$newStatus, $userId]);
        $successMessage = "User status updated successfully!";
    }
}

// Get all users
$stmt = $pdo->query("SELECT user_id, full_name, email, phone_number, role, created_at, last_login, is_active FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body { background: #cbd5e0; font-family: sans-serif; margin: 0; }
        
        .navbar-custom { 
            background-color: #1e88e5; 
            height: 55px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 0 20px; 
            color: white; 
            position: relative; 
        }
        
        .nav-center-title {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-weight: bold;
            font-size: 1.25rem;
            pointer-events: none;
            white-space: nowrap;
            text-transform: uppercase;
        }

        .logo-admin { height: 35px; width: 35px; display: flex; align-items: center; z-index: 1; }
        .logo-admin img { height: 100%; width: 100%; object-fit: contain; }
        .nav-right-group { display: flex; align-items: center; gap: 10px; z-index: 1; }

        /* Admin profile picture in navbar */
        .admin-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            cursor: pointer;
        }

        .profile-dropdown .btn-profile { background: none; border: none; padding: 0; transition: 0.3s; display: flex; align-items: center; }
        .profile-dropdown .btn-profile:after { display: none; } 
        .dropdown-menu-end { border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: none; margin-top: 12px; min-width: 220px; padding: 10px 0; }
        .dropdown-item { font-size: 0.9rem; font-weight: 500; color: #4a5568; padding: 10px 20px; display: flex; align-items: center; gap: 10px; }
        .dropdown-item:hover { background-color: #f7fafc; color: #1e88e5; }

        .badge-active { background: #28a745; width: 75px; display: inline-block; color: white; padding: 5px; border-radius: 6px; text-align: center; font-size: 0.75rem; }
        .badge-inactive { background: #dc3545; width: 75px; display: inline-block; color: white; padding: 5px; border-radius: 6px; text-align: center; font-size: 0.75rem; }
        .card { border-radius: 20px; border: 6px solid white; background-color: #edf2f7; margin-top: 30px; }
        .table thead { background-color: #e2e8f0; }
        .status-container { display: flex; align-items: center; gap: 8px; justify-content: start; }
    </style>
</head>

<body>
    <div class="navbar-custom shadow-sm">
        <div class="logo-admin">
            <a href="admin_reports.php">
                <img src="../src/images/homeImages/Sese-Logo3.png" alt="Logo" />
            </a>
        </div>

        <div class="nav-center-title">User Management</div>

        <div class="nav-right-group">
            <a href="admin_reports.php" class="btn btn-light btn-sm fw-bold me-2">
                <i class="fas fa-chart-line me-1"></i> Dashboard
            </a>
            
            <div class="dropdown profile-dropdown">
                <button class="btn btn-profile dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= htmlspecialchars($adminProfileImage); ?>" alt="Admin" class="admin-avatar">
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <div class="d-flex align-items-center gap-2 px-3 py-2">
                            <img src="<?= htmlspecialchars($adminProfileImage); ?>" class="rounded-circle" width="40" height="40" style="object-fit:cover; border: 2px solid #1e88e5;">
                            <div>
                                <div class="fw-bold text-dark small"><?= htmlspecialchars($user['name']); ?></div>
                                <div class="text-muted" style="font-size:0.75rem;">Administrator</div>
                            </div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../src/mains/main.php"><i class="fas fa-home"></i> Main Feed</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent border-0 py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-users me-2"></i>Registered Users</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Last Login</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="ps-4"><?= $u['user_id']; ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($u['full_name']); ?></td>
                                    <td><?= htmlspecialchars($u['email']); ?></td>
                                    <td><?= htmlspecialchars($u['phone_number']); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $u['user_id']; ?>">
                                            <input type="hidden" name="update_role" value="1">
                                            <select name="role" class="form-select form-select-sm w-auto d-inline"
                                                onchange="confirmRoleChange(this, '<?= htmlspecialchars($u['full_name']); ?>')">
                                                <option value="user" <?= $u['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" class="m-0">
                                            <div class="status-container">
                                                <input type="hidden" name="user_id" value="<?= $u['user_id']; ?>">
                                                <input type="hidden" name="is_active" value="<?= $u['is_active'] ? 0 : 1; ?>">
                                                <input type="hidden" name="toggle_status" value="1">
                                                
                                                <span class="<?= $u['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                                    <?= $u['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                                
                                                <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center"
                                                    style="padding: 0.25rem 0.5rem;"
                                                    onclick="confirmStatusToggle(this, '<?= htmlspecialchars($u['full_name']); ?>')">
                                                    <i class="fas fa-sync-alt" style="font-size: 0.75rem;"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($u['created_at'])); ?></td>
                                    <td><small><?= $u['last_login'] ? date('M d, Y H:i', strtotime($u['last_login'])) : 'Never'; ?></small></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm text-white" style="background-color: #3182ce;" 
                                            onclick="viewUser('<?= $u['user_id']; ?>', '<?= htmlspecialchars($u['full_name']); ?>', '<?= htmlspecialchars($u['email']); ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        <?php if ($successMessage !== ""): ?>
            Swal.fire({
                title: 'Success!',
                text: '<?= $successMessage; ?>',
                icon: 'success',
                confirmButtonColor: '#1e88e5'
            });
        <?php endif; ?>

        function confirmLogout() {
            Swal.fire({
                title: 'Ready to leave?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e53e3e',
                confirmButtonText: 'Logout Now'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = 'logout.php';
            });
        }

        function confirmRoleChange(selectElement, userName) {
            const newRole = selectElement.value;
            Swal.fire({
                title: 'Change User Role?',
                text: `Are you sure you want to change ${userName} to ${newRole.toUpperCase()}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1e88e5',
                confirmButtonText: 'Yes, change it!'
            }).then((result) => {
                if (result.isConfirmed) selectElement.form.submit();
                else location.reload(); 
            });
        }

        function confirmStatusToggle(buttonElement, userName) {
            Swal.fire({
                title: 'Update Status?',
                text: `Do you want to toggle the active status for ${userName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1e88e5',
                confirmButtonText: 'Yes, update'
            }).then((result) => {
                if (result.isConfirmed) buttonElement.closest('form').submit();
            });
        }

        function viewUser(id, name, email) {
            Swal.fire({
                title: 'User Information',
                html: `<div class="text-start mt-3">
                        <p><strong>User ID:</strong> ${id}</p>
                        <p><strong>Full Name:</strong> ${name}</p>
                        <p><strong>Email Address:</strong> ${email}</p>
                    </div>`,
                icon: 'info',
                confirmButtonColor: '#1e88e5'
            });
        }
    </script>
</body>
</html>