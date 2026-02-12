<?php
require_once 'check_session.php';
require_once 'db_config.php';

// Only admins can access
requireRole(['admin']);

$user = getLoggedInUser();

// Handle role update
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
    <style>
        body {
            background: #f8f9fa;
        }

        .admin-header {
            background: linear-gradient(135deg, #1e88ff, #0d6efd);
            color: #fff;
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .badge-admin {
            background: #dc3545;
        }

        .badge-user {
            background: #198754;
        }

        .badge-active {
            background: #0d6efd;
        }

        .badge-inactive {
            background: #6c757d;
        }
    </style>
</head>

<body>
    <div class="admin-header">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">User Management</h2>
                <p class="mb-0">Manage user roles and permissions</p>
            </div>
            <div>
                <span class="me-3">Welcome, <?= htmlspecialchars($user['name']); ?></span>
                <a href="admin_reports.php" class="btn btn-light btn-sm me-2">Dashboard</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $successMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="row mb-4 text-center">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Total Users</h5>
                        <h2 class="text-primary"><?= count($users); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Admins</h5>
                        <h2 class="text-danger"><?= count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Users</h5>
                        <h2 class="text-success"><?= count(array_filter($users, fn($u) => $u['role'] === 'user')); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Active</h5>
                        <h2 class="text-info"><?= count(array_filter($users, fn($u) => $u['is_active'])); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Users</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= $u['user_id']; ?></td>
                                    <td><?= htmlspecialchars($u['full_name']); ?></td>
                                    <td><?= htmlspecialchars($u['email']); ?></td>
                                    <td><?= htmlspecialchars($u['phone_number']); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $u['user_id']; ?>">
                                            <select name="role" class="form-select form-select-sm w-auto d-inline"
                                                onchange="if(confirm('Change role for <?= htmlspecialchars($u['full_name']); ?>?')) this.form.submit();">
                                                <option value="user" <?= $u['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <input type="hidden" name="update_role" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $u['user_id']; ?>">
                                            <input type="hidden" name="is_active" value="<?= $u['is_active'] ? 0 : 1; ?>">
                                            <span class="badge <?= $u['is_active'] ? 'badge-active' : 'badge-inactive'; ?>"><?= $u['is_active'] ? 'Active' : 'Inactive'; ?></span>
                                            <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-secondary ms-1"
                                                onclick="return confirm('Toggle status for <?= htmlspecialchars($u['full_name']); ?>?');">
                                                Toggle
                                            </button>
                                        </form>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($u['created_at'])); ?></td>
                                    <td><?= $u['last_login'] ? date('M d, Y H:i', strtotime($u['last_login'])) : 'Never'; ?></td>
                                    <td><button class="btn btn-sm btn-info" onclick="alert('View user ID: <?= $u['user_id']; ?>');">View</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>