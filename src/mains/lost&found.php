<?php
session_start();

require_once '../config.php';

// Assume you have a logged-in user ID in the session
$current_user_id = $_SESSION['user_id'] ?? null; 

// Handle AJAX actions (Status Updates)
if (isset($_POST['update_status'])) {
    $pet_id = intval($_POST['pet_id']);
    $action = $_POST['action']; 

    if ($action === 'to_pending') {
        $sql = "UPDATE pets SET category = 'Pending' WHERE pet_id = $pet_id AND category = 'Lost'";
        $success_msg = "Status updated to Pending!";
    } 
    elseif ($action === 'confirm_found') {
        $sql = "UPDATE pets SET category = 'Found' WHERE pet_id = $pet_id AND user_id = $current_user_id";
        $success_msg = "Wonderful! Your pet has been marked as Found.";
    }

    if (isset($sql) && $conn->query($sql) && $conn->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => $success_msg]);
    } else {
        echo json_encode(['success' => false, 'error' => "Unauthorized or pet not found."]);
    }
    exit;
}


// 1. Get the filter types from the GET request
$types = isset($_GET['type']) ? $_GET['type'] : [];

// 2. Build the WHERE clause
$where_clauses = [];

// Apply category filters if any are checked
if (!empty($types)) {
    // Sanitize each type to prevent SQL injection
    $sanitized_types = array_map(function($t) use ($conn) {
        return "'" . $conn->real_escape_string($t) . "'";
    }, $types);
    
    $where_clauses[] = "category IN (" . implode(',', $sanitized_types) . ")";
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
        .main-container { display: flex; padding: 30px; gap: 30px; max-width: 1400px; margin: 0 auto; }
        .filter-sidebar { width: 280px; background: white; padding: 25px; border: 1px solid #dee2e6; border-radius: 12px; height: fit-content; }
        .content-area { flex-grow: 1; }
        .pet-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .pet-card { background: white; border: 1px solid #dee2e6; border-radius: 15px; padding: 15px; transition: 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .pet-card:hover { transform: translateY(-5px); }
        .pet-img-container img { width: 100%; height: 200px; object-fit: cover; border-radius: 10px; margin-bottom: 12px; }
        
        .status-tag { display: inline-block; padding: 4px 12px; border-radius: 6px; color: white; font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }
        .tag-lost { background-color: #dc3545; }
        .tag-pending { background-color: #fd7e14; }
        .tag-found { background-color: #198754; }
        
        .view-btn { width: 100%; margin-top: 15px; padding: 10px; border: none; border-radius: 8px; color: white; font-weight: 600; }
        #petDetailImage { width: 100%; height: 350px; object-fit: cover; border-radius: 12px; }
        .modal-content { border-radius: 20px; border: none; }
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
                
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="type[]" value="Lost" id="filterLost" <?php echo in_array('Lost', $types) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filterLost">Lost</label>
                </div>
                
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="type[]" value="Pending" id="filterPending" <?php echo in_array('Pending', $types) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filterPending">Pending</label>
                </div>
                
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="type[]" value="Found" id="filterFound" <?php echo in_array('Found', $types) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filterFound">Found</label>
                </div>
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
                    <h5 class="text-muted">No pets found matching these filters.</h5>
                </div>
            <?php else: ?>
                <?php foreach ($pets as $pet): ?>
                    <div class="pet-card animate__animated animate__fadeIn">
                        <div class="pet-img-container">
                            <img src="../<?php echo htmlspecialchars($pet['image_url']); ?>" alt="Pet">
                        </div>
                        <span class="status-tag tag-<?php echo strtolower($pet['category']); ?>">
                            <?php echo htmlspecialchars($pet['category']); ?>
                        </span>
                        <div class="pet-name h5 fw-bold mb-1"><?php echo htmlspecialchars($pet['pet_name']); ?></div>
                        <div class="text-muted small mb-3"><?php echo htmlspecialchars($pet['last_seen_location']); ?></div>
                        <button class="view-btn btn-primary" onclick='openPetModal(<?php echo json_encode($pet); ?>, <?php echo json_encode($current_user_id); ?>)'>
                            View Details
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<div class="modal fade" id="petModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-3">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-primary">Pet Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <img id="petDetailImage" src="" alt="Pet" class="img-fluid rounded shadow-sm">
                    </div>
                    <div class="col-md-6">
                        <span id="modalStatusBadge" class="status-tag"></span>
                        <h2 id="modalPetNameDisplay" class="fw-bold"></h2>
                        <hr>
                        <p><strong>Breed:</strong> <span id="modalBreed"></span></p>
                        <p><strong>Location:</strong> <span id="modalLocation"></span></p>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentPet = null;
let currentUserId = null;

function openPetModal(pet, userId) {
    currentPet = pet;
    currentUserId = userId;
    
    document.getElementById('modalPetNameDisplay').innerText = pet.pet_name;
    document.getElementById('petDetailImage').src = "../" + pet.image_url;
    document.getElementById('modalBreed').innerText = pet.breed || 'Unknown';
    document.getElementById('modalLocation').innerText = pet.last_seen_location;
    document.getElementById('modalDescription').innerText = pet.description || '';
    document.getElementById('modalContact').innerText = pet.contact_number;
    
    renderActionButtons();
    new bootstrap.Modal(document.getElementById('petModal')).show();
}

function renderActionButtons() {
    const container = document.getElementById('actionButtonContainer');
    const badge = document.getElementById('modalStatusBadge');
    const cat = currentPet.category.toLowerCase();
    const isOwner = (currentPet.user_id == currentUserId);

    badge.innerText = currentPet.category;
    badge.className = `status-tag tag-${cat}`;
    container.innerHTML = '';

    if (cat === 'lost') {
        if (!isOwner) {
            container.innerHTML = `<button class="alert alert-danger w-100 fw-bold" onclick="handleUpdate('to_pending')">I found this pet!</button>`;
        } else {
            container.innerHTML = `<div class="alert alert-success py-2 text-center small"><strong>Your post is active. Hope you find your pet.</strong></div>`;
        }
    } 
    else if (cat === 'pending') {
        if (isOwner) {
            container.innerHTML = `
                <div class="alert alert-info py-2 text-center small mb-2">Someone reported they found this pet!</div>
                <button class="btn btn-success w-100 fw-bold" onclick="handleUpdate('confirm_found')">Confirm Reunited ❤️</button>
            `;
        } else {
            container.innerHTML = `<button class="alert alert-warning w-100 fw-bold" disabled>Recovery in progress...</button>`;
        }
    }
    else if (cat === 'found') {
        container.innerHTML = `<div class="alert alert-success py-2 text-center fw-bold">Reunited with owner! ❤️</div>`;
    }
}

function handleUpdate(actionType) {
    const isConfirmFound = actionType === 'confirm_found';
    
    Swal.fire({
        title: isConfirmFound ? 'Reunited! ❤️' : 'Help the owner?',
        text: isConfirmFound ? "This will mark your pet as FOUND." : "Mark as pending? The owner will be notified to confirm.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: 'Yes, proceed!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('update_status', true);
            formData.append('pet_id', currentPet.pet_id);
            formData.append('action', actionType);

            fetch(window.location.href, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Updated!', data.message, 'success').then(() => {
                        // Redirect to the "Found" category if it was just confirmed
                        if (isConfirmFound) {
                            window.location.href = 'lost&found.php?type[]=Found';
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            });
        }
    });
}
</script>

</body>
</html>