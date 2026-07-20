
<?php
// Start session to check if user is already logged in
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'staff') {
        header("Location: staff_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gombe State University - Virtual Learning Environment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --gsu-green: #006837;
            --gsu-dark: #004d29;
            --gsu-gold: #f1c40f;
            --gsu-light-green: #e8f5e9;
            --gsu-gradient: linear-gradient(135deg, #006837 0%, #004d29 100%);
            --gsu-gradient-gold: linear-gradient(135deg, #f1c40f 0%, #d4ac0d 100%);
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-md: 0 10px 25px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            line-height: 1.6;
            color: #333;
            background-color: #f9fafc;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }
        
        /* Modern Navbar */
        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-sm);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }
        
        .navbar-scrolled {
            background-color: rgba(255, 255, 255, 0.98);
            padding: 0.7rem 0;
            box-shadow: var(--shadow-md);
        }
        
        .navbar-brand {
            font-weight: 800;
            letter-spacing: 0.5px;
            font-size: 1.8rem;
            background: var(--gsu-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            font-weight: 500;
            color: #444 !important;
            margin: 0 0.5rem;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--gsu-green) !important;
        }
        
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--gsu-gradient);
            border-radius: 3px;
        }
        
        /* Buttons */
        .btn-gold {
            background: var(--gsu-gradient-gold);
            color: #333;
            font-weight: 600;
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(241, 196, 15, 0.3);
        }
        
        .btn-gold:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(241, 196, 15, 0.4);
            color: #222;
        }
        
        .btn-outline-gsu {
            border: 2px solid var(--gsu-green);
            color: var(--gsu-green);
            font-weight: 600;
            padding: 0.7rem 1.8rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-gsu:hover {
            background-color: var(--gsu-green);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 104, 55, 0.2);
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(0, 104, 55, 0.9) 0%, rgba(0, 77, 41, 0.95) 100%), 
                        url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 95vh;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow: hidden;
            padding-top: 80px;
        }
        
        .hero-content h1 {
            font-size: 3.8rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
        }
        
        .hero-content h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: var(--gsu-gold);
        }
        
        .hero-content p {
            font-size: 1.25rem;
            margin-bottom: 2.5rem;
            max-width: 750px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0.9;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Floating shapes in hero */
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            z-index: 0;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            top: 10%;
            left: 5%;
            animation: float 8s ease-in-out infinite;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: 15%;
            right: 8%;
            animation: float 10s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        
        /* Features Section */
        .section-title {
            position: relative;
            margin-bottom: 4rem;
        }
        
        .section-title h2 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: var(--gsu-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section-title .title-line {
            width: 80px;
            height: 5px;
            background: var(--gsu-gradient);
            margin: 0 auto;
            border-radius: 5px;
        }
        
        .feature-box {
            padding: 2.5rem 2rem;
            border-radius: 15px;
            background: white;
            box-shadow: var(--shadow-md);
            transition: all 0.4s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .feature-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gsu-gradient);
            z-index: 2;
        }
        
        .feature-box:hover {
            transform: translateY(-15px);
            box-shadow: var(--shadow-lg);
        }
        
        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            display: inline-block;
            background: var(--gsu-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .feature-box h4 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--gsu-dark);
        }
        
        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 5rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .stat-box {
            text-align: center;
            padding: 2rem;
            transition: transform 0.3s ease;
        }
        
        .stat-box:hover {
            transform: translateY(-10px);
        }
        
        .stat-number {
            font-size: 4rem;
            font-weight: 800;
            background: var(--gsu-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        /* CTA Section */
        .cta-section {
            background: var(--gsu-gradient);
            color: white;
            padding: 6rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .cta-content {
            position: relative;
            z-index: 2;
        }
        
        .cta-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        /* Footer */
        footer {
            background-color: #1a1a1a;
            color: white;
            padding: 5rem 0 2rem;
        }
        
        .footer-logo {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: var(--gsu-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .footer-heading {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            color: white;
            position: relative;
            padding-bottom: 0.8rem;
        }
        
        .footer-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--gsu-gold);
        }
        
        .footer-links {
            list-style: none;
            padding-left: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        
        .footer-links a {
            color: #aaa;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
            padding-left: 8px;
        }
        
        .footer-contact li {
            color: #aaa;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
        }
        
        .footer-contact i {
            color: var(--gsu-gold);
            margin-right: 1rem;
            margin-top: 0.2rem;
        }
        
        .footer-bottom {
            border-top: 1px solid #333;
            padding-top: 2rem;
            margin-top: 3rem;
            text-align: center;
            color: #777;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .hero-content h1 {
                font-size: 3rem;
            }
            
            .hero-content h2 {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .section-title h2 {
                font-size: 2.2rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .hero-buttons a {
                width: 100%;
                max-width: 300px;
            }
        }
        
        @media (max-width: 576px) {
            .hero-content h1 {
                font-size: 2.2rem;
            }
            
            .stat-number {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-mortarboard-fill me-2"></i> FUK VLE
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#stats">Stats</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-gsu px-4" href="login.php">Login</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-gold px-4" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        
        <div class="container hero-content position-relative">
            <h1>Welcome to Federal University Kashere</h1>
            <h2>Virtual Learning Environment</h2>
            <p>Access your courses, join live interactive classrooms, and connect with lecturers from anywhere. Education without boundaries, designed for tomorrow's leaders.</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn btn-gold btn-lg px-5 py-3">Get Started <i class="bi bi-arrow-right ms-2"></i></a>
                <a href="#features" class="btn btn-outline-light btn-lg px-5 py-3">Explore Features</a>
            </div>
        </div>
        
        <div class="hero-scroll-indicator">
            <a href="#features" class="scroll-down">
                <i class="bi bi-chevron-down"></i>
            </a>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container py-5">
            <div class="section-title text-center">
                <h6 class="text-success fw-bold text-uppercase">Why Choose FUK VLE?</h6>
                <h2>Modern Learning Experience</h2>
                <div class="title-line"></div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="bi bi-camera-video"></i>
                        </div>
                        <h4>Live Interactive Classes</h4>
                        <p class="text-muted">Join real-time video sessions with screen sharing, whiteboard, and breakout rooms. Experience the classroom from the comfort of your home.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="bi bi-journal-bookmark"></i>
                        </div>
                        <h4>Course Management</h4>
                        <p class="text-muted">Organized access to all your course materials, session history, assignments, and important announcements in one secure dashboard.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4>Collaborative Learning</h4>
                        <p class="text-muted">Connect with peers and instructors through discussion forums, group projects, and real-time messaging for seamless collaboration.</p>
                    </div>
                </div>
            </div>
            
            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="bi bi-laptop"></i>
                        </div>
                        <h4>Mobile Ready</h4>
                        <p class="text-muted">Access your courses on any device with our responsive platform that works perfectly on smartphones, tablets, and desktops.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4>Secure & Private</h4>
                        <p class="text-muted">Enterprise-grade security ensures your data and learning materials are protected with end-to-end encryption and privacy controls.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h4>Progress Tracking</h4>
                        <p class="text-muted">Monitor your learning journey with detailed analytics, performance reports, and personalized recommendations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <div class="stat-number">24/7</div>
                        <p>Platform Access</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <div class="stat-number">100%</div>
                        <p>Secure Learning</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <div class="stat-number">5000+</div>
                        <p>Active Users</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <div class="stat-number">Live</div>
                        <p>Video Integration</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-pattern"></div>
        <div class="container cta-content text-center">
            <h2 class="mb-4">Ready to Transform Your Learning Experience?</h2>
            <p class="mb-5" style="font-size: 1.2rem; max-width: 700px; margin: 0 auto;">Join thousands of students and staff already benefiting from our advanced virtual learning platform.</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="register.php" class="btn btn-light btn-lg px-5 py-3 fw-bold">Create Account</a>
                <a href="login.php" class="btn btn-outline-light btn-lg px-5 py-3">Login Now</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5">
                    <div class="footer-logo">FUK VLE</div>
                    <p class="text-white-50 mb-4">Excellence in learning and character. Our VLE platform bridges the gap between traditional learning and digital innovation, empowering the next generation of leaders.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white-50 fs-5"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white-50 fs-5"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white-50 fs-5"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-white-50 fs-5"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-5">
                    <h6 class="footer-heading">Quick Links</h6>
                    <ul class="footer-links">
                        <li><a href="login.php">Student Login</a></li>
                        <li><a href="login.php">Staff Login</a></li>
                        <li><a href="register.php">New Account</a></li>
                        <li><a href="#features">Platform Features</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-5">
                    <h6 class="footer-heading">Resources</h6>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">System Status</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-5">
                    <h6 class="footer-heading">Contact Us</h6>
                    <ul class="footer-contact">
                        <li><i class="bi bi-geo-alt"></i> Federal University of Kashere, Gombe, Nigeria</li>
                        <li><i class="bi bi-envelope"></i> support@fuk.edu.ng</li>
                        <li><i class="bi bi-telephone"></i> +234 8131078886</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Federal University Of Kashere. All rights reserved. | Virtual Learning Environment v2.0</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('mainNavbar');
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>