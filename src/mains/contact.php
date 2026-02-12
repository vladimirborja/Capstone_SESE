<?php 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SESE | About Us</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/styles.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<body>

<?php include 'header.php'; ?>
<section id="contact" class="contact-section">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-7">
                <div class="contact-form-wrapper">
                    <h2 class="contact-main-title">Contact Us</h2>
                    <h5 class="connect-subtitle">Let's Connect</h5>
                    <p class="form-intro">For inquiries, partnerships, or support.</p>

                   <form class="contact-form" action="../send-message.php" method="POST">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" placeholder="First name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" placeholder="Last name" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Details</label>
                            <div class="input-group">
                                <span class="input-group-text">+63</span>
                                <input type="tel" name="contact" class="form-control" placeholder="Contact number">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="Subject">
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Message ..." required></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-send-blue mt-4 fs-5">
                            Send <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>

                </div>
            </div>

            <div class="col-lg-5">
                <div class="contact-info-card">
                    <h3 class="contact-info-title">Get in Touch with the SESE Team.</h3>

                    <div class="info-inner-box">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Mobile Number:</h4>
                            <p>+63 912 345 6789</p>
                        </div>
                    </div>

                    <div class="info-inner-box">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email Address:</h4>
                            <p>sese@hau.dev</p>
                        </div>
                    </div>

                    <div class="info-inner-box">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Address:</h4>
                            <p>#1 Holy Angel St., Angeles City, Pampanga, Philippines 2009</p>
                        </div>
                    </div>

                    <div class="social-media-section">
                        <h4 class="social-text">Follow us on social media for the latest updates!</h4>
                        <div class="social-grid">
                            <a href="https://www.facebook.com/profile.php?id=61588023028977" class="social-box fb"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-box ig"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-box tk"><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        Swal.fire({
            title: 'Success!',
            text: 'Your message has been sent successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
</script>

</body>
</html>