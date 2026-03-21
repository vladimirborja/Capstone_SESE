<?php
session_start();
include '../db_config.php';
header('Content-Type: application/json');

function ensureColumn(PDO $pdo, string $table, string $column, string $definition): void
{
    // Avoid placeholders in SHOW statements for MariaDB compatibility.
    $quotedColumn = $pdo->quote($column);
    $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE $quotedColumn");
    if ($stmt && !$stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'list_public_establishments') {
    try {
        $rows = $pdo->query(
            "SELECT id, name, description, address, latitude, longitude, type, barangay,
                    COALESCE(owner_id, user_id, requester_id) AS ownerId,
                    COALESCE(owner_verified, 0) AS ownerVerified,
                    COALESCE(verified_by, NULL) AS verifiedBy
             FROM establishments
             WHERE status IN ('approved','active')
               AND latitude IS NOT NULL
               AND longitude IS NOT NULL"
        )->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'establishments' => $rows]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage(), 'establishments' => []]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = (int)($_SESSION['user_id'] ?? 0);
        $isAdmin = (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'], true));
        $currentRole = strtolower((string)($_SESSION['role'] ?? 'user'));
        ensureColumn($pdo, 'establishments', 'verified_by', "VARCHAR(50) DEFAULT NULL");
        ensureColumn($pdo, 'establishments', 'verified_at', "TIMESTAMP NULL DEFAULT NULL");
        ensureColumn($pdo, 'ownership_claims', 'reviewed_by', "VARCHAR(100) DEFAULT NULL");
        ensureColumn($pdo, 'ownership_claims', 'rejection_reason', "TEXT DEFAULT NULL");
        try {
            $pdo->exec("ALTER TABLE ownership_claims MODIFY COLUMN status ENUM('pending','self_verified','admin_verified','rejected') NOT NULL DEFAULT 'pending'");
        } catch (Exception $ignore) {
        }

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
            if ($user_id <= 0) {
                throw new Exception("You must be logged in to submit a claim.");
            }
            $establishmentId = (int)($_POST['establishment_id'] ?? 0);
            $fullName = trim((string)($_POST['full_name'] ?? ''));
            $permitNo = trim((string)($_POST['permit_number'] ?? ($_POST['business_permit_number'] ?? '')));
            $contactNumber = trim((string)($_POST['contact_number'] ?? ''));
            $message = trim((string)($_POST['message'] ?? ''));

            if ($establishmentId <= 0) {
                throw new Exception("Invalid establishment selection.");
            }
            $userStmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ?");
            $userStmt->execute([$user_id]);
            $accountName = trim((string)($userStmt->fetchColumn() ?: ''));
            if ($accountName === '') {
                throw new Exception("Unable to verify your account details.");
            }
            if (strcasecmp($fullName, $accountName) !== 0) {
                throw new Exception("Full name must match your account name exactly.");
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
            if ((int)($est['owner_verified'] ?? 0) === 1 && (int)($est['current_owner_id'] ?? 0) !== $user_id) {
                throw new Exception("This establishment already has a verified owner.");
            }

            $activeClaimStmt = $pdo->prepare(
                "SELECT id FROM ownership_claims
                 WHERE claimant_user_id = ?
                 AND status IN ('pending','self_verified','admin_verified')
                 LIMIT 1"
            );
            $activeClaimStmt->execute([$user_id]);
            if ($activeClaimStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("You can only have one active ownership claim at a time.");
            }

            $sameEstClaimStmt = $pdo->prepare(
                "SELECT id FROM ownership_claims
                 WHERE establishment_id = ? AND status IN ('pending','self_verified','admin_verified')
                 LIMIT 1"
            );
            $sameEstClaimStmt->execute([$establishmentId]);
            if ($sameEstClaimStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("This establishment already has an active ownership claim.");
            }

            $documentPath = null;
            $documentUploaded = false;
            $documentFile = $_FILES['proof_document'] ?? ($_FILES['ownership_document'] ?? null);
            if (!empty($documentFile['name']) && (int)($documentFile['error'] ?? 1) === 0) {
                $projectRoot = realpath(__DIR__ . '/../../');
                if ($projectRoot === false) {
                    throw new Exception("Upload directory is unavailable.");
                }
                $uploadDir = $projectRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'ownership_claims';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Failed to prepare upload directory.");
                }
                $originalName = (string)$documentFile['name'];
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                if (!in_array($ext, $allowed, true)) {
                    throw new Exception("Invalid file format. Allowed: PDF, JPG, JPEG, PNG.");
                }
                $size = (int)($documentFile['size'] ?? 0);
                if ($size <= 0 || $size > (5 * 1024 * 1024)) {
                    throw new Exception("File size must be 5MB or below.");
                }
                $filename = 'claim_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                if (!move_uploaded_file($documentFile['tmp_name'], $targetPath)) {
                    throw new Exception("Failed to upload ownership document.");
                }
                $documentPath = 'storage/ownership_claims/' . $filename;
                $documentUploaded = true;
            }
            $hasAllRequiredFields = ($fullName !== '' && $permitNo !== '' && $contactNumber !== '');
            $isSelfVerified = ($hasAllRequiredFields && $documentUploaded);
            $claimStatus = $isSelfVerified ? 'self_verified' : 'pending';

            $insert = $pdo->prepare("INSERT INTO ownership_claims (
                                        establishment_id, claimant_user_id, full_name, permit_number, document_path, contact_number,
                                        message, status, submitted_at, reviewed_at, reviewed_by
                                     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
            $reviewedAt = $isSelfVerified ? date('Y-m-d H:i:s') : null;
            $reviewedBy = $isSelfVerified ? 'self' : null;
            $insert->execute([
                $establishmentId,
                $user_id,
                $fullName,
                $permitNo,
                $documentPath,
                $contactNumber,
                $message,
                $claimStatus,
                $reviewedAt,
                $reviewedBy
            ]);
            $claimId = (int)$pdo->lastInsertId();

            if ($isSelfVerified) {
                $selfVerify = $pdo->prepare(
                    "UPDATE establishments
                     SET owner_id = ?, user_id = ?, owner_verified = 1, verified_by = 'self', verified_at = NOW()
                     WHERE id = ?"
                );
                $selfVerify->execute([$user_id, $user_id, $establishmentId]);
            }

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

            if ($isSelfVerified) {
                echo json_encode([
                    'success' => true,
                    'status' => 'self_verified',
                    'claim_id' => $claimId,
                    'message' => 'Your establishment has been verified! Your verified owner badge is now active.'
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'status' => 'pending',
                    'claim_id' => $claimId,
                    'message' => 'Your claim has been submitted for admin review. Complete all requirements for instant verification.'
                ]);
            }
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
            if (!$claim || !in_array(strtolower((string)$claim['status']), ['pending', 'self_verified'], true)) {
                throw new Exception("Claim is not eligible for admin approval.");
            }

            $approveClaim = $pdo->prepare("UPDATE ownership_claims SET status = 'admin_verified', reviewed_at = NOW(), reviewed_by = ? WHERE id = ?");
            $reviewedByRole = $currentRole === 'super_admin' ? 'super_admin' : 'admin';
            $approveClaim->execute([$reviewedByRole, $claimId]);
            
            $rejectOthers = $pdo->prepare("UPDATE ownership_claims
                                           SET status = 'rejected', message = CONCAT(IFNULL(message, ''), '\n[System] Another ownership claim was approved.'), reviewed_at = NOW(), reviewed_by = ?
                                           WHERE establishment_id = ? AND id <> ? AND status IN ('pending','self_verified')");
            $rejectOthers->execute([$reviewedByRole, $claim['establishment_id'], $claimId]);

            $updateEst = $pdo->prepare("UPDATE establishments
                                        SET owner_id = ?, user_id = ?, owner_verified = 1, verified_by = ?, verified_at = NOW()
                                        WHERE id = ?");
            $updateEst->execute([
                $claim['claimant_user_id'],
                $claim['claimant_user_id'],
                $reviewedByRole,
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
                                      VALUES (?, ?, 'ownership_claim_approved', ?, 'admin_verified', ?, ?, ?, NOW())");
                $log->execute([
                    $user_id,
                    $adminName,
                    $claim['status'],
                    'Approved ownership claim for "' . ($claim['establishment_name'] ?? 'Establishment') . '".',
                    $claim['claimant_user_id'],
                    $getUserName($claim['claimant_user_id'])
                ]);
            } catch (Exception $ignore) {
            }

            $notifyUser($claim['claimant_user_id'], 'Your ownership claim for "' . ($claim['establishment_name'] ?? 'Establishment') . '" was admin-verified.');
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
            if (!$claim || !in_array(strtolower((string)$claim['status']), ['pending', 'self_verified', 'admin_verified'], true)) {
                throw new Exception("Claim is not eligible for rejection.");
            }

            $reviewedByRole = $currentRole === 'super_admin' ? 'super_admin' : 'admin';
            $rejectClaim = $pdo->prepare("UPDATE ownership_claims
                                          SET status = 'rejected',
                                              rejection_reason = ?,
                                              message = CONCAT(IFNULL(message, ''), '\n[Admin Rejection Reason] ', ?),
                                              reviewed_at = NOW(),
                                              reviewed_by = ?
                                          WHERE id = ?");
            $rejectClaim->execute([$reason, $reason, $reviewedByRole, $claimId]);
            
            $revokeEst = $pdo->prepare(
                "UPDATE establishments
                 SET owner_verified = CASE WHEN owner_id = ? THEN 0 ELSE owner_verified END,
                     owner_id = CASE WHEN owner_id = ? THEN NULL ELSE owner_id END,
                     verified_by = CASE WHEN owner_id = ? THEN NULL ELSE verified_by END,
                     verified_at = CASE WHEN owner_id = ? THEN NULL ELSE verified_at END
                 WHERE id = ?"
            );
            $revokeEst->execute([
                $claim['claimant_user_id'],
                $claim['claimant_user_id'],
                $claim['claimant_user_id'],
                $claim['claimant_user_id'],
                $claim['establishment_id']
            ]);

            try {
                $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, admin_name, action_type, old_status, new_status, reason, affected_user_id, affected_user_name, created_at)
                                      VALUES (?, ?, 'ownership_claim_rejected', ?, 'rejected', ?, ?, ?, NOW())");
                $log->execute([
                    $user_id,
                    $adminName,
                    $claim['status'],
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