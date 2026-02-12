<?php
require_once __DIR__ . '/../db_config.php'; 

$user_id = $_SESSION['user_id'] ?? 0;
$unread_count = 0;
$notifications = [];

if ($user_id > 0) {
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt_count->execute([$user_id]);
    $unread_count = $stmt_count->fetchColumn();
    $stmt_notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt_notifs->execute([$user_id]);
    $notifications = $stmt_notifs->fetchAll();
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<header class="topbar container mx-auto px-4 rounded-4" style="margin-top: 20px;">
    <div class="topbar-inner d-flex justify-content-between align-items-center">

        <div class="logo">
            <a href="../index.php">
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
                            <div class="notif-item p-2 border-bottom <?php echo $n['is_read'] == 0 ? 'bg-light' : ''; ?>">
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
            fetch('process/mark_read.php')
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    badge.style.display = 'none';
                }
            })
            .catch(err => console.error('Error marking notifications:', err));
        }
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