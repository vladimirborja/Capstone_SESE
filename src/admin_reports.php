<?php
session_start();
include 'db_config.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Statistics Fetching
$active_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
$inactive_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 0")->fetchColumn();
$found_pets = $pdo->query("SELECT COUNT(*) FROM pets WHERE category = 'Found'")->fetchColumn();
$lost_pets = $pdo->query("SELECT COUNT(*) FROM pets WHERE category = 'Lost'")->fetchColumn();
$report_count = $pdo->query("SELECT COUNT(*) FROM post_reports")->fetchColumn();
$message_stat_count = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

// 2. FETCH REPORTS WITH CORRECT JOINS
$sql_reports = "SELECT 
                    pr.report_id, 
                    pr.report_type, 
                    pr.description, 
                    pr.post_id,
                    p.content AS post_content,
                    p.image_url, 
                    u1.full_name AS reporter_name,
                    u2.full_name AS reported_user_name 
                FROM post_reports pr 
                LEFT JOIN users u1 ON pr.user_id = u1.user_id 
                LEFT JOIN posts p ON pr.post_id = p.post_id 
                LEFT JOIN users u2 ON p.user_id = u2.user_id 
                ORDER BY pr.created_at DESC";
$recent_reports = $pdo->query($sql_reports)->fetchAll(PDO::FETCH_ASSOC);

// 3. Messages Fetching
$recent_messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);

