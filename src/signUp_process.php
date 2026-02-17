<?php
ob_start();
session_start();

if (!file_exists('db_config.php')) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database configuration file not found'
    ]);
    exit;
}

require_once 'db_config.php';
require_once 'Mailer.php';

if (!isset($pdo) || $pdo === null) {
    ob_clean();
    header('Content-Type: application/json');
    $errorMsg = isset($dbError) ? $dbError : 'Database connection failed';
    echo json_encode([
        'success' => false,
        'message' => 'Cannot connect to database. Error: ' . $errorMsg
    ]);
    exit;
}

ob_clean();
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $firstName = trim($input['firstName'] ?? '');
    $lastName = trim($input['lastName'] ?? '');
    $email = trim($input['email'] ?? '');
    $phoneNumber = trim($input['phoneNumber'] ?? '');
    $password = $input['password'] ?? '';
    $repeatPassword = $input['repeatPassword'] ?? '';
    
    $termsAccepted = $input['terms'] ?? false;

    if (empty($firstName) || empty($lastName) || empty($email) || empty($phoneNumber) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit;
    }

    if (!$termsAccepted) {
        echo json_encode([
            'success' => false,
            'message' => 'You must agree to the Terms and Conditions to create an account.'
        ]);
        exit;
    }

    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-+?.,_]).{8,}$/';
    
    if (!preg_match($passwordPattern, $password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 8 characters and include a mixture of uppercase, lowercase, numbers, and symbols.'
        ]);
        exit;
    }

    if ($password !== $repeatPassword) {
        echo json_encode([
            'success' => false,
            'message' => 'Passwords do not match'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already registered'
        ]);
        exit;
    }

    $role = 'user'; 
    if ($email === 'vladimirborja013@gmail.com') {
        $role = 'admin'; 
    }

    $token = bin2hex(random_bytes(32));
    $fullName = $firstName . ' ' . $lastName;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 1. Start the transaction
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, phone_number, password, role, verification_code) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$fullName, $email, $phoneNumber, $hashedPassword, $role, $token]);

    // 2. Attempt to send the email
    if (Mailer::sendVerification($email, $fullName, $token)) {
        // 3. If email succeeds, COMMIT the changes to the DB
        $pdo->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Registration successful! Check your email to verify.'
        ]);
    } else {
        // 4. If email fails, ROLLBACK (delete the user record automatically)
        $pdo->rollBack();

        echo json_encode([
            'success' => false, 
            'message' => 'Failed to send verification email. Please try again later.'
        ]);
    }

} catch (Exception $e) {
    // 5. Rollback on any other error (like DB connection loss)
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'System error: ' . $e->getMessage()
    ]);
}