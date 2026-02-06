<?php
session_start();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Sign In</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css" />
</head>

<body>
  <div class="login-wrapper" style="background: url('images/signImages/bg1.png') no-repeat center center; background-size: cover;">
    <div class="login-card text-center">
      <div class="logo mb-0 pb-0">
        <img src="images/signImages/logo.png" alt="SESE Logo" />
      </div>

      <h4 class="fw-bold fs-2 mb-4 mt-2" style="color: #1e88ff">SIGN IN</h4>

      <div id="alertMessage" class="alert alert-dismissible fade d-none" role="alert">
        <span id="alertText"></span>
        <button type="button" class="btn-close" onclick="hideAlert()"></button>
      </div>

      <form id="signinForm">
        <div class="mb-0 text-start">
          <label class="form-label" style="color: #1e88ff">Email or Phone number</label>
          <input type="text" class="form-control" id="emailOrPhone" name="emailOrPhone" placeholder="Email or Phone number" required />
        </div>

        <div class="mb-1 text-start position-relative">
          <label class="form-label" style="color: #1e88ff">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Password" required />
          <span class="password-toggle" onclick="togglePassword()" style="cursor:pointer; position: absolute; right: 10px; top: 38px;">👁</span>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check" style="color: #1e88ff">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" />
            <label class="form-check-label" for="remember">
              Remember me
            </label>
          </div>
          <a href="#" class="forgot-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3" id="signinBtn">
          Sign In
        </button>

        <p class="signup-text" style="color: #1e88ff">
          Don't have an account? <a href="signUp.php">Sign up</a>
        </p>

        <div class="mt-2">
          <a href="http://localhost/Capstone/src/index.php" class="btn btn-outline-primary btn-sm">
            ← Back to Home
          </a>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // Check for "Account Created" status from Sign Up page
    window.onload = function() {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('status') === 'registered') {
        Swal.fire({
          icon: 'success',
          title: 'Account Created!',
          text: 'You can now sign in with your new account.',
          confirmButtonColor: '#1e88ff'
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      }
    };

    function togglePassword() {
      const passwordInput = document.getElementById("password");
      const toggleIcon = document.querySelector(".password-toggle");
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.textContent = "🙈";
      } else {
        passwordInput.type = "password";
        toggleIcon.textContent = "👁";
      }
    }

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
        const response = await fetch('signin_process.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Welcome Back!',
            text: result.message,
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            if (result.user.role === 'admin') {
              window.location.href = 'manage_users.php';
            } else {
              window.location.href = 'mains/main.php';
            }
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: result.message,
            confirmButtonColor: '#1e88ff'
          });
          signinBtn.disabled = false;
          signinBtn.textContent = 'Sign In';
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'An error occurred. Please try again.',
          confirmButtonColor: '#1e88ff'
        });
        signinBtn.disabled = false;
        signinBtn.textContent = 'Sign In';
      }
    });
  </script>
</body>

</html>