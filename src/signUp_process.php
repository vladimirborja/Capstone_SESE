<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
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

    $fullName = $firstName . ' ' . $lastName;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, phone_number, password, role) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$fullName, $email, $phoneNumber, $hashedPassword, $role]);

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful!'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}