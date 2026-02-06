<?php
session_start();
// Database connection
$conn = new mysqli("localhost", "root", "", "capstone_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Logout Logic
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: ../index.php"); 
    exit();
}

$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 7;

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

// --- 5. COMMENT LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
    $post_id = intval($_POST['post_id']);
    $comment_text = $conn->real_escape_string($_POST['comment_text']);
    $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $post_id, $current_user_id, $comment_text);
    $stmt->execute();
    header("Location: main.php");
    exit();
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

$all_posts = $conn->query("SELECT p.*, u.full_name, 
    (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id) as total_likes 
    FROM posts p 
    JOIN users u ON p.user_id = u.user_id 
    ORDER BY p.created_at DESC");
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
        .comment-user { font-weight: normal; color: #050505; margin-right: 5px; }
        .sidebar-card { background: #fff; border-radius: 15px; padding: 15px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .like-count-display { font-size: 0.85rem; color: #65676b; margin-left: -15px; }
        .missing-dog-card { border-left: 5px solid #dc3545; background-color: #fff9f9; }
        
        /* FAQ Styles */
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
    </style>
</head>
<body class="home-body">

    <div class="modal fade" id="editPostModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form action="main.php" method="POST" enctype="multipart/form-data">
            <div class="modal-header"><h5>Edit Post</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="edit_post_id" id="edit_post_id">
                <textarea name="edit_content" id="edit_content" class="form-control mb-3" rows="4" required></textarea>
                <input type="file" name="edit_image" class="form-control">
            </div>
            <div class="modal-footer"><button type="submit" name="update_post" class="btn btn-primary w-100">Save Changes</button></div>
        </form>
    </div></div></div>

    <div class="modal fade" id="reportPostModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form action="main.php" method="POST">
            <div class="modal-header"><h5>Report Post</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
            <div class="modal-footer"><button type="submit" name="submit_report" class="btn btn-danger w-100">Submit Report</button></div>
        </form>
    </div></div></div>

    <div class="modal fade" id="createPostModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form action="main.php" method="POST" enctype="multipart/form-data"><div class="modal-header"><h5>Create Post</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><textarea name="post_content" class="form-control mb-3" rows="4" placeholder="What's on your mind?" required></textarea><input type="file" name="post_image" class="form-control"></div><div class="modal-footer"><button type="submit" name="submit_post" class="btn btn-primary">Post</button></div></form></div></div></div>

    <div class="modal fade" id="missingDogModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form id="missingDogForm" action="main.php" method="POST" enctype="multipart/form-data">
            <div class="modal-header bg-danger text-white"><h5>Report Missing Dog</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label small fw-bold">Dog Details</label>
                <input type="text" name="dog_name" class="form-control mb-2" placeholder="Dog's Name" required>
                <input type="text" name="last_seen" class="form-control mb-2" placeholder="Last Seen Location" required>
                <input type="text" name="contact_info" class="form-control mb-2" placeholder="Your Contact Number" required>
                
                <label class="form-label small fw-bold mt-2">Upload Photo</label>
                <input type="file" name="missing_image" id="missing_image_input" class="form-control" accept="image/*" onchange="previewImage(this)">
                <img id="missing_preview" src="#" alt="Preview">
            </div>
            <div class="modal-footer">
                <button type="submit" name="submit_missing" class="btn btn-danger w-100">Post Alert</button>
            </div>
        </form>
    </div></div></div>

    <header class="topbar container mx-auto rounded-3 mt-3">
        <div class="topbar-inner">
            <div class="logo"><a href="#"><img src="../images/homeImages/Sese-Logo3.png" alt="Logo" /></a></div>
            <nav class="top-menu">
                <a class="active">Home</a>
                <a href="lostAndFound.html">Lost & Found</a>
                <a href="aboutUs.html">About Us</a>
                <a href="contactUs.html">Contact Us</a>
            </nav>
            <div class="top-icons">
                <div class="icon-wrapper avatar-wrapper" onclick="document.getElementById('dropL').style.display = (document.getElementById('dropL').style.display == 'block') ? 'none' : 'block';">
                    <img src="../images/homeImages/profile icon.png" class="icon-img avatar" />
                    <div class="notif-dropdown" id="dropL" style="display:none;">
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
            <button class="chip red" style="background:#dc3545; color:white" data-bs-toggle="modal" data-bs-target="#missingDogModal">Missing Dog?</button>
            <button class="chip purple">Location</button>
            <button class="chip green">Pet Allowed</button>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row justify-content-center">
            
            <div class="col-xl-5 col-lg-6">
                <?php while($post = $all_posts->fetch_assoc()): ?>
                    <div class="card mb-4 p-3 shadow-sm border-0 rounded-4 <?php echo (strpos($post['content'], '[MISSING DOG]') !== false) ? 'missing-dog-card' : ''; ?>">
                        <div class="d-flex align-items-center">
                            <img src="../images/homeImages/profile icon.png" class="rounded-circle me-2" width="40" height="40" />
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($post['full_name']); ?></div>
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
                            $count = $post['total_likes'];
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
                            <div style="cursor:pointer" onclick="document.getElementById('com-in-<?php echo $post['post_id']; ?>').focus();"><i class="bi bi-chat me-1"></i> Comment</div>
                        </div>

                        <div class="mt-3">
                            <?php 
                            $pid = $post['post_id'];
                            $comments = $conn->query("SELECT c.*, u.full_name FROM post_comments c JOIN users u ON c.user_id = u.user_id WHERE c.post_id = $pid ORDER BY c.created_at ASC");
                            while($com = $comments->fetch_assoc()): ?>
                                <div class="comment-item">
                                    <span class="comment-user"><?php echo htmlspecialchars($com['full_name']); ?></span>
                                    <span><?php echo htmlspecialchars($com['comment_text']); ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <form action="main.php" method="POST" class="mt-2 d-flex gap-2">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                            <input type="text" name="comment_text" id="com-in-<?php echo $post['post_id']; ?>" class="form-control rounded-pill bg-light border-0" placeholder="Write a comment..." required>
                            <button type="submit" name="submit_comment" class="btn btn-sm btn-primary rounded-circle"><i class="bi bi-send"></i></button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="col-xl-3 col-lg-4">
                <div class="sidebar-card">
                    <h6 class="fw-bold mb-3" style="color: #21a9ff">EXPLORE</h6>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d123281.8210344795!2d120.536962!3d15.144983!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3396f26555555555%3A0x123456789abcdef!2sAngeles%2C%20Pampanga!5e0!3m2!1sen!2sph!4v1700000000000" width="100%" height="150" style="border:0;border-radius:10px;"></iframe>
                    <button class="btn btn-primary w-100 mt-2 btn-sm">Explore Angeles Now!</button>
                </div>

                <div class="faq-section mb-4">
                    <h4 class="faq-title">Have a Question?</h4>
                    <p class="faq-subtitle">Here are some FAQ's</p>
                    <div class="faq-item">
                        <div class="faq-question">Is SESE free to use?</div>
                        <div class="faq-answer">Yes. SESE is free for pet owners to explore pet- friendly places, read community posts, and view Lost & Found reports. Creating an account allows you to share posts, reviews, and pet-friendly locations with the community.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">How do I share a pet-friendly place?</div>
                        <div class="faq-answer">After logging in, go to the Home page and click "Share a Furrendly Place." Fill in the details such as description, photos, location, and star rating, then submit your post. Your shared place will appear on the community feed and help other pet owners discover pet-inclusive spots.</div>
                    </div>
                    <div class="faq-btn-group">
                        <button class="faq-btn-read">Read more</button>
                        <button class="faq-btn-ask">Ask now</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Image Preview for Missing Dog
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

        // SWAL for Missing Dog Submission
        document.getElementById('missingDogForm').onsubmit = function() {
            Swal.fire({
                title: 'Posting Alert...',
                text: 'Please wait while we notify the community.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        };

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
            });
        }

        const msg = new URLSearchParams(window.location.search).get('msg');
        if (msg === 'missing_posted') Swal.fire('Alert Shared!', 'We hope you find your furry friend soon.', 'success');
        if (msg === 'reported') Swal.fire('Thank you!', 'Your report has been submitted.', 'success');
        if (msg === 'posted') Swal.fire('Success', 'Post shared!', 'success');
        if (msg === 'deleted') Swal.fire('Deleted!', 'The post has been removed.', 'success');
        if (msg === 'updated') Swal.fire('Updated!', 'Your changes have been saved.', 'success');
    </script>
</body>
</html>