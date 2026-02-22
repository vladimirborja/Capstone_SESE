<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: manage_users.php");
    } else {
        header("Location: mains/main.php");
    }
    exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Sign In</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="favicon.png" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css" />
  <style>
    .form-label { margin-bottom: 2px !important; font-size: 0.9rem; }
    .mb-3 { margin-bottom: 0.75rem !important; }
    .mb-2 { margin-bottom: 0.5rem !important; }
    .back-link {
        transition: color 0.2s ease, transform 0.2s ease;
    }
    .back-link:hover {
        color: #0d6efd !important; /* Bootstrap primary color */
        transform: translateX(-3px); /* Subtle nudge to the left */
    }
  </style>
</head>

<body>
  <div class="login-wrapper" style="background: url('images/signImages/bg1.png') no-repeat center center; background-size: cover; min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div class="login-card text-center bg-white p-4 shadow-sm rounded">
      <div class="logo mb-1">
        <img src="images/signImages/logo.png" alt="SESE Logo" />
      </div>

      <h4 class="fw-bold fs-2 mb-3 mt-1" style="color: #1e88ff">SIGN IN</h4>

      <div id="alertMessage" class="alert alert-dismissible fade d-none p-2" role="alert">
        <span id="alertText"></span>
        <button type="button" class="btn-close" onclick="hideAlert()"></button>
      </div>

      <form id="signinForm">
        <div class="mb-2 text-start">
          <label class="form-label" style="color: #1e88ff">Email or Phone number</label>
          <input type="text" class="form-control" id="emailOrPhone" name="emailOrPhone" placeholder="Email or Phone number" required />
        </div>

        <div class="mb-2 text-start">
          <label class="form-label" style="color: #1e88ff">Password</label>
          <div class="position-relative">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required />
            <span class="position-absolute" style="top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer;" id="togglePassword">
              <i id="toggle-password-icon" class="fas fa-eye-slash" style="color: #1e88ff"></i>
            </span>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="form-check" style="color: #1e88ff; font-size: 0.85rem;">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" />
            <label class="form-check-label" for="remember">Remember me</label>
          </div>
          <a href="forgot_pass.php" class="forgot-link" style="font-size: 0.85rem; text-decoration: none;">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-2" id="signinBtn">Sign In</button>

        <p class="signup-text mb-1" style="color: #1e88ff; font-size: 0.9rem;">
          Don't have an account? <a href="signUp.php" style="text-decoration: none;">Sign up</a>
        </p>
        <div class="text-center mb-3 font-weight-bold">
          <span class="text-muted" style="font-size: 0.8rem; font-weight: 500;">OR</span>
        </div>

        <?php 
          require_once 'google_api.php'; 
          $auth_url = $client->createAuthUrl();
        ?>

        <a href="<?= filter_var($auth_url, FILTER_SANITIZE_URL) ?>" 
          class="btn btn-light w-100 mb-4 py-2 border shadow-sm d-flex align-items-center justify-content-center"
          style="font-weight: 500; border-radius: 8px; transition: all 0.3s ease;">
            
            <img src="https://developers.google.com/identity/images/g-logo.png" 
                style="width: 20px; border-radius: 8px;" 
                class="me-2" 
                alt="Google Logo">
            
            <span style="color: #3c4043;">Sign in with Google</span>
        </a>

        <div class="mt-4 text-center">
            <a href="index.php" 
              class="text-decoration-none text-muted small d-inline-flex align-items-center back-link border shadow-sm px-3 py-1" 
              style="border-radius: 8px;" >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                </svg>
                Back to Home
            </a>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    // Handle status from Registration
    window.onload = function() {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('status') === 'verified') {
        Swal.fire({
          icon: 'success',
          title: 'Account Verified!',
          text: 'You can now sign in with your new account.',
          confirmButtonColor: '#1e88ff'
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      }
    };

    // Password Toggle Logic
    document.getElementById('togglePassword').addEventListener('click', function () {
      const passwordField = document.getElementById('password');
      const icon = document.getElementById('toggle-password-icon');
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      } else {
        passwordField.type = 'password';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      }
    });

    function hideAlert() {
      const alertDiv = document.getElementById('alertMessage');
      alertDiv.classList.add('d-none');
    }

    // Sign In Logic
    document.getElementById('signinForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const signinBtn = document.getElementById('signinBtn');
      
      signinBtn.disabled = true;
      signinBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing in...';

      const formData = {
        emailOrPhone: document.getElementById('emailOrPhone').value,
        password: document.getElementById('password').value,
        remember: document.getElementById('remember').checked
      };

      try {
        const response = await fetch('signIn_process.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(formData)
        });
        
        // This parses the JSON response from your signIn_process.php
        const result = await response.json();
        
        if (result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Welcome Back!',
            text: result.message || 'Login successful.',
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            // Check the role returned from your process script
            window.location.href = result.user.role === 'admin' ? 'manage_users.php' : 'mains/main.php';
          });
        } else {
          Swal.fire({ 
            icon: 'error', 
            title: 'Login Failed', 
            text: result.message || 'Invalid credentials',
            confirmButtonColor: '#1e88ff'
          });
          signinBtn.disabled = false;
          signinBtn.textContent = 'Sign In';
        }
      } catch (error) {
        console.error("Error details:", error);
        Swal.fire({ 
          icon: 'error', 
          title: 'System Error', 
          text: 'Could not connect to the server. Check if signIn_process.php exists.',
          confirmButtonColor: '#1e88ff'
        });
        signinBtn.disabled = false;
        signinBtn.textContent = 'Sign In';
      }
    });
  </script>
</body>
</html>