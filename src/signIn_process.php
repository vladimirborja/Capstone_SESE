<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
session_start();

// Set JSON header early
header('Content-Type: application/json');

// Check if db_config.php exists
if (!file_exists('db_config.php')) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Database configuration file not found'
    ]);
    exit;
}

require_once 'db_config.php';

// Check if database connection exists
if (!isset($pdo) || $pdo === null) {
    ob_clean();
    $errorMsg = isset($dbError) ? $dbError : 'Database connection failed';
    echo json_encode([
        'success' => false,
        'message' => 'Cannot connect to database. Error: ' . $errorMsg
    ]);
    exit;
}

try {
    // Get the posted data
    $input = json_decode(file_get_contents('php://input'), true);

    $emailOrPhone = trim($input['emailOrPhone'] ?? '');
    $password = $input['password'] ?? '';
    $remember = $input['remember'] ?? false;

    // Check required fields
    if (empty($emailOrPhone) || empty($password)) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Email/Phone and password are required'
        ]);
        exit;
    }

    // Check if it's email or phone
    $isEmail = filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL);

    // Note: Ensure column names match your DB ('phone_number' vs 'phone')
    if ($isEmail) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 AND is_verified = 1");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number = ? AND is_active = 1 AND is_verified = 1");
    }

    $stmt->execute([$emailOrPhone]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // Update last login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $updateStmt->execute([$user['user_id']]);

        // Log successful login
        $logStmt = $pdo->prepare("
            INSERT INTO login_history (user_id, ip_address, user_agent, login_status) 
            VALUES (?, ?, ?, 'success')
        ");
        $logStmt->execute([
            $user['user_id'],
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Login successful!',
            'user' => [
                'id' => $user['user_id'],
                'name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } else {
        // Check if user exists but is not verified
        $checkStatus = $pdo->prepare("SELECT is_verified FROM users WHERE email = ? OR phone_number = ?");
        $checkStatus->execute([$emailOrPhone, $emailOrPhone]);
        $status = $checkStatus->fetch();

        ob_clean();
        if ($status && $status['is_verified'] == 0) {
            echo json_encode(['success' => false, 'message' => 'Please verify your email before logging in.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
        }
    }
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
ob_end_flush();