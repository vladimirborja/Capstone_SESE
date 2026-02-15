<?php 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SESE | About Us</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">

    <style>
        /* 1. ENTRY REVEAL ANIMATION */
        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(50px);
                filter: blur(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
                filter: blur(0);
            }
        }

        /* 2. BREATHING FLOATING EFFECT */
        @keyframes breathing {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .team-section {
            padding: 80px 0;
            background-color: #ffffff;
        }

        .team-card {
            background: #fff;
            border-radius: 30px;
            padding: 40px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            overflow: hidden;
            border: 1px solid #eee;
            /* Apply entry animation */
            animation: cardEntrance 0.8s ease-out backwards;
        }

        /* Staggered entry timing */
        .col-lg-3:nth-child(1) .team-card { animation-delay: 0.1s; }
        .col-lg-3:nth-child(2) .team-card { animation-delay: 0.2s; }
        .col-lg-3:nth-child(3) .team-card { animation-delay: 0.3s; }
        .col-lg-3:nth-child(4) .team-card { animation-delay: 0.4s; }

        /* 3. HOVER "POP-OUT" ANIMATION */
        .team-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 50px rgba(17, 119, 254, 0.2);
            border-color: #1177FE;
        }

        .team-avatar {
            width: 140px;
            height: 140px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #f0f7ff;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all 0.5s ease;
        }

        .team-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* The "Pop-Out" effect: Image grows and shadow deepens */
        .team-card:hover .team-avatar img {
            transform: scale(1.2) translateY(-10px);
            box-shadow: 0 15px 30px rgba(17, 119, 254, 0.3);
        }

        /* 4. TEXT SWEEP ANIMATION */
        .team-name {
            font-weight: 700;
            margin-top: 15px;
            color: #333;
            position: relative;
            display: inline-block;
        }

        .team-name::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 50%;
            background: #1177FE;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .team-card:hover .team-name::after {
            width: 80%;
        }

        /* 5. ROLE REVEAL */
        .team-role {
            font-size: 0.9rem;
            color: #777;
            opacity: 0.7;
            transform: translateY(10px);
            transition: all 0.4s ease;
        }

        .team-card:hover .team-role {
            opacity: 1;
            transform: translateY(0);
            color: #1177FE;
        }

    </style>
</head>

<body>
<?php include 'header.php'; ?>

<section id="about" class="about-section">
    <div class="container">
        <h2 class="about-title mb-5 fs-1" style="color: #1177FE;">About Us</h2>

        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="about-card">
                    <h3 class="about-card-title text-start">Our Mission</h3>
                    <p>
                        Our mission is to empower pet owners and local businesses
                        in Angeles City by providing a verified and community-driven
                        platform that simplifies pet-inclusive living.
                    </p>
                </div>
            </div>

            <div class="col-lg-6" >
                <div class="about-card" style="background-color: #21A9FF;">
                    <h3 class="about-card-title text-start">Our Vision</h3>
                    <p>
                        To be the leading digital heart of the pet community
                        in Angeles City, creating a future where every establishment
                        is a pet-welcome zone.
                    </p>
                </div>
            </div>
        </div>

        <div class="who-we-are-section">
            <h3 class="text-center mb-2">Who We Are</h3>
            <p class="text-center mb-4">
                SESE is a social platform for pet parents who want to experience
                Angeles City together.
            </p>

            <h3 class="text-center mb-3">Who is Sese For?</h3>

            <div class="row g-4 justify-content-center">
                <div class="col-lg-4 col-md-6">
                    <div class="target-card">
                        <div class="target-image-box">
                            <img src="../images/about_section/6.png" alt="Pet Owners">
                        </div>
                        <div class="target-body">
                            <h4>Pet Owners</h4>
                            <p>Explore the city stress-free and informed.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="target-card">
                        <div class="target-image-box">
                            <img src="../images/about_section/7.png" alt="Businesses">
                        </div>
                        <div class="target-body">
                            <h4>Pet-Friendly Businesses</h4>
                            <p>Promote your services to pet lovers.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="target-card">
                        <div class="target-image-box">
                            <img src="../images/about_section/8.png" alt="Animal Lovers">
                        </div>
                        <div class="target-body">
                            <h4>Animal Lovers</h4>
                            <p>Support responsible pet ownership.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 mb-2">
            <p class="cta-text fs-5">
                Sniff Out Pet-Friendly Spots Around the City
                <span>Only on SESE!</span>
            </p>
        </div>
    </div>
</section>

<section class="stats-section">
    <div class="container">
        <div class="row justify-content-center g-4">
            <div class="col-md-4 col-lg-3">
                <div class="stat-card">
                    <h3 class="stat-number">50+</h3>
                    <p class="stat-label">Establishments</p>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="stat-card">
                    <h3 class="stat-number">10+</h3>
                    <p class="stat-label">Trusted Partners</p>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="stat-card">
                    <h3 class="stat-number">100+</h3>
                    <p class="stat-label">Happy Pet Parents</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="team-section mb-3">
    <div class="container text-center">
        <h2 class="team-heading">Meet the Team behind Sese</h2>
        <p class="team-subtext mb-5 ">
            The passionate individuals building a pet-friendly community in Angeles City.
        </p>

        <div class="row justify-content-center g-4">
            <div class="col-lg-3 col-md-6">
                <div class="team-card">
                    <div class="team-avatar">
                        <img src="../images/about_section/Anicete, Cayoh.jpg" alt="Cayoh Anicete">
                    </div>
                    <h5 class="team-name">Cayoh Allou Anicete</h5>
                    <p class="team-role"></p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="team-card">
                    <div class="team-avatar">
                         <img src="../images/about_section/Borja, Vladimir.jpeg" alt="Vladimir Borja">
                    </div>
                    <h5 class="team-name">Karl Vladimir Borja</h5>
                    <p class="team-role"></p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="team-card">
                    <div class="team-avatar">
                         <img src="../images/about_section/Punsalang, Ariana.jpg" alt="Ariana Punsalang">
                    </div>
                    <h5 class="team-name">Ariana Punsalang</h5>
                    <p class="team-role"></p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="team-card">
                    <div class="team-avatar">
                         <img src="../images/about_section/Tuazon, Kristine.jpg" alt="Kristine Tuazon">
                    </div>
                    <h5 class="team-name">Kristine Faith Tuazon</h5>
                    <p class="team-role"> </p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="footer-bottom-bar" >
    <div class="container d-flex justify-content-between align-items-center">
        <p class="mb-0" style="color: #fff;">© 2026 SESE All Rights Reserved.</p>
        <p class="mb-0" style="color: #fff;">Designed & Developed by: SESE HAU Web Developers</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>