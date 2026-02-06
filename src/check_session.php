<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin($redirectTo = 'signIn.php')
{
    if (!isLoggedIn()) {
        header("Location: $redirectTo");
        exit;
    }
}

function getUserRole()
{
    return $_SESSION['role'] ?? null;
}

function requireRole($allowedRoles = [])
{
    requireLogin();

    $userRole = getUserRole();

    if (!in_array($userRole, $allowedRoles)) {
        http_response_code(403);
        die('Access denied. You do not have permission to view this page.');
    }
}

function getLoggedInUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['full_name'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'role' => $_SESSION['role'] ?? null
    ];
}
