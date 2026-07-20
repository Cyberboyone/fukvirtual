<?php 
include 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, fullname, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'staff') {
                header("Location: staff_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        }
    }
    $error = "Invalid email or password. Please try again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GSU Virtual Learning Environment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
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
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23006837' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            z-index: -1;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: none;
            transition: transform 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
        }
        
        .card-header-custom {
            background: var(--gsu-gradient);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header-custom::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.2;
            animation: float 20s linear infinite;
        }
        
        @keyframes float {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .university-logo {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .login-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .card-body-custom {
            padding: 2.5rem 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #444;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-control-custom {
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            padding: 0.85rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f9fafc;
        }
        
        .form-control-custom:focus {
            border-color: var(--gsu-green);
            box-shadow: 0 0 0 0.25rem rgba(0, 104, 55, 0.15);
            background-color: white;
        }
        
        .btn-login {
            background: var(--gsu-gradient);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
            font-size: 1.05rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 0.5rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 104, 55, 0.25);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
        }
        
        .password-container {
            position: relative;
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 2rem 0;
            color: #999;
            font-size: 0.9rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .divider:not(:empty)::before {
            margin-right: 1rem;
        }
        
        .divider:not(:empty)::after {
            margin-left: 1rem;
        }
        
        .register-link {
            color: var(--gsu-green);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .register-link:hover {
            color: var(--gsu-dark);
            transform: translateX(5px);
        }
        
        .register-link i {
            margin-left: 5px;
            transition: transform 0.3s ease;
        }
        
        .register-link:hover i {
            transform: translateX(3px);
        }
        
        .alert-custom {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.25rem;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
        
        .footer-links a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--gsu-green);
        }
        
        .feature-highlight {
            background: var(--gsu-light-green);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: center;
            border-left: 4px solid var(--gsu-green);
        }
        
        .feature-highlight h6 {
            color: var(--gsu-dark);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 10px;
            }
            
            .card-body-custom {
                padding: 2rem 1.5rem;
            }
            
            .card-header-custom {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    
    <div class="login-container">
        <div class="login-card">
            <div class="card-header-custom">
                <i class="bi bi-mortarboard-fill university-logo"></i>
                <h2 class="login-title">Welcome Back</h2>
                <p class="login-subtitle">Sign in to access your FUK Virtual Learning account</p>
            </div>
            
            <div class="card-body-custom">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-custom d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?php echo $error; ?></div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="mb-4">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-custom" 
                               placeholder="Enter your university email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <div class="form-text">Use your FUK email address</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="password-container">
                            <input type="password" name="password" id="password" 
                                   class="form-control form-control-custom" 
                                   placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            <a href="#" class="text-decoration-none" style="font-size: 0.85rem;">Forgot password?</a>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                    </button>
                    
                    <div class="divider">New to FUK VLE?</div>
                    
                    <div class="text-center">
                        <a href="register.php" class="register-link">
                            Create your account <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </form>
                
                <div class="feature-highlight">
                    <h6><i class="bi bi-shield-check me-2"></i>Secure Login</h6>
                    <p class="mb-0 small">Your credentials are protected with industry-standard encryption</p>
                </div>
                
                <div class="footer-links">
                    <a href="index.php">Home</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Support</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        });
        
        // Form validation
        const form = document.getElementById('loginForm');
        form.addEventListener('submit', function(e) {
            const email = form.querySelector('input[name="email"]').value;
            const password = form.querySelector('input[name="password"]').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Signing in...';
            submitBtn.disabled = true;
            
            return true;
        });
        
        // Add focus effect to form inputs
        const inputs = document.querySelectorAll('.form-control-custom');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    </script>
</body>
</html>