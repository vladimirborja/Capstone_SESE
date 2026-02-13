<?php
session_start();
include '../db_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user_id'];
        $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

        // ACTION: APPROVE (Admin Only)
        if (isset($_POST['action']) && $_POST['action'] === 'approve_establishment' && $isAdmin) {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("UPDATE establishments SET status = 'active', user_id = ? WHERE id = ?");
            $stmt->execute([$user_id, $id]);
            echo json_encode(['success' => true]);
            exit;
        }

        // ACTION: ADD NEW
        if (isset($_POST['name'])) {
            if ($isAdmin) {
                // Admin adds directly as active
                $sql = "INSERT INTO establishments (user_id, requester_id, status, name, description, address, latitude, longitude) 
                        VALUES (:uid, NULL, 'active', :name, :desc, :addr, :lat, :lng)";
                $params = ['uid' => $user_id];
            } else {
                // User adds as pending
                $sql = "INSERT INTO establishments (user_id, requester_id, status, name, description, address, latitude, longitude) 
                        VALUES (NULL, :req_id, 'pending', :name, :desc, :addr, :lat, :lng)";
                $params = ['req_id' => $user_id];
            }

            $params = array_merge($params, [
                'name' => $_POST['name'],
                'desc' => $_POST['description'],
                'addr' => $_POST['address'],
                'lat'  => $_POST['latitude'],
                'lng'  => $_POST['longitude']
            ]);
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}