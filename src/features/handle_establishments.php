<?php
session_start();
include '../db_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user_id'];
        $isAdmin = (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'], true));

        $getUserName = function ($id) use ($pdo) {
            $stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetchColumn() ?: 'User';
        };

        // ACTION: APPROVE (ADMIN ONLY)
        if (isset($_POST['action']) && $_POST['action'] === 'approve_establishment' && $isAdmin) {
            $id = $_POST['id'];
            $target = $pdo->prepare("SELECT id, name, requester_id FROM establishments WHERE id = ? AND status = 'pending'");
            $target->execute([$id]);
            $est = $target->fetch(PDO::FETCH_ASSOC);
            if (!$est) {
                throw new Exception("Pending establishment not found.");
            }

            $ownerId = !empty($est['requester_id']) ? $est['requester_id'] : null;

            try {
                $stmt = $pdo->prepare("UPDATE establishments SET status = 'approved', user_id = COALESCE(user_id, requester_id) WHERE id = ?");
                $stmt->execute([$id]);
            } catch (Exception $e) {
                $stmt = $pdo->prepare("UPDATE establishments SET status = 'active', user_id = COALESCE(user_id, requester_id) WHERE id = ?");
                $stmt->execute([$id]);
            }

            $adminName = $getUserName($user_id);
            try {
                $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, admin_name, action_type, old_status, new_status, reason, affected_user_id, affected_user_name) VALUES (?, ?, 'establishment_approval', 'pending', 'approved', ?, ?, ?)");
                $log->execute([
                    $user_id,
                    $adminName,
                    'Approved establishment: ' . ($est['name'] ?? 'N/A'),
                    $ownerId,
                    $ownerId ? $getUserName($ownerId) : null
                ]);
                if ($ownerId) {
                    $notif = $pdo->prepare("INSERT INTO notifications (post_id, user_id, message, is_read, created_at) VALUES (NULL, ?, ?, 0, NOW())");
                    $notif->execute([$ownerId, 'Your establishment "' . ($est['name'] ?? 'Listing') . '" was approved.']);
                }
            } catch (Exception $ignore) {
            }
            try {
                $record = $pdo->prepare("INSERT INTO establishment_records (
                                            establishment_id, establishment_name, category, barangay,
                                            submitted_by_user_id, submitted_by_name, status,
                                            admin_id, admin_name, submitted_at, actioned_at, rejection_reason
                                        )
                                        SELECT e.id, e.name, e.type, e.barangay, e.requester_id, COALESCE(u.full_name, 'N/A'),
                                               'approved', ?, ?, e.created_at, NOW(), NULL
                                        FROM establishments e
                                        LEFT JOIN users u ON u.user_id = e.requester_id
                                        WHERE e.id = ?
                                        ON DUPLICATE KEY UPDATE
                                            establishment_name = VALUES(establishment_name),
                                            category = VALUES(category),
                                            barangay = VALUES(barangay),
                                            submitted_by_user_id = VALUES(submitted_by_user_id),
                                            submitted_by_name = VALUES(submitted_by_name),
                                            status = VALUES(status),
                                            admin_id = VALUES(admin_id),
                                            admin_name = VALUES(admin_name),
                                            submitted_at = VALUES(submitted_at),
                                            actioned_at = VALUES(actioned_at),
                                            rejection_reason = NULL");
                $record->execute([$user_id, $adminName, $id]);
            } catch (Exception $ignore) {
            }
            echo json_encode(['success' => true]);
            exit;
        }

        if (isset($_POST['action']) && $_POST['action'] === 'reject_establishment' && $isAdmin) {
            $id = $_POST['id'];
            $reason = trim($_POST['reason'] ?? '');
            if ($reason === '') {
                throw new Exception("Rejection reason is required.");
            }

            $target = $pdo->prepare("SELECT id, name, requester_id FROM establishments WHERE id = ? AND status = 'pending'");
            $target->execute([$id]);
            $est = $target->fetch(PDO::FETCH_ASSOC);
            if (!$est) {
                throw new Exception("Pending establishment not found.");
            }

            try {
                $stmt = $pdo->prepare("UPDATE establishments SET status = 'rejected', rejection_reason = ? WHERE id = ?");
                $stmt->execute([$reason, $id]);
            } catch (Exception $e) {
                $stmt = $pdo->prepare("DELETE FROM establishments WHERE id = ? AND status = 'pending'");
                $stmt->execute([$id]);
            }

            $ownerId = !empty($est['requester_id']) ? $est['requester_id'] : null;
            $adminName = $getUserName($user_id);
            try {
                $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, admin_name, action_type, old_status, new_status, reason, affected_user_id, affected_user_name) VALUES (?, ?, 'establishment_rejection', 'pending', 'rejected', ?, ?, ?)");
                $log->execute([
                    $user_id,
                    $adminName,
                    'Rejected establishment "' . ($est['name'] ?? 'N/A') . '". Reason: ' . $reason,
                    $ownerId,
                    $ownerId ? $getUserName($ownerId) : null
                ]);

                if ($ownerId) {
                    $notif = $pdo->prepare("INSERT INTO notifications (post_id, user_id, message, is_read, created_at) VALUES (NULL, ?, ?, 0, NOW())");
                    $notif->execute([$ownerId, 'Your establishment "' . ($est['name'] ?? 'Listing') . '" was rejected. Reason: ' . $reason]);
                }
            } catch (Exception $ignore) {
            }
            try {
                $record = $pdo->prepare("INSERT INTO establishment_records (
                                            establishment_id, establishment_name, category, barangay,
                                            submitted_by_user_id, submitted_by_name, status,
                                            admin_id, admin_name, submitted_at, actioned_at, rejection_reason
                                        )
                                        SELECT e.id, e.name, e.type, e.barangay, e.requester_id, COALESCE(u.full_name, 'N/A'),
                                               'rejected', ?, ?, e.created_at, NOW(), ?
                                        FROM establishments e
                                        LEFT JOIN users u ON u.user_id = e.requester_id
                                        WHERE e.id = ?
                                        ON DUPLICATE KEY UPDATE
                                            establishment_name = VALUES(establishment_name),
                                            category = VALUES(category),
                                            barangay = VALUES(barangay),
                                            submitted_by_user_id = VALUES(submitted_by_user_id),
                                            submitted_by_name = VALUES(submitted_by_name),
                                            status = VALUES(status),
                                            admin_id = VALUES(admin_id),
                                            admin_name = VALUES(admin_name),
                                            submitted_at = VALUES(submitted_at),
                                            actioned_at = VALUES(actioned_at),
                                            rejection_reason = VALUES(rejection_reason)");
                $record->execute([$user_id, $adminName, $reason, $id]);
            } catch (Exception $ignore) {
            }
            echo json_encode(['success' => true]);
            exit;
        }

        // ACTION: ADD NEW
        if (isset($_POST['name'])) {
            if (empty($_POST['guidelines_agree'])) {
                throw new Exception("Please agree to the Community Guidelines.");
            }

            // Logic for 'Others' specification
            $type = ($_POST['type'] === 'Others') ? $_POST['other_type_input'] : $_POST['type'];
            $barangay = $_POST['barangay'] ?? '';
            $policies = $_POST['policies'] ?? '';
            $petTypesAllowed = isset($_POST['pet_types_allowed']) ? implode(', ', $_POST['pet_types_allowed']) : '';
            $venueSize = $_POST['venue_size'] ?? '';
            $operatingHours = $_POST['operating_hours'] ?? '';
            $contactNumber = $_POST['contact_number'] ?? '';
            $socialLinks = $_POST['social_links'] ?? '';

            if ($isAdmin) {
                $sql = "INSERT INTO establishments (
                            user_id, requester_id, status, name, description, address, latitude, longitude, type,
                            barangay, policies, pet_types_allowed, venue_size, operating_hours, contact_number, social_links, guidelines_accepted, created_at
                        ) VALUES (
                            :uid, NULL, 'approved', :name, :desc, :addr, :lat, :lng, :type,
                            :barangay, :policies, :pet_types_allowed, :venue_size, :operating_hours, :contact_number, :social_links, 1, NOW()
                        )";
                $params = ['uid' => $user_id];
            } else {
                $sql = "INSERT INTO establishments (
                            user_id, requester_id, status, name, description, address, latitude, longitude, type,
                            barangay, policies, pet_types_allowed, venue_size, operating_hours, contact_number, social_links, guidelines_accepted, created_at
                        ) VALUES (
                            NULL, :req_id, 'pending', :name, :desc, :addr, :lat, :lng, :type,
                            :barangay, :policies, :pet_types_allowed, :venue_size, :operating_hours, :contact_number, :social_links, 1, NOW()
                        )";
                $params = ['req_id' => $user_id];
            }

            $params = array_merge($params, [
                'name' => $_POST['name'],
                'desc' => $_POST['description'],
                'addr' => $_POST['address'],
                'lat'  => $_POST['latitude'],
                'lng'  => $_POST['longitude'],
                'type' => $type,
                'barangay' => $barangay,
                'policies' => $policies,
                'pet_types_allowed' => $petTypesAllowed,
                'venue_size' => $venueSize,
                'operating_hours' => $operatingHours,
                'contact_number' => $contactNumber,
                'social_links' => $socialLinks
            ]);

            if ($params['lat'] === '' || $params['lng'] === '') {
                throw new Exception("Please pin the exact establishment location on the map.");
            }
            if (!is_numeric($params['lat']) || !is_numeric($params['lng'])) {
                throw new Exception("Invalid map coordinates. Please set the pin again.");
            }
            
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            } catch (Exception $e) {
                // Backward-compatible fallback when new columns are not yet migrated.
                if ($isAdmin) {
                    $legacySql = "INSERT INTO establishments (user_id, requester_id, status, name, description, address, latitude, longitude, type)
                                  VALUES (:uid, NULL, 'active', :name, :desc, :addr, :lat, :lng, :type)";
                    $legacyParams = array_intersect_key($params, array_flip(['uid','name','desc','addr','lat','lng','type']));
                } else {
                    $legacySql = "INSERT INTO establishments (user_id, requester_id, status, name, description, address, latitude, longitude, type)
                                  VALUES (NULL, :req_id, 'pending', :name, :desc, :addr, :lat, :lng, :type)";
                    $legacyParams = array_intersect_key($params, array_flip(['req_id','name','desc','addr','lat','lng','type']));
                }
                $stmt = $pdo->prepare($legacySql);
                $stmt->execute($legacyParams);
            }
            echo json_encode(['success' => true]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}