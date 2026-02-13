<?php
session_start();
include '../db_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    try {
        $user_id = $_SESSION['user_id']; 

        $sql = "INSERT INTO establishments (user_id, requester_id, status, name, description, address, latitude, longitude) 
                VALUES (:uid, NULL, 'active', :name, :desc, :addr, :lat, :lng)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'uid'  => $user_id,
            'name' => $_POST['name'],
            'desc' => $_POST['description'],
            'addr' => $_POST['address'],
            'lat'  => $_POST['latitude'],
            'lng'  => $_POST['longitude']
        ]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}