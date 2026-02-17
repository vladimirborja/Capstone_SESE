<?php
session_start();

require_once '../config.php';

// Default user ID if session isn't set
$current_user_id = $_SESSION['user_id'] ?? 1; 

// --- BACKEND: SAVE NEW PET ---
if (isset($_POST['action']) && $_POST['action'] === 'save_pet') {
    try {
        $name = $conn->real_escape_string($_POST['pet_name']);
        $category = $_POST['tag'];
        $gender = $_POST['gender']; 
        $color = $conn->real_escape_string($_POST['pet_color']); 
        
        $type = $_POST['pet_type'];
        if ($type === 'Other' && !empty($_POST['other_type'])) {
            $type = $conn->real_escape_string($_POST['other_type']);
        }

        $location = $conn->real_escape_string($_POST['location']);
        $desc = $conn->real_escape_string($_POST['description']);
        $contact = $conn->real_escape_string($_POST['contact']);
        
        $image_db_path = "uploads/default_pet.png"; 

        if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] === 0) {
            $upload_directory = "../uploads/"; 
            
            if (!is_dir($upload_directory)) {
                mkdir($upload_directory, 0777, true);
            }

            $file_name = $_FILES['pet_image']['name'];
            $unique_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
            $destination = $upload_directory . $unique_name;

            if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $destination)) {
                $image_db_path = "uploads/" . $unique_name;
            } else {
                throw new Exception("Failed to move file to " . $upload_directory);
            }
        }

        $sql = "INSERT INTO pets (user_id, pet_name, category, gender, breed, color, last_seen_location, description, image_url, contact_number, created_at) 
                VALUES ('$current_user_id', '$name', '$category', '$gender', '$type', '$color', '$location', '$desc', '$image_db_path', '$contact', NOW())";

        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Pet successfully posted!']);
        } else {
            throw new Exception($conn->error);
        }
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
                        <label class="fw-bold small">Tag</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tag" id="lostTag" value="Lost" checked>
                            <label class="form-check-label" for="lostTag">Lost</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tag" id="foundTag" value="Found">
                            <label class="form-check-label" for="foundTag">Found</label>
                        </div>
                    </div>

                    <div class="row g-2"> <div class="col-md-4 mb-3">
                            <label class="fw-bold small">Gender</label>
                            <select name="gender" class="form-select shadow-sm">
                                <option>Male</option>
                                <option>Female</option>
                                <option>Unknown</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold small">Pet Type</label>
                            <select name="pet_type" class="form-select shadow-sm" onchange="toggleOtherInput(this.value)">
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold small">Pet Color</label>
                            <input type="text" name="pet_color" class="form-control shadow-sm" placeholder="e.g. White" required>
                        </div>
                    </div>

                    <div class="mb-3 animate__animated animate__fadeIn" id="otherTypeContainer">
                        <label class="small fw-bold text-danger">Specify Pet Type</label>
                        <input type="text" name="other_type" class="form-control border-danger shadow-sm">
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small">Last Seen Location</label>
                        <input type="text" name="location" class="form-control shadow-sm" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small">Contact Number</label>
                        <input type="text" name="contact" class="form-control shadow-sm" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small">Description</label>
                        <textarea name="description" class="form-control shadow-sm" rows="3"></textarea>
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
<script>
    function toggleOtherInput(v) { document.getElementById('otherTypeContainer').style.display = (v === 'Other') ? 'block' : 'none'; }
    
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('imgPreview').src = e.target.result;
                document.getElementById('imgPreview').style.display = 'block';
                document.getElementById('uploadPlaceholder').style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById("addPetForm").addEventListener("submit", function(e) {
        e.preventDefault();
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