// map logic
$stmt = $pdo->query("SELECT name, description, address, latitude, longitude, type FROM establishments WHERE status = 'active'");
$establishments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body { background-color: #cbd5e0; font-family: sans-serif; margin: 0; }
        .navbar-custom { background-color: #1e88e5; height: 50px; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; color: white; }
        .logo-admin { height: 30px; width: 30px; display: flex; align-items: center; }
        .logo-admin img { height: 100%; width: 100%; object-fit: contain;  }
        
        /* Dropdown Styling */
        .profile-dropdown .btn-profile { color: white; background: none; border: none; padding: 0; font-size: 1.5rem; transition: 0.3s; }
        .profile-dropdown .btn-profile:after { display: none; } 
        .dropdown-menu-end { border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: none; margin-top: 12px; min-width: 200px; padding: 10px 0; }
        .dropdown-header { font-weight: 700; color: #2d3748; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
        .dropdown-item { font-size: 0.9rem; font-weight: 500; color: #4a5568; padding: 10px 20px; display: flex; align-items: center; gap: 10px; }
        .dropdown-item i { width: 18px; text-align: center; }
        .dropdown-item:hover { background-color: #f7fafc; color: #1e88e5; }
        .dropdown-item.text-danger:hover { background-color: #fff5f5; color: #e53e3e !important; }

        .white-box-container { border: 6px solid white; border-radius: 30px; padding: 25px; display: flex; gap: 25px; align-items: stretch; margin: 20px; height: 600px; background-color: #cbd5e0; }
        .reports-panel { flex: 0 0 40%; display: flex; flex-direction: column; overflow: hidden; }
        .reports-scroll-area { overflow-y: auto; flex-grow: 1; padding-right: 10px; }
        .report-item { background-color: #edf2f7; border-radius: 12px; padding: 12px 18px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .stats-grid { flex: 1; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(2, 1fr); gap: 20px; }
        .stat-card { background-color: #cbd5e0; border: 4px solid white; border-radius: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
        .stat-card span { font-size: 0.8rem; font-weight: bold; color: #4a5568; }
        .stat-card h2 { font-size: 3.5rem; font-weight: bold; margin: 0; color: #1a202c; }
        .btn-eye { color: #3182ce; border: none; background: none; font-size: 1.4rem; cursor: pointer; transition: 0.2s; }
        .btn-eye:hover { transform: scale(1.1); }
        .btn-trash { background-color: #f56565; color: white; border: none; border-radius: 8px; padding: 6px 12px; cursor: pointer; }
        .messages-container { background-color: #d1d9e6; border: 6px solid white; border-radius: 30px; padding: 25px; margin: 20px; }
        .message-row { display: grid; grid-template-columns: 1.2fr 1.5fr 2.5fr 50px; gap: 15px; align-items: center; margin-bottom: 12px; }
        .msg-box { background: #f8fafc; border-radius: 10px; padding: 10px 15px; font-size: 0.9rem; color: #4a5568; height: 45px; display: flex; align-items: center; overflow: hidden; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>

<div class="navbar-custom">
    <div class="logo-admin">
        <a href="../src/manage_users.php">
            <img src="../src/images/homeImages/Sese-Logo3.png" alt="Logo" />
        </a>
    </div>
    <div class="fw-bold">ADMIN DASHBOARD</div>
    
    <div class="dropdown profile-dropdown">
        <button class="btn btn-profile dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user-circle"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow">
            <li><h6 class="dropdown-header">Admin Account</h6></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="../src/mains/main.php"><i class="fas fa-home"></i> Main Feed</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</div>

<div class="container-fluid">
    <div class="white-box-container shadow-sm">
        <div class="reports-panel">
            <h4 class="fw-bold mb-4 text-dark">REPORTS <span class="badge bg-primary rounded-pill"><?php echo count($recent_reports); ?></span></h4>
            <div class="reports-scroll-area">
                <?php foreach ($recent_reports as $report): ?>
                <div class="report-item">
                    <div class="d-flex align-items-center overflow-hidden">
                        <i class="fas fa-exclamation-triangle text-warning me-3"></i>
                        <span class="text-truncate fw-bold text-secondary" style="font-size: 0.85rem;">
                            <?php echo htmlspecialchars($report['report_type'] . ": " . $report['description']); ?>
                        </span>
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn-eye" onclick='viewReport(<?php echo json_encode($report); ?>)'><i class="fas fa-eye"></i></button>
                        <button class="btn-trash" onclick="deletePost(<?php echo $report['post_id']; ?>)"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><span>ACTIVE USERS</span><h2><?php echo $active_users; ?></h2></div>
            <div class="stat-card"><span>INACTIVE USERS</span><h2><?php echo $inactive_users; ?></h2></div>
            <div class="stat-card"><span>MESSAGES</span><h2><?php echo $message_stat_count; ?></h2></div>
            <div class="stat-card"><span>FOUND PETS</span><h2><?php echo $found_pets; ?></h2></div>
            <div class="stat-card"><span>LOST PETS</span><h2><?php echo $lost_pets; ?></h2></div>
            <div class="stat-card"><span>REPORTS</span><h2><?php echo $report_count; ?></h2></div>
        </div>
    </div>

    <div class="messages-container shadow-sm">
        <h5 class="fw-bold mb-4"><i class="fas fa-envelope me-2"></i> RECENT MESSAGES</h5>
        <?php foreach ($recent_messages as $msg): ?>
        <div class="message-row">
            <div class="msg-box"><?php echo htmlspecialchars($msg['name'] ?? 'Guest'); ?></div>
            <div class="msg-box"><?php echo htmlspecialchars($msg['email']); ?></div>
            <div class="msg-box text-truncate"><?php echo htmlspecialchars($msg['message']); ?></div>
            <button class="btn-trash py-2" onclick="deleteMessage(<?php echo $msg['id']; ?>)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once './features/pet_establishments.php'; ?>

<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0">
                <h5 class="fw-bold">Moderation View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small mb-1">REASON: <span id="modalReason" class="text-danger fw-bold"></span></p>
                <p class="small mb-3">REPORTER: <span id="modalReporter" class="text-primary fw-bold"></span></p>
                
                <div class="p-3 border rounded bg-light">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-user-circle fs-4 me-2 text-primary"></i>
                        <span id="modalReportedUser" class="fw-bold text-dark"></span>
                    </div>
                    <p id="modalPetName" class="small text-secondary mb-2"></p>
                    <div class="text-center">
                        <img id="modalPostImg" src="" class="img-fluid rounded mt-2 shadow-sm" 
                             style="max-height: 250px; width: 100%; object-fit: contain; display:none;">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-danger w-100 fw-bold py-2" id="confirmDeleteBtn">DELETE POST & NOTIFY OWNER</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Convert PHP array to JS object
    const establishmentData = <?php echo json_encode($establishments); ?>;
</script>
<script>
    const API_BASE_URL = "./features/handle_establishments.php";
    const AUTO_INIT_MAP = true;
    const USER_ROLE = <?php echo json_encode($_SESSION['role']); ?>;
</script>
<script src="script/map_init.js"></script>
<script>
    // SweetAlert Logout Logic
    function confirmLogout() {
        Swal.fire({
            title: 'Ready to leave?',
            text: "You will need to login again to access the admin area.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#718096',
            confirmButtonText: 'Logout Now'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        })
    }

    function viewReport(data) {
        document.getElementById('modalReason').innerText = data.report_type;
        document.getElementById('modalReporter').innerText = data.reporter_name || 'Anonymous';
        document.getElementById('modalReportedUser').innerText = "Reported User: " + (data.reported_user_name || 'Not Available');
        document.getElementById('modalPetName').innerText = "Post Content: " + (data.post_content || 'N/A');
        
        const img = document.getElementById('modalPostImg');
        if (data.image_url && data.image_url.trim() !== "") {
            img.src = '../src/' + data.image_url; 
            img.style.display = 'block';
            img.onerror = function() { this.style.display = 'none'; };
        } else {
            img.style.display = 'none';
        }

        document.getElementById('confirmDeleteBtn').onclick = function() { deletePost(data.post_id); };
        new bootstrap.Modal(document.getElementById('reportModal')).show();
    }

    function deletePost(id) {
        if(!id) return;
        Swal.fire({
            title: 'Are you sure?',
            text: "This will remove the reported pet post permanently.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('delete_post_id', id);
                fetch('handle_reports.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                    else Swal.fire('Error', data.error, 'error');
                });
            }
        });
    }

    function deleteMessage(id) {
        if(!id) return;
        Swal.fire({
            title: 'Archive Message?',
            text: "This message will be moved to history.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1e88e5',
            confirmButtonText: 'Yes, Archive'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('delete_msg_id', id);
                fetch('handle_messages.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                    else Swal.fire('Error', data.error, 'error');
                })
                .catch(err => Swal.fire('Error', 'Connection failed', 'error'));
            }
        });
    }
</script>
</body>
</html>