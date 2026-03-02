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
        $notifyUser = function ($targetUserId, $message) use ($pdo) {
            $notif = $pdo->prepare("INSERT INTO adoption_notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
            $notif->execute([(int)$targetUserId, (string)$message]);
        };

        if (isset($_POST['action']) && $_POST['action'] === 'submit_ownership_claim') {
            $establishmentId = (int)($_POST['establishment_id'] ?? 0);
            $fullName = trim((string)($_POST['full_name'] ?? ''));
            $permitNo = trim((string)($_POST['business_permit_number'] ?? ''));
            $contactNumber = trim((string)($_POST['contact_number'] ?? ''));
            $message = trim((string)($_POST['message'] ?? ''));

            if ($establishmentId <= 0) {
                throw new Exception("Invalid establishment selection.");
            }
            if ($fullName === '' || $permitNo === '' || $contactNumber === '' || $message === '') {
                throw new Exception("Please complete all ownership claim fields.");
            }
            if (empty($_FILES['ownership_document']['name']) || (int)($_FILES['ownership_document']['error'] ?? 1) !== 0) {
                throw new Exception("Ownership document upload is required.");
            }

            $estStmt = $pdo->prepare("SELECT id, name, COALESCE(owner_id, user_id, requester_id) AS current_owner_id,
                                             COALESCE(owner_verified, 0) AS owner_verified
                                      FROM establishments
                                      WHERE id = ? AND status IN ('approved', 'active')");
            $estStmt->execute([$establishmentId]);
            $est = $estStmt->fetch(PDO::FETCH_ASSOC);
            if (!$est) {
                throw new Exception("Establishment not found or not yet active.");
            }
            if ((int)($est['owner_verified'] ?? 0) === 1) {
                throw new Exception("This establishment already has a verified owner.");
            }

            $dupStmt = $pdo->prepare("SELECT id FROM ownership_claims WHERE establishment_id = ? AND claimant_user_id = ? AND status = 'pending' LIMIT 1");
            $dupStmt->execute([$establishmentId, $user_id]);
            if ($dupStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("You already have a pending claim for this establishment.");
            }

            $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'ownership_claims';
            if ($uploadDir === false) {
                throw new Exception("Upload directory is unavailable.");
            }
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
                throw new Exception("Failed to prepare upload directory.");
            }

            $originalName = (string)$_FILES['ownership_document']['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed, true)) {
                throw new Exception("Invalid file format. Allowed: PDF, JPG, JPEG, PNG, WEBP.");
            }
            $filename = 'claim_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
            if (!move_uploaded_file($_FILES['ownership_document']['tmp_name'], $targetPath)) {
                throw new Exception("Failed to upload ownership document.");
            }
            $relativePath = 'uploads/ownership_claims/' . $filename;

            $insert = $pdo->prepare("INSERT INTO ownership_claims (
                                        establishment_id, claimant_user_id, full_name, permit_number, document_path, contact_number,
                                        message, status, submitted_at, reviewed_at
                                     ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NULL)");
            $insert->execute([
                $establishmentId,
                $user_id,
                $fullName,
                $permitNo,
                $relativePath,
                $contactNumber,
                $message
            ]);

            try {
                $adminName = $getUserName($user_id);
                $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, admin_name, action_type, old_status, new_status, reason, affected_user_id, affected_user_name, created_at)
                                      VALUES (?, ?, 'ownership_claim_submitted', 'none', 'pending', ?, ?, ?, NOW())");
                $log->execute([
                    $user_id,
                    $adminName,
                    'Submitted ownership claim for "' . ($est['name'] ?? 'Establishment') . '".',
                    $user_id,
                    $getUserName($user_id)
                ]);
            } catch (Exception $ignore) {
            }

            echo json_encode(['success' => true]);
            exit;
        }

        if (isset($_POST['action']) && $_POST['action'] === 'approve_ownership_claim' && $isAdmin) {
            $claimId = (int)($_POST['claim_id'] ?? 0);
            if ($claimId <= 0) {
                throw new Exception("Invalid claim ID.");
            }
            $adminName = $getUserName($user_id);

            $pdo->beginTransaction();
            $claimStmt = $pdo->prepare("SELECT bc.id AS claim_id, bc.establishment_id, bc.claimant_user_id, bc.status,
                                               e.name AS establishment_name
                                        FROM ownership_claims bc
                                        JOIN establishments e ON e.id = bc.establishment_id
                                        WHERE bc.id = ? FOR UPDATE");
            $claimStmt->execute([$claimId]);
            $claim = $claimStmt->fetch(PDO::FETCH_ASSOC);
            if (!$claim || strtolower((string)$claim['status']) !== 'pending') {
                throw new Exception("Pending ownership claim not found.");
            }

            $approveClaim = $pdo->prepare("UPDATE ownership_claims SET status = 'approved', reviewed_at = NOW() WHERE id = ?");
            $approveClaim->execute([$claimId]);

            $rejectOthers = $pdo->prepare("UPDATE ownership_claims
                                           SET status = 'rejected', message = CONCAT(IFNULL(message, ''), '\n[System] Another ownership claim was approved.'), reviewed_at = NOW()
                                           WHERE establishment_id = ? AND id <> ? AND status = 'pending'");
            $rejectOthers->execute([$claim['establishment_id'], $claimId]);

            $updateEst = $pdo->prepare("UPDATE establishments
                                        SET owner_id = ?, user_id = ?, owner_verified = 1
                                        WHERE id = ?");
            $updateEst->execute([
                $claim['claimant_user_id'],
                $claim['claimant_user_id'],
                $claim['establishment_id']
            ]);

            $roleStmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
            $roleStmt->execute([$claim['claimant_user_id']]);
            $currentRole = strtolower((string)($roleStmt->fetchColumn() ?: 'user'));
            if (!in_array($currentRole, ['admin', 'super_admin', 'business_owner'], true)) {
                $setRole = $pdo->prepare("UPDATE users SET role = 'business_owner' WHERE user_id = ?");
                $setRole->execute([$claim['claimant_user_id']]);
            }

            try {
                $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, admin_name, action_type, old_status, new_status, reason, affected_user_id, affected_user_name, created_at)
                                      VALUES (?, ?, 'ownership_claim_approved', 'pending', 'approved', ?, ?, ?, NOW())");
                $log->execute([
                    $user_id,
                    $adminName,
                    'Approved ownership claim for "' . ($claim['establishment_name'] ?? 'Establishment') . '".',
                    $claim['claimant_user_id'],
                    $getUserName($claim['claimant_user_id'])
                ]);
            } catch (Exception $ignore) {
            }

            $notifyUser($claim['claimant_user_id'], 'Your ownership claim for "' . ($claim['establishment_name'] ?? 'Establishment') . '" was approved.');
            $pdo->commit();
            echo json_encode(['success' => true]);
            exit;
        }

        if (isset($_POST['action']) && $_POST['action'] === 'reject_ownership_claim' && $isAdmin) {
            $claimId = (int)($_POST['claim_id'] ?? 0);
            $reason = trim((string)($_POST['reason'] ?? ''));
            if ($claimId <= 0) {
                throw new Exception("Invalid claim ID.");
            }
            if ($reason === '') {
                throw new Exception("Rejection reason is required.");
            }
            $adminName = $getUserName($user_id);

            $pdo->beginTransaction();
            $claimStmt = $pdo->prepare("SELECT bc.id AS claim_id, bc.establishment_id, bc.claimant_user_id, bc.status,
                                               e.name AS establishment_name
                                        FROM ownership_claims bc
                                        JOIN establishments e ON e.id = bc.establishment_id
                                        WHERE bc.id = ? FOR UPDATE");
            $claimStmt->execute([$claimId]);
            $claim = $claimStmt->fetch(PDO::FETCH_ASSOC);
            if (!$claim || strtolower((string)$claim['status']) !== 'pending') {
                throw new Exception("Pending ownership claim not found.");
            }

            $rejectClaim = $pdo->prepare("UPDATE ownership_claims SET status = 'rejected', message = CONCAT(IFNULL(message, ''), '\n[Admin Rejection Reason] ', ?), reviewed_at = NOW() WHERE id = ?");
            $rejectClaim->execute([$reason, $claimId]);

            try {
                $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, admin_name, action_type, old_status, new_status, reason, affected_user_id, affected_user_name, created_at)
                                      VALUES (?, ?, 'ownership_claim_rejected', 'pending', 'rejected', ?, ?, ?, NOW())");
                $log->execute([
                    $user_id,
                    $adminName,
                    'Rejected ownership claim for "' . ($claim['establishment_name'] ?? 'Establishment') . '". Reason: ' . $reason,
                    $claim['claimant_user_id'],
                    $getUserName($claim['claimant_user_id'])
                ]);
            } catch (Exception $ignore) {
            }

            $notifyUser($claim['claimant_user_id'], 'Your ownership claim for "' . ($claim['establishment_name'] ?? 'Establishment') . '" was rejected. Reason: ' . $reason);
            $pdo->commit();
            echo json_encode(['success' => true]);
            exit;
        }

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
                $stmt = $pdo->prepare("UPDATE establishments
                                       SET status = 'approved',
                                           user_id = COALESCE(user_id, requester_id),
                                           owner_id = COALESCE(owner_id, user_id, requester_id),
                                           owner_verified = COALESCE(owner_verified, 0)
                                       WHERE id = ?");
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
                    $notifyUser($ownerId, 'Your establishment "' . ($est['name'] ?? 'Listing') . '" was approved.');
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
                    $notifyUser($ownerId, 'Your establishment "' . ($est['name'] ?? 'Listing') . '" was rejected. Reason: ' . $reason);
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
                            user_id, owner_id, owner_verified, requester_id, status, name, description, address, latitude, longitude, type,
                            barangay, policies, pet_types_allowed, venue_size, operating_hours, contact_number, social_links, guidelines_accepted, created_at
                        ) VALUES (
                            :uid, :uid, 0, NULL, 'approved', :name, :desc, :addr, :lat, :lng, :type,
                            :barangay, :policies, :pet_types_allowed, :venue_size, :operating_hours, :contact_number, :social_links, 1, NOW()
                        )";
                $params = ['uid' => $user_id];
            } else {
                $sql = "INSERT INTO establishments (
                            user_id, owner_id, owner_verified, requester_id, status, name, description, address, latitude, longitude, type,
                            barangay, policies, pet_types_allowed, venue_size, operating_hours, contact_number, social_links, guidelines_accepted, created_at
                        ) VALUES (
                            NULL, NULL, 0, :req_id, 'pending', :name, :desc, :addr, :lat, :lng, :type,
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