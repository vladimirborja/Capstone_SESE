<?php
// signup.php
session_start();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Sign Up</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/signup.css" />
</head>

<body class="signup-body">
  <div class="login-wrapper" style="background: url('images/signImages/bg1.png') no-repeat center center; background-size: cover;">
    <div class="signup-wrapper">
      <div class="signup-card text-center">
        <div class="signup-logo mb-3">
          <img src="images/signImages/logo.png" alt="Logo" />
        </div>

        <h5 class="fw-bold fs-3 signup-title">SIGN UP</h5>

        <div id="alertMessage" class="alert alert-dismissible fade d-none" role="alert">
          <span id="alertText"></span>
          <button type="button" class="btn-close" onclick="hideAlert()"></button>
        </div>

        <form id="signupForm">
          <div class="row g-2 mb-2">
            <div class="col">
              <label class="form-label text-start w-100" style="color: #1e88ff">First name</label>
              <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First name" required />
            </div>
            <div class="col">
              <label class="form-label text-start w-100" style="color: #1e88ff">Last name</label>
              <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last name" required />
            </div>
          </div>

          <div class="mb-2 text-start">
            <label class="form-label" style="color: #1e88ff">Email address</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Email address" required />
          </div>

          <div class="mb-2 text-start">
            <label class="form-label" style="color: #1e88ff">Phone number</label>
            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="Phone number" required />
          </div>

          <div class="mb-2 position-relative text-start">
            <label class="form-label" style="color: #1e88ff">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password (min. 8 characters)" required />
            <span class="password-toggle" onclick="togglePassword('password', this)" style="cursor:pointer; position: absolute; right: 10px; top: 38px;">👁</span>
          </div>

          <div class="mb-2 position-relative text-start">
            <label class="form-label" style="color: #1e88ff">Repeat password</label>
            <input type="password" class="form-control" id="repeatPassword" name="repeatPassword" placeholder="Repeat password" required />
            <span class="password-toggle" onclick="togglePassword('repeatPassword', this)" style="cursor:pointer; position: absolute; right: 10px; top: 38px;">👁</span>
          </div>

          <input type="hidden" id="role" name="role" value="user" />

          <button type="submit" class="btn btn-primary w-100 mb-2" id="signupBtn">
            Sign Up
          </button>
        </form>

        <p class="signup-text mb-0">
          Already have an account? <a href="signIn.php">Sign in</a>
        </p>

        <div class="mt-2">
          <a href="http://localhost/Capstone/src/index.php" class="btn btn-outline-primary btn-sm">
            ← Back to Home
          </a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    function togglePassword(fieldId, icon) {
      const passwordField = document.getElementById(fieldId);
      const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
      passwordField.setAttribute("type", type);
      icon.textContent = type === "password" ? "👁" : "🙈";
    }

    function hideAlert() {
      const alertDiv = document.getElementById('alertMessage');
      alertDiv.className = 'alert alert-dismissible fade d-none';
    }

    document.getElementById('signupForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const signupBtn = document.getElementById('signupBtn');
      signupBtn.disabled = true;
      signupBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing up...';

      const formData = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        email: document.getElementById('email').value,
        phoneNumber: document.getElementById('phoneNumber').value,
        password: document.getElementById('password').value,
        repeatPassword: document.getElementById('repeatPassword').value,
        role: document.getElementById('role').value
      };

      try {
        const response = await fetch('signup_process.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: result.message,
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            // Redirect to sign in with status parameter
            window.location.href = 'signIn.php?status=registered';
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Registration Failed',
            text: result.message,
            confirmButtonColor: '#1e88ff'
          });
          signupBtn.disabled = false;
          signupBtn.textContent = 'Sign Up';
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'An unexpected error occurred. Please try again.',
          confirmButtonColor: '#1e88ff'
        });
        signupBtn.disabled = false;
        signupBtn.textContent = 'Sign Up';
      }
    });
  </script>
</body>

</html>