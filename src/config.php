<?php

// Set the timezone to the Philippines
date_default_timezone_set('Asia/Manila');

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'capstone_db';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}