<?php
session_start();

require_once '../config.php';

$current_user_id = $_SESSION['user_id'] ?? 1;

function uploadPetPhoto($fileField, $defaultPath = "uploads/default_pet.png")
{
    if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] !== 0) {
        return $defaultPath;
    }

    $upload_directory = "../uploads/";
    if (!is_dir($upload_directory)) {
        mkdir($upload_directory, 0777, true);
    }

    $file_name = $_FILES[$fileField]['name'];
    $unique_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
    $destination = $upload_directory . $unique_name;

    if (!move_uploaded_file($_FILES[$fileField]['tmp_name'], $destination)) {
        throw new Exception("Failed to upload file.");
    }

    return "uploads/" . $unique_name;
}

function tableColumns(mysqli $conn, $tableName)
{
    $safeTable = $conn->real_escape_string($tableName);
    $columns = [];
    $res = $conn->query("SHOW COLUMNS FROM `{$safeTable}`");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }
    return $columns;
}

if (isset($_POST['action']) && $_POST['action'] === 'save_pet') {
    header('Content-Type: application/json');
    try {
        if (empty($_POST['guidelines_agree'])) {
            throw new Exception("You must agree to the Community Guidelines.");
        }

        $petName = trim($_POST['pet_name'] ?? '');
        $status = strtolower(trim($_POST['status'] ?? 'lost'));
        $requestedCategory = in_array($status, ['lost', 'found', 'for_adoption'], true) ? $status : 'lost';
        $petType = trim($_POST['pet_type'] ?? 'Dog');
        $breed = trim($_POST['breed'] ?? 'Unknown');
        $size = trim($_POST['size'] ?? '');
        $color = trim($_POST['pet_color'] ?? '');
        $foundDate = trim($_POST['last_seen_date'] ?? '');
        $barangay = trim($_POST['barangay'] ?? '');
        $adoptionBarangay = trim($_POST['adoption_barangay'] ?? '');
        $adoptionReason = trim($_POST['adoption_reason'] ?? '');
        $adoptionRequirements = trim($_POST['adoption_requirements'] ?? '');
        $rewardOffered = isset($_POST['reward_offered']) ? '1' : '0';
        $rewardDetails = trim($_POST['reward_details'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $lat = trim($_POST['lat'] ?? '');
        $lng = trim($_POST['lng'] ?? '');
        $location = trim($_POST['location'] ?? ($status === 'for_adoption' ? $adoptionBarangay : $barangay));
        if ($status === 'for_adoption') {
            $barangay = $adoptionBarangay !== '' ? $adoptionBarangay : $barangay;
            if ($description === '') {
                $description = $adoptionReason;
            } elseif ($adoptionReason !== '' || $adoptionRequirements !== '') {
                $description .= "\n\nAdoption Info:\n" . $adoptionReason . "\nRequirements: " . $adoptionRequirements;
            }
        }
        if ($status !== 'lost') {
            $rewardOffered = '0';
            $rewardDetails = '';
        } elseif ($rewardOffered === '1' && $rewardDetails === '') {
            throw new Exception("Please enter reward details or amount.");
        }

        if ($petName === '' || $barangay === '' || $contact === '') {
            throw new Exception("Please complete all required fields.");
        }

        $publicImagePath = uploadPetPhoto("pet_image");
        $ownerImagePath = uploadPetPhoto("owner_pet_image", "");
        $existingColumns = tableColumns($conn, 'pets');

        $data = [
            'user_id' => $current_user_id,
            'pet_name' => $petName,
            // All new Lost/Found/Adoption posts must pass admin review first.
            'category' => 'waiting_approval',
            'requested_category' => $requestedCategory,
            'gender' => 'Unknown',
            'breed' => $breed !== '' ? $breed : $petType,
            'color' => $color,
            'last_seen_location' => $location,
            'description' => $description,
            'image_url' => $publicImagePath,
            'contact_number' => $contact,
            'created_at' => date('Y-m-d H:i:s'),
            'pet_type' => $petType,
            'size' => $size,
            'last_seen_date' => $foundDate,
            'last_seen_barangay' => $barangay,
            'latitude' => $lat !== '' ? $lat : null,
            'longitude' => $lng !== '' ? $lng : null,
            'owner_with_pet_image_url' => $ownerImagePath,
            'verification_status' => 'pending',
            'guidelines_accepted' => 1
            ,'adoption_reason' => $adoptionReason
            ,'adoption_requirements' => $adoptionRequirements
            ,'reward_offered' => $rewardOffered
            ,'reward_details' => $rewardDetails
        ];

        $insertCols = [];
        $insertVals = [];
        foreach ($data as $col => $val) {
            if (in_array($col, $existingColumns, true)) {
                $insertCols[] = "`$col`";
                if ($val === null) {
                    $insertVals[] = "NULL";
                } else {
                    $insertVals[] = "'" . $conn->real_escape_string((string)$val) . "'";
                }
            }
        }

        if (empty($insertCols)) {
            throw new Exception("Pets table is missing required columns.");
        }

        $sql = "INSERT INTO pets (" . implode(", ", $insertCols) . ") VALUES (" . implode(", ", $insertVals) . ")";
        if (!$conn->query($sql)) {
            throw new Exception($conn->error);
        }

        $statusLabel = $requestedCategory === 'for_adoption' ? 'for adoption' : ($requestedCategory === 'found' ? 'found pet' : 'lost pet');
        echo json_encode([
            'success' => true,
            'message' => 'Your ' . $statusLabel . ' post has been submitted and is currently under review. It will be visible to the public once approved by our team.'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Pet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../css/styles.css">
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .form-container-custom { 
            background: #e9f2ff; border-radius: 20px; padding: 30px; 
            max-width: 900px; margin: 50px auto; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        .image-box { 
            width: 100%; height: 250px; border: 2px dashed #aac; background: #fff; 
            border-radius: 15px; display: flex; flex-direction: column; 
            align-items: center; justify-content: center; cursor: pointer; overflow: hidden;
        }
        .image-box img { width: 100%; height: 100%; object-fit: cover; }
        #otherTypeContainer { display: none; }
        #lost-found-map { height: 300px; border-radius: 12px; border: 1px solid #ddd; }
        
        /* Ensures all form inputs and selects are identical in height */
        .form-control, .form-select {
            height: 45px; 
            border-radius: 8px;
        }

        .btn-post { background-color: #ffc107; border: none; font-weight: bold; padding: 12px; border-radius: 10px; transition: 0.3s; color: #000; }
        .btn-post:hover { background-color: #e0ac00; transform: translateY(-1px); }
        .btn-close-custom { position: absolute; top: 20px; right: 20px; text-decoration: none; color: #333; font-size: 24px; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <div class="form-container-custom animate__animated animate__fadeInDown">
        <a href="lost&found.php" class="btn-close-custom">×</a>
        <h2 class="fw-bold text-primary mb-4">Post Missing/Found Pet</h2>

        <form id="addPetForm">
            <div class="row">
                <div class="col-md-4">
                    <div class="image-box" onclick="document.getElementById('petImageInput').click();">
                        <span id="uploadPlaceholder">📸<br>Click to Upload Image</span>
                        <img id="imgPreview" style="display:none;">
                    </div>
                    <input type="file" name="pet_image" id="petImageInput" style="display:none;" accept="image/*" required onchange="previewImage(this)">
                    <p class="text-muted small text-center mt-2">Upload a clear photo</p>
                </div>

                <div class="col-md-8">
                    <input type="hidden" name="action" value="save_pet">
                    
                    <div class="mb-3">
                        <label class="fw-bold small">Pet Name</label>
                        <input type="text" name="pet_name" class="form-control shadow-sm" placeholder="e.g. Otlum" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small">Status</label>
                        <select name="status" id="status-select" class="form-select shadow-sm" required>
                            <option value="lost">Lost Pet</option>
                            <option value="found">Found Pet</option>
                            <option value="for_adoption">For Adoption</option>
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold small">Pet Type</label>
                            <select name="pet_type" class="form-select shadow-sm">
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                                <option value="Bird">Bird</option>
                                <option value="Rabbit">Rabbit</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold small">Size</label>
                            <select name="size" class="form-select shadow-sm">
                                <option value="Small">Small</option>
                                <option value="Medium">Medium</option>
                                <option value="Large">Large</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold small">Breed</label>
                            <input type="text" name="breed" class="form-control shadow-sm" placeholder="e.g., Shih Tzu, Aspin, Ragdoll..." required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small">Color / Markings</label>
                        <input type="text" name="pet_color" class="form-control shadow-sm" placeholder="e.g., white with brown spots, red collar..." required>
                    </div>

                    <div class="row g-2" id="lost-found-fields">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold small">Last Seen / Found Date</label>
                            <input type="date" name="last_seen_date" class="form-control shadow-sm" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold small">Last Seen Barangay</label>
                            <select id="barangay-select" name="barangay" class="form-select shadow-sm" required></select>
                        </div>
                    </div>

                    <div class="mb-3" id="reward-field-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rewardOffered" name="reward_offered" value="1">
                            <label class="form-check-label fw-bold small" for="rewardOffered">I am offering a reward</label>
                        </div>
                        <div class="mt-2 d-none" id="reward-input-wrap">
                            <input type="text" id="rewardDetails" name="reward_details" class="form-control shadow-sm" placeholder="e.g., ₱500 cash reward or Any amount as a token of gratitude">
                        </div>
                    </div>

                    <input type="hidden" name="lat" id="lat">
                    <input type="hidden" name="lng" id="lng">
                    <input type="hidden" name="location" id="location_text">

                    <div class="row g-2 d-none" id="adoption-fields">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold small">Adoption Barangay</label>
                            <select id="adoption-barangay-select" name="adoption_barangay" class="form-select shadow-sm"></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold small">Adoption Reason</label>
                            <input type="text" name="adoption_reason" class="form-control shadow-sm" placeholder="e.g., Relocation, rescue rehoming, cannot provide enough care">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="fw-bold small">Adoption Requirements</label>
                            <textarea name="adoption_requirements" class="form-control shadow-sm" rows="2" placeholder="e.g., Must have a secure home, vet references, and regular updates for first month"></textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small" id="map-label">Pinpoint on Map</label>
                        <div id="lost-found-map"></div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small">Contact Number</label>
                        <input type="tel" name="contact" class="form-control shadow-sm" placeholder="e.g., 09123456789" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small">Description</label>
                        <textarea name="description" class="form-control shadow-sm" rows="3" placeholder="Describe your pet's appearance, behavior, and any identifying features that could help with identification..."></textarea>
                    </div>

                    <div class="mb-3" id="owner-verification-block">
                        <label class="fw-bold small">Photo with Owner – For Verification (Admin Only)</label>
                        <div class="image-box" onclick="document.getElementById('ownerPetImageInput').click();">
                            <span id="ownerUploadPlaceholder">📸<br>Click to Upload Verification Photo</span>
                            <img id="ownerImgPreview" style="display:none;">
                        </div>
                        <input type="file" name="owner_pet_image" id="ownerPetImageInput" style="display:none;" accept="image/*" onchange="previewImage(this, 'ownerImgPreview', 'ownerUploadPlaceholder')">
                        <p class="text-muted small text-center mt-2">This photo is only shown in admin verification.</p>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="guidelinesAgree" name="guidelines_agree" required>
                        <label class="form-check-label small" for="guidelinesAgree">
                            I agree to the <a href="../pages/community-guidelines.php" target="_blank" rel="noopener noreferrer">Community Guidelines</a>.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-success w-100" id="submitBtn">
                        <span id="btnText">Post Now</span>
                        <span id="btnSpinner" class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../script/barangay-coords.js"></script>
<script src="../script/map-utils.js"></script>
<script src="../script/barangay-dropdown.js"></script>
<script>
    let lostFoundMap;
    let lostFoundMarker = null;

    function previewImage(input, imgId = 'imgPreview', placeholderId = 'uploadPlaceholder') {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById(imgId).src = e.target.result;
                document.getElementById(imgId).style.display = 'block';
                document.getElementById(placeholderId).style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('petImageInput').addEventListener('change', function () {
        previewImage(this, 'imgPreview', 'uploadPlaceholder');
    });

    document.addEventListener('DOMContentLoaded', function () {
        populateBarangayDropdown('barangay-select');
        populateBarangayDropdown('adoption-barangay-select');
        lostFoundMap = initAngelesMap('lost-found-map');
        const statusSelect = document.getElementById('status-select');
        const lostFoundFields = document.getElementById('lost-found-fields');
        const adoptionFields = document.getElementById('adoption-fields');
        const mapLabel = document.getElementById('map-label');
        const ownerVerificationBlock = document.getElementById('owner-verification-block');
        const rewardWrap = document.getElementById('reward-field-wrap');
        const rewardCheck = document.getElementById('rewardOffered');
        const rewardInputWrap = document.getElementById('reward-input-wrap');
        const rewardDetailsInput = document.getElementById('rewardDetails');

        document.getElementById('barangay-select').addEventListener('change', function () {
            const selectedText = this.options[this.selectedIndex] ? this.options[this.selectedIndex].text : '';
            document.getElementById('location_text').value = selectedText;
            lostFoundMarker = zoomToBarangay(lostFoundMap, this.value, lostFoundMarker);
            if (lostFoundMarker) {
                bindMarkerToInputs(lostFoundMarker, 'lat', 'lng');
            }
        });

        document.getElementById('adoption-barangay-select').addEventListener('change', function () {
            const selectedText = this.options[this.selectedIndex] ? this.options[this.selectedIndex].text : '';
            document.getElementById('location_text').value = selectedText;
            lostFoundMarker = zoomToBarangay(lostFoundMap, this.value, lostFoundMarker);
            if (lostFoundMarker) {
                bindMarkerToInputs(lostFoundMarker, 'lat', 'lng');
            }
        });

        function toggleStatusForm() {
            const isAdoption = statusSelect.value === 'for_adoption';
            const isLost = statusSelect.value === 'lost';
            lostFoundFields.classList.toggle('d-none', isAdoption);
            adoptionFields.classList.toggle('d-none', !isAdoption);
            if (rewardWrap) rewardWrap.classList.toggle('d-none', !isLost);
            if (!isLost && rewardCheck && rewardDetailsInput && rewardInputWrap) {
                rewardCheck.checked = false;
                rewardDetailsInput.value = '';
                rewardDetailsInput.required = false;
                rewardInputWrap.classList.add('d-none');
            }
            if (ownerVerificationBlock) {
                // Owner verification photo is only needed for lost/found posts.
                ownerVerificationBlock.classList.toggle('d-none', isAdoption);
            }
            mapLabel.textContent = isAdoption ? 'Adoption Pickup / Meetup Location' : 'Pinpoint on Map';
            document.querySelector('input[name="last_seen_date"]').required = !isAdoption;
            document.getElementById('barangay-select').required = !isAdoption;
            document.getElementById('adoption-barangay-select').required = isAdoption;
        }

        statusSelect.addEventListener('change', toggleStatusForm);
        if (rewardCheck && rewardInputWrap && rewardDetailsInput) {
            rewardCheck.addEventListener('change', function () {
                const show = this.checked;
                rewardInputWrap.classList.toggle('d-none', !show);
                rewardDetailsInput.required = show;
                if (!show) rewardDetailsInput.value = '';
            });
        }
        toggleStatusForm();
    });

    document.getElementById("addPetForm").addEventListener("submit", function(e) {
        e.preventDefault();
        if (!document.getElementById('guidelinesAgree').checked) {
            Swal.fire('Required', 'Please agree to the Community Guidelines first.', 'warning');
            return;
        }

        const btn = document.getElementById('submitBtn');
        const txt = document.getElementById('btnText');
        const spinner = document.getElementById('btnSpinner');

        btn.disabled = true;
        txt.innerText = "Posting...";
        spinner.classList.remove('d-none');

        fetch("postapet.php", { method: "POST", body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#ffc107'
                }).then(() => {
                    window.location.href = "lost&found.php";
                });
            } else {
                Swal.fire('Error', data.error, 'error');
                btn.disabled = false;
                txt.innerText = "Post Now";
                spinner.classList.add('d-none');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Could not connect to the server.', 'error');
            btn.disabled = false;
            txt.innerText = "Post Now";
            spinner.classList.add('d-none');
        });
    });
</script>
</body>
</html>