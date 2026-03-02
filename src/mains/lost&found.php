<?php
session_start();

require_once '../config.php';

// Assume you have a logged-in user ID in the session
$current_user_id = $_SESSION['user_id'] ?? null;
$currentUserName = '';
$currentUserContact = '';
$currentUserAge = '';
$currentUserOccupation = '';
if ($current_user_id) {
    $meStmt = $conn->prepare("SELECT full_name, phone_number FROM users WHERE user_id = ?");
    $meStmt->bind_param("i", $current_user_id);
    $meStmt->execute();
    $meData = $meStmt->get_result()->fetch_assoc();
    $currentUserName = $meData['full_name'] ?? '';
    $currentUserContact = $meData['phone_number'] ?? '';
    $currentUserAge = $_SESSION['age'] ?? '';
    $currentUserOccupation = $_SESSION['occupation'] ?? '';
}

// Backward compatibility for DBs that haven't added found_reports.message yet.
$foundReportsHasMessageColumn = false;
if ($colCheck = $conn->query("SHOW COLUMNS FROM found_reports LIKE 'message'")) {
    $foundReportsHasMessageColumn = ($colCheck->num_rows > 0);
}

// Handle AJAX actions (Status Updates)
if (isset($_POST['update_status'])) {
    $pet_id = intval($_POST['pet_id']);
    $action = $_POST['action']; 
    header('Content-Type: application/json');
    try {
        if (!$current_user_id) {
            throw new Exception("Please log in first.");
        }

        if ($action === 'to_pending') {
            $locationFound = trim((string)($_POST['location_found'] ?? ''));
            $finderContact = trim((string)($_POST['contact_number'] ?? ''));
            if ($locationFound === '' || $finderContact === '') {
                throw new Exception("Found location and contact number are required.");
            }

            $petStmt = $conn->prepare("SELECT pet_id, user_id, pet_name, category FROM pets WHERE pet_id = ? LIMIT 1");
            $petStmt->bind_param("i", $pet_id);
            $petStmt->execute();
            $petRow = $petStmt->get_result()->fetch_assoc();
            if (!$petRow || strtolower((string)$petRow['category']) !== 'lost') {
                throw new Exception("This listing is no longer available for lost-pet verification.");
            }
            if ((int)$petRow['user_id'] === (int)$current_user_id) {
                throw new Exception("You cannot submit a found report on your own listing.");
            }

            $photoPath = null;
            if (!empty($_FILES['found_photo']['name']) && (int)($_FILES['found_photo']['error'] ?? 1) === 0) {
                $ext = strtolower(pathinfo((string)$_FILES['found_photo']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                    throw new Exception("Found photo must be JPG or PNG.");
                }
                if ((int)($_FILES['found_photo']['size'] ?? 0) > (5 * 1024 * 1024)) {
                    throw new Exception("Found photo must be 5MB or below.");
                }
                $uploadDir = "../uploads/found_reports";
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Unable to prepare upload directory.");
                }
                $fileName = "found_" . time() . "_" . mt_rand(1000, 9999) . "." . $ext;
                $target = $uploadDir . "/" . $fileName;
                if (!move_uploaded_file($_FILES['found_photo']['tmp_name'], $target)) {
                    throw new Exception("Unable to upload found photo.");
                }
                $photoPath = "uploads/found_reports/" . $fileName;
            }

            $finderMessage = trim((string)($_POST['finder_message'] ?? ''));
            if ($foundReportsHasMessageColumn) {
                $reportStmt = $conn->prepare("INSERT INTO found_reports (pet_id, finder_user_id, location_found, contact_number, message, photo, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
                $reportStmt->bind_param("iissss", $pet_id, $current_user_id, $locationFound, $finderContact, $finderMessage, $photoPath);
            } else {
                $reportStmt = $conn->prepare("INSERT INTO found_reports (pet_id, finder_user_id, location_found, contact_number, photo, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
                $reportStmt->bind_param("iisss", $pet_id, $current_user_id, $locationFound, $finderContact, $photoPath);
            }
            $reportStmt->execute();

            $updateStmt = $conn->prepare("UPDATE pets SET category = 'pending' WHERE pet_id = ? AND LOWER(category) = 'lost'");
            $updateStmt->bind_param("i", $pet_id);
            $updateStmt->execute();
            if ($updateStmt->affected_rows <= 0) {
                throw new Exception("Unable to update listing status.");
            }

            $ownerMsg = $currentUserName . " reported that they found your pet \"" . ($petRow['pet_name'] ?? 'your pet') . "\". Please review and confirm.";
            if ($notif = $conn->prepare("INSERT INTO adoption_notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())")) {
                $ownerId = (int)$petRow['user_id'];
                $notif->bind_param("is", $ownerId, $ownerMsg);
                $notif->execute();
            }
            echo json_encode(['success' => true, 'message' => 'Status updated to Pending verification.']);
            exit;
        } elseif ($action === 'confirm_found') {
            $foundReportId = intval($_POST['found_report_id'] ?? 0);

            $foundReportMessageExpr = $foundReportsHasMessageColumn ? "fr.message" : "'' AS message";
            $foundReportSql = "SELECT fr.id, fr.pet_id, fr.contact_number, fr.status, fr.created_at, fr.photo, fr.location_found, {$foundReportMessageExpr}
                               FROM found_reports fr
                               JOIN pets p ON p.pet_id = fr.pet_id
                               WHERE fr.pet_id = ? AND p.user_id = ? AND LOWER(p.category) = 'pending' AND fr.status = 'pending'";
            if ($foundReportId > 0) {
                $foundReportSql .= " AND fr.id = ?";
            }
            $foundReportSql .= " ORDER BY fr.created_at DESC LIMIT 1";
            $foundReportStmt = $conn->prepare($foundReportSql);
            if ($foundReportId > 0) {
                $foundReportStmt->bind_param("iii", $pet_id, $current_user_id, $foundReportId);
            } else {
                $foundReportStmt->bind_param("ii", $pet_id, $current_user_id);
            }
            $foundReportStmt->execute();
            $foundReport = $foundReportStmt->get_result()->fetch_assoc();
            if (!$foundReport) {
                throw new Exception("No pending finder report was found for this listing.");
            }

            $updateStmt = $conn->prepare("UPDATE pets SET category = 'found' WHERE pet_id = ? AND user_id = ? AND LOWER(category) = 'pending'");
            $updateStmt->bind_param("ii", $pet_id, $current_user_id);
            $updateStmt->execute();
            if ($updateStmt->affected_rows <= 0) {
                throw new Exception("Unauthorized or pet not ready for confirmation.");
            }

            $foundStmt = $conn->prepare("UPDATE found_reports SET status = 'confirmed' WHERE id = ? AND status = 'pending'");
            $foundStmt->bind_param("i", $foundReport['id']);
            $foundStmt->execute();
            $finderContact = trim((string)($foundReport['contact_number'] ?? ''));
            $successMsg = "Your pet has been confirmed as found! Thank you to the kind person who reported finding them.";
            if ($finderContact !== '') {
                $successMsg .= " Contact the finder at: " . $finderContact . ".";
            }
            echo json_encode(['success' => true, 'message' => $successMsg]);
            exit;
        }

        throw new Exception("Invalid status action.");
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if (isset($_POST['bookmark_action']) && $current_user_id) {
    header('Content-Type: application/json');
    try {
        $pet_id = intval($_POST['pet_id'] ?? 0);
        $stmt = $conn->prepare("SELECT bookmark_id FROM pet_bookmarks WHERE user_id = ? AND pet_id = ?");
        $stmt->bind_param("ii", $current_user_id, $pet_id);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();

        if ($existing) {
            $del = $conn->prepare("DELETE FROM pet_bookmarks WHERE bookmark_id = ?");
            $del->bind_param("i", $existing['bookmark_id']);
            $del->execute();
            echo json_encode(['success' => true, 'status' => 'removed']);
        } else {
            $ins = $conn->prepare("INSERT INTO pet_bookmarks (user_id, pet_id, created_at) VALUES (?, ?, NOW())");
            $ins->bind_param("ii", $current_user_id, $pet_id);
            $ins->execute();
            echo json_encode(['success' => true, 'status' => 'saved']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Bookmarks table is not available yet.']);
    }
    exit;
}

if (isset($_POST['adopt_inquiry_action']) && $current_user_id) {
    header('Content-Type: application/json');
    try {
        $pet_id = intval($_POST['pet_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $owner_user_id = intval($_POST['owner_user_id'] ?? 0);
        if ($pet_id <= 0 || $message === '') {
            throw new Exception('Please provide your inquiry message.');
        }
        if ($owner_user_id <= 0 || $owner_user_id === (int)$current_user_id) {
            throw new Exception('Invalid adoption request.');
        }

        $dup = $conn->prepare("SELECT response_id FROM pet_responses WHERE pet_id = ? AND responder_user_id = ?");
        $dup->bind_param("ii", $pet_id, $current_user_id);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            throw new Exception('You already sent an adoption request for this pet.');
        }

        $fullName = trim($_POST['full_name'] ?? $currentUserName);
        $age = trim($_POST['age'] ?? '');
        $occupation = trim($_POST['occupation'] ?? '');
        $contactDetails = trim($_POST['contact_number'] ?? $currentUserContact);
        $livingType = trim($_POST['living_type'] ?? '');
        $outdoorSpace = trim($_POST['outdoor_space'] ?? '');
        $petExperience = trim($_POST['pet_experience'] ?? '');
        $experienceDetails = trim($_POST['experience_details'] ?? '');
        $adoptionReason = trim($_POST['adoption_reason'] ?? '');
        $agreement = trim($_POST['agreement_confirm'] ?? '');

        if ($fullName === '' || $contactDetails === '' || $livingType === '' || $petExperience === '' || $adoptionReason === '') {
            throw new Exception('Please complete all required adoption form fields.');
        }
        if (strlen($adoptionReason) < 40) {
            throw new Exception('Please provide a more detailed reason (minimum 40 characters).');
        }
        if ($agreement !== '1') {
            throw new Exception('You must confirm the adoption agreement.');
        }

        $fullMessage = "Adoption Application\n"
            . "Name: " . $fullName . "\n"
            . "Age: " . ($age !== '' ? $age : 'N/A') . "\n"
            . "Occupation: " . ($occupation !== '' ? $occupation : 'N/A') . "\n"
            . "Contact: " . $contactDetails . "\n"
            . "Living Type: " . $livingType . "\n"
            . "Outdoor Space: " . ($outdoorSpace !== '' ? $outdoorSpace : 'N/A') . "\n"
            . "Pet Experience: " . $petExperience . "\n"
            . "Experience Details: " . ($experienceDetails !== '' ? $experienceDetails : 'N/A') . "\n\n"
            . "Why adopt this pet:\n" . $adoptionReason . "\n\n"
            . "Additional Message:\n" . $message;
        $ins = $conn->prepare("INSERT INTO pet_responses (pet_id, responder_user_id, owner_user_id, message, adopter_name, adopter_contact, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $ins->bind_param("iiisss", $pet_id, $current_user_id, $owner_user_id, $fullMessage, $fullName, $contactDetails);
        $ins->execute();

        $ownerMsg = $currentUserName . " sent an adoption request for your pet post.";
        if ($note = $conn->prepare("INSERT INTO adoption_notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())")) {
            $note->bind_param("is", $owner_user_id, $ownerMsg);
            $note->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Your adoption inquiry was sent to the pet owner.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if (isset($_POST['adoption_owner_action']) && $current_user_id) {
    header('Content-Type: application/json');
    try {
        $response_id = intval($_POST['response_id'] ?? 0);
        $decision = $_POST['decision'] ?? '';
        $declineReason = trim($_POST['decline_reason'] ?? '');
        if (!in_array($decision, ['approve', 'decline'], true)) {
            throw new Exception('Invalid decision.');
        }
        if ($decision === 'decline' && $declineReason === '') {
            throw new Exception('Please provide a reason for disagreeing.');
        }

        $stmt = $conn->prepare("SELECT pr.response_id, pr.pet_id, pr.responder_user_id, pr.owner_user_id, p.pet_name, p.category FROM pet_responses pr JOIN pets p ON p.pet_id = pr.pet_id WHERE pr.response_id = ?");
        $stmt->bind_param("i", $response_id);
        $stmt->execute();
        $req = $stmt->get_result()->fetch_assoc();
        if (!$req || (int)$req['owner_user_id'] !== (int)$current_user_id) {
            throw new Exception('Unauthorized action.');
        }

        if ($decision === 'approve') {
            $approve = $conn->prepare("UPDATE pet_responses SET status = 'approved', decided_at = NOW() WHERE response_id = ?");
            $approve->bind_param("i", $response_id);
            $approve->execute();

            $declineOthers = $conn->prepare("UPDATE pet_responses SET status = 'declined', decided_at = NOW() WHERE pet_id = ? AND response_id <> ? AND status = 'pending'");
            $declineOthers->bind_param("ii", $req['pet_id'], $response_id);
            $declineOthers->execute();

            $pet = $conn->prepare("UPDATE pets SET category = 'adopted' WHERE pet_id = ?");
            $pet->bind_param("i", $req['pet_id']);
            $pet->execute();

            $ownerInfo = $conn->prepare("SELECT full_name, phone_number FROM users WHERE user_id = ?");
            $ownerInfo->bind_param("i", $current_user_id);
            $ownerInfo->execute();
            $ownerData = $ownerInfo->get_result()->fetch_assoc();
            $ownerContactMsg = "Your adoption request for " . ($req['pet_name'] ?? 'the pet') . " was approved. Contact owner: " . ($ownerData['full_name'] ?? 'Owner') . " (" . ($ownerData['phone_number'] ?? 'N/A') . ").";

            if ($approvedNote = $conn->prepare("INSERT INTO adoption_notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())")) {
                $approvedNote->bind_param("is", $req['responder_user_id'], $ownerContactMsg);
                $approvedNote->execute();
            }

            $others = $conn->prepare("SELECT responder_user_id FROM pet_responses WHERE pet_id = ? AND response_id <> ? AND status = 'declined'");
            $others->bind_param("ii", $req['pet_id'], $response_id);
            $others->execute();
            $othersRes = $others->get_result();
            while ($o = $othersRes->fetch_assoc()) {
                $declinedMsg = "Your adoption request for " . ($req['pet_name'] ?? 'the pet') . " was declined because the pet has already been adopted.";
                if ($n = $conn->prepare("INSERT INTO adoption_notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())")) {
                    $n->bind_param("is", $o['responder_user_id'], $declinedMsg);
                    $n->execute();
                }
            }
        } else {
            $decline = $conn->prepare("UPDATE pet_responses SET status = 'declined', decided_at = NOW(), decline_reason = ? WHERE response_id = ?");
            if ($decline) {
                $decline->bind_param("si", $declineReason, $response_id);
            } else {
                $decline = $conn->prepare("UPDATE pet_responses SET status = 'declined', decided_at = NOW() WHERE response_id = ?");
                $decline->bind_param("i", $response_id);
            }
            $decline->execute();

            $declinedMsg = "Your adoption request for " . ($req['pet_name'] ?? 'the pet') . " was declined. Reason: " . $declineReason;
            if ($note = $conn->prepare("INSERT INTO adoption_notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())")) {
                $note->bind_param("is", $req['responder_user_id'], $declinedMsg);
                $note->execute();
            }
        }
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}


// 1. Get the filter types from the GET request
$types = isset($_GET['type']) ? $_GET['type'] : [];
$bookmarkedOnly = isset($_GET['bookmarked']) && $_GET['bookmarked'] === '1';

// 2. Build the WHERE clause
$where_clauses = [];
$publicLiveStatuses = ['lost', 'pending', 'found', 'for_adoption'];
$where_clauses[] = "REPLACE(LOWER(category), ' ', '_') IN ('" . implode("','", array_map([$conn, 'real_escape_string'], $publicLiveStatuses)) . "')";

// Apply category filters if any are checked
if (!empty($types)) {
    $sanitized_types = array_map(function($t) use ($conn) {
        return "'" . $conn->real_escape_string(strtolower($t)) . "'";
    }, $types);

    $where_clauses[] = "REPLACE(LOWER(category), ' ', '_') IN (" . implode(',', $sanitized_types) . ")";
}

if ($bookmarkedOnly && $current_user_id) {
    $where_clauses[] = "pet_id IN (SELECT pet_id FROM pet_bookmarks WHERE user_id = " . (int)$current_user_id . ")";
}

// 3. Construct the Final Query
$sql = "SELECT * FROM pets";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);
$pets = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pets[] = $row;
    }
}

$foundReportsByPet = [];
if ($current_user_id) {
    $foundReportMessageExpr = $foundReportsHasMessageColumn ? "fr.message" : "'' AS message";
    $foundReportStmt = $conn->prepare(
        "SELECT fr.id,
                fr.pet_id,
                fr.location_found,
                fr.contact_number,
                {$foundReportMessageExpr},
                fr.photo,
                fr.created_at,
                fr.status,
                COALESCE(u.full_name, 'Finder') AS finder_name
         FROM found_reports fr
         JOIN pets p ON p.pet_id = fr.pet_id
         LEFT JOIN users u ON u.user_id = fr.finder_user_id
         WHERE p.user_id = ? AND LOWER(p.category) = 'pending' AND fr.status = 'pending'
         ORDER BY fr.created_at DESC"
    );
    if ($foundReportStmt) {
        $foundReportStmt->bind_param("i", $current_user_id);
        $foundReportStmt->execute();
        $frRes = $foundReportStmt->get_result();
        while ($fr = $frRes->fetch_assoc()) {
            $petKey = (int)($fr['pet_id'] ?? 0);
            if ($petKey > 0 && !isset($foundReportsByPet[$petKey])) {
                $foundReportsByPet[$petKey] = [
                    'id' => (int)($fr['id'] ?? 0),
                    'finder_name' => (string)($fr['finder_name'] ?? 'Finder'),
                    'location_found' => (string)($fr['location_found'] ?? ''),
                    'contact_number' => (string)($fr['contact_number'] ?? ''),
                    'message' => (string)($fr['message'] ?? ''),
                    'photo' => (string)($fr['photo'] ?? ''),
                    'created_at' => (string)($fr['created_at'] ?? '')
                ];
            }
        }
    }
}

$myRequestsByPet = [];
if ($current_user_id) {
    $mine = $conn->prepare("SELECT pr.pet_id, pr.status, pr.created_at, p.pet_name, p.image_url, p.breed, p.pet_type FROM pet_responses pr JOIN pets p ON p.pet_id = pr.pet_id WHERE pr.responder_user_id = ?");
    $mine->bind_param("i", $current_user_id);
    $mine->execute();
    $mineRes = $mine->get_result();
    while ($r = $mineRes->fetch_assoc()) {
        $myRequestsByPet[(int)$r['pet_id']] = [
            'status' => $r['status'],
            'pet_name' => $r['pet_name'] ?? 'Pet',
            'created_at' => $r['created_at'] ?? null,
            'image_url' => $r['image_url'] ?? '',
            'breed' => $r['breed'] ?? '',
            'pet_type' => $r['pet_type'] ?? ''
        ];
    }
}

$bookmarkedPetIds = [];
if ($current_user_id) {
    $bookmarkStmt = $conn->prepare("SELECT pet_id FROM pet_bookmarks WHERE user_id = ?");
    if ($bookmarkStmt) {
        $bookmarkStmt->bind_param("i", $current_user_id);
        $bookmarkStmt->execute();
        $bookmarkRes = $bookmarkStmt->get_result();
        while ($bm = $bookmarkRes->fetch_assoc()) {
            $bookmarkedPetIds[(int)$bm['pet_id']] = true;
        }
    }
}

$ownerAdoptionRequests = [];
if ($current_user_id) {
    $ownerReqSql = "SELECT pr.response_id, pr.pet_id, pr.message, pr.status, pr.adopter_name, pr.adopter_contact, pr.created_at, p.pet_name
                    FROM pet_responses pr
                    JOIN pets p ON p.pet_id = pr.pet_id
                    WHERE pr.owner_user_id = ?
                    ORDER BY pr.created_at DESC";
    $ownerReqStmt = $conn->prepare($ownerReqSql);
    $ownerReqStmt->bind_param("i", $current_user_id);
    $ownerReqStmt->execute();
    $ownerAdoptionRequests = $ownerReqStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$adoptionNotifications = [];
if ($current_user_id) {
    if ($notifStmt = $conn->prepare("SELECT * FROM adoption_notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20")) {
        $notifStmt->bind_param("i", $current_user_id);
        $notifStmt->execute();
        $adoptionNotifications = $notifStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

$mySubmittedPetPosts = [];
if ($current_user_id) {
    $myPostStmt = $conn->prepare("SELECT pet_id, pet_name, category, requested_category, verification_reason, created_at
                                  FROM pets
                                  WHERE user_id = ?
                                  ORDER BY created_at DESC");
    if ($myPostStmt) {
        $myPostStmt->bind_param("i", $current_user_id);
        $myPostStmt->execute();
        $mySubmittedPetPosts = $myPostStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

function petStatusMeta($rawCategory) {
    $key = str_replace([' ', '-'], '_', strtolower(trim((string)$rawCategory)));
    if ($key === 'lost') {
        return ['label' => 'LOST PET', 'class' => 'tag-lost', 'key' => 'lost'];
    }
    if ($key === 'found') {
        return ['label' => 'FOUND PET', 'class' => 'tag-found', 'key' => 'found'];
    }
    if ($key === 'for_adoption') {
        return ['label' => 'FOR ADOPTION', 'class' => 'tag-adoption', 'key' => 'for_adoption'];
    }
    if ($key === 'pending') {
        return ['label' => 'PENDING', 'class' => 'tag-pending', 'key' => 'pending'];
    }
    if ($key === 'adopted') {
        return ['label' => 'ADOPTED', 'class' => 'tag-adopted', 'key' => 'adopted'];
    }
    if ($key === 'waiting_approval') {
        return ['label' => 'WAITING FOR APPROVAL', 'class' => 'tag-waiting', 'key' => 'waiting_approval'];
    }
    if ($key === 'rejected') {
        return ['label' => 'REJECTED', 'class' => 'tag-rejected', 'key' => 'rejected'];
    }
    if ($key === 'resolved') {
        return ['label' => 'RESOLVED', 'class' => 'tag-resolved', 'key' => 'resolved'];
    }
    return ['label' => ucfirst($rawCategory), 'class' => 'tag-pending', 'key' => $key];
}

function parseAdoptionApplication(string $rawMessage, string $fallbackName = '', string $fallbackContact = ''): array {
    $data = [
        'name' => $fallbackName !== '' ? $fallbackName : 'N/A',
        'age' => 'N/A',
        'occupation' => 'N/A',
        'contact' => $fallbackContact !== '' ? $fallbackContact : 'N/A',
        'living_type' => 'N/A',
        'outdoor_space' => 'N/A',
        'pet_experience' => 'N/A',
        'experience_details' => 'N/A',
        'adoption_reason' => 'N/A',
        'additional_message' => 'N/A'
    ];

    $message = trim((string)$rawMessage);
    if ($message === '') {
        return $data;
    }

    $lines = preg_split('/\R/', $message) ?: [];
    $mode = '';
    $reasonLines = [];
    $additionalLines = [];

    foreach ($lines as $lineRaw) {
        $line = trim($lineRaw);
        if ($line === '') {
            if ($mode === 'reason') {
                $reasonLines[] = '';
            } elseif ($mode === 'additional') {
                $additionalLines[] = '';
            }
            continue;
        }

        if (stripos($line, 'Name:') === 0) { $data['name'] = trim(substr($line, 5)); continue; }
        if (stripos($line, 'Age:') === 0) { $data['age'] = trim(substr($line, 4)); continue; }
        if (stripos($line, 'Occupation:') === 0) { $data['occupation'] = trim(substr($line, 11)); continue; }
        if (stripos($line, 'Contact:') === 0) { $data['contact'] = trim(substr($line, 8)); continue; }
        if (stripos($line, 'Living Type:') === 0) { $data['living_type'] = trim(substr($line, 12)); continue; }
        if (stripos($line, 'Outdoor Space:') === 0) { $data['outdoor_space'] = trim(substr($line, 14)); continue; }
        if (stripos($line, 'Pet Experience:') === 0) { $data['pet_experience'] = trim(substr($line, 15)); continue; }
        if (stripos($line, 'Experience Details:') === 0) { $data['experience_details'] = trim(substr($line, 19)); continue; }

        if (stripos($line, 'About Me:') === 0 && ($data['name'] === 'N/A' || $data['name'] === '')) {
            $data['name'] = trim(substr($line, 9));
            continue;
        }
        if (stripos($line, 'Why I want to adopt / living situation:') === 0) {
            $mode = 'reason';
            continue;
        }

        if (stripos($line, 'Why adopt this pet:') === 0) {
            $mode = 'reason';
            continue;
        }
        if (stripos($line, 'Additional Message:') === 0) {
            $mode = 'additional';
            continue;
        }

        if ($mode === 'reason') {
            $reasonLines[] = $line;
        } elseif ($mode === 'additional') {
            $additionalLines[] = $line;
        }
    }

    $reason = trim(implode("\n", $reasonLines));
    $additional = trim(implode("\n", $additionalLines));
    if ($reason !== '') { $data['adoption_reason'] = $reason; }
    if ($additional !== '') { $data['additional_message'] = $additional; }

    return $data;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost & Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="icon" type="image/png" href="../favicon.png" />
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .main-container { display: flex; align-items: flex-start; padding: 30px; gap: 30px; max-width: 1400px; margin: 0 auto; }
        .filter-sidebar {
            flex: 0 0 280px;
            width: 280px;
            min-width: 280px;
            max-width: 280px;
            background: white;
            padding: 25px;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            height: fit-content;
        }
        .content-area { flex: 1 1 auto; min-width: 0; }
        .pet-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .pet-card { position: relative; background: white; border: 1px solid #dee2e6; border-radius: 15px; padding: 15px; transition: 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .pet-card:hover { transform: translateY(-5px); }
        .pet-img-container img { width: 100%; height: 200px; object-fit: cover; border-radius: 10px; margin-bottom: 12px; }
        
        .status-tag { display: inline-block; padding: 4px 12px; border-radius: 6px; color: white; font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }
        .tag-lost { background-color: #ffebee; color: #c62828; }
        .tag-pending { background-color: #fff3e0; color: #e65100; }
        .tag-waiting { background-color: #dc3545; }
        .tag-found { background-color: #e8f5e9; color: #2e7d32; }
        .tag-adoption {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .tag-adopted { background-color: #198754; }
        .tag-rejected { background-color: #dc3545; }
        .tag-resolved { background-color: #6c757d; }
        .status-tag { border: 0 !important; box-shadow: none !important; line-height: 1.1; }
        
        .view-btn { width: 100%; margin-top: 15px; padding: 10px; border: none; border-radius: 8px; color: white; font-weight: 600; }
        #petDetailImage { width: 100%; height: 340px; object-fit: cover; border-radius: 10px; }
        .modal-content { border-radius: 20px; border: none; }
        #petModal .modal-dialog {
            width: min(92vw, 880px);
            max-width: 880px;
            margin-left: auto;
            margin-right: auto;
        }
        #petModal .modal-content {
            border-radius: 22px;
            border: none;
            background: #f8f9fb;
            padding: 18px !important;
            transform: translateY(38px);
            overflow: hidden;
        }
        #petModal .modal-header {
            border: 0;
            padding: 0 0 12px;
        }
        #petModal .modal-body { padding: 0; overflow-x: hidden; }
        #petModal .row {
            --bs-gutter-x: 0;
            margin-left: 0;
            margin-right: 0;
            align-items: stretch;
        }
        #petModal .row > [class*="col-"] { padding-left: 0; padding-right: 0; }
        #petModal .row > .col-md-6:last-child { padding-left: 18px; }
        #petModal #modalPetNameDisplay {
            font-size: clamp(1.8rem, 3.6vw, 3.2rem);
            line-height: 1.05;
            margin-bottom: 0.6rem;
        }
        #petModal .col-md-6 p { font-size: 0.92rem; margin-bottom: 0.7rem; }
        #petModal .col-md-6 p strong { font-size: 0.95rem; }
        #petModal #modalStatusBadge.tag-found { background-color: #198754; color: #fff; }
        #petModal hr { margin: 0.7rem 0 1rem; }
        #petModal #modalDescription { margin: 0.8rem 0 0.6rem; }
        #petModal #actionButtonContainer .alert,
        #petModal #actionButtonContainer .btn { border-radius: 8px; }
        @media (max-width: 767.98px) {
            #petModal .modal-content { padding: 16px !important; }
            #petDetailImage { height: 260px; margin-bottom: 10px; }
        }
        .adoption-action-group { display: flex; gap: 8px; flex-wrap: wrap; }
        .adoption-action-group .btn { min-width: 145px; }
        .status-pill { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #fff; }
        .status-pill-success { background: #198754; }
        .status-pill-danger { background: #dc3545; }
        .status-pill-warning { background: #fd7e14; }
        .bookmark-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 50%;
            background: #ffffff;
            color: #6c757d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            cursor: pointer;
            z-index: 3;
            transition: all 0.2s ease;
            padding: 0;
        }
        .bookmark-btn:hover { transform: translateY(-1px); color: #8e44ad; }
        .bookmark-btn.active { color: #8e44ad; background: #f6effc; }
        .bookmark-btn .bookmark-icon { font-size: 1rem; line-height: 1; pointer-events: none; }
        .bookmarked-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 28px 16px;
            border: 1px dashed #d0d7de;
            border-radius: 12px;
            color: #6c757d;
            background: #f8fafc;
        }
        .bookmarked-empty .icon {
            display: block;
            font-size: 1.4rem;
            margin-bottom: 6px;
            line-height: 1;
        }
        .adoption-form-popup { border-radius: 18px !important; padding: 0.25rem !important; }
        .adoption-form-title { color: #1e88ff !important; font-weight: 700 !important; }
        .adoption-form-card { border: 1px solid #dbe7ff; border-radius: 14px; padding: 12px; margin-bottom: 10px; background: #f9fbff; }
        .adoption-form-card h6 { margin-bottom: 10px; color: #1e88ff; font-weight: 700; font-size: 0.9rem; }
        .adoption-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .adoption-field,
        .adoption-select,
        .adoption-textarea {
            width: 100%;
            border: 1px solid #c9d9f7;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.9rem;
            background: #fff;
        }
        .adoption-textarea { min-height: 88px; resize: vertical; }
        .adoption-field:focus,
        .adoption-select:focus,
        .adoption-textarea:focus {
            border-color: #1e88ff;
            box-shadow: 0 0 0 0.15rem rgba(30,136,255,0.18);
            outline: none;
        }
        .adoption-field.is-invalid,
        .adoption-select.is-invalid,
        .adoption-textarea.is-invalid { border-color: #dc3545; }
        .adoption-counter { font-size: 0.75rem; color: #6c757d; text-align: right; margin-top: 2px; }
        .adoption-submit-btn {
            width: 100%;
            border: none;
            border-radius: 10px;
            padding: 11px;
            font-weight: 700;
            background: #1e88ff;
            color: #fff;
        }
        .adopt-cta-btn {
            background: #ffffff;
            border: 1px solid #198754;
            color: #198754;
        }
        .adopt-cta-btn:hover {
            background: #198754;
            color: #ffffff;
        }
        .adopt-state-btn[disabled] {
            opacity: 1 !important;
            filter: none !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            box-shadow: none !important;
        }
        .adopt-state-pending[disabled] {
            background: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }
        .adopt-state-declined[disabled] {
            background: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
        }
        .reward-badge {
            display: inline-block;
            margin-bottom: 8px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            background: #ffe8a1;
            color: #8a5a00;
        }
        .owner-requests-card {
            border: 1px solid #dbe5f0 !important;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        }
        .owner-requests-table {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .owner-requests-table table { margin-bottom: 0; }
        .owner-requests-table thead th {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            color: #334155;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            padding: 0.75rem 0.7rem;
            white-space: nowrap;
        }
        .owner-requests-table tbody td {
            border-top: 1px solid #eef2f7;
            vertical-align: middle;
            padding: 0.8rem 0.7rem;
        }
        .owner-requests-table tbody tr:hover { background: #fafcff; }
        .view-application-btn { border-radius: 8px; font-weight: 600; }
        .application-view-section {
            border: 1px solid #dbe5f0;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 10px;
            background: #f8fbff;
        }
        .application-view-section h6 {
            font-weight: 700;
            color: #1e88ff;
            margin-bottom: 8px;
            border-bottom: 1px solid #dbe5f0;
            padding-bottom: 5px;
        }
        .application-view-row {
            display: grid;
            grid-template-columns: 170px 1fr;
            gap: 8px;
            margin-bottom: 6px;
            word-break: break-word;
        }
        .application-view-row strong { color: #334155; }
        @media (max-width: 767px) {
            .application-view-row { grid-template-columns: 1fr; gap: 4px; }
        }
        .notifications-panel {
            max-height: 260px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 6px;
        }
        .notifications-panel ul { margin-bottom: 0; padding-left: 18px; }
        .notifications-panel li {
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.35;
        }
        .my-adoption-requests-card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .my-adoption-requests-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 14px;
        }
        .my-adoption-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }
        .my-adoption-item {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .my-adoption-thumb {
            width: 100%;
            height: 140px;
            border-radius: 10px;
            background: #f1f3f5;
            border: 1px dashed #ced4da;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 0.82rem;
            overflow: hidden;
        }
        .my-adoption-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .my-adoption-name {
            font-size: 1rem;
            font-weight: 700;
            color: #212529;
            margin: 0;
            line-height: 1.2;
        }
        .my-adoption-meta {
            font-size: 0.82rem;
            color: #6c757d;
            margin: 0;
        }
        .my-adoption-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            line-height: 1.2;
            width: fit-content;
            text-transform: uppercase;
        }
        .my-adoption-status-pending {
            background: #f6c343;
            color: #3d2f00;
        }
        .my-adoption-status-approved {
            background: #198754;
            color: #fff;
        }
        .my-adoption-status-rejected {
            background: #dc3545;
            color: #fff;
        }
        .my-adoption-empty {
            text-align: center;
            padding: 22px 14px;
            border: 1px dashed #d0d7de;
            border-radius: 12px;
            color: #6c757d;
            background: #f8fafc;
        }
        .my-adoption-empty .icon {
            font-size: 1.4rem;
            line-height: 1;
            margin-bottom: 6px;
            display: block;
        }
        @media (max-width: 767px) {
            .adoption-form-grid { grid-template-columns: 1fr; }
            .my-adoption-grid { grid-template-columns: 1fr; }
            .bookmark-btn {
                width: 44px;
                height: 44px;
                top: 10px;
                right: 10px;
            }
        }
        @media (min-width: 768px) and (max-width: 1024px) {
            .my-adoption-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 991px) {
            .main-container { flex-direction: column; padding: 16px; gap: 16px; }
            .filter-sidebar {
                flex: 1 1 auto;
                width: 100%;
                min-width: 0;
                max-width: 100%;
            }
            .content-area { width: 100%; }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main-container">
    <aside class="filter-sidebar">
        <h2 class="h4 text-primary fw-bold">Filter</h2>
        <form action="lost&found.php" method="GET">
            <div class="filter-group mb-3">
                <p class="fw-bold mb-2">Status</p>
                <?php $activeTypes = array_map('strtolower', $types); ?>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="type[]" value="lost" id="filterLost" <?php echo in_array('lost', $activeTypes) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filterLost">Lost</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="type[]" value="pending" id="filterPending" <?php echo in_array('pending', $activeTypes) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filterPending">Pending</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="type[]" value="found" id="filterFound" <?php echo in_array('found', $activeTypes) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filterFound">Found</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="type[]" value="for_adoption" id="filterAdoption" <?php echo in_array('for_adoption', $activeTypes) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filterAdoption">For Adoption</label>
                </div>
                <?php if ($current_user_id): ?>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="bookmarked" value="1" id="filterBookmarked" <?php echo $bookmarkedOnly ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filterBookmarked">Bookmarked</label>
                </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold">Apply Filters</button>
            <div class="text-center mt-2">
                <a href="lost&found.php" class="text-decoration-none small">Clear Filters</a>
            </div>
        </form>
    </aside>

    <main class="content-area">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Pet Listings</h1>
            <a href="postapet.php" class="btn btn-primary fw-bold">+ Post a Pet</a>
        </div>
        
        <div class="pet-grid">
            <?php if (empty($pets)): ?>
                <div class="col-12 text-center py-5">
                    <?php if ($bookmarkedOnly && $current_user_id): ?>
                        <div class="bookmarked-empty">
                            <span class="icon">🔖</span>
                            You haven't bookmarked any pets yet.
                        </div>
                    <?php else: ?>
                        <h5 class="text-muted">No pets found matching these filters.</h5>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($pets as $pet): ?>
                    <?php $statusMeta = petStatusMeta($pet['category']); ?>
                    <div class="pet-card animate__animated animate__fadeIn">
                        <?php $isBookmarked = !empty($bookmarkedPetIds[(int)$pet['pet_id']]); ?>
                        <button
                            class="bookmark-btn <?php echo $isBookmarked ? 'active bookmarked' : ''; ?>"
                            data-pet-id="<?php echo (int)$pet['pet_id']; ?>"
                            onclick="toggleBookmark(<?php echo (int)$pet['pet_id']; ?>, this)"
                            title="<?php echo $isBookmarked ? 'Remove bookmark' : 'Save bookmark'; ?>"
                            aria-label="<?php echo $isBookmarked ? 'Remove bookmark' : 'Save bookmark'; ?>">
                            <span class="bookmark-icon"><?php echo $isBookmarked ? '★' : '☆'; ?></span>
                        </button>
                        <div class="pet-img-container">
                            <img src="../<?php echo htmlspecialchars($pet['image_url']); ?>" alt="Pet">
                        </div>
                        <span class="status-tag <?php echo $statusMeta['class']; ?>">
                            <?php echo htmlspecialchars($statusMeta['label']); ?>
                        </span>
                        <div class="pet-name h5 fw-bold mb-1"><?php echo htmlspecialchars($pet['pet_name']); ?></div>
                        <?php
                            $rewardOffered = !empty($pet['reward_offered']) && (string)$pet['reward_offered'] !== '0';
                            $rewardDetails = trim((string)($pet['reward_details'] ?? ''));
                            $showReward = ($statusMeta['key'] === 'lost') && ($rewardOffered || $rewardDetails !== '');
                        ?>
                        <?php if ($showReward): ?>
                            <div class="reward-badge">
                                <?php echo $rewardDetails !== '' ? htmlspecialchars($rewardDetails) : 'With Reward'; ?>
                            </div>
                        <?php endif; ?>
                        <div class="text-muted small mb-3"><?php echo htmlspecialchars($pet['last_seen_location']); ?></div>
                        <button class="view-btn btn-primary" onclick='openPetModal(<?php echo json_encode($pet); ?>, <?php echo json_encode($current_user_id); ?>)'>
                            View Details
                        </button>
                        <?php if ($current_user_id): ?>
                            <?php if ($statusMeta['key'] === 'for_adoption'): ?>
                                <?php
                                    $petId = (int)$pet['pet_id'];
                                    $isOwner = ((int)$pet['user_id'] === (int)$current_user_id);
                                    $myReq = $myRequestsByPet[$petId] ?? null;
                                    $myReqStatus = $myReq['status'] ?? null;
                                ?>
                                <?php if (!$isOwner && $myReqStatus === null): ?>
                                    <button class="view-btn adopt-cta-btn mt-2" onclick="openAdoptModal(<?php echo (int)$pet['pet_id']; ?>, <?php echo (int)$pet['user_id']; ?>)">
                                        I Want to Adopt
                                    </button>
                                <?php elseif (!$isOwner): ?>
                                    <?php
                                        $stateClass = $myReqStatus === 'declined' ? 'adopt-state-declined' : ($myReqStatus === 'pending' ? 'adopt-state-pending' : 'btn-success');
                                    ?>
                                    <button class="view-btn adopt-state-btn <?php echo $stateClass; ?> mt-2" disabled>
                                        <?php echo $myReqStatus === 'approved' ? 'Approved' : ($myReqStatus === 'declined' ? 'Request Declined' : 'Request Sent'); ?>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="row mt-4 g-3">
            <div class="col-md-6">
                <div class="p-3 bg-white border rounded-3 h-100">
                    <h6 class="fw-bold text-primary">What to do if you find a lost pet</h6>
                    <p class="small text-muted mb-0">Check for tags, keep the pet safe, post clear photos, and contact the owner using verified channels.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-white border rounded-3 h-100">
                    <h6 class="fw-bold text-primary">How to report a stray</h6>
                    <p class="small text-muted mb-0">Provide barangay, date, behavior notes, and a photo to help nearby shelters and volunteers respond faster.</p>
                </div>
            </div>
        </div>
        <?php if ($current_user_id): ?>
            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="text-primary fw-bold">Adoption Notifications</h5>
                    <?php if (empty($adoptionNotifications)): ?>
                        <p class="small text-muted mb-0">No adoption notifications yet.</p>
                    <?php else: ?>
                        <div class="notifications-panel">
                            <ul class="small">
                                <?php foreach ($adoptionNotifications as $n): ?>
                                    <li class="mb-1"><?php echo htmlspecialchars($n['message']); ?> <span class="text-muted">(<?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($n['created_at']))); ?>)</span></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($current_user_id && !empty($ownerAdoptionRequests)): ?>
            <div class="card mt-4 owner-requests-card">
                <div class="card-body">
                    <h5 class="text-primary fw-bold">Adoption Requests For Your Pets</h5>
                    <div class="table-responsive owner-requests-table">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Pet</th>
                                    <th>Requester</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ownerAdoptionRequests as $req): ?>
                                    <?php
                                        $applicationData = parseAdoptionApplication(
                                            (string)($req['message'] ?? ''),
                                            (string)($req['adopter_name'] ?? ''),
                                            (string)($req['adopter_contact'] ?? '')
                                        );
                                        $applicationJson = htmlspecialchars(
                                            json_encode($applicationData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($req['pet_name']); ?></td>
                                        <td><?php echo htmlspecialchars($req['adopter_name'] ?: 'User'); ?><br><small class="text-muted"><?php echo htmlspecialchars($req['adopter_contact'] ?: 'No contact'); ?></small></td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-outline-primary btn-sm view-application-btn"
                                                data-application="<?php echo $applicationJson; ?>"
                                                onclick="openApplicationModal(this)">
                                                View Application
                                            </button>
                                        </td>
                                        <td>
                                            <?php
                                                $status = strtolower((string)$req['status']);
                                                $statusClass = $status === 'declined' ? 'status-pill-danger' : ($status === 'pending' ? 'status-pill-warning' : 'status-pill-success');
                                            ?>
                                            <span class="status-pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($req['status'] === 'pending'): ?>
                                                <div class="adoption-action-group">
                                                    <button class="btn btn-success btn-sm" onclick="ownerAdoptionAction(<?php echo (int)$req['response_id']; ?>, 'approve')">Confirm Adoption</button>
                                                    <button class="btn btn-danger btn-sm" onclick="ownerAdoptionAction(<?php echo (int)$req['response_id']; ?>, 'decline')">Disagree</button>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">Finalized</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($current_user_id): ?>
            <div class="card mt-4 border-0 shadow-sm my-adoption-requests-card">
                <div class="card-body">
                    <h5 class="text-primary fw-bold">My Adoption Requests</h5>
                    <div class="my-adoption-requests-subtitle">Status updates for requests you sent appear automatically here on reload.</div>
                    <?php if (empty($myRequestsByPet)): ?>
                        <div class="my-adoption-empty">
                            <span class="icon">📋</span>
                            You have no adoption requests yet.
                        </div>
                    <?php else: ?>
                        <div class="my-adoption-grid">
                            <?php foreach ($myRequestsByPet as $pid => $reqInfo): ?>
                                <?php
                                    $myStatus = strtolower((string)($reqInfo['status'] ?? 'pending'));
                                    $statusText = $myStatus === 'approved' ? 'Approved' : ($myStatus === 'declined' ? 'Rejected' : 'Pending');
                                    $myStatusClass = $myStatus === 'approved'
                                        ? 'my-adoption-status-approved'
                                        : ($myStatus === 'declined' ? 'my-adoption-status-rejected' : 'my-adoption-status-pending');
                                    $requestedDate = !empty($reqInfo['created_at']) ? date('M d, Y h:i A', strtotime((string)$reqInfo['created_at'])) : 'Not available';
                                    $breedTypeText = trim((string)($reqInfo['breed'] ?? ''));
                                    if ($breedTypeText === '') {
                                        $breedTypeText = trim((string)($reqInfo['pet_type'] ?? ''));
                                    }
                                    if ($breedTypeText === '') {
                                        $breedTypeText = 'N/A';
                                    }
                                ?>
                                <article class="my-adoption-item">
                                    <div class="my-adoption-thumb">
                                        <?php if (!empty($reqInfo['image_url'])): ?>
                                            <img src="../<?php echo htmlspecialchars((string)$reqInfo['image_url']); ?>" alt="<?php echo htmlspecialchars((string)($reqInfo['pet_name'] ?? 'Pet')); ?>">
                                        <?php else: ?>
                                            Pet image
                                        <?php endif; ?>
                                    </div>
                                    <h6 class="my-adoption-name"><?php echo htmlspecialchars((string)($reqInfo['pet_name'] ?? 'Pet')); ?></h6>
                                    <p class="my-adoption-meta">Breed / Type: <?php echo htmlspecialchars($breedTypeText); ?></p>
                                    <p class="my-adoption-meta"><strong>Date requested:</strong> <?php echo htmlspecialchars($requestedDate); ?></p>
                                    <span class="my-adoption-status <?php echo $myStatusClass; ?>"><?php echo htmlspecialchars($statusText); ?></span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($current_user_id): ?>
            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="text-primary fw-bold">My Submitted Lost &amp; Found Posts</h5>
                    <?php if (empty($mySubmittedPetPosts)): ?>
                        <p class="small text-muted mb-0">You have not submitted any pet posts yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Pet</th>
                                        <th>Current Status</th>
                                        <th>Submitted</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mySubmittedPetPosts as $myPost): ?>
                                        <?php
                                            $meta = petStatusMeta($myPost['category'] ?? '');
                                            $current = strtolower((string)($myPost['category'] ?? ''));
                                            $requested = strtolower((string)($myPost['requested_category'] ?? ''));
                                            $nextLive = $requested !== '' ? str_replace('_', ' ', strtoupper($requested)) : 'N/A';
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($myPost['pet_name'] ?? 'N/A') ?></td>
                                            <td><span class="status-tag <?= htmlspecialchars($meta['class']) ?>"><?= htmlspecialchars($meta['label']) ?></span></td>
                                            <td><?= !empty($myPost['created_at']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($myPost['created_at']))) : 'N/A' ?></td>
                                            <td class="small">
                                                <?php if ($current === 'waiting_approval'): ?>
                                                    <span class="text-danger fw-bold">Waiting for Approval</span><br>
                                                    <span class="text-muted">Will go live as: <?= htmlspecialchars($nextLive) ?></span>
                                                <?php elseif ($current === 'rejected'): ?>
                                                    <span class="text-secondary fw-bold">Rejected</span><br>
                                                    <span class="text-muted"><?= htmlspecialchars($myPost['verification_reason'] ?? 'No rejection reason provided.') ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">Live status is updated from admin review.</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<div class="modal fade" id="petModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-primary">Pet Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-0">
                    <div class="col-md-6">
                        <img id="petDetailImage" src="" alt="Pet" class="img-fluid rounded shadow-sm">
                    </div>
                    <div class="col-md-6">
                        <span id="modalStatusBadge" class="status-tag"></span>
                        <h2 id="modalPetNameDisplay" class="fw-bold"></h2>
                        <hr>
                        <p><strong>Breed:</strong> <span id="modalBreed"></span></p>
                        <p><strong>Location:</strong> <span id="modalLocation"></span></p>
                        <p id="modalRewardRow" class="d-none"><strong>Reward:</strong> <span id="modalReward"></span></p>
                        <p id="modalDescription" class="text-muted"></p>
                        <div class="p-3 bg-light rounded mt-3">
                            <strong>Contact:</strong> <span id="modalContact" class="text-primary fw-bold"></span>
                        </div>
                        <div id="actionButtonContainer" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="applicationViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary">Adoption Application Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="application-view-section">
                    <h6>Personal Information</h6>
                    <div class="application-view-row"><strong>Name</strong><span id="appName">N/A</span></div>
                    <div class="application-view-row"><strong>Age</strong><span id="appAge">N/A</span></div>
                    <div class="application-view-row"><strong>Occupation</strong><span id="appOccupation">N/A</span></div>
                    <div class="application-view-row"><strong>Contact Number</strong><span id="appContact">N/A</span></div>
                </div>

                <div class="application-view-section">
                    <h6>Living Situation</h6>
                    <div class="application-view-row"><strong>Living Type</strong><span id="appLivingType">N/A</span></div>
                    <div class="application-view-row"><strong>Outdoor Space</strong><span id="appOutdoorSpace">N/A</span></div>
                </div>

                <div class="application-view-section">
                    <h6>Pet Experience</h6>
                    <div class="application-view-row"><strong>Has Owned Pets Before</strong><span id="appPetExperience">N/A</span></div>
                    <div class="application-view-row"><strong>Experience Details</strong><span id="appExperienceDetails">N/A</span></div>
                </div>

                <div class="application-view-section">
                    <h6>Adoption Reason</h6>
                    <div class="application-view-row"><strong>Why They Want to Adopt This Pet</strong><span id="appAdoptionReason">N/A</span></div>
                </div>

                <div class="application-view-section mb-0">
                    <h6>Additional Message</h6>
                    <div class="application-view-row"><strong>Additional Message</strong><span id="appAdditionalMessage">N/A</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentPet = null;
let currentUserId = null;
let currentAdoptPetId = null;
const myRequestsByPet = <?php echo json_encode($myRequestsByPet); ?>;
const foundReportsByPet = <?php echo json_encode($foundReportsByPet); ?>;

function escapeModalText(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function openPetModal(pet, userId) {
    currentPet = pet;
    currentUserId = userId;
    
    document.getElementById('modalPetNameDisplay').innerText = pet.pet_name;
    document.getElementById('petDetailImage').src = "../" + pet.image_url;
    document.getElementById('modalBreed').innerText = pet.breed || 'Unknown';
    document.getElementById('modalLocation').innerText = pet.last_seen_location;
    const rewardRow = document.getElementById('modalRewardRow');
    const rewardText = document.getElementById('modalReward');
    const hasReward = ((String(pet.reward_offered || '') !== '' && String(pet.reward_offered) !== '0') || (pet.reward_details && String(pet.reward_details).trim() !== ''))
        && (String(pet.category || '').toLowerCase().replace(/[\s-]+/g, '_') === 'lost');
    if (rewardRow && rewardText) {
        if (hasReward) {
            rewardText.innerText = (pet.reward_details && String(pet.reward_details).trim() !== '') ? pet.reward_details : 'With Reward';
            rewardRow.classList.remove('d-none');
        } else {
            rewardText.innerText = '';
            rewardRow.classList.add('d-none');
        }
    }
    document.getElementById('modalDescription').innerText = pet.description || '';
    document.getElementById('modalContact').innerText = pet.contact_number;
    
    renderActionButtons();
    new bootstrap.Modal(document.getElementById('petModal')).show();
}

function renderActionButtons() {
    const container = document.getElementById('actionButtonContainer');
    const badge = document.getElementById('modalStatusBadge');
    const cat = (currentPet.category || '').toLowerCase().replace(/[\s-]+/g, '_');
    const isOwner = (currentPet.user_id == currentUserId);

    const statusLabelMap = {
        lost: 'LOST PET',
        found: 'FOUND PET',
        for_adoption: 'FOR ADOPTION',
        pending: 'PENDING',
        adopted: 'ADOPTED'
    };
    const statusClassMap = {
        lost: 'tag-lost',
        found: 'tag-found',
        for_adoption: 'tag-adoption',
        pending: 'tag-pending',
        adopted: 'tag-adopted'
    };
    badge.innerText = statusLabelMap[cat] || currentPet.category;
    badge.className = `status-tag ${statusClassMap[cat] || 'tag-pending'}`;
    container.innerHTML = '';

    if (cat === 'lost') {
        if (!isOwner) {
            container.innerHTML = `<button class="alert alert-danger w-100 fw-bold" onclick="handleUpdate('to_pending')">I Have Found This Pet</button>`;
        } else {
            container.innerHTML = `<div class="alert alert-success py-2 text-center small"><strong>Your post is active. Hope you find your pet.</strong></div>`;
        }
    } 
    else if (cat === 'pending') {
        if (isOwner) {
            const report = foundReportsByPet[String(currentPet.pet_id)] || null;
            const finderSummary = report
                ? `<div class="small text-muted mb-2">Latest report from <strong>${escapeModalText(report.finder_name || 'Finder')}</strong> (${escapeModalText(report.contact_number || 'No contact provided')})</div>`
                : `<div class="small text-muted mb-2">No pending finder report details are available yet.</div>`;
            container.innerHTML = `
                <div class="alert alert-info py-2 text-center small mb-2">Someone reported they found this pet!</div>
                ${finderSummary}
                <button class="btn btn-success w-100 fw-bold" onclick="handleUpdate('confirm_found')">This Is My Pet — Confirm Found</button>
            `;
        } else {
            container.innerHTML = `<button class="alert alert-warning w-100 fw-bold" disabled>Pending Verification</button>`;
        }
    }
    else if (cat === 'found') {
        container.innerHTML = `<div class="alert alert-success py-2 text-center fw-bold">Reunited with owner! ❤️</div>`;
    } else if (cat === 'adopted') {
        container.innerHTML = `<div class="alert alert-success py-2 text-center fw-bold">This pet has already been adopted.</div>`;
    } else if (cat === 'for_adoption') {
        if (!isOwner) {
            const req = myRequestsByPet[String(currentPet.pet_id)] || null;
            if (req && req.status) {
                const label = req.status === 'approved' ? 'Approved' : (req.status === 'declined' ? 'Request Declined' : 'Request Sent');
                const stateClass = req.status === 'declined' ? 'adopt-state-declined' : (req.status === 'pending' ? 'adopt-state-pending' : 'btn-success');
                container.innerHTML = `<button class="btn adopt-state-btn ${stateClass} w-100 fw-bold" disabled>${label}</button>`;
            } else {
                container.innerHTML = `<button class="btn adopt-cta-btn w-100 fw-bold" onclick="openAdoptModal(${currentPet.pet_id}, ${currentPet.user_id})">I Want to Adopt</button>`;
            }
        } else {
            container.innerHTML = `<div class="alert alert-primary py-2 text-center fw-bold">Your pet is listed for adoption.</div>`;
        }
    }
}

function handleUpdate(actionType) {
    const isConfirmFound = actionType === 'confirm_found';
    if (!isConfirmFound) {
        const openFoundReportDialog = () => Swal.fire({
            title: 'Report Found Pet',
            html: `
                <div class="text-start">
                    <label class="form-label fw-bold small">Where was the pet found?</label>
                    <input type="text" id="foundLocationInput" class="form-control mb-2" placeholder="Exact location found">
                    <label class="form-label fw-bold small">Contact number</label>
                    <input type="text" id="foundContactInput" class="form-control mb-2" placeholder="Your contact number">
                    <label class="form-label fw-bold small">Optional photo</label>
                    <input type="file" id="foundPhotoInput" class="form-control" accept=".jpg,.jpeg,.png">
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#e65100',
            confirmButtonText: 'Submit Found Report',
            preConfirm: () => {
                const locationVal = (document.getElementById('foundLocationInput')?.value || '').trim();
                const contactVal = (document.getElementById('foundContactInput')?.value || '').trim();
                const photoFile = document.getElementById('foundPhotoInput')?.files?.[0] || null;
                if (!locationVal || !contactVal) {
                    Swal.showValidationMessage('Please provide where you found the pet and your contact number.');
                    return false;
                }
                return { locationVal, contactVal, photoFile };
            }
        }).then((result) => {
            if (!result.isConfirmed) return;
            const formData = new FormData();
            formData.append('update_status', '1');
            formData.append('pet_id', currentPet.pet_id);
            formData.append('action', actionType);
            formData.append('location_found', result.value.locationVal);
            formData.append('contact_number', result.value.contactVal);
            if (result.value.photoFile) {
                formData.append('found_photo', result.value.photoFile);
            }
            fetch(window.location.href, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Updated!', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.error, 'error');
                    }
                });
        });

        // Prevent Bootstrap modal focus trap from blocking typing inside SweetAlert inputs.
        const petModalEl = document.getElementById('petModal');
        const petModalInstance = petModalEl ? bootstrap.Modal.getInstance(petModalEl) : null;
        if (petModalEl && petModalInstance) {
            petModalEl.addEventListener('hidden.bs.modal', () => {
                openFoundReportDialog();
            }, { once: true });
            petModalInstance.hide();
        } else {
            openFoundReportDialog();
        }
        return;
    }

    const report = foundReportsByPet[String(currentPet.pet_id)] || null;
    if (!report || !report.id) {
        Swal.fire('Unable to confirm', 'No pending finder report is available for this pet yet.', 'warning');
        return;
    }

    const reportPhotoHtml = report.photo
        ? `<div class="report-detail mb-2" style="background:#fff;border:1px solid #e7eefb;border-radius:10px;padding:10px 12px;"><span class="label d-block fw-bold">🖼️ Photo of found pet</span><img src="../${escapeModalText(report.photo)}" alt="Found pet photo" style="max-width:100%;border-radius:8px;margin-top:8px;"></div>`
        : '';
    const finderMessage = (report.message || '').trim();
    const finderMessageHtml = finderMessage !== ''
        ? `<div class="report-detail mb-2" style="background:#fff;border:1px solid #e7eefb;border-radius:10px;padding:10px 12px;"><span class="label d-block fw-bold">💬 Message from finder</span><span class="value">${escapeModalText(finderMessage)}</span></div>`
        : `<div class="report-detail mb-2" style="background:#fff;border:1px solid #e7eefb;border-radius:10px;padding:10px 12px;"><span class="label d-block fw-bold">💬 Message from finder</span><span class="value text-muted">No additional message provided.</span></div>`;
    const reportedAt = report.created_at
        ? new Date(report.created_at.replace(' ', 'T')).toLocaleString()
        : 'N/A';

    const openOwnerVerificationDialog = () => Swal.fire({
        title: 'Someone Found Your Pet!',
        width: window.innerWidth <= 768 ? '95vw' : (window.innerWidth <= 1024 ? '80vw' : 760),
        html: `
            <div class="text-start">
                <div class="finder-report-section" style="border:1px solid #dbe8ff;border-radius:14px;padding:14px;background:linear-gradient(180deg,#f8fbff 0%,#f2f7ff 100%);">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
                        <h6 class="fw-bold mb-0" style="color:#0d47a1;">📋 Finder's Report</h6>
                        <span class="badge rounded-pill text-bg-primary">${escapeModalText(report.finder_name || 'Finder')}</span>
                    </div>
                    <div class="report-detail mb-2" style="background:#fff;border:1px solid #e7eefb;border-radius:10px;padding:10px 12px;"><span class="label d-block fw-bold">📍 Where found</span><span class="value">${escapeModalText(report.location_found || 'N/A')}</span></div>
                    <div class="report-detail mb-2" style="background:#fff;border:1px solid #e7eefb;border-radius:10px;padding:10px 12px;"><span class="label d-block fw-bold">📞 Finder's contact number</span><span class="value">${escapeModalText(report.contact_number || 'N/A')}</span></div>
                    ${finderMessageHtml}
                    ${reportPhotoHtml}
                    <div class="report-detail mb-0" style="background:#fff;border:1px solid #e7eefb;border-radius:10px;padding:10px 12px;"><span class="label d-block fw-bold">🕐 Reported at</span><span class="value">${escapeModalText(reportedAt)}</span></div>
                </div>
                <p class="small text-muted mt-3 mb-0">Please review the report details above before confirming your pet as found.</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: '✓ Confirm — This Is My Pet'
    }).then((result) => {
        if (!result.isConfirmed) return;
        const formData = new FormData();
        formData.append('update_status', '1');
        formData.append('pet_id', currentPet.pet_id);
        formData.append('action', actionType);
        formData.append('found_report_id', String(report.id));

        fetch(window.location.href, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Confirmed',
                        text: data.message,
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'lost&found.php?type[]=found';
                    });
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            });
    });

    const petModalEl = document.getElementById('petModal');
    const petModalInstance = petModalEl ? bootstrap.Modal.getInstance(petModalEl) : null;
    if (petModalEl && petModalInstance) {
        petModalEl.addEventListener('hidden.bs.modal', () => {
            openOwnerVerificationDialog();
        }, { once: true });
        petModalInstance.hide();
    } else {
        openOwnerVerificationDialog();
    }
}

function toggleBookmark(petId, btnEl) {
    if (!<?php echo $current_user_id ? 'true' : 'false'; ?>) {
        Swal.fire({
            icon: 'info',
            title: 'Login required',
            text: 'Please log in to bookmark pets'
        }).then(() => {
            window.location.href = '../signIn.php';
        });
        return;
    }

    const formData = new FormData();
    formData.append('bookmark_action', 'toggle');
    formData.append('pet_id', petId);
    fetch(window.location.href, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (btnEl) {
                    const isSaved = data.status === 'saved';
                    btnEl.classList.toggle('active', isSaved);
                    btnEl.classList.toggle('bookmarked', isSaved);
                    btnEl.title = isSaved ? 'Remove bookmark' : 'Save bookmark';
                    btnEl.setAttribute('aria-label', isSaved ? 'Remove bookmark' : 'Save bookmark');
                    const iconEl = btnEl.querySelector('.bookmark-icon');
                    if (iconEl) iconEl.textContent = isSaved ? '★' : '☆';
                }
                Swal.fire('Saved', data.status === 'saved' ? 'Post bookmarked.' : 'Bookmark removed.', 'success');
            } else {
                Swal.fire('Notice', data.error || 'Bookmark is currently unavailable.', 'info');
            }
        });
}

function openAdoptModal(petId, ownerUserId) {
    const minReasonLength = 40;
    const defaultName = <?php echo json_encode($currentUserName); ?>;
    const defaultContact = <?php echo json_encode($currentUserContact); ?>;
    const defaultAge = <?php echo json_encode($currentUserAge); ?>;
    const defaultOccupation = <?php echo json_encode($currentUserOccupation); ?>;
    currentAdoptPetId = petId;
    Swal.fire({
        title: 'Adoption Request Form',
        customClass: {
            popup: 'adoption-form-popup',
            title: 'adoption-form-title',
            confirmButton: 'adoption-submit-btn'
        },
        width: 760,
        html: `
            <div class="text-start">
                <div class="adoption-form-card">
                    <h6>Personal Information</h6>
                    <div class="adoption-form-grid">
                        <input id="adoptFullNameInput" class="adoption-field" placeholder="Full Name" value="${defaultName || ''}">
                        <input id="adoptAgeInput" class="adoption-field" type="number" min="1" placeholder="Age" value="${defaultAge || ''}">
                        <input id="adoptOccupationInput" class="adoption-field" placeholder="Occupation" value="${defaultOccupation || ''}">
                        <input id="adoptContactInput" class="adoption-field" placeholder="Contact Number" value="${defaultContact || ''}">
                    </div>
                </div>

                <div class="adoption-form-card">
                    <h6>Living Situation</h6>
                    <div class="adoption-form-grid">
                        <select id="adoptLivingTypeInput" class="adoption-select">
                            <option value="">Select home type</option>
                            <option value="House">House</option>
                            <option value="Apartment">Apartment</option>
                            <option value="Condo">Condo</option>
                            <option value="Other">Other</option>
                        </select>
                        <input id="adoptOutdoorInput" class="adoption-field" placeholder="Do you have outdoor space for the pet?">
                    </div>
                </div>

                <div class="adoption-form-card">
                    <h6>Pet Experience</h6>
                    <div class="adoption-form-grid">
                        <select id="adoptExperienceSelect" class="adoption-select">
                            <option value="">Have you owned a pet before?</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                            <option value="Currently Have Pets">Currently Have Pets</option>
                        </select>
                        <textarea id="adoptExperienceInput" class="adoption-textarea" placeholder="Briefly describe your experience with pets."></textarea>
                    </div>
                </div>

                <div class="adoption-form-card">
                    <h6>Why Do You Want to Adopt This Pet?</h6>
                    <textarea id="adoptReasonInput" class="adoption-textarea" placeholder="Tell the owner why you want to adopt this pet and how you plan to care for it responsibly."></textarea>
                    <div id="adoptReasonCounter" class="adoption-counter">0 / ${minReasonLength} minimum characters</div>
                </div>

                <div class="adoption-form-card">
                    <h6>Additional Message</h6>
                    <textarea id="adoptMessageInput" class="adoption-textarea" placeholder="Any additional details you want to share with the owner."></textarea>
                </div>

                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="adoptAgreementInput">
                    <label class="form-check-label small" for="adoptAgreementInput">
                        I confirm that all the information I provided is accurate and I am committed to providing a safe and loving home for this pet.
                    </label>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Adoption Request',
        didOpen: () => {
            const reasonInput = document.getElementById('adoptReasonInput');
            const counter = document.getElementById('adoptReasonCounter');
            if (reasonInput && counter) {
                const updateCount = () => {
                    const len = reasonInput.value.trim().length;
                    counter.textContent = `${len} / ${minReasonLength} minimum characters`;
                    counter.style.color = len >= minReasonLength ? '#198754' : '#6c757d';
                };
                reasonInput.addEventListener('input', updateCount);
                updateCount();
            }
        },
        preConfirm: () => {
            const fullName = document.getElementById('adoptFullNameInput');
            const age = document.getElementById('adoptAgeInput');
            const occupation = document.getElementById('adoptOccupationInput');
            const contact = document.getElementById('adoptContactInput');
            const livingType = document.getElementById('adoptLivingTypeInput');
            const outdoorSpace = document.getElementById('adoptOutdoorInput');
            const petExperience = document.getElementById('adoptExperienceSelect');
            const experienceDetails = document.getElementById('adoptExperienceInput');
            const adoptionReason = document.getElementById('adoptReasonInput');
            const message = document.getElementById('adoptMessageInput');
            const agreement = document.getElementById('adoptAgreementInput');

            const controls = [fullName, contact, livingType, petExperience, adoptionReason];
            controls.forEach((el) => el && el.classList.remove('is-invalid'));

            let hasError = false;
            if (!fullName.value.trim()) { fullName.classList.add('is-invalid'); hasError = true; }
            if (!contact.value.trim()) { contact.classList.add('is-invalid'); hasError = true; }
            if (!livingType.value.trim()) { livingType.classList.add('is-invalid'); hasError = true; }
            if (!petExperience.value.trim()) { petExperience.classList.add('is-invalid'); hasError = true; }
            if (adoptionReason.value.trim().length < minReasonLength) { adoptionReason.classList.add('is-invalid'); hasError = true; }

            if (hasError) {
                Swal.showValidationMessage('Please complete all required fields. Adoption reason must be at least 40 characters.');
                return false;
            }
            if (!agreement.checked) {
                Swal.showValidationMessage('Please confirm the adoption agreement first.');
                return false;
            }

            return {
                fullName: fullName.value.trim(),
                age: age.value.trim(),
                occupation: occupation.value.trim(),
                contact: contact.value.trim(),
                livingType: livingType.value.trim(),
                outdoorSpace: outdoorSpace.value.trim(),
                petExperience: petExperience.value.trim(),
                experienceDetails: experienceDetails.value.trim(),
                adoptionReason: adoptionReason.value.trim(),
                message: message.value.trim(),
                agreement: agreement.checked ? '1' : '0'
            };
        }
    }).then((result) => {
        if (!result.isConfirmed) return;
        const formData = new FormData();
        formData.append('adopt_inquiry_action', '1');
        formData.append('pet_id', currentAdoptPetId);
        formData.append('owner_user_id', ownerUserId);
        formData.append('message', result.value.message);
        formData.append('full_name', result.value.fullName);
        formData.append('age', result.value.age);
        formData.append('occupation', result.value.occupation);
        formData.append('contact_number', result.value.contact);
        formData.append('living_type', result.value.livingType);
        formData.append('outdoor_space', result.value.outdoorSpace);
        formData.append('pet_experience', result.value.petExperience);
        formData.append('experience_details', result.value.experienceDetails);
        formData.append('adoption_reason', result.value.adoptionReason);
        formData.append('agreement_confirm', result.value.agreement);
        fetch(window.location.href, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) Swal.fire('Sent', data.message, 'success');
                else Swal.fire('Notice', data.error || 'Unable to send inquiry.', 'info');
            });
    });
}

function ownerAdoptionAction(responseId, decision) {
    const submitAction = (declineReason = '') => {
        const formData = new FormData();
        formData.append('adoption_owner_action', '1');
        formData.append('response_id', responseId);
        formData.append('decision', decision);
        if (declineReason) formData.append('decline_reason', declineReason);
        fetch(window.location.href, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Updated', 'Adoption request status updated.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.error || 'Action failed.', 'error');
                }
            });
    };

    if (decision === 'decline') {
        Swal.fire({
            title: 'Disagree with this request',
            input: 'textarea',
            inputLabel: 'Reason (required)',
            inputPlaceholder: 'Explain why you are declining this adoption request...',
            showCancelButton: true,
            preConfirm: (reason) => {
                if (!reason || !reason.trim()) {
                    Swal.showValidationMessage('Reason is required.');
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) submitAction(result.value.trim());
        });
    } else {
        submitAction();
    }
}

function openApplicationModal(button) {
    let data = {};
    try {
        data = JSON.parse(button.getAttribute('data-application') || '{}');
    } catch (e) {
        data = {};
    }

    const setValue = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = (value && String(value).trim() !== '') ? String(value) : 'N/A';
    };

    setValue('appName', data.name);
    setValue('appAge', data.age);
    setValue('appOccupation', data.occupation);
    setValue('appContact', data.contact);
    setValue('appLivingType', data.living_type);
    setValue('appOutdoorSpace', data.outdoor_space);
    setValue('appPetExperience', data.pet_experience);
    setValue('appExperienceDetails', data.experience_details);
    setValue('appAdoptionReason', data.adoption_reason);
    setValue('appAdditionalMessage', data.additional_message);

    const modalEl = document.getElementById('applicationViewModal');
    if (!modalEl) return;
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}
</script>

</body>
</html>