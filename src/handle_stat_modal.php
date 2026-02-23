<?php
// Suppress ALL PHP warnings/notices from breaking the JSON output
error_reporting(0);
ini_set('display_errors', 0);

// Buffer output so stray warnings don't corrupt JSON
ob_start();

session_start();

// Always respond with JSON
header('Content-Type: application/json');

// ── Auth guard ──────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode(['error' => 'Unauthorized. Please log in again.']);
    exit;
}

// ── DB connection ────────────────────────────────────────────────────────────
// NOTE: adjust this path if your db_config.php is in a different folder
$configPath = __DIR__ . '/db_config.php';
if (!file_exists($configPath)) {
    ob_end_clean();
    echo json_encode(['error' => 'db_config.php not found at: ' . $configPath]);
    exit;
}

include $configPath;

if (!isset($pdo)) {
    ob_end_clean();
    echo json_encode(['error' => 'Database connection failed — $pdo not set in db_config.php']);
    exit;
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ── Query switch ─────────────────────────────────────────────────────────────
$type = $_GET['type'] ?? '';

try {
    switch ($type) {

        // TABLE: users
        // Columns used: full_name, username, email, role, is_active, created_at
        case 'active_users':
            $rows = $pdo->query("
                SELECT full_name, username, email, role, created_at
                FROM users
                WHERE is_active = 1
                ORDER BY full_name ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'inactive_users':
            $rows = $pdo->query("
                SELECT full_name, username, email, role, created_at
                FROM users
                WHERE is_active = 0
                ORDER BY full_name ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
            break;

        // TABLE: contact_messages
        // Columns: id, name, email, contact, subject, message, created_at
        case 'messages':
            $rows = $pdo->query("
                SELECT name, email, contact, subject, message, created_at
                FROM contact_messages
                ORDER BY created_at DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            break;

        // TABLE: pets
        // Columns: pet_id, user_id, pet_name, category, gender, breed, color,
        //          last_seen_location, description, image_url, contact_number, created_at
        case 'found_pets':
            $rows = $pdo->query("
                SELECT
                    p.pet_name,
                    p.breed,
                    p.gender,
                    p.color,
                    p.last_seen_location,
                    p.contact_number,
                    p.category,
                    p.created_at,
                    u.full_name
                FROM pets p
                LEFT JOIN users u ON p.user_id = u.user_id
                WHERE p.category = 'Found'
                ORDER BY p.created_at DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'lost_pets':
            $rows = $pdo->query("
                SELECT
                    p.pet_name,
                    p.breed,
                    p.gender,
                    p.color,
                    p.last_seen_location,
                    p.contact_number,
                    p.category,
                    p.created_at,
                    u.full_name
                FROM pets p
                LEFT JOIN users u ON p.user_id = u.user_id
                WHERE p.category = 'Lost'
                ORDER BY p.created_at DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            break;

        // TABLE: post_reports
        // Columns: report_id, post_id, user_id, report_type, description, created_at
        // JOINs  : users (reporter), posts, users (reported user via post owner)
        case 'reports':
            $rows = $pdo->query("
                SELECT
                    pr.report_type,
                    pr.description,
                    pr.created_at,
                    u1.full_name AS reporter_name,
                    u2.full_name AS reported_user_name
                FROM post_reports pr
                LEFT JOIN users u1 ON pr.user_id = u1.user_id
                LEFT JOIN posts  p  ON pr.post_id  = p.post_id
                LEFT JOIN users u2 ON p.user_id   = u2.user_id
                ORDER BY pr.created_at DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            break;

        default:
            ob_end_clean();
            echo json_encode(['error' => 'Invalid type: ' . htmlspecialchars($type)]);
            exit;
    }

    // Discard any stray output (PHP notices etc.) before sending JSON
    ob_end_clean();
    echo json_encode(['rows' => $rows]);

} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>