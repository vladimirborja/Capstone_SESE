<?php
ob_start();
session_start();

// 1. Load your existing database config
if (!file_exists('db_config.php')) {
    die("Database configuration file not found");
}
require_once 'db_config.php';

$error = '';
$success = false;

// 2. Handle the Password Reset Logic
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['email'])) {
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // --- PASSWORD VALIDATION RULES ---
    // At least 8 chars, Upper, Lower, Number, and Symbol (including dot)
    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-+?.]).{8,}$/';

    if (!preg_match($passwordPattern, $new_password)) {
        // Simplified error message as requested
        $error = "Password must be at least 8 characters and include a mixture of uppercase, lowercase, numbers, and symbols.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists in your 'users' table
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "Email is not registered.";
        } else {
            // Hash the password using the standard PHP algorithm
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update the password in your database
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            if ($update->execute([$hashed_password, $email])) {
                $success = true;
            } else {
                $error = "Failed to update password. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css" />
    <style>
        .form-label { margin-bottom: 2px !important; font-size: 0.9rem; }
        .mb-3 { margin-bottom: 0.75rem !important; }
        .password-toggle { cursor: pointer; color: #1e88ff; }
    </style>
</head>
<body>
    <div class="login-wrapper" style="background: url('images/signImages/bg1.png') no-repeat center center; background-size: cover; min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div class="login-card text-center" style="background: white; padding: 2rem; border-radius: 15px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div class="logo mb-1">
                <img src="images/signImages/logo.png" alt="SESE Logo" style="max-width: 100px;" />
            </div>

            <h4 class="fw-bold fs-2 mb-3 mt-1" style="color: #1e88ff">FORGOT PASSWORD</h4>

            <form method="POST" action="">
                <div class="mb-2 text-start">
                    <label class="form-label" style="color: #1e88ff">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter registered email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                </div>

                <div class="mb-2 text-start">
                    <label class="form-label" style="color: #1e88ff">New Password</label>
                    <div class="position-relative">
                        <input type="password" id="new_password" name="new_password" class="form-control" 
                               placeholder="New password" 
                               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()\-+?.]).{8,}"
                               title="At least 8 characters, including upper, lower, number, and symbol."
                               required />
                        <span class="position-absolute" style="top: 50%; right: 10px; transform: translateY(-50%);" onclick="togglePass('new_password', 'icon1')">
                            <i id="icon1" class="fas fa-eye-slash password-toggle"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3 text-start">
                    <label class="form-label" style="color: #1e88ff">Confirm Password</label>
                    <div class="position-relative">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm password" required />
                        <span class="position-absolute" style="top: 50%; right: 10px; transform: translateY(-50%);" onclick="togglePass('confirm_password', 'icon2')">
                            <i id="icon2" class="fas fa-eye-slash password-toggle"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-2" style="background-color: #1e88ff; border: none;">
                    Reset Password
                </button>

                <div class="mt-1">
                    <a href="signIn.php" class="btn btn-outline-primary btn-sm py-0">
                        ← Back to Sign In
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                input.type = "password";
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }

        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Password reset successful!',
                confirmButtonColor: '#1e88ff'
            }).then(() => {
                window.location.href = 'signIn.php';
            });
        <?php elseif (!empty($error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $error; ?>',
                confirmButtonColor: '#1e88ff'
            });
        <?php endif; ?>
    </script>
</body>
</html>