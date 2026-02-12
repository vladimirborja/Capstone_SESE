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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/signup.css" />
  <style>
    .form-label { margin-bottom: 2px !important; font-size: 0.9rem; }
    .mb-2 { margin-bottom: 0.5rem !important; }
    .row.g-2.mb-2 { margin-bottom: 0.5rem !important; }
    .terms-text { font-size: 0.85rem; color: #666; }
    .terms-link { color: #1e88ff; text-decoration: none; font-weight: bold; cursor: pointer; }
    /* Ensure modal text is readable */
    .modal-body { text-align: left; font-size: 0.9rem; color: #444; line-height: 1.5; }
  </style>
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
            <div class="position-relative">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password (min. 8 characters)" required />
                <span class="password-toggle" onclick="togglePassword('password', this)" style="cursor:pointer; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">
                    <i class="fas fa-eye-slash" style="color: #1e88ff"></i>
                </span>
            </div>
          </div>

          <div class="mb-2 position-relative text-start">
            <label class="form-label" style="color: #1e88ff">Repeat password</label>
            <div class="position-relative">
                <input type="password" class="form-control" id="repeatPassword" name="repeatPassword" placeholder="Repeat password" required />
                <span class="password-toggle" onclick="togglePassword('repeatPassword', this)" style="cursor:pointer; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">
                    <i class="fas fa-eye-slash" style="color: #1e88ff"></i>
                </span>
            </div>
          </div>

          <div class="mb-3 form-check text-start">
            <input type="checkbox" class="form-check-input" id="terms" required>
            <label class="form-check-label terms-text" for="terms">
              I agree to the <span class="terms-link" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</span>
            </label>
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

  <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="termsModalLabel" style="color: #1e88ff">Terms and Conditions</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <h6>1. Account Security</h6>
          <p>By registering, you agree to provide accurate information and maintain a strong password (at least 8 characters, including uppercase, lowercase, numbers, and symbols).</p>
          
          <h6>2. Data Privacy</h6>
          <p>We respect your privacy. Your data is stored securely and is used solely for the purpose of managing your account and providing our services.</p>
          
          <h6>3. Prohibited Use</h6>
          <p>You may not use this platform for any illegal or unauthorized purposes. Bypassing security or providing false identities is strictly prohibited.</p>
          
          <h6>4. User Agreement</h6>
          <p>By checking the box on the registration form, you acknowledge that you have read and understood these terms in full.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background-color: #1e88ff; border: none;">I Understand</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    function togglePassword(fieldId, wrapper) {
      const passwordField = document.getElementById(fieldId);
      const icon = wrapper.querySelector('i');
      if (passwordField.getAttribute("type") === "password") {
        passwordField.setAttribute("type", "text");
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      } else {
        passwordField.setAttribute("type", "password");
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      }
    }

    function hideAlert() {
      const alertDiv = document.getElementById('alertMessage');
      alertDiv.className = 'alert alert-dismissible fade d-none';
    }

    document.getElementById('signupForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      // Check for Terms Acceptance before sending
      if (!document.getElementById('terms').checked) {
        Swal.fire({
          icon: 'warning',
          title: 'Requirement',
          text: 'You must agree to the Terms and Conditions.',
          confirmButtonColor: '#1e88ff'
        });
        return;
      }

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
        role: document.getElementById('role').value,
        terms: document.getElementById('terms').checked 
      };

      try {
        const response = await fetch('signUp_process.php', {
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
        console.error("Error details:", error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'An unexpected error occurred. Check if signUp.php exists.',
          confirmButtonColor: '#1e88ff'
        });
        signupBtn.disabled = false;
        signupBtn.textContent = 'Sign Up';
      }
    });
  </script>
</body>
</html>