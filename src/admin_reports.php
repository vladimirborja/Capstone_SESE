<?php
session_start();
include 'db_config.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch admin profile image from DB
$adminStmt = $pdo->prepare("SELECT full_name, profile_image FROM users WHERE user_id = ?");
$adminStmt->execute([$_SESSION['user_id']]);
$adminData = $adminStmt->fetch();
$adminName = $adminData['full_name'] ?? 'Admin';
$adminProfileImage = (!empty($adminData['profile_image']))
    ? str_replace('../', '', $adminData['profile_image'])
    : 'images/homeImages/profile icon.png';

$isAdminUser = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_pet_action'])) {
    header('Content-Type: application/json');
    try {
        if (!$isAdminUser) {
            throw new Exception('Unauthorized action.');
        }

        $incomingPostId = $_POST['post_id'] ?? null;
        $incomingPetId = $_POST['pet_id'] ?? null;
        $petId = (int)($incomingPetId !== null ? $incomingPetId : $incomingPostId);
        $decision = trim((string)($_POST['decision'] ?? ''));
        $reason = trim((string)($_POST['reason'] ?? ''));
        if ($petId <= 0 || !in_array($decision, ['approve', 'reject'], true)) {
            throw new Exception('Invalid verification request: missing or invalid post ID.');
        }
        if ($decision === 'reject' && $reason === '') {
            throw new Exception('Rejection reason is required.');
        }

        $petStmt = $pdo->prepare("SELECT p.pet_id, p.pet_name, p.user_id, p.category, p.requested_category,
                                         COALESCE(u.full_name, 'User') AS owner_name
                                  FROM pets p
                                  LEFT JOIN users u ON u.user_id = p.user_id
                                  WHERE p.pet_id = ?");
        $petStmt->execute([$petId]);
        $pet = $petStmt->fetch(PDO::FETCH_ASSOC);
        if (!$pet) {
            throw new Exception('Pet post not found.');
        }
        if (strtolower((string)$pet['category']) !== 'waiting_approval') {
            throw new Exception('This post is no longer pending approval.');
        }

        $requested = strtolower(trim((string)($pet['requested_category'] ?? '')));
        $liveCategory = in_array($requested, ['lost', 'found', 'for_adoption'], true) ? $requested : 'lost';

        $pdo->beginTransaction();
        if ($decision === 'approve') {
            $update = $pdo->prepare("UPDATE pets
                                     SET category = ?, verification_status = 'approved', verification_reason = NULL
                                     WHERE pet_id = ?");
            $update->execute([$liveCategory, $petId]);

            $message = 'Your lost & found post "' . ($pet['pet_name'] ?? 'Pet') . '" has been approved and is now live.';
            $notify = $pdo->prepare("INSERT INTO adoption_notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
            $notify->execute([$pet['user_id'], $message]);

            $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, admin_name, action_type, old_status, new_status, reason, affected_user_id, affected_user_name, created_at)
                                  VALUES (?, ?, 'pet_post_approval', 'waiting_approval', ?, ?, ?, ?, NOW())");
            $log->execute([
                $_SESSION['user_id'],
                $adminName,
                $liveCategory,
                'Approved pet post: ' . ($pet['pet_name'] ?? 'N/A') . ' (' . strtoupper($liveCategory) . ')',
                $pet['user_id'],
                $pet['owner_name']
            ]);
            $rec = $pdo->prepare("INSERT INTO lost_found_review_records
                                    (pet_id, pet_name, post_type, submitted_by_user_id, submitted_by_name, status, admin_id, admin_name, rejection_reason, submitted_at, actioned_at)
                                  VALUES
                                    (?, ?, ?, ?, ?, 'approved', ?, ?, NULL, (SELECT created_at FROM pets WHERE pet_id = ?), NOW())
                                  ON DUPLICATE KEY UPDATE
                                    pet_name = VALUES(pet_name),
                                    post_type = VALUES(post_type),
                                    submitted_by_user_id = VALUES(submitted_by_user_id),
                                    submitted_by_name = VALUES(submitted_by_name),
                                    status = 'approved',
                                    admin_id = VALUES(admin_id),
                                    admin_name = VALUES(admin_name),
                                    rejection_reason = NULL,
                                    actioned_at = NOW()");
            $rec->execute([
                $petId,
                $pet['pet_name'] ?? 'N/A',
                $liveCategory,
                $pet['user_id'],
                $pet['owner_name'],
                $_SESSION['user_id'],
                $adminName,
                $petId
            ]);
        } else {
            $update = $pdo->prepare("UPDATE pets
                                     SET category = 'rejected', verification_status = 'rejected', verification_reason = ?
                                     WHERE pet_id = ?");
            $update->execute([$reason, $petId]);

            $message = 'Your lost & found post "' . ($pet['pet_name'] ?? 'Pet') . '" was rejected. Reason: ' . $reason;
            $notify = $pdo->prepare("INSERT INTO adoption_notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
            $notify->execute([$pet['user_id'], $message]);

            $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, admin_name, action_type, old_status, new_status, reason, affected_user_id, affected_user_name, created_at)
                                  VALUES (?, ?, 'pet_post_rejection', 'waiting_approval', 'rejected', ?, ?, ?, NOW())");
            $log->execute([
                $_SESSION['user_id'],
                $adminName,
                'Rejected pet post: ' . ($pet['pet_name'] ?? 'N/A') . '. Reason: ' . $reason,
                $pet['user_id'],
                $pet['owner_name']
            ]);
            $rec = $pdo->prepare("INSERT INTO lost_found_review_records
                                    (pet_id, pet_name, post_type, submitted_by_user_id, submitted_by_name, status, admin_id, admin_name, rejection_reason, submitted_at, actioned_at)
                                  VALUES
                                    (?, ?, ?, ?, ?, 'rejected', ?, ?, ?, (SELECT created_at FROM pets WHERE pet_id = ?), NOW())
                                  ON DUPLICATE KEY UPDATE
                                    pet_name = VALUES(pet_name),
                                    post_type = VALUES(post_type),
                                    submitted_by_user_id = VALUES(submitted_by_user_id),
                                    submitted_by_name = VALUES(submitted_by_name),
                                    status = 'rejected',
                                    admin_id = VALUES(admin_id),
                                    admin_name = VALUES(admin_name),
                                    rejection_reason = VALUES(rejection_reason),
                                    actioned_at = NOW()");
            $rec->execute([
                $petId,
                $pet['pet_name'] ?? 'N/A',
                $liveCategory,
                $pet['user_id'],
                $pet['owner_name'],
                $_SESSION['user_id'],
                $adminName,
                $reason,
                $petId
            ]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// 1. Statistics
$active_users       = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
$inactive_users     = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 0")->fetchColumn();
$found_pets         = $pdo->query("SELECT COUNT(*) FROM pets WHERE category = 'Found'")->fetchColumn();
$lost_pets          = $pdo->query("SELECT COUNT(*) FROM pets WHERE category = 'Lost'")->fetchColumn();
$report_count       = $pdo->query("SELECT COUNT(*) FROM post_reports")->fetchColumn();
$message_stat_count = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

// 2. Reports
$sql_reports = "SELECT pr.report_id, pr.report_type, pr.description, pr.post_id, pr.created_at,
                    p.content AS post_content, p.image_url,
                    u1.full_name AS reporter_name, u2.full_name AS reported_user_name
                FROM post_reports pr
                LEFT JOIN users u1 ON pr.user_id = u1.user_id
                LEFT JOIN posts p  ON pr.post_id  = p.post_id
                LEFT JOIN users u2 ON p.user_id   = u2.user_id
                ORDER BY pr.created_at DESC";
$recent_reports = $pdo->query($sql_reports)->fetchAll(PDO::FETCH_ASSOC);

// 3. Distinct years for filter
$report_years = $pdo->query(
    "SELECT DISTINCT YEAR(created_at) AS yr FROM post_reports ORDER BY yr DESC"
)->fetchAll(PDO::FETCH_COLUMN);

// 4. Recent Messages (dashboard display)
$recent_messages = $pdo->query(
    "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 7"
)->fetchAll(PDO::FETCH_ASSOC);

// 5. Map / Establishments
$establishments = $pdo->query(
    "SELECT name, description, address, latitude, longitude, type, barangay FROM establishments WHERE status IN ('approved','active')"
)->fetchAll(PDO::FETCH_ASSOC);

$establishment_records = [];
try {
    $establishment_records = $pdo->query(
        "SELECT er.establishment_name AS name,
                er.category AS type,
                er.barangay,
                er.submitted_by_name AS submitted_by,
                er.status,
                er.admin_name AS actioned_by,
                er.submitted_at,
                er.actioned_at
         FROM establishment_records er
         WHERE er.status IN ('approved','rejected')
         ORDER BY er.actioned_at DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    if (empty($establishment_records)) {
        $establishment_records = $pdo->query(
            "SELECT e.id,
                    e.name,
                    e.type,
                    e.barangay,
                    COALESCE(u.full_name, 'N/A') AS submitted_by,
                    CASE WHEN e.status = 'active' THEN 'approved' ELSE e.status END AS status,
                    'N/A' AS actioned_by,
                    e.created_at AS submitted_at,
                    e.created_at AS actioned_at
             FROM establishments e
             LEFT JOIN users u ON u.user_id = e.requester_id
             WHERE e.status IN ('approved','rejected','active')
             ORDER BY e.created_at DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $establishment_records = [];
}

$adoption_activity = [];
try {
    $adoption_activity = $pdo->query(
        "SELECT p.pet_name, COALESCE(pr.adopter_name, a.full_name, 'N/A') AS adopter_name, o.full_name AS owner_name, pr.status, pr.created_at
         FROM pet_responses pr
         JOIN pets p ON p.pet_id = pr.pet_id
         LEFT JOIN users o ON o.user_id = pr.owner_user_id
         LEFT JOIN users a ON a.user_id = pr.responder_user_id
         ORDER BY pr.created_at DESC
         LIMIT 100"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $adoption_activity = [];
}

$pending_pet_reviews = [];
$reviewed_pet_posts = [];
try {
    $pending_pet_reviews = $pdo->query(
        "SELECT p.pet_id, p.pet_name, p.category, p.requested_category, p.pet_type, p.breed, p.size, p.last_seen_barangay,
                p.description, p.contact_number, p.reward_offered, p.reward_details, p.image_url, p.owner_with_pet_image_url,
                p.verification_status, p.verification_reason, p.created_at,
                COALESCE(u.full_name, 'N/A') AS owner_name, COALESCE(u.username, 'N/A') AS owner_username, COALESCE(u.email, 'N/A') AS owner_email
         FROM pets p
         LEFT JOIN users u ON u.user_id = p.user_id
         WHERE LOWER(p.category) = 'waiting_approval'
         ORDER BY p.created_at DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    $reviewed_pet_posts = $pdo->query(
        "SELECT r.pet_id, r.pet_name, r.post_type, r.submitted_by_name AS owner_name,
                r.status AS review_status, r.admin_name, r.rejection_reason, r.submitted_at, r.actioned_at,
                p.image_url, p.owner_with_pet_image_url
         FROM lost_found_review_records r
         LEFT JOIN pets p ON p.pet_id = r.pet_id
         WHERE r.status IN ('approved','rejected')
         ORDER BY r.actioned_at DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $pending_pet_reviews = [];
    $reviewed_pet_posts = [];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="icon" type="image/png" href="favicon.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body { background-color: #cbd5e0; font-family: sans-serif; margin: 0; }

        /* ── Navbar ── */
        .navbar-custom {
            background-color: #1e88e5; height: 55px; display: flex;
            align-items: center; justify-content: space-between;
            padding: 0 20px; color: white; position: relative;
        }
        .nav-center-title {
            position: absolute; left: 50%; transform: translateX(-50%);
            font-weight: bold; font-size: 1.25rem; pointer-events: none; white-space: nowrap;
        }
        .logo-admin { height: 35px; width: 35px; display: flex; align-items: center; z-index: 1; }
        .logo-admin img { height: 100%; width: 100%; object-fit: contain; }
        .nav-right-group { display: flex; align-items: center; gap: 10px; z-index: 1; }
        .admin-avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid white; cursor: pointer; }
        .profile-dropdown .btn-profile { background: none; border: none; padding: 0; transition: 0.3s; display: flex; align-items: center; }
        .profile-dropdown .btn-profile:after { display: none; }
        .dropdown-menu-end { border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: none; margin-top: 12px; min-width: 220px; padding: 10px 0; }
        .dropdown-item { font-size: 0.9rem; font-weight: 500; color: #4a5568; padding: 10px 20px; display: flex; align-items: center; gap: 10px; }
        .dropdown-item i { width: 18px; text-align: center; }
        .dropdown-item:hover { background-color: #f7fafc; color: #1e88e5; }
        .dropdown-item.text-danger:hover { background-color: #fff5f5; color: #e53e3e !important; }

        /* ── Layout ── */
        .white-box-container { border: 6px solid white; border-radius: 30px; padding: 25px; display: flex; gap: 25px; align-items: stretch; margin: 20px; height: 600px; background-color: #cbd5e0; }
        .reports-panel { flex: 0 0 40%; display: flex; flex-direction: column; overflow: hidden; }
        .reports-scroll-area { overflow-y: auto; flex-grow: 1; padding-right: 10px; }
        .report-item { background-color: #edf2f7; border-radius: 12px; padding: 12px 18px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: opacity 0.2s; }

        /* ── Reports Header Row ── */
        .reports-header-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .reports-header-row h4 { margin: 0; }

        /* ── Export Buttons ── */
        .export-btn-group { display: flex; gap: 6px; }
        .btn-export {
            display: flex; align-items: center; gap: 5px;
            padding: 5px 12px; border: none; border-radius: 8px;
            font-size: 0.75rem; font-weight: 700; cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            text-transform: uppercase; letter-spacing: 0.04em;
        }
        .btn-export:active { transform: scale(0.96); }
        .btn-export-print { background-color: #1e88e5; color: white; }
        .btn-export-print:hover { background-color: #1565c0; }
        .btn-export-csv   { background-color: #38a169; color: white; }
        .btn-export-csv:hover   { background-color: #276749; }

        /* ── Filter Bar ── */
        .filter-bar { display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; align-items: center; }
        .filter-bar select {
            background-color: #edf2f7; border: 2px solid white; border-radius: 8px;
            font-size: 0.78rem; font-weight: 600; color: #4a5568;
            cursor: pointer; padding: 5px 10px; outline: none; transition: border-color 0.2s;
        }
        .filter-bar select:focus { border-color: #1e88e5; }
        .filter-bar .btn-filter { background-color: #1e88e5; color: white; border: none; border-radius: 8px; padding: 5px 14px; font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .filter-bar .btn-filter:hover { background-color: #1565c0; }
        .filter-bar .btn-clear { background-color: #edf2f7; color: #4a5568; border: 2px solid white; border-radius: 8px; padding: 5px 12px; font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .filter-bar .btn-clear:hover { background-color: #e2e8f0; }
        .filter-count { font-size: 0.75rem; color: #718096; font-weight: 600; margin-left: 2px; }
        .no-results-msg { text-align: center; padding: 30px 0; color: #a0aec0; font-size: 0.85rem; display: none; }
        .no-results-msg i { display: block; font-size: 2rem; margin-bottom: 8px; }

        /* ── Stats Grid ── */
        .stats-grid { flex: 1; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(2, 1fr); gap: 20px; }
        .stat-card { background-color: #cbd5e0; border: 4px solid white; border-radius: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
        .stat-card span { font-size: 0.8rem; font-weight: bold; color: #4a5568; }
        .stat-card h2 { font-size: 3.5rem; font-weight: bold; margin: 0; color: #1a202c; }
        .stat-card-clickable { cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease; }
        .stat-card-clickable:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(30,136,229,0.18); border-color: #1e88e5 !important; }
        .stat-card-clickable:active { transform: translateY(-1px); }

        /* ── Buttons ── */
        .btn-eye { color: #3182ce; border: none; background: none; font-size: 1.4rem; cursor: pointer; transition: 0.2s; }
        .btn-eye:hover { transform: scale(1.1); }
        .btn-trash { background-color: #f56565; color: white; border: none; border-radius: 8px; padding: 6px 12px; cursor: pointer; }

        /* ── Messages ── */
        .messages-container { background-color: #d1d9e6; border: 6px solid white; border-radius: 30px; padding: 25px; margin: 20px; }
        .message-row { display: grid; grid-template-columns: 1.2fr 1.5fr 2.5fr 50px; gap: 15px; align-items: center; margin-bottom: 12px; }
        .msg-box { background: #f8fafc; border-radius: 10px; padding: 10px 15px; font-size: 0.9rem; color: #4a5568; height: 45px; display: flex; align-items: center; overflow: hidden; border: 1px solid #e2e8f0; }
        .pet-photo-pair { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; min-width: 280px; }
        .pet-photo-box { border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px; background: #fff; }
        .pet-photo-box .photo-title { font-size: 0.75rem; font-weight: 700; color: #4a5568; margin-bottom: 6px; }
        .pet-photo-box img { width: 100%; height: 150px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }
        .pet-photo-placeholder { display: flex; align-items: center; justify-content: center; height: 150px; border-radius: 8px; border: 1px dashed #cbd5e1; color: #64748b; font-size: 0.78rem; text-align: center; padding: 8px; background: #f8fafc; }

        /* ── Stat Modal ── */
        #statModal .modal-content  { border-radius: 20px; border: none; overflow: hidden; }
        #statModal .modal-header   { background: linear-gradient(135deg, #1e88e5, #1565c0); color: white; border: none; padding: 18px 24px; }
        #statModal .modal-title    { font-size: 1rem; font-weight: 700; letter-spacing: 0.03em; }
        #statModal .btn-close      { filter: invert(1) opacity(0.8); }
        #statModal .modal-body     { padding: 24px; background: #f7fafc; }
        #statModal .stat-table     { border-collapse: separate; border-spacing: 0; width: 100%; }
        #statModal .stat-table thead th { font-size: 0.72rem; text-transform: uppercase; color: #718096; font-weight: 700; background: #edf2f7; padding: 10px 14px; border-bottom: 2px solid #e2e8f0; letter-spacing: 0.05em; }
        #statModal .stat-table thead th:first-child { border-radius: 10px 0 0 0; }
        #statModal .stat-table thead th:last-child  { border-radius: 0 10px 0 0; }
        #statModal .stat-table tbody td { font-size: 0.85rem; color: #2d3748; padding: 10px 14px; border-bottom: 1px solid #e2e8f0; background: white; vertical-align: middle; }
        #statModal .stat-table tbody tr:last-child td { border-bottom: none; }
        #statModal .stat-table tbody tr:hover td { background: #ebf8ff; }
        #statModal .stat-badge { display: inline-block; padding: 3px 11px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.03em; }
        #statModal .badge-active   { background: #c6f6d5; color: #276749; }
        #statModal .badge-inactive { background: #fed7d7; color: #9b2c2c; }
        #statModal .badge-found    { background: #bee3f8; color: #2b6cb0; }
        #statModal .badge-lost     { background: #feebc8; color: #7b341e; }
        #statModal .badge-report   { background: #fed7d7; color: #9b2c2c; }
        #statModal .stat-modal-loader { text-align: center; padding: 50px 0; color: #a0aec0; }
        #statModal .stat-modal-loader i { display: block; margin-bottom: 10px; }
        #statModal .stat-empty { text-align: center; padding: 50px 0; color: #a0aec0; }
        #statModal .stat-summary-bar { background: white; border-radius: 12px; padding: 12px 20px; margin-bottom: 16px; font-size: 0.82rem; color: #4a5568; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px; }
        #statModal .stat-summary-bar strong { color: #1e88e5; }

        /* ── Print Selection Modal ── */
        #printSelectModal .modal-content { border-radius: 20px; border: none; overflow: hidden; }
        #printSelectModal .modal-header  { background: linear-gradient(135deg, #1e88e5, #1565c0); color: white; border: none; padding: 18px 24px; }
        #printSelectModal .modal-title   { font-size: 1rem; font-weight: 700; }
        #printSelectModal .btn-close     { filter: invert(1) opacity(0.8); }
        #printSelectModal .modal-body    { padding: 24px; background: #f7fafc; }

        .print-option-card {
            background: white; border: 2px solid #e2e8f0; border-radius: 14px;
            padding: 16px 18px; cursor: pointer; transition: all 0.18s ease;
            display: flex; align-items: center; gap: 14px; user-select: none;
        }
        .print-option-card:hover { border-color: #1e88e5; background: #ebf8ff; }
        .print-option-card.selected { border-color: #1e88e5; background: #ebf8ff; box-shadow: 0 0 0 3px rgba(30,136,229,0.15); }
        .print-option-card .option-icon {
            width: 42px; height: 42px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: white; flex-shrink: 0;
        }
        .print-option-card .option-text { flex: 1; }
        .print-option-card .option-text strong { display: block; font-size: 0.88rem; color: #2d3748; }
        .print-option-card .option-text span   { font-size: 0.75rem; color: #718096; }
        .print-option-card .option-check {
            width: 20px; height: 20px; border-radius: 50%; border: 2px solid #cbd5e0;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            transition: all 0.18s;
        }
        .print-option-card.selected .option-check { background: #1e88e5; border-color: #1e88e5; }
        .print-option-card.selected .option-check::after { content: '✓'; color: white; font-size: 0.7rem; font-weight: bold; }

        .select-all-row { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; padding-bottom: 14px; border-bottom: 2px solid #e2e8f0; }
        .select-all-row input[type="checkbox"] { width: 17px; height: 17px; cursor: pointer; accent-color: #1e88e5; }
        .select-all-row label { font-size: 0.82rem; font-weight: 700; color: #4a5568; cursor: pointer; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; }

        .print-format-row { display: flex; gap: 10px; margin-top: 16px; padding-top: 16px; border-top: 2px solid #e2e8f0; }
        .btn-do-print { flex: 1; background: #1e88e5; color: white; border: none; border-radius: 10px; padding: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .btn-do-print:hover { background: #1565c0; }
        .btn-do-csv   { flex: 1; background: #38a169; color: white; border: none; border-radius: 10px; padding: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .btn-do-csv:hover { background: #276749; }

        /* ── Print Styles ── */
        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { position: absolute; left: 0; top: 0; width: 100%; padding: 20px; }
            .print-section { margin-bottom: 36px; page-break-inside: avoid; }
            .print-section-title { font-size: 14pt; font-weight: bold; color: #1e88e5; margin: 0 0 6px; }
            .print-meta { font-size: 9pt; color: #718096; margin: 0 0 10px; }
            .print-table { width: 100%; border-collapse: collapse; font-size: 9pt; }
            .print-table th { background: #1e88e5 !important; color: white !important; padding: 7px 9px; text-align: left; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-table td { padding: 6px 9px; border-bottom: 1px solid #e2e8f0; }
            .print-table tr:nth-child(even) td { background: #f7fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-main-header { margin-bottom: 24px; border-bottom: 2px solid #1e88e5; padding-bottom: 12px; }
            .print-main-header h1 { font-size: 18pt; color: #1e88e5; margin: 0 0 4px; }
            .print-main-header p  { font-size: 10pt; color: #718096; margin: 0; }
        }
    </style>
</head>
<body>

<!-- Hidden Print Area -->
<div id="printArea" style="display:none;"></div>

<!-- ═══════════ NAVBAR ═══════════ -->
<div class="navbar-custom">
    <div class="logo-admin">
        <a href="../src/manage_users.php">
            <img src="../src/images/homeImages/Sese-Logo3.png" alt="Logo" />
        </a>
    </div>
    <div class="nav-center-title">ADMIN DASHBOARD</div>
    <div class="nav-right-group">
        <a href="manage_users.php" class="btn btn-light btn-sm fw-bold me-2">
            <i class="fas fa-users-cog me-1"></i> User Management
        </a>
        <a href="admin_logs.php" class="btn btn-light btn-sm fw-bold me-2">
            <i class="fas fa-clipboard-list me-1"></i> Admin Logs
        </a>
        <div class="dropdown profile-dropdown">
            <button class="btn btn-profile dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= htmlspecialchars($adminProfileImage); ?>" alt="Admin" class="admin-avatar">
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li>
                    <div class="d-flex align-items-center gap-2 px-3 py-2">
                        <img src="<?= htmlspecialchars($adminProfileImage); ?>" class="rounded-circle" width="40" height="40"
                             style="object-fit:cover; border: 2px solid #1e88e5;">
                        <div>
                            <div class="fw-bold text-dark small"><?= htmlspecialchars($adminName); ?></div>
                            <div class="text-muted" style="font-size:0.75rem;">Administrator</div>
                        </div>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../src/mains/main.php"><i class="fas fa-home"></i> Main Feed</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- ═══════════ MAIN CONTENT ═══════════ -->
<div class="container-fluid">
    <div class="white-box-container shadow-sm">

        <!-- ── Reports Panel ── -->
        <div class="reports-panel">
            <div class="reports-header-row">
                <h4 class="fw-bold text-dark">
                    REPORTS
                    <span class="badge bg-primary rounded-pill" id="reportBadge"><?php echo count($recent_reports); ?></span>
                </h4>
                <div class="export-btn-group">
                    <button class="btn-export btn-export-print" onclick="openPrintModal()" title="Print / Export data">
                        <i class="fas fa-print"></i> Print / Export
                    </button>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <select id="filterMonth">
                    <option value="">All Months</option>
                    <option value="01">January</option><option value="02">February</option>
                    <option value="03">March</option><option value="04">April</option>
                    <option value="05">May</option><option value="06">June</option>
                    <option value="07">July</option><option value="08">August</option>
                    <option value="09">September</option><option value="10">October</option>
                    <option value="11">November</option><option value="12">December</option>
                </select>
                <select id="filterYear">
                    <option value="">All Years</option>
                    <?php foreach ($report_years as $year): ?>
                        <option value="<?= $year ?>"><?= $year ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn-filter" onclick="applyReportFilter()">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <button class="btn-clear" onclick="clearReportFilter()">
                    <i class="fas fa-times me-1"></i> Clear
                </button>
                <span class="filter-count" id="filterCount"></span>
            </div>

            <!-- Report List -->
            <div class="reports-scroll-area">
                <div class="no-results-msg" id="noResultsMsg">
                    <i class="fas fa-search"></i>
                    No reports found for the selected filter.
                </div>
                <?php foreach ($recent_reports as $report):
                    $reportDate = date('Y-m', strtotime($report['created_at']));
                ?>
                <div class="report-item"
                     data-date="<?= $reportDate ?>"
                     data-type="<?= htmlspecialchars($report['report_type']) ?>"
                     data-desc="<?= htmlspecialchars($report['description'] ?? '') ?>"
                     data-reporter="<?= htmlspecialchars($report['reporter_name'] ?? 'Anonymous') ?>"
                     data-reported="<?= htmlspecialchars($report['reported_user_name'] ?? 'N/A') ?>"
                     data-created="<?= htmlspecialchars(date('M d, Y', strtotime($report['created_at']))) ?>">
                    <div class="d-flex align-items-center overflow-hidden">
                        <i class="fas fa-exclamation-triangle text-warning me-3"></i>
                        <div class="overflow-hidden">
                            <span class="text-truncate fw-bold text-secondary d-block" style="font-size: 0.85rem;">
                                <?php echo htmlspecialchars($report['report_type'] . ': ' . $report['description']); ?>
                            </span>
                            <span class="text-muted" style="font-size: 0.72rem;">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo date('M d, Y', strtotime($report['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1 ms-2 flex-shrink-0">
                        <button class="btn-eye" onclick='viewReport(<?php echo json_encode($report); ?>)'>
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-trash" onclick="deletePost(<?php echo $report['post_id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── Stats Grid ── -->
        <div class="stats-grid">
            <div class="stat-card stat-card-clickable" onclick="openStatModal('active_users')">
                <span>ACTIVE USERS</span><h2><?php echo $active_users; ?></h2>
            </div>
            <div class="stat-card stat-card-clickable" onclick="openStatModal('inactive_users')">
                <span>INACTIVE USERS</span><h2><?php echo $inactive_users; ?></h2>
            </div>
            <div class="stat-card stat-card-clickable" onclick="openStatModal('messages')">
                <span>MESSAGES</span><h2><?php echo $message_stat_count; ?></h2>
            </div>
            <div class="stat-card stat-card-clickable" onclick="openStatModal('found_pets')">
                <span>FOUND PETS</span><h2><?php echo $found_pets; ?></h2>
            </div>
            <div class="stat-card stat-card-clickable" onclick="openStatModal('lost_pets')">
                <span>LOST PETS</span><h2><?php echo $lost_pets; ?></h2>
            </div>
            <div class="stat-card stat-card-clickable" onclick="openStatModal('reports')">
                <span>REPORTS</span><h2><?php echo $report_count; ?></h2>
            </div>
        </div>
    </div>

    <!-- ── Recent Messages ── -->
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

    <?php if ($isAdminUser): ?>
    <div class="messages-container shadow-sm">
        <h5 class="fw-bold mb-3"><i class="fas fa-user-check me-2"></i> PENDING LOST &amp; FOUND POSTS</h5>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Pet</th>
                        <th>Post Type</th>
                        <th>Submitted By</th>
                        <th>Details</th>
                        <th>Photos</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pending_pet_reviews)): ?>
                        <?php foreach ($pending_pet_reviews as $pet): ?>
                            <tr>
                                <td><?= htmlspecialchars($pet['pet_name'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-info text-uppercase">
                                        <?= htmlspecialchars(str_replace('_', ' ', (string)($pet['requested_category'] ?? 'lost'))) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($pet['owner_name'] ?? 'N/A') ?></div>
                                    <div class="small text-muted">@<?= htmlspecialchars($pet['owner_username'] ?? 'N/A') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($pet['owner_email'] ?? 'N/A') ?></div>
                                </td>
                                <td class="small">
                                    <div><strong>Type:</strong> <?= htmlspecialchars($pet['pet_type'] ?? 'N/A') ?></div>
                                    <div><strong>Breed:</strong> <?= htmlspecialchars($pet['breed'] ?? 'N/A') ?></div>
                                    <div><strong>Size:</strong> <?= htmlspecialchars($pet['size'] ?? 'N/A') ?></div>
                                    <div><strong>Last Seen Barangay:</strong> <?= htmlspecialchars($pet['last_seen_barangay'] ?? 'N/A') ?></div>
                                    <div><strong>Contact:</strong> <?= htmlspecialchars($pet['contact_number'] ?? 'N/A') ?></div>
                                    <div><strong>Description:</strong> <?= htmlspecialchars($pet['description'] ?? 'N/A') ?></div>
                                    <?php if (!empty($pet['reward_offered']) || !empty($pet['reward_details'])): ?>
                                        <div><strong>Reward:</strong> <?= htmlspecialchars($pet['reward_details'] ?? 'With Reward') ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="pet-photo-pair">
                                        <div class="pet-photo-box">
                                            <div class="photo-title">Pet Photo</div>
                                            <?php if (!empty($pet['image_url'])): ?>
                                                <img src="<?= htmlspecialchars($pet['image_url']) ?>" alt="Pet Photo">
                                            <?php else: ?>
                                                <div class="pet-photo-placeholder">No pet photo uploaded.</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="pet-photo-box">
                                            <div class="photo-title">Verification Photo (Admin Only)</div>
                                            <?php if (!empty($pet['owner_with_pet_image_url'])): ?>
                                                <img src="<?= htmlspecialchars($pet['owner_with_pet_image_url']) ?>" alt="Verification Photo">
                                            <?php else: ?>
                                                <div class="pet-photo-placeholder">No verification photo uploaded.</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= !empty($pet['created_at']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($pet['created_at']))) : 'N/A' ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-success btn-sm" onclick="verifyPetPost(<?= (int)$pet['pet_id'] ?>, 'approve')">Approve</button>
                                        <button class="btn btn-danger btn-sm" onclick="verifyPetPost(<?= (int)$pet['pet_id'] ?>, 'reject')">Reject</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No pending pet posts for approval.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="messages-container shadow-sm">
        <h5 class="fw-bold mb-3"><i class="fas fa-history me-2"></i> LOST &amp; FOUND REVIEW RECORDS</h5>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Pet Name</th>
                        <th>Post Type</th>
                        <th>Submitted By</th>
                        <th>Status</th>
                        <th>Admin Who Actioned</th>
                        <th>Date Submitted</th>
                        <th>Date Actioned</th>
                        <th>Rejection Reason</th>
                        <th>Photos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reviewed_pet_posts)): ?>
                        <?php foreach ($reviewed_pet_posts as $pet): ?>
                            <?php $isApproved = strtolower((string)$pet['review_status']) === 'approved'; ?>
                            <tr>
                                <td><?= htmlspecialchars($pet['pet_name'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-info text-uppercase">
                                        <?= htmlspecialchars(str_replace('_', ' ', (string)($pet['post_type'] ?? 'N/A'))) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($pet['owner_name'] ?? 'N/A') ?></td>
                                <td><span class="badge <?= $isApproved ? 'bg-success' : 'bg-danger' ?> text-uppercase"><?= htmlspecialchars($pet['review_status'] ?? 'N/A') ?></span></td>
                                <td><?= htmlspecialchars($pet['admin_name'] ?? 'N/A') ?></td>
                                <td><?= !empty($pet['submitted_at']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($pet['submitted_at']))) : 'N/A' ?></td>
                                <td><?= !empty($pet['actioned_at']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($pet['actioned_at']))) : 'N/A' ?></td>
                                <td><?= htmlspecialchars($pet['rejection_reason'] ?? 'N/A') ?></td>
                                <td>
                                    <div class="pet-photo-pair">
                                        <div class="pet-photo-box">
                                            <div class="photo-title">Pet Photo</div>
                                            <?php if (!empty($pet['image_url'])): ?>
                                                <img src="<?= htmlspecialchars($pet['image_url']) ?>" alt="Pet Photo">
                                            <?php else: ?>
                                                <div class="pet-photo-placeholder">No pet photo uploaded.</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="pet-photo-box">
                                            <div class="photo-title">Verification Photo (Admin Only)</div>
                                            <?php if (!empty($pet['owner_with_pet_image_url'])): ?>
                                                <img src="<?= htmlspecialchars($pet['owner_with_pet_image_url']) ?>" alt="Verification Photo">
                                            <?php else: ?>
                                                <div class="pet-photo-placeholder">No verification photo uploaded.</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center">No reviewed pet posts yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once './features/pet_establishments.php'; ?>

<!-- ═══════════════════════════════════════
     PRINT SELECTION MODAL
═══════════════════════════════════════ -->
<div class="modal fade" id="printSelectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-print me-2"></i> Print / Export Data
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Select the data you want to include in your print or CSV export.</p>

                <!-- Select All -->
                <div class="select-all-row">
                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                    <label for="selectAll">Select All</label>
                </div>

                <!-- Options Grid -->
                <div class="d-flex flex-column gap-2" id="printOptionsContainer">

                    <div class="print-option-card" data-key="active_users" onclick="toggleOption(this)">
                        <div class="option-icon" style="background:#38a169;"><i class="fas fa-user-check"></i></div>
                        <div class="option-text">
                            <strong>Active Users</strong>
                            <span>List of all currently active user accounts</span>
                        </div>
                        <div class="option-check"></div>
                    </div>

                    <div class="print-option-card" data-key="inactive_users" onclick="toggleOption(this)">
                        <div class="option-icon" style="background:#e53e3e;"><i class="fas fa-user-slash"></i></div>
                        <div class="option-text">
                            <strong>Inactive Users</strong>
                            <span>List of all inactive / deactivated accounts</span>
                        </div>
                        <div class="option-check"></div>
                    </div>

                    <div class="print-option-card" data-key="messages" onclick="toggleOption(this)">
                        <div class="option-icon" style="background:#3182ce;"><i class="fas fa-envelope"></i></div>
                        <div class="option-text">
                            <strong>Messages</strong>
                            <span>All contact messages submitted by users</span>
                        </div>
                        <div class="option-check"></div>
                    </div>

                    <div class="print-option-card" data-key="found_pets" onclick="toggleOption(this)">
                        <div class="option-icon" style="background:#1e88e5;"><i class="fas fa-paw"></i></div>
                        <div class="option-text">
                            <strong>Found Pets</strong>
                            <span>All pets posted under the Found category</span>
                        </div>
                        <div class="option-check"></div>
                    </div>

                    <div class="print-option-card" data-key="lost_pets" onclick="toggleOption(this)">
                        <div class="option-icon" style="background:#dd6b20;"><i class="fas fa-search"></i></div>
                        <div class="option-text">
                            <strong>Lost Pets</strong>
                            <span>All pets posted under the Lost category</span>
                        </div>
                        <div class="option-check"></div>
                    </div>

                    <div class="print-option-card" data-key="reports" onclick="toggleOption(this)">
                        <div class="option-icon" style="background:#9b2c2c;"><i class="fas fa-flag"></i></div>
                        <div class="option-text">
                            <strong>Reports</strong>
                            <span>All post reports submitted by users</span>
                        </div>
                        <div class="option-check"></div>
                    </div>

                </div>

                <!-- Action Buttons -->
                <div class="print-format-row">
                    <button class="btn-do-print" onclick="executePrint()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="btn-do-csv" onclick="executeCSV()">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     REPORT DETAIL MODAL
═══════════════════════════════════════ -->
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
                <button class="btn btn-danger w-100 fw-bold py-2" id="confirmDeleteBtn">DELETE POST</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     STAT DETAIL MODAL
═══════════════════════════════════════ -->
<div class="modal fade" id="statModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="statModalTitle">
                    <i id="statModalIcon" class="fas fa-chart-bar me-2"></i>
                    <span id="statModalTitleText"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="statModalBody">
                <div class="stat-modal-loader">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="script/barangay-coords.js"></script>
<script src="script/map-utils.js"></script>
<script src="script/barangay-dropdown.js"></script>
<script>
    const establishmentData = <?php echo json_encode($establishments); ?>;
    const API_BASE_URL      = "./features/handle_establishments.php";
    const AUTO_INIT_MAP     = true;
    const USER_ROLE         = <?php echo json_encode($_SESSION['role']); ?>;
</script>
<script src="script/map_init.js"></script>
<script>

/* ═══════════════════════════════════════════
   EXISTING FUNCTIONS
═══════════════════════════════════════════ */
function confirmLogout() {
    Swal.fire({
        title: 'Ready to leave?', text: "You will need to login again to access the admin area.",
        icon: 'question', showCancelButton: true,
        confirmButtonColor: '#e53e3e', cancelButtonColor: '#718096', confirmButtonText: 'Logout Now'
    }).then(r => { if (r.isConfirmed) window.location.href = 'logout.php'; });
}

function viewReport(data) {
    document.getElementById('modalReason').innerText       = data.report_type;
    document.getElementById('modalReporter').innerText     = data.reporter_name || 'Anonymous';
    document.getElementById('modalReportedUser').innerText = 'Reported User: ' + (data.reported_user_name || 'Not Available');
    document.getElementById('modalPetName').innerText      = 'Post Content: ' + (data.post_content || 'N/A');
    const img = document.getElementById('modalPostImg');
    if (data.image_url && data.image_url.trim() !== '') {
        img.src = '../src/' + data.image_url; img.style.display = 'block';
        img.onerror = function () { this.style.display = 'none'; };
    } else { img.style.display = 'none'; }
    document.getElementById('confirmDeleteBtn').onclick = function () { deletePost(data.post_id); };
    new bootstrap.Modal(document.getElementById('reportModal')).show();
}

function deletePost(id) {
    if (!id) return;
    Swal.fire({ title: 'Are you sure?', text: "This will remove the reported pet post permanently.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, Delete' })
    .then(r => {
        if (r.isConfirmed) {
            const fd = new FormData(); fd.append('delete_post_id', id);
            fetch('handle_reports.php', { method: 'POST', body: fd })
                .then(res => res.json()).then(d => { if (d.success) location.reload(); else Swal.fire('Error', d.error, 'error'); });
        }
    });
}

function deleteMessage(id) {
    if (!id) return;
    Swal.fire({ title: 'Archive Message?', text: "This message will be moved to history.", icon: 'question', showCancelButton: true, confirmButtonColor: '#1e88e5', confirmButtonText: 'Yes, Archive' })
    .then(r => {
        if (r.isConfirmed) {
            const fd = new FormData(); fd.append('delete_msg_id', id);
            fetch('handle_messages.php', { method: 'POST', body: fd })
                .then(res => res.json()).then(d => { if (d.success) location.reload(); else Swal.fire('Error', d.error, 'error'); })
                .catch(() => Swal.fire('Error', 'Connection failed', 'error'));
        }
    });
}

function verifyPetPost(petId, decision) {
    const submit = (reason = '') => {
        const fd = new FormData();
        fd.append('verify_pet_action', '1');
        fd.append('pet_id', String(petId));
        fd.append('post_id', String(petId));
        fd.append('decision', decision);
        if (reason) fd.append('reason', reason);

        fetch('admin_reports.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Updated', 'Pet verification status updated.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Unable to update verification.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Connection failed.', 'error'));
    };

    if (decision === 'reject') {
        Swal.fire({
            title: 'Reject Pet Verification',
            input: 'textarea',
            inputLabel: 'Reason (required)',
            inputPlaceholder: 'State the reason for rejection...',
            showCancelButton: true,
            preConfirm: (value) => {
                if (!value || !value.trim()) {
                    Swal.showValidationMessage('Rejection reason is required.');
                    return false;
                }
                return value.trim();
            }
        }).then((result) => {
            if (result.isConfirmed) submit(result.value);
        });
    } else {
        submit('');
    }
}

/* ═══════════════════════════════════════════
   REPORT FILTER
═══════════════════════════════════════════ */
function applyReportFilter() {
    const month = document.getElementById('filterMonth').value;
    const year  = document.getElementById('filterYear').value;
    const items = document.querySelectorAll('.report-item');
    let visible = 0;
    items.forEach(item => {
        const parts = item.getAttribute('data-date').split('-');
        const ok    = (!year || parts[0] === year) && (!month || parts[1] === month);
        item.style.display = ok ? '' : 'none';
        if (ok) visible++;
    });
    document.getElementById('noResultsMsg').style.display = visible === 0 ? 'block' : 'none';
    if (month || year) {
        document.getElementById('filterCount').textContent = `${visible} of ${items.length} shown`;
        document.getElementById('reportBadge').textContent = visible;
    } else {
        document.getElementById('filterCount').textContent = '';
        document.getElementById('reportBadge').textContent = items.length;
    }
}

function clearReportFilter() {
    document.getElementById('filterMonth').value = '';
    document.getElementById('filterYear').value  = '';
    document.querySelectorAll('.report-item').forEach(i => i.style.display = '');
    document.getElementById('noResultsMsg').style.display = 'none';
    document.getElementById('filterCount').textContent    = '';
    document.getElementById('reportBadge').textContent    = document.querySelectorAll('.report-item').length;
}

document.getElementById('filterMonth').addEventListener('change', applyReportFilter);
document.getElementById('filterYear').addEventListener('change',  applyReportFilter);

/* ═══════════════════════════════════════════
   PRINT SELECTION MODAL
═══════════════════════════════════════════ */
let printModalInstance = null;

function openPrintModal() {
    if (!printModalInstance) {
        printModalInstance = new bootstrap.Modal(document.getElementById('printSelectModal'));
    }
    // Reset selections
    document.querySelectorAll('.print-option-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('selectAll').checked = false;
    printModalInstance.show();
}

function toggleOption(card) {
    card.classList.toggle('selected');
    // Update select-all checkbox state
    const all   = document.querySelectorAll('.print-option-card');
    const sel   = document.querySelectorAll('.print-option-card.selected');
    document.getElementById('selectAll').checked = all.length === sel.length;
}

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.print-option-card').forEach(card => {
        if (checkbox.checked) card.classList.add('selected');
        else                  card.classList.remove('selected');
    });
}

function getSelectedKeys() {
    const keys = [];
    document.querySelectorAll('.print-option-card.selected').forEach(card => {
        keys.push(card.getAttribute('data-key'));
    });
    return keys;
}

/* ─── Fetch data for a given key from handle_stat_modal.php ─── */
async function fetchDataForKey(key) {
    const res = await fetch('handle_stat_modal.php?type=' + encodeURIComponent(key));
    const ct  = res.headers.get('content-type') || '';
    if (!ct.includes('application/json')) throw new Error('Server error for key: ' + key);
    const json = await res.json();
    if (json.error) throw new Error(json.error);
    return json.rows || [];
}

/* ─── Table builders per data type ─── */
const TABLE_CONFIG = {
    active_users:   { title: 'Active Users',   headers: ['#','Full Name','Username','Email','Role','Joined'],
        row: (r,i) => [i+1, r.full_name, r.username||'—', r.email, r.role||'user', r.created_at||'—'] },
    inactive_users: { title: 'Inactive Users', headers: ['#','Full Name','Username','Email','Role','Joined'],
        row: (r,i) => [i+1, r.full_name, r.username||'—', r.email, r.role||'user', r.created_at||'—'] },
    messages:       { title: 'Messages',       headers: ['#','Name','Email','Contact','Subject','Message','Date'],
        row: (r,i) => [i+1, r.name||'Guest', r.email, r.contact||'—', r.subject||'—', r.message, r.created_at||'—'] },
    found_pets:     { title: 'Found Pets',     headers: ['#','Pet Name','Breed','Gender','Color','Last Seen','Contact','Posted By','Date'],
        row: (r,i) => [i+1, r.pet_name||'Unknown', r.breed||'—', r.gender||'—', r.color||'—', r.last_seen_location||'—', r.contact_number||'—', r.full_name||'N/A', r.created_at||'—'] },
    lost_pets:      { title: 'Lost Pets',      headers: ['#','Pet Name','Breed','Gender','Color','Last Seen','Contact','Posted By','Date'],
        row: (r,i) => [i+1, r.pet_name||'Unknown', r.breed||'—', r.gender||'—', r.color||'—', r.last_seen_location||'—', r.contact_number||'—', r.full_name||'N/A', r.created_at||'—'] },
    reports:        { title: 'Reports',        headers: ['#','Report Type','Reported By','Reported User','Description','Date'],
        row: (r,i) => [i+1, r.report_type, r.reporter_name||'Anonymous', r.reported_user_name||'N/A', r.description||'—', r.created_at||'—'] },
};

function escCell(val) {
    return String(val ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function buildPrintSection(key, rows) {
    const cfg = TABLE_CONFIG[key];
    const headerCells = cfg.headers.map(h => `<th>${h}</th>`).join('');
    const bodyRows = rows.map((r, i) => {
        const cells = cfg.row(r, i).map(v => `<td>${escCell(v)}</td>`).join('');
        return `<tr>${cells}</tr>`;
    }).join('');
    return `
        <div class="print-section">
            <div class="print-section-title">${cfg.title}</div>
            <div class="print-meta">Total records: ${rows.length}</div>
            <table class="print-table">
                <thead><tr>${headerCells}</tr></thead>
                <tbody>${bodyRows}</tbody>
            </table>
        </div>`;
}

/* ─── PRINT ─── */
async function executePrint() {
    const keys = getSelectedKeys();
    if (keys.length === 0) {
        Swal.fire('Nothing Selected', 'Please select at least one data type to print.', 'warning');
        return;
    }

    // Show loading state
    const btns = document.querySelectorAll('.btn-do-print, .btn-do-csv');
    btns.forEach(b => { b.disabled = true; b.style.opacity = '0.6'; });

    try {
        const sections = await Promise.all(keys.map(async key => {
            const rows = await fetchDataForKey(key);
            return buildPrintSection(key, rows);
        }));

        const todayStr = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const printArea = document.getElementById('printArea');
        printArea.innerHTML = `
            <div class="print-main-header">
                <h1>Admin Dashboard — Data Export</h1>
                <p>Printed by: <strong><?= htmlspecialchars($adminName) ?></strong> &nbsp;|&nbsp; Date: <strong>${todayStr}</strong></p>
            </div>
            ${sections.join('')}`;

        printArea.style.display = 'block';

        // Close modal first, then print
        printModalInstance.hide();
        setTimeout(() => {
            window.print();
            printArea.style.display = 'none';
        }, 400);

    } catch (err) {
        Swal.fire('Error', 'Failed to fetch data: ' + err.message, 'error');
    } finally {
        btns.forEach(b => { b.disabled = false; b.style.opacity = '1'; });
    }
}

/* ─── CSV EXPORT ─── */
async function executeCSV() {
    const keys = getSelectedKeys();
    if (keys.length === 0) {
        Swal.fire('Nothing Selected', 'Please select at least one data type to export.', 'warning');
        return;
    }

    const btns = document.querySelectorAll('.btn-do-print, .btn-do-csv');
    btns.forEach(b => { b.disabled = true; b.style.opacity = '0.6'; });

    try {
        let csvContent = '';

        for (const key of keys) {
            const cfg  = TABLE_CONFIG[key];
            const rows = await fetchDataForKey(key);

            // Section title row
            csvContent += `"=== ${cfg.title} ==="\n`;
            csvContent += cfg.headers.map(h => `"${h}"`).join(',') + '\n';
            rows.forEach((r, i) => {
                const cells = cfg.row(r, i).map(v => `"${String(v ?? '').replace(/"/g, '""')}"`);
                csvContent += cells.join(',') + '\n';
            });
            csvContent += '\n'; // blank line between sections
        }

        const blob     = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url      = URL.createObjectURL(blob);
        const filename = 'admin_export_' + new Date().toISOString().slice(0,10) + '.csv';

        const link = document.createElement('a');
        link.href = url; link.download = filename;
        document.body.appendChild(link); link.click();
        document.body.removeChild(link); URL.revokeObjectURL(url);

        printModalInstance.hide();

    } catch (err) {
        Swal.fire('Error', 'Failed to export data: ' + err.message, 'error');
    } finally {
        btns.forEach(b => { b.disabled = false; b.style.opacity = '1'; });
    }
}

/* ═══════════════════════════════════════════
   STAT MODAL FUNCTIONS
═══════════════════════════════════════════ */
const STAT_META = {
    active_users:   { text: 'Active Users',    icon: 'fa-user-check' },
    inactive_users: { text: 'Inactive Users',   icon: 'fa-user-slash' },
    messages:       { text: 'Contact Messages', icon: 'fa-envelope'   },
    found_pets:     { text: 'Found Pets',       icon: 'fa-paw'        },
    lost_pets:      { text: 'Lost Pets',        icon: 'fa-search'     },
    reports:        { text: 'Reports',          icon: 'fa-flag'       },
};

let statModalInstance = null;

function openStatModal(type) {
    const meta = STAT_META[type];
    if (!meta) return;
    document.getElementById('statModalTitleText').innerText = meta.text;
    document.getElementById('statModalIcon').className = 'fas ' + meta.icon + ' me-2';
    document.getElementById('statModalBody').innerHTML = `
        <div class="stat-modal-loader">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2 mb-0">Loading data...</p>
        </div>`;
    if (!statModalInstance) statModalInstance = new bootstrap.Modal(document.getElementById('statModal'));
    statModalInstance.show();
    fetch('handle_stat_modal.php?type=' + encodeURIComponent(type))
        .then(res => {
            const ct = res.headers.get('content-type') || '';
            if (!ct.includes('application/json')) return res.text().then(t => { throw new Error('PHP error: ' + t.substring(0, 300)); });
            return res.json();
        })
        .then(data => { if (data.error) { showStatError(data.error); return; } renderStatModal(type, data.rows, meta.text); })
        .catch(err => showStatError(err.message));
}

function showStatError(msg) {
    document.getElementById('statModalBody').innerHTML = `
        <div class="stat-empty">
            <i class="fas fa-exclamation-circle fa-2x mb-3 text-danger"></i>
            <p class="text-danger mb-0">${msg}</p>
        </div>`;
}

function renderStatModal(type, rows, title) {
    const body = document.getElementById('statModalBody');
    if (!rows || rows.length === 0) {
        body.innerHTML = `<div class="stat-empty"><i class="fas fa-inbox fa-2x mb-3"></i><p class="mb-0">No records found.</p></div>`;
        return;
    }
    let html = `
        <div class="stat-summary-bar">
            <i class="fas ${STAT_META[type].icon} text-primary"></i>
            Showing <strong>${rows.length}</strong> record${rows.length !== 1 ? 's' : ''} for <strong>${title}</strong>
        </div>
        <div class="table-responsive"><table class="table stat-table mb-0">`;

    if (type === 'active_users' || type === 'inactive_users') {
        const cls = type === 'active_users' ? 'badge-active' : 'badge-inactive';
        const lbl = type === 'active_users' ? 'Active' : 'Inactive';
        html += `<thead><tr><th>#</th><th>Full Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead><tbody>`;
        rows.forEach((r,i) => { html += `<tr><td class="text-muted">${i+1}</td><td><i class="fas fa-user-circle text-primary me-1"></i>${esc(r.full_name)}</td><td>${esc(r.username??'—')}</td><td>${esc(r.email)}</td><td>${esc(r.role??'user')}</td><td><span class="stat-badge ${cls}">${lbl}</span></td><td class="text-muted">${esc(r.created_at??'')}</td></tr>`; });
    } else if (type === 'messages') {
        html += `<thead><tr><th>#</th><th>Name</th><th>Email</th><th>Contact</th><th>Subject</th><th>Message</th><th>Date</th></tr></thead><tbody>`;
        rows.forEach((r,i) => { html += `<tr><td class="text-muted">${i+1}</td><td>${esc(r.name??'Guest')}</td><td>${esc(r.email)}</td><td>${esc(r.contact??'—')}</td><td>${esc(r.subject??'—')}</td><td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(r.message)}</td><td class="text-muted">${esc(r.created_at??'')}</td></tr>`; });
    } else if (type === 'found_pets' || type === 'lost_pets') {
        html += `<thead><tr><th>#</th><th>Pet Name</th><th>Breed</th><th>Gender</th><th>Color</th><th>Last Seen</th><th>Contact</th><th>Posted By</th><th>Category</th><th>Date</th></tr></thead><tbody>`;
        rows.forEach((r,i) => { const bc = r.category==='Found'?'badge-found':'badge-lost'; html += `<tr><td class="text-muted">${i+1}</td><td><i class="fas fa-paw text-warning me-1"></i>${esc(r.pet_name??'Unknown')}</td><td>${esc(r.breed??'—')}</td><td>${esc(r.gender??'—')}</td><td>${esc(r.color??'—')}</td><td>${esc(r.last_seen_location??'—')}</td><td>${esc(r.contact_number??'—')}</td><td>${esc(r.full_name??'N/A')}</td><td><span class="stat-badge ${bc}">${esc(r.category)}</span></td><td class="text-muted">${esc(r.created_at??'')}</td></tr>`; });
    } else if (type === 'reports') {
        html += `<thead><tr><th>#</th><th>Type</th><th>Reporter</th><th>Reported User</th><th>Description</th><th>Date</th></tr></thead><tbody>`;
        rows.forEach((r,i) => { html += `<tr><td class="text-muted">${i+1}</td><td><span class="stat-badge badge-report">${esc(r.report_type)}</span></td><td>${esc(r.reporter_name??'Anonymous')}</td><td>${esc(r.reported_user_name??'N/A')}</td><td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(r.description??'—')}</td><td class="text-muted">${esc(r.created_at??'')}</td></tr>`; });
    }

    html += `</tbody></table></div>`;
    body.innerHTML = html;
}

function esc(str) {
    if (str == null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

</script>
</body>
</html>