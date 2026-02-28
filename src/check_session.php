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
    return $_SESSION['role'] ?? 'user';
}

function requireRole($allowedRoles = [])
{
    requireLogin();

    $userRole = getUserRole();

    if (!in_array($userRole, $allowedRoles)) {
        $target = 'mains/main.php';
        if ($userRole === 'admin' || $userRole === 'super_admin') {
            $target = 'admin_reports.php';
        } elseif ($userRole === 'business_owner' || $userRole === 'veterinarian' || $userRole === 'salon_owner') {
            $target = 'mains/main.php';
        }

        $_SESSION['auth_error'] = 'You are not authorized to access that page.';
        header("Location: $target");
        exit;
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
