<?php
// Safety Check
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

require_once '../config.php';

// Get & Clean Form Data
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$contact    = trim($_POST['contact'] ?? '');
$subject    = trim($_POST['subject'] ?? '');
$message    = trim($_POST['message'] ?? '');

// Combine names for your 'name' column in the DB
$name = trim($first_name . ' ' . $last_name);

// Basic Validation
if (empty($name) || empty($email) || empty($message)) {
    die("Error: Required fields are missing.");
}

// Prepare SQL
$sql = "INSERT INTO contact_messages (name, email, contact, subject, message) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("sssss", $name, $email, $contact, $subject, $message);

// Execute and Redirect
if ($stmt->execute()) {
    // Determine where to redirect back to
    // Use the hidden 'redirect' input if it exists, otherwise use the previous page
    $target = !empty($_POST['redirect']) ? $_POST['redirect'] : $_SERVER['HTTP_REFERER'];
    
    // Remove existing query strings to avoid success=1&success=1
    $clean_url = explode('?', $target)[0];
    
    header("Location: " . $clean_url . "?success=1#contact");
    exit;
} else {
    echo "Database Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>