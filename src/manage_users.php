<?php
require_once 'check_session.php';
require_once 'db_config.php';

// Only admins can access
requireRole(['admin']);

$user = getLoggedInUser();

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
        body { background: #f8f9fa; }
        .admin-header { background: linear-gradient(135deg, #1e88ff, #0d6efd); color: #fff; padding: 20px 0; margin-bottom: 30px; }
        .badge-active { background: #28a745; width: 75px; display: inline-block; }
        .badge-inactive { background: #dc3545; width: 75px; display: inline-block; } /* Standardized width for alignment */
        .card { border-radius: 15px; }
        .table thead { background-color: #f1f5f9; }
        /* Fixes the alignment of the badge and sync button */
        .status-container { display: flex; align-items: center; gap: 8px; justify-content: start; }
    </style>
</head>

<body>
    <div class="admin-header shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0 fw-bold">User Management</h2>
                <p class="mb-0 opacity-75">Manage user roles and permissions</p>
            </div>
            <div>
                <span class="me-3">Welcome, <strong><?= htmlspecialchars($user['name']); ?></strong></span>
                <a href="admin_reports.php" class="btn btn-light btn-sm me-2 fw-bold">Dashboard</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm fw-bold">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-secondary">All Registered Users</h5>
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
                                                
                                                <span class="badge <?= $u['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
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
                                        <button class="btn btn-sm btn-info text-white" 
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
                confirmButtonColor: '#1e88ff'
            });
        <?php endif; ?>

        function confirmRoleChange(selectElement, userName) {
            const newRole = selectElement.value;
            Swal.fire({
                title: 'Change User Role?',
                text: `Are you sure you want to change ${userName} to ${newRole.toUpperCase()}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1e88ff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, change it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    selectElement.form.submit();
                } else {
                    location.reload(); 
                }
            });
        }

        function confirmStatusToggle(buttonElement, userName) {
            Swal.fire({
                title: 'Update Status?',
                text: `Do you want to toggle the active status for ${userName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1e88ff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, update'
            }).then((result) => {
                if (result.isConfirmed) {
                    buttonElement.closest('form').submit();
                }
            });
        }

        function viewUser(id, name, email) {
            Swal.fire({
                title: 'User Information',
                html: `
                    <div class="text-start mt-3">
                        <p><strong>User ID:</strong> ${id}</p>
                        <p><strong>Full Name:</strong> ${name}</p>
                        <p><strong>Email Address:</strong> ${email}</p>
                    </div>
                `,
                icon: 'info',
                confirmButtonColor: '#1e88ff'
            });
        }
    </script>
</body>
</html>