<?php
session_start();

$login_message = '';
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true) {
    $login_message = 'yes';
    unset($_SESSION['login_success']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SESE - Explore Angeles City, One Wag and Whisker at a Time</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="main.css">
</head>

<body>
    <!-- LANDING PAGE SECTION -->
    <?php include 'sections/header.php'; ?>
    <?php include 'sections/home.php'; ?>
    <?php include 'sections/explore.php'; ?>
    <?php include 'sections/lost-found.php'; ?>
    <?php include 'sections/about-us.php'; ?>
    <?php include 'sections/contact-us.php'; ?>
    <?php include 'includes/footer.php'; ?>

    
    

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);

            // LOGOUT SWAL
            if (urlParams.get('logout') === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Logged Out',
                    text: 'Logout successfully!',
                    timer: 2500,
                    showConfirmButton: false
                });
                // Clean the URL so the alert doesn't show again on refresh
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // LOGIN SWAL (Based on your PHP variable)
            var loginStatus = "<?php echo $login_message; ?>";
            if (loginStatus === "yes") {
                Swal.fire({
                    icon: 'success',
                    title: 'Welcome!',
                    text: 'Logged in successfully!',
                    timer: 2500,
                    showConfirmButton: false
                });
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            var lostOwl = $('#lost_found_carousel');

            lostOwl.owlCarousel({
                loop: true,
                center: false,
                margin: 15,
                autoplay: true,
                autoplayTimeout: 2500,
                autoplaySpeed: 600,
                autoplayHoverPause: false,
                dots: false,
                nav: false,
                mouseDrag: true,
                touchDrag: true,
                smartSpeed: 600,
                fluidSpeed: true,
                responsive: {
                    0: { items: 1, margin: 10 },
                    576: { items: 2, margin: 12 },
                    768: { items: 3, margin: 15 },
                    1024: { items: 4, margin: 15 }
                }
            });

            $('.lost-prev-btn').click(function() {
                lostOwl.trigger('prev.owl.carousel');
            });

            $('.lost-next-btn').click(function() {
                lostOwl.trigger('next.owl.carousel');
            });

            $('.lost-prev-btn, .lost-next-btn').click(function() {
                lostOwl.trigger('stop.owl.autoplay');
                lostOwl.trigger('play.owl.autoplay');
            });

            lostOwl.on('dragged.owl.carousel', function() {
                lostOwl.trigger('stop.owl.autoplay');
                lostOwl.trigger('play.owl.autoplay');
            });
        });
    </script>

</body>
</html>