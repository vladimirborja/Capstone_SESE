<?php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Optionally restart session to set a logout flag
session_start();
$_SESSION['logged_out'] = true;

// Redirect back to login page
header("Location: index.php");
exit;
