<?php
// Ensure session is started for user_id check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (file_exists(__DIR__ . '/../db_config.php')) {
    require_once __DIR__ . '/../db_config.php';
} elseif (file_exists(__DIR__ . '/db_config.php')) {
    require_once __DIR__ . '/db_config.php';
}

$user_id = $_SESSION['user_id'] ?? 0;
$unread_count = 0;
$notifications = [];

// Fetch current user's profile image
$current_user_profile_img = '../images/homeImages/profile icon.png'; // default
if ($user_id > 0 && isset($conn)) {
    $stmt_user = $conn->prepare("SELECT profile_image FROM users WHERE user_id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_result = $stmt_user->get_result()->fetch_assoc();
    if ($user_result && !empty($user_result['profile_image'])) {
        $current_user_profile_img = $user_result['profile_image'];
    }
}

// Only run query if $pdo exists and user is logged in
if ($user_id > 0 && isset($pdo)) {
    // 1. Get unread count
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt_count->execute([$user_id]);
    $unread_count = $stmt_count->fetchColumn();
    
    // 2. Fetch notifications with post details
    $stmt_notifs = $pdo->prepare("
        SELECT n.*, p.content, p.image_url 
        FROM notifications n 
        LEFT JOIN posts p ON n.post_id = p.post_id 
        WHERE n.user_id = ? 
        ORDER BY n.created_at DESC LIMIT 5
    ");
    $stmt_notifs->execute([$user_id]);
    $notifications = $stmt_notifs->fetchAll();
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<style>
    /* Profile Icon Styling */
    .avatar-wrapper {
        position: relative;
        cursor: pointer;
    }
    
    .avatar-wrapper .icon-img.avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid white;
        transition: all 0.3s ease;
    }

    /* ===== ADMIN STYLE PROFILE DROPDOWN ===== */
    #dropL {
        display:none;
        position:absolute;
        right:0;
        width:220px;
        background:#f1f1f1;
        border-radius:18px;
        box-shadow:0 10px 25px rgba(0,0,0,0.15);
        top:55px;
        z-index:1000;
        padding:10px 0;
    }

    #dropL .dropdown-header {
        font-weight:700;
        color:#2d3748;
        text-transform:uppercase;
        font-size:0.75rem;
        letter-spacing:0.05em;
        padding:10px 20px;
    }

    #dropL hr {
        margin:6px 0;
    }

    #dropL .dropdown-item {
        font-size:0.9rem;
        font-weight:500;
        color:#4a5568;
        padding:10px 20px;
        display:flex;
        align-items:center;
        gap:10px;
        text-decoration:none;
        transition:0.2s;
    }

    #dropL .dropdown-item i {
        width:18px;
        text-align:center;
    }

    #dropL .dropdown-item:hover {
        background-color:#e2e8f0;
        color:#1e88e5;
    }

    #dropL .dropdown-item.text-danger:hover {
        background-color:#fff5f5;
        color:#e53e3e !important;
    }

</style>

<header class="topbar container mx-auto px-4 rounded-4" style="margin-top: 20px;">
    <div class="topbar-inner d-flex justify-content-between align-items-center">
        <div class="logo">
            <a href="../mains/main.php">
                <img src="../images/homeImages/Sese-Logo3.png" alt="Logo" />
            </a>
        </div>

        <nav class="nav-links">
            <a href="main.php">Home</a>
            <a href="lost&found.php">Lost & Found</a>
            <a href="about.php">About Us</a>
            <a href="contact.php">Contact Us</a>
        </nav>

        <div class="top-icons d-flex align-items-center gap-3">
            <div class="icon-wrapper position-relative" onclick="toggleDropdown('notifDrop')">
                <i class="bi bi-bell-fill fs-4" style="cursor: pointer; color: #fff;"></i>
                <?php if ($unread_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        <?php echo $unread_count; ?>
                    </span>
                <?php endif; ?>
                
                <div class="notif-dropdown shadow p-3" id="notifDrop" style="display:none; position: absolute; right: 0; width: 300px; background: white; z-index: 1000; border-radius: 10px; top: 40px;">
                    <h6 class="border-bottom pb-2 mb-2" style="color: #333;">Notifications</h6>
                    <?php if (empty($notifications)): ?>
                        <p class="text-muted small py-2 mb-0">No notifications yet.</p>
                    <?php else: ?>
                        <?php foreach ($notifications as $n): ?>
                            <div class="notif-item p-2 border-bottom <?php echo $n['is_read'] == 0 ? 'bg-light' : ''; ?>" 
                                 style="cursor: pointer;"
                                 data-message="<?php echo htmlspecialchars($n['message']); ?>"
                                 data-date="<?php echo $n['created_at']; ?>"
                                 data-content="<?php echo htmlspecialchars($n['content'] ?? 'No text content'); ?>"
                                 data-image="<?php echo htmlspecialchars($n['image_url'] ?? ''); ?>"
                                 data-postid="<?php echo $n['post_id']; ?>"
                                 onclick="prepareNotifModal(this)">
                                <p class="small mb-1 <?php echo $n['is_read'] == 0 ? 'fw-bold' : ''; ?>" style="color: #333;">
                                    <?php echo htmlspecialchars($n['message']); ?>
                                </p>
                                <span class="text-muted" style="font-size: 0.7rem;">
                                    <?php echo date('M d, g:i a', strtotime($n['created_at'])); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="icon-wrapper avatar-wrapper position-relative" onclick="toggleDropdown('dropL')">
                <img src="<?php echo htmlspecialchars($current_user_profile_img); ?>" class="icon-img avatar" alt="Your Profile" />
            
                <div class="notif-dropdown shadow" id="dropL">
                    <a href="profile.php" class="dropdown-item">
                        <i class="bi bi-person"></i> Profile
                    </a>
                    <hr>
                    <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="modal fade" id="notifDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 15px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" style="color: #333;">Activity Detail</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-4">
        <h5 id="modal-notif-message" class="fw-bold mb-1" style="color: #000;"></h5>
        <p id="modal-notif-date" class="text-muted mb-4" style="font-size: 14px;"></p>
        
        <div style="background: #f0f2f5; border-radius: 12px; padding: 15px; text-align: left;">
            <span class="d-block fw-bold text-muted mb-1" style="font-size: 10px; text-transform: uppercase;">YOUR POST</span>
            <p id="modal-post-text" class="mb-0" style="color: #1c1e21; font-size: 14px;"></p>
            <img id="modal-post-image" src="" style="max-width: 100%; border-radius: 8px; margin-top: 10px; display: none;" alt="Post Image">
        </div>
      </div>
      <div class="modal-footer border-0 flex-column">
        <button type="button" class="btn btn-light w-100 fw-bold text-muted mt-2" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
    function toggleDropdown(id) {
        const drop = document.getElementById(id);
        const badge = document.querySelector('.badge.bg-danger'); 
        const allDrops = ['notifDrop', 'dropL'];
        
        allDrops.forEach(dId => {
            if(dId !== id) {
                const otherDrop = document.getElementById(dId);
                if(otherDrop) otherDrop.style.display = 'none';
            }
        });

        const isOpening = (drop.style.display !== 'block');
        drop.style.display = isOpening ? 'block' : 'none';

        if (id === 'notifDrop' && isOpening && badge) {
            const markReadPath = window.location.origin + "/Capstone/src/mains/process/mark_read.php";
            fetch(markReadPath)
            .then(res => res.json())
            .then(data => { if(data.success) badge.style.display = 'none'; })
            .catch(err => console.error('Error marking read:', err));
        }
    }

    function prepareNotifModal(el) {
        const message = el.getAttribute('data-message');
        const date = el.getAttribute('data-date');
        const content = el.getAttribute('data-content');
        const image = el.getAttribute('data-image');
        const pid = el.getAttribute('data-postid');
        showNotifDetail(message, date, content, image, pid);
    }

    function showNotifDetail(message, date, postText, postImage, postId) {
        document.getElementById('modal-notif-message').innerText = message;
        
        const d = new Date(date);
        document.getElementById('modal-notif-date').innerText = d.toLocaleString('en-US', { 
            weekday: 'short', month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true 
        });
        
        document.getElementById('modal-post-text').innerText = postText;
        
        const imgEl = document.getElementById('modal-post-image');
        
        if (postImage && postImage.trim() !== "" && postImage !== "null") {
            const fullPath = "../" + postImage;
            imgEl.src = fullPath; 
            imgEl.style.display = 'block';
            
            imgEl.onerror = function() {
                this.style.display = 'none';
            };
        } else {
            imgEl.style.display = 'none';
        }
                
        var myModal = new bootstrap.Modal(document.getElementById('notifDetailModal'));
        myModal.show();
    }

    window.onclick = function(event) {
        if (!event.target.closest('.icon-wrapper')) {
            const nDrop = document.getElementById('notifDrop');
            const lDrop = document.getElementById('dropL');
            if(nDrop) nDrop.style.display = 'none';
            if(lDrop) lDrop.style.display = 'none';
        }
    }

    function confirmLogout() {
        Swal.fire({
            title: 'Logout?',
            text: "You will need to login again to access your account.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#718096',
            confirmButtonText: 'Logout'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = window.location.origin + '/Capstone/src/mains/main.php?action=logout';
            }
        });
    }
</script>