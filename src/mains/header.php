<?php
require_once __DIR__ . '/../db_config.php'; 

$user_id = $_SESSION['user_id'] ?? 0;
$unread_count = 0;
$notifications = [];

if ($user_id > 0) {
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt_count->execute([$user_id]);
    $unread_count = $stmt_count->fetchColumn();
    $stmt_notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt_notifs->execute([$user_id]);
    $notifications = $stmt_notifs->fetchAll();
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    .notif-dropdown { max-height: 400px; overflow-y: auto; border: 1px solid #ddd; }
    .notif-item { transition: background 0.2s; cursor: pointer; }
    .notif-item:hover { background-color: #f1f1f1; }
    .notif-item.unread { border-left: 4px solid #007bff; }
</style>

<header class="topbar container mx-auto px-4 rounded-4" style="margin-top: 20px;">
    <div class="topbar-inner d-flex justify-content-between align-items-center">
        <div class="logo">
            <a href="../index.php"><img src="../images/homeImages/Sese-Logo3.png" alt="Logo" /></a>
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
                    <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        <?php echo $unread_count; ?>
                    </span>
                <?php endif; ?>
                
                <div class="notif-dropdown shadow p-3" id="notifDrop" style="display:none; position: absolute; right: 0; width: 320px; background: white; z-index: 1000; border-radius: 10px; top: 40px;">
                    <h6 class="border-bottom pb-2 mb-2" style="color: #333;">Notifications</h6>
                    <div id="notif-list">
                        <?php if (empty($notifications)): ?>
                            <p class="text-muted small py-2 mb-0 text-center">No notifications yet.</p>
                        <?php else: ?>
                            <?php foreach ($notifications as $n): ?>
                                <div class="notif-item p-2 border-bottom <?php echo $n['is_read'] == 0 ? 'unread bg-light' : ''; ?>" 
                                     onclick="showNotifDetail('<?php echo addslashes($n['message']); ?>', '<?php echo $n['created_at']; ?>')">
                                    <p class="small mb-1 <?php echo $n['is_read'] == 0 ? 'fw-bold' : ''; ?>" style="color: #333; line-height: 1.2;">
                                        <?php echo htmlspecialchars($n['message']); ?>
                                    </p>
                                    <span class="text-muted" style="font-size: 0.65rem;">
                                        <i class="bi bi-clock me-1"></i><?php echo date('M d, g:i a', strtotime($n['created_at'])); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="icon-wrapper avatar-wrapper position-relative" onclick="toggleDropdown('dropL')">
                <img src="../images/homeImages/profile icon.png" class="icon-img avatar" />
                <div class="notif-dropdown shadow" id="dropL" style="display:none; position: absolute; right: 0; width: 150px; background: white; z-index: 1000; border-radius: 10px; top: 50px;">
                    <p class="p-2 mb-0 text-center">
                        <a href="javascript:void(0)" onclick="confirmLogout()" class="text-danger text-decoration-none">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="modal fade" id="notifDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Notification Detail</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center mb-3">
            <div class="bg-primary rounded-circle p-2 text-white me-3">
                <i class="bi bi-info-circle-fill fs-4"></i>
            </div>
            <div>
                <p id="modal-notif-message" class="mb-0 fw-bold"></p>
                <small id="modal-notif-date" class="text-muted"></small>
            </div>
        </div>
        <hr>
        <p class="text-muted small">This activity was recorded on your post.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
    function toggleDropdown(id) {
        const drop = document.getElementById(id);
        const badge = document.getElementById('notif-badge'); 
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
            fetch('../process/mark_read.php')
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    badge.style.display = 'none';
                }
            })
            .catch(err => console.error('Error marking notifications:', err));
        }
    }

    // Function to show the Pop-up
    function showNotifDetail(message, date) {
        document.getElementById('modal-notif-message').innerText = message;
        // Format date nicely
        const formattedDate = new Date(date).toLocaleString('en-US', { 
            month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true 
        });
        document.getElementById('modal-notif-date').innerText = formattedDate;

        // Initialize and show Bootstrap Modal
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
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Logout'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'main.php?action=logout';
            }
        });
    }
</script>