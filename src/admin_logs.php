<?php
session_start();
require_once 'check_session.php';
requireRole(['admin', 'super_admin']);
require_once 'db_config.php';
$currentRole = $_SESSION['role'] ?? 'admin';

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$admin = $_GET['admin'] ?? '';
$action = $_GET['action'] ?? '';

$where = [];
$params = [];

if ($from !== '') {
    $where[] = "DATE(created_at) >= :from_date";
    $params['from_date'] = $from;
}
if ($to !== '') {
    $where[] = "DATE(created_at) <= :to_date";
    $params['to_date'] = $to;
}
if ($admin !== '') {
    $where[] = "admin_id = :admin_id";
    $params['admin_id'] = $admin;
}
if ($action !== '') {
    $where[] = "action_type = :action_type";
    $params['action_type'] = $action;
}

$sql = "SELECT * FROM admin_logs";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY created_at DESC LIMIT 500";

$logs = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $logs = [];
}

$admins = [];
try {
    $admins = $pdo->query("SELECT DISTINCT admin_id, admin_name FROM admin_logs ORDER BY admin_name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $admins = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .super-admin-badge {
            background: #4a00e0;
            color: #ffffff;
        }
        .admin-badge {
            background: #1565c0;
            color: #ffffff;
        }
        @media (max-width: 1024px) {
            .container.py-4 { padding-left: 10px; padding-right: 10px; }
            .d-flex.justify-content-between.align-items-center.mb-3 {
                flex-wrap: wrap;
                gap: 10px;
            }
            .d-flex.justify-content-between.align-items-center.mb-3 .btn {
                min-height: 44px;
            }
        }
        @media (max-width: 768px) {
            .row.g-2.mb-3 > [class*="col-"] { width: 100%; }
            .row.g-2.mb-3 .form-control,
            .row.g-2.mb-3 .form-select,
            .row.g-2.mb-3 .btn {
                min-height: 44px;
            }
            .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .table { font-size: 0.85rem; }
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <h1 class="h4 mb-0">Admin Logs</h1>
            <?php if ($currentRole === 'super_admin'): ?>
                <span class="role-badge super-admin-badge">Super Admin</span>
            <?php else: ?>
                <span class="role-badge admin-badge">Admin</span>
            <?php endif; ?>
        </div>
        <a href="admin_reports.php" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-2"><input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>" class="form-control"></div>
        <div class="col-md-2"><input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>" class="form-control"></div>
        <div class="col-md-3">
            <select name="admin" class="form-select">
                <option value="">All Admins</option>
                <?php foreach ($admins as $a): ?>
                    <option value="<?php echo (int)$a['admin_id']; ?>" <?php echo ($admin == $a['admin_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($a['admin_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><input type="text" name="action" value="<?php echo htmlspecialchars($action); ?>" class="form-control" placeholder="Action type"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>

    <div class="table-responsive bg-white rounded-3 border">
        <table class="table table-sm table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Admin</th>
                    <th>Affected User</th>
                    <th>Action</th>
                    <th>Old Status</th>
                    <th>New Status</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="7" class="text-center py-3">No logs found.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($log['admin_name']); ?></td>
                            <td><?php echo htmlspecialchars($log['affected_user_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($log['action_type']); ?></td>
                            <td><?php echo htmlspecialchars($log['old_status'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($log['new_status'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($log['reason'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
