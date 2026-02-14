<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "capstone_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Logout Logic
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: ../index.php"); 
    exit();
}

// --- 1. ADD POST LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_post'])) {
    $content = $conn->real_escape_string($_POST['post_content']);
    $image_path = "";
    if (!empty($_FILES['post_image']['name'])) {
        $target_dir = "../uploads/"; 
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $image_name = time() . "_" . basename($_FILES["post_image"]["name"]);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES["post_image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/" . $image_name; 
        }
    }
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image_url, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $current_user_id, $content, $image_path);
    $stmt->execute();
    header("Location: main.php?msg=posted");
    exit();
}

// --- 2. EDIT POST LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_post'])) {
    $post_id = intval($_POST['edit_post_id']);
    $content = $conn->real_escape_string($_POST['edit_content']);
    if (!empty($_FILES['edit_image']['name'])) {
        $target_dir = "../uploads/";
        $image_name = time() . "_" . basename($_FILES["edit_image"]["name"]);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES["edit_image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/" . $image_name;
            $stmt = $conn->prepare("UPDATE posts SET content = ?, image_url = ? WHERE post_id = ? AND user_id = ?");
            $stmt->bind_param("ssii", $content, $image_path, $post_id, $current_user_id);
        }
    } else {
        $stmt = $conn->prepare("UPDATE posts SET content = ? WHERE post_id = ? AND user_id = ?");
        $stmt->bind_param("sii", $content, $post_id, $current_user_id);
    }
    $stmt->execute();
    header("Location: main.php?msg=updated");
    exit();
}

// --- 3. DELETE LOGIC ---
if (isset($_GET['delete_id'])) {
    $post_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM posts WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $current_user_id);
    $stmt->execute();
    header("Location: main.php?msg=deleted");
    exit();
}

// --- 4. REPORT LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
    $post_id = intval($_POST['report_post_id']);
    $report_type = $conn->real_escape_string($_POST['report_type']);
    $description = $conn->real_escape_string($_POST['report_description']);
    
    $stmt = $conn->prepare("INSERT INTO post_reports (post_id, user_id, report_type, description, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiss", $post_id, $current_user_id, $report_type, $description);
    $stmt->execute();
    header("Location: main.php?msg=reported");
    exit();
}

// --- 5. COMMENT LOGIC (Fallback for non-AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $post_id = intval($_POST['post_id']);
    $comment_text = trim($_POST['comment_text']);

    if ($comment_text !== '') {
        $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $post_id, $current_user_id, $comment_text);
        $stmt->execute();
        header("Location: main.php?msg=commented");
        exit();
    }
}

// --- 6. MISSING DOG LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_missing'])) {
    $dog_name = $conn->real_escape_string($_POST['dog_name']);
    $last_seen = $conn->real_escape_string($_POST['last_seen']);
    $contact = $conn->real_escape_string($_POST['contact_info']);
    $image_path = "";

    if (!empty($_FILES['missing_image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $image_name = "missing_" . time() . "_" . basename($_FILES["missing_image"]["name"]);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES["missing_image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/" . $image_name;
        }
    }

    $missing_content = "[MISSING DOG] Name: $dog_name | Last Seen: $last_seen | Contact: $contact";
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image_url, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $current_user_id, $missing_content, $image_path);
    $stmt->execute();
    header("Location: main.php?msg=missing_posted");
    exit();
}

function hasUserLiked($postId, $userId, $conn) {
    $stmt = $conn->prepare("SELECT like_id FROM post_likes WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

$all_posts = $conn->query("SELECT p.*, u.full_name, u.user_id as author_id
    FROM posts p 
    JOIN users u ON p.user_id = u.user_id 
    ORDER BY p.created_at DESC");
?>

<?php
require_once '../db_config.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$est_stmt = $pdo->query("SELECT name, description, address, latitude, longitude FROM establishments WHERE status = 'active'");
$active_establishments = $est_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/styles.css" />
    <style>
        .post-menu-btn { cursor: pointer; color: #65676b; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
        .post-menu-btn:hover { background: #f2f2f2; }
        .dropdown-toggle::after { display: none; }
        .post-image { max-width: 100%; border-radius: 8px; margin-top: 10px; }
        .like-btn { cursor: pointer; color: #65676b; transition: 0.2s; font-size: 0.95rem; display: flex; align-items: center; }
        .like-btn.liked { color: #1877f2; font-weight: bold; }
        .comment-item { background: #f0f2f5; border-radius: 12px; padding: 6px 12px; margin-bottom: 5px; font-size: 0.9rem; }
        .comment-user { font-weight: bold; color: #050505; margin-right: 5px; }
        .sidebar-card { background: #fff; border-radius: 15px; padding: 15px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .like-count-display { font-size: 0.85rem; color: #65676b; margin-left: -15px; }
        .missing-dog-card { border-left: 5px solid #dc3545; background-color: #fff9f9; }
        .faq-section { background: #fff; border-radius: 15px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .faq-title { color: #0d6efd; font-weight: 700; margin-bottom: 2px; }
        .faq-subtitle { color: #6c757d; font-size: 0.85rem; margin-bottom: 15px; }
        .faq-item { background: #e7f3ff; border-radius: 10px; padding: 12px; margin-bottom: 12px; }
        .faq-question { color: #0d6efd; font-weight: 700; font-size: 0.9rem; margin-bottom: 5px; }
        .faq-answer { color: #4b4b4b; font-size: 0.8rem; line-height: 1.4; }
        .faq-btn-group { display: flex; gap: 10px; margin-top: 15px; }
        .faq-btn-read { background: #3ab0ff; border: none; color: white; flex: 1; padding: 10px; border-radius: 8px; font-weight: 600; }
        .faq-btn-ask { background: #0d6efd; border: none; color: white; flex: 1; padding: 10px; border-radius: 8px; font-weight: 600; }
        #missing_preview { width: 100%; max-height: 200px; object-fit: cover; border-radius: 10px; display: none; margin-top: 10px; }
        .sticky-column { position: -webkit-sticky; position: sticky; bottom: 20px; align-self: flex-end; }
        .btn-send-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; padding: 0; flex-shrink: 0; transition: all 0.2s ease-in-out; border: none; background-color: #0d6efd; color: white; }
        .btn-send-circle:hover { background-color: #0b5ed7; transform: scale(1.08); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .btn-send-circle i { font-size: 1.1rem; margin-left: 2px; }
        .comment-input-pill { height: 40px; border-radius: 20px !important; padding-left: 15px; background-color: #f0f2f5 !important; }
        .view-all-comments { font-size: 0.9rem; color: #65676b; font-weight: 600; cursor: pointer; margin-bottom: 8px; display: inline-block; }
        .view-all-comments:hover { text-decoration: underline; }
        /* User interaction styles */
        .clickable-user { cursor: pointer; }
        .clickable-user:hover { text-decoration: underline; }
    </style>
</head>
<body class="home-body">

    <div class="modal fade" id="editPostModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="main.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5>Edit Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_post_id" id="edit_post_id">
                    <textarea name="edit_content" id="edit_content" class="form-control mb-3" rows="4" required></textarea>
                    <input type="file" name="edit_image" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_post" class="btn btn-primary w-100">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="reportPostModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="main.php" method="POST">
                <div class="modal-header">
                    <h5>Report Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="report_post_id" id="report_post_id">
                    <label class="form-label fw-bold">Why are you reporting this?</label>
                    <select name="report_type" class="form-select mb-3" required>
                        <option value="">Select a reason...</option>
                        <option value="Fake Content">Fake Content / Misinformation</option>
                        <option value="Verbal Abuse">Verbal Abuse / Harassment</option>
                        <option value="Inappropriate">Inappropriate Media</option>
                        <option value="Spam">Spam</option>
                        <option value="Other">Other</option>
                    </select>
                    <textarea name="report_description" class="form-control" rows="4" placeholder="Describe the issue here..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit_report" class="btn btn-danger w-100">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="createPostModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="main.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5>Share a Furrendly Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea name="post_content" class="form-control mb-3" rows="4" placeholder="What's on your mind?" required></textarea>
                    <input type="file" name="post_image" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit_post" class="btn btn-primary">Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="missingDogModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="missingDogForm" action="main.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-danger text-white">
                    <h5>Report Missing Dog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label small fw-bold">Dog Details</label>
                    <input type="text" name="dog_name" class="form-control mb-2" placeholder="Dog's Name" required>
                    <input type="text" name="last_seen" class="form-control mb-2" placeholder="Last Seen Location" required>
                    <input type="text" name="contact_info" class="form-control mb-2" placeholder="Your Contact Number" required>
                    
                    <label class="form-label small fw-bold mt-2">Upload Photo</label>
                    <input type="file" name="missing_image" id="missing_image_input" class="form-control" accept="image/*" onchange="previewImage(this)">
                    <img id="missing_preview" src="#" alt="Preview" style="max-width: 100%; margin-top: 10px; display: none;">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit_missing" class="btn btn-danger w-100">Post Alert</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <div class="modal fade" id="userProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pb-5">
                    <img src="../images/homeImages/profile icon.png" id="popup_user_img" class="rounded-circle mb-3" width="100" height="100">
                    <h4 id="popup_user_name" class="fw-bold mb-1">...</h4>
                    <p id="popup_user_role" class="text-muted small mb-3">User Profile</p>
                    <hr>
                    <div class="d-grid gap-2 col-8 mx-auto">
                        <!-- <button class="btn btn-primary rounded-pill">Message</button> -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <header class="topbar container mx-auto px-4 rounded-4" style="margin-top: 20px;">
        <div class="topbar-inner">
            <div class="logo"><a href="main.php"><img src="../images/homeImages/Sese-Logo3.png" alt="Logo" /></a></div>
            <nav class="nav-links">
                <a href="main.php">Home</a>
                <a href="Lost&found.php">Lost & Found</a>
                <a href="about.php">About Us</a>
                <a href="contact.php">Contact Us</a>
            </nav>
            <div class="top-icons">
                <div class="icon-wrapper avatar-wrapper" onclick="document.getElementById('dropL').style.display = (document.getElementById('dropL').style.display == 'block') ? 'none' : 'block';">
                    <img src="../images/homeImages/profile icon.png" class="icon-img avatar" />
                    <div class="notif-dropdown" id="dropL" style="display:none;">
                         <p>
                        <a href="profile.php">
                            <i class="bi bi-person me-2"></i>Profile
                        </a>
                    </p>
                        <p><a href="javascript:void(0)" onclick="confirmLogout()"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="search-row d-flex justify-content-center align-items-center gap-3 mb-4 mt-4">
        <div class="search-box d-flex align-items-center"><i class="bi bi-search me-2"></i><input type="text" placeholder="Search here..." /></div>
        <div class="filters d-flex gap-2">
            <button class="chip blue" data-bs-toggle="modal" data-bs-target="#createPostModal">+ Share a Furrendly Place</button>
            <button class="chip red" style="background:#dc3545; color:white" data-bs-toggle="modal" data-bs-target="#missingDogModal">Missing a Pet?</button>
            <button class="chip green" data-bs-toggle="modal" data-bs-target="#addEstablishmentModal">+ Add Establishment</button>
            <button class="chip purple" id="toggleMapBtn" onclick="toggleMapView()">Location</button>
        </div>
    </div>

    <div class="container-fluid px-lg-5">
        <div class="row d-flex align-items-start">
            
            <div class="col-lg-9">
                <div id="feed-container">
                    <?php while($post = $all_posts->fetch_assoc()): 
                        $p_id = $post['post_id'];
                        // Query counts for likes/comments
                        $lc = $conn->query("SELECT (SELECT COUNT(*) FROM post_likes WHERE post_id=$p_id) as tl, (SELECT COUNT(*) FROM post_comments WHERE post_id=$p_id) as tc")->fetch_assoc();
                    ?>
                        <div class="card mb-4 p-3 shadow-sm border-0 rounded-4 <?php echo (strpos($post['content'], '[MISSING DOG]') !== false) ? 'missing-dog-card' : ''; ?>">
                            <div class="d-flex align-items-center">
                                <img src="../images/homeImages/profile icon.png" class="rounded-circle me-2 clickable-user" width="40" height="40" onclick="viewUserProfile('<?php echo addslashes($post['full_name']); ?>')" />
                                <div>
                                    <div class="fw-bold clickable-user" onclick="viewUserProfile('<?php echo addslashes($post['full_name']); ?>')"><?php echo htmlspecialchars($post['full_name']); ?></div>
                                    <small class="text-muted"><?php echo date('M d, g:i a', strtotime($post['created_at'])); ?></small>
                                </div>
                                <div class="dropdown ms-auto">
                                    <div class="post-menu-btn dropdown-toggle" data-bs-toggle="dropdown">•••</div>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <?php if ($post['user_id'] == $current_user_id): ?>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editPostModal" onclick="fillEditModal(<?php echo $post['post_id']; ?>, '<?php echo addslashes($post['content']); ?>')"><i class="bi bi-pencil me-2"></i>Edit Post</a></li>
                                            <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmDelete(<?php echo $post['post_id']; ?>)"><i class="bi bi-trash3 me-2"></i>Delete Post</a></li>
                                        <?php else: ?>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#reportPostModal" onclick="document.getElementById('report_post_id').value=<?php echo $post['post_id']; ?>"><i class="bi bi-flag me-2"></i>Report Post</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>

                            <p class="mt-3"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                            <?php if ($post['image_url']): ?> <img src="../<?php echo $post['image_url']; ?>" class="post-image" /> <?php endif; ?>

                            <div class="mt-3 pt-2 border-top d-flex align-items-center gap-4">
                                <?php 
                                $isLiked = hasUserLiked($post['post_id'], $current_user_id, $conn); 
                                $count = $lc['tl'];
                                ?>
                                <div class="like-btn <?php echo $isLiked ? 'liked' : ''; ?>" onclick="handleLike(this, <?php echo $post['post_id']; ?>)">
                                    <i class="bi <?php echo $isLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up'; ?> me-1"></i> 
                                    <span class="like-label"><?php echo $isLiked ? 'Liked' : 'Like'; ?></span>
                                </div>

                                <small class="like-count-display" id="like-display-<?php echo $post['post_id']; ?>">
                                    <?php
                                    if ($isLiked) {
                                        echo ($count > 1) ? "You and " . ($count - 1) . " others" : "You liked this";
                                    } else {
                                        echo ($count > 0) ? $count . " likes" : "";
                                    }
                                    ?>
                                </small>
                                <div style="cursor:pointer" onclick="toggleComments(<?php echo $post['post_id']; ?>); document.getElementById('com-in-<?php echo $post['post_id']; ?>').focus();">
                                    <i class="bi bi-chat me-1"></i> Comment
                                </div>
                            </div>

                            <div class="mt-3" id="comment-wrapper-<?php echo $post['post_id']; ?>">
                                <?php if ($lc['tc'] > 0): ?>
                                    <div class="view-all-comments" id="toggle-text-<?php echo $post['post_id']; ?>" onclick="toggleComments(<?php echo $post['post_id']; ?>)">
                                        View all <?php echo $lc['tc']; ?> comments
                                    </div>
                                <?php endif; ?>

                                <div class="comment-container" id="comment-list-<?php echo $post['post_id']; ?>" style="display: none;">
                                    <?php 
                                    $pid = $post['post_id'];
                                    $comments = $conn->query("SELECT c.*, u.full_name FROM post_comments c JOIN users u ON c.user_id = u.user_id WHERE c.post_id = $pid ORDER BY c.created_at ASC");
                                    while($com = $comments->fetch_assoc()): ?>
                                        <div class="comment-item">
                                            <span class="comment-user clickable-user" onclick="viewUserProfile('<?php echo addslashes($com['full_name']); ?>')"><?php echo htmlspecialchars($com['full_name']); ?></span>
                                            <span><?php echo htmlspecialchars($com['comment_text']); ?></span>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>

                            <form action="main.php" method="POST" class="mt-3 d-flex align-items-center gap-2 comment-form">
                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                <input type="text" name="comment_text" id="com-in-<?php echo $post['post_id']; ?>" 
                                    class="form-control comment-input-pill border-0" 
                                    placeholder="Write a comment..." required>
                                <button type="submit" name="submit_comment" class="btn-send-circle">
                                    <i class="bi bi-send-fill"></i>
                                </button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div id="map-container" style="display: none;">
                    <div class="card border-0 shadow-sm rounded-4 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold m-0"><i class="bi bi-geo-alt-fill me-2 text-primary"></i>Pet-Friendly Establishments</h5>
                            <button class="btn btn-secondary btn-sm" onclick="toggleMapView()"><i class="bi bi-arrow-left"></i> Back to Feed</button>
                        </div>
                        <div id="map" style="height: 600px; width: 100%; border-radius: 15px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 sticky-column">
                <div class="sidebar-card">
                    <h6 class="fw-bold mb-3" style="color: #21a9ff">EXPLORE</h6>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d61633.24294026367!2d120.55171720815411!3d15.132931818146714!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3396f2723c347f31%3A0x7d2427b3b3e3e00e!2sAngeles%2C%20Pampanga!5e0!3m2!1sen!2sph!4v1700000000000!5m2!1sen!2sph" width="100%" height="150" style="border:0;border-radius:10px;" allowfullscreen="" loading="lazy"></iframe>
                    <button class="btn btn-primary w-100 mt-2 btn-sm">Explore Angeles Now!</button>
                </div>

                <div class="faq-section mb-4">
                    <h4 class="faq-title">Have a Question?</h4>
                    <p class="faq-subtitle">Here are some FAQ's</p>
                    <div class="faq-item">
                        <div class="faq-question">Is SESE free to use?</div>
                        <div class="faq-answer">Yes. SESE is free for pet owners to explore pet- friendly places, read community posts, and view Lost & Found reports.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">How do I share a pet-friendly place?</div>
                    </div>
                    <div class="faq-btn-group">
                        <a href="../mains/about.php">Read More</a>
                        <a href="../mains/contact.php">Ask Now</a>
                    </div>
                </div>

                <div class="sidebar-card">
                    <h6 class="fw-bold mb-3">Community Rules</h6>
                    <ul class="small text-muted ps-3">
                        <li>Be kind to fellow pet owners.</li>
                        <li>Report missing pets immediately.</li>
                        <li>No spam or unrelated content.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../features/modal_establishments.php'; ?>
    <script>
        const API_BASE_URL = "../features/handle_establishments.php";
        const AUTO_INIT_MAP = false;
    </script>
    <script>
        const establishmentData = <?php echo json_encode($active_establishments); ?>;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../script/map_init.js"></script>

    <script>
        // NEW: View User Profile Popup
        function viewUserProfile(name) {
            document.getElementById('popup_user_name').innerText = name;
            var myModal = new bootstrap.Modal(document.getElementById('userProfileModal'));
            myModal.show();
        }

        // FUNCTION TO TOGGLE COMMENTS
        function toggleComments(postId) {
            const list = document.getElementById('comment-list-' + postId);
            const toggleText = document.getElementById('toggle-text-' + postId);
            if (list.style.display === "none") {
                list.style.display = "block";
                if(toggleText) toggleText.innerText = "Hide comments";
            } else {
                list.style.display = "none";
                if(toggleText) toggleText.innerText = "View all comments";
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('missing_preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function fillEditModal(id, content) {
            document.getElementById('edit_post_id').value = id;
            document.getElementById('edit_content').value = content;
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => { if (result.isConfirmed) window.location.href = 'main.php?delete_id=' + id; });
        }

        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Logout'
            }).then((result) => { if (result.isConfirmed) window.location.href = 'main.php?action=logout'; });
        }

        function handleLike(btn, pid) {
            fetch('../process/handle_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'like', post_id: pid })
            })
            .then(res => res.json())
            .then(data => {
                const icon = btn.querySelector('i');
                const label = btn.querySelector('.like-label');
                const display = document.getElementById('like-display-' + pid);
                if (data.status === 'liked') {
                    btn.classList.add('liked');
                    icon.classList.replace('bi-hand-thumbs-up', 'bi-hand-thumbs-up-fill');
                    label.innerText = "Liked";
                    display.innerText = (data.new_count > 1) ? "You and " + (data.new_count - 1) + " others" : "You liked this";
                } else {
                    btn.classList.remove('liked');
                    icon.classList.replace('bi-hand-thumbs-up-fill', 'bi-hand-thumbs-up');
                    label.innerText = "Like";
                    display.innerText = (data.new_count > 0) ? data.new_count + " likes" : "";
                }
            })
            .catch(err => console.error("Like failed:", err));
        }

        document.querySelectorAll('.comment-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const postId = this.querySelector('input[name="post_id"]').value;
                const commentInput = this.querySelector('input[name="comment_text"]');
                const commentText = commentInput.value;

                if(!commentText.trim()) return;

                fetch('../process/handle_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        action: 'comment', 
                        post_id: postId, 
                        text: commentText 
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success' || data.message === 'Comment posted') {
                        const container = document.getElementById('comment-list-' + postId);
                        const toggleText = document.getElementById('toggle-text-' + postId);
                        
                        const newComment = document.createElement('div');
                        newComment.className = 'comment-item';
                        newComment.innerHTML = `<span class="comment-user clickable-user" onclick="viewUserProfile('${data.user_name}')">${data.user_name}</span> <span>${data.comment}</span>`;
                        container.appendChild(newComment);
                        
                        container.style.display = 'block';
                        if(toggleText) toggleText.innerText = "Hide comments";
                        
                        commentInput.value = ''; 
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Comment Successful!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Something went wrong', 'error');
                    }
                })
                .catch(err => {
                    console.error("Submission failed:", err);
                    Swal.fire('Error', 'Server connection failed.', 'error');
                });
            });
        });

        const msg = new URLSearchParams(window.location.search).get('msg');
        if (msg === 'missing_posted') Swal.fire('Alert Shared!', 'Success', 'success');
        if (msg === 'reported') Swal.fire('Thank you!', 'Report submitted.', 'success');
        if (msg === 'posted') Swal.fire('Success', 'Post shared!', 'success');
        if (msg === 'deleted') Swal.fire('Deleted!', 'Removed.', 'success');
        if (msg === 'updated') Swal.fire('Updated!', 'Saved.', 'success');
        if (msg === 'commented') Swal.fire('Success', 'Comment Successful!', 'success');
    </script>
</body>
</html>