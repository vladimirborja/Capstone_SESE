<?php
session_start();
include '../db_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user_id'];
        $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

        // ACTION: APPROVE (ADMIN ONLY)
        if (isset($_POST['action']) && $_POST['action'] === 'approve_establishment' && $isAdmin) {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("UPDATE establishments SET status = 'active', user_id = ? WHERE id = ?");
            $stmt->execute([$user_id, $id]);
            echo json_encode(['success' => true]);
            exit;
        }

        // ACTION: ADD NEW
        if (isset($_POST['name'])) {
            // Logic for 'Others' specification
            $type = ($_POST['type'] === 'Others') ? $_POST['other_type_input'] : $_POST['type'];

            if ($isAdmin) {
                $sql = "INSERT INTO establishments (user_id, requester_id, status, name, description, address, latitude, longitude, type) 
                        VALUES (:uid, NULL, 'active', :name, :desc, :addr, :lat, :lng, :type)";
                $params = ['uid' => $user_id];
            } else {
                $sql = "INSERT INTO establishments (user_id, requester_id, status, name, description, address, latitude, longitude, type) 
                        VALUES (NULL, :req_id, 'pending', :name, :desc, :addr, :lat, :lng, :type)";
                $params = ['req_id' => $user_id];
            }

            $params = array_merge($params, [
                'name' => $_POST['name'],
                'desc' => $_POST['description'],
                'addr' => $_POST['address'],
                'lat'  => $_POST['latitude'],
                'lng'  => $_POST['longitude'],
                'type' => $type
            ]);
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}