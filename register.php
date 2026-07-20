<?php 
include 'db.php'; 

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
            
            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GSU Virtual Learning Environment</title>
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
        
        .register-container {
            width: 100%;
            max-width: 550px;
            margin: 0 auto;
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .register-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: none;
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
        }
        
        .university-logo {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .register-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .register-subtitle {
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
        
        .select-custom {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23006837' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px;
            padding-right: 2.5rem;
        }
        
        .password-container {
            position: relative;
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
            z-index: 2;
        }
        
        .password-strength {
            height: 5px;
            border-radius: 5px;
            margin-top: 5px;
            background: #e1e5e9;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 5px;
        }
        
        .password-requirements {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.5rem;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 0.25rem;
        }
        
        .requirement i {
            font-size: 0.75rem;
            margin-right: 0.5rem;
        }
        
        .requirement.valid i {
            color: #28a745;
        }
        
        .requirement.invalid i {
            color: #dc3545;
        }
        
        .role-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .role-option {
            flex: 1;
            text-align: center;
            padding: 1.5rem 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .role-option:hover {
            border-color: var(--gsu-green);
            transform: translateY(-2px);
        }
        
        .role-option.selected {
            border-color: var(--gsu-green);
            background: var(--gsu-light-green);
            box-shadow: 0 5px 15px rgba(0, 104, 55, 0.1);
        }
        
        .role-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .role-student .role-icon {
            color: var(--gsu-green);
        }
        
        .role-staff .role-icon {
            color: var(--gsu-gold);
        }
        
        .btn-register {
            background: var(--gsu-gradient);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
            font-size: 1.05rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 104, 55, 0.25);
        }
        
        .login-link {
            color: var(--gsu-green);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .login-link:hover {
            color: var(--gsu-dark);
            transform: translateX(5px);
        }
        
        .login-link i {
            margin-left: 5px;
            transition: transform 0.3s ease;
        }
        
        .login-link:hover i {
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
        
        .success-message {
            text-align: center;
            padding: 3rem 2rem;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1.5rem;
            display: block;
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .role-selector {
                flex-direction: column;
            }
            
            .card-body-custom {
                padding: 2rem 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .register-container {
                padding: 10px;
            }
            
            .card-header-custom {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="card-header-custom">
                <i class="bi bi-mortarboard-fill university-logo"></i>
                <h2 class="register-title">Join FUK VLE</h2>
                <p class="register-subtitle">Create your account to access the virtual learning platform</p>
            </div>
            
            <div class="card-body-custom">
                <?php if ($success): ?>
                    <div class="success-message">
                        <i class="bi bi-check-circle-fill success-icon"></i>
                        <h3 class="text-success">Registration Successful!</h3>
                        <p class="mb-4">Your account has been created successfully.</p>
                        <div class="d-grid gap-2">
                            <a href="login.php" class="btn btn-register">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Go to Login
                            </a>
                            <a href="index.php" class="btn btn-outline-success">
                                Back to Homepage
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-custom d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div><?php echo $error; ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="registerForm">
                        <div class="mb-4">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="fullname" class="form-control form-control-custom" 
                                   placeholder="Enter your full name" required 
                                   value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-custom" 
                                   placeholder="Enter your university email" required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <div class="form-text">Use your FUK email address if available</div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group mb-4">
                                <label class="form-label">Password</label>
                                <div class="password-container">
                                    <input type="password" name="password" id="password" 
                                           class="form-control form-control-custom" 
                                           placeholder="Create a password" required>
                                    <button type="button" class="password-toggle" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                                </div>
                                <div class="password-requirements" id="passwordRequirements">
                                    <div class="requirement invalid" id="reqLength">
                                        <i class="bi bi-circle"></i> At least 8 characters
                                    </div>
                                    <div class="requirement invalid" id="reqUpper">
                                        <i class="bi bi-circle"></i> One uppercase letter
                                    </div>
                                    <div class="requirement invalid" id="reqLower">
                                        <i class="bi bi-circle"></i> One lowercase letter
                                    </div>
                                    <div class="requirement invalid" id="reqNumber">
                                        <i class="bi bi-circle"></i> One number
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-4">
                                <label class="form-label">Confirm Password</label>
                                <div class="password-container">
                                    <input type="password" name="confirm_password" id="confirmPassword" 
                                           class="form-control form-control-custom" 
                                           placeholder="Confirm your password" required>
                                    <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text" id="passwordMatch"></div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">I am a:</label>
                            <div class="role-selector">
                                <div class="role-option role-student <?php echo (!isset($_POST['role']) || $_POST['role'] == 'student') ? 'selected' : ''; ?>" 
                                     data-role="student">
                                    <i class="bi bi-person-circle role-icon"></i>
                                    <h6>Student</h6>
                                    <p class="small text-muted mb-0">Access courses and learning materials</p>
                                </div>
                                <div class="role-option role-staff <?php echo (isset($_POST['role']) && $_POST['role'] == 'staff') ? 'selected' : ''; ?>" 
                                     data-role="staff">
                                    <i class="bi bi-person-badge role-icon"></i>
                                    <h6>Staff</h6>
                                    <p class="small text-muted mb-0">Manage courses and interact with students</p>
                                </div>
                            </div>
                            <input type="hidden" name="role" id="roleInput" value="<?php echo isset($_POST['role']) ? $_POST['role'] : 'student'; ?>">
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="termsCheck" required>
                            <label class="form-check-label small" for="termsCheck">
                                I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-register" id="submitBtn">
                            <i class="bi bi-person-plus me-2"></i> Create Account
                        </button>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">Already have an account? 
                                <a href="login.php" class="login-link">
                                    Sign in <i class="bi bi-arrow-right"></i>
                                </a>
                            </p>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Role selection
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.role-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
                document.getElementById('roleInput').value = this.dataset.role;
            });
        });
        
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        });
        
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        });
        
        // Password strength checker
        password.addEventListener('input', function() {
            const pass = this.value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            let strength = 0;
            
            // Check requirements
            const hasLength = pass.length >= 8;
            const hasUpper = /[A-Z]/.test(pass);
            const hasLower = /[a-z]/.test(pass);
            const hasNumber = /\d/.test(pass);
            
            // Update requirement indicators
            updateRequirement('reqLength', hasLength);
            updateRequirement('reqUpper', hasUpper);
            updateRequirement('reqLower', hasLower);
            updateRequirement('reqNumber', hasNumber);
            
            // Calculate strength
            if (hasLength) strength += 25;
            if (hasUpper) strength += 25;
            if (hasLower) strength += 25;
            if (hasNumber) strength += 25;
            
            // Update strength bar
            strengthBar.style.width = strength + '%';
            
            // Update color based on strength
            if (strength < 50) {
                strengthBar.style.backgroundColor = '#dc3545';
            } else if (strength < 75) {
                strengthBar.style.backgroundColor = '#ffc107';
            } else {
                strengthBar.style.backgroundColor = '#28a745';
            }
            
            // Check password match
            checkPasswordMatch();
        });
        
        confirmPassword.addEventListener('input', checkPasswordMatch);
        
        function checkPasswordMatch() {
            const pass = password.value;
            const confirm = confirmPassword.value;
            const matchElement = document.getElementById('passwordMatch');
            
            if (confirm === '') {
                matchElement.textContent = '';
                matchElement.style.color = '';
            } else if (pass === confirm) {
                matchElement.textContent = '✓ Passwords match';
                matchElement.style.color = '#28a745';
            } else {
                matchElement.textContent = '✗ Passwords do not match';
                matchElement.style.color = '#dc3545';
            }
        }
        
        function updateRequirement(elementId, isValid) {
            const element = document.getElementById(elementId);
            if (isValid) {
                element.classList.remove('invalid');
                element.classList.add('valid');
                element.innerHTML = '<i class="bi bi-check-circle"></i> ' + element.textContent.substring(3);
            } else {
                element.classList.remove('valid');
                element.classList.add('invalid');
                element.innerHTML = '<i class="bi bi-circle"></i> ' + element.textContent.substring(3);
            }
        }
        
        // Form validation
        const form = document.getElementById('registerForm');
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const termsCheck = document.getElementById('termsCheck');
            
            // Check password strength
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                return false;
            }
            
            // Check password match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                return false;
            }
            
            // Check terms agreement
            if (!termsCheck.checked) {
                e.preventDefault();
                alert('You must agree to the Terms of Service and Privacy Policy.');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Creating Account...';
            submitBtn.disabled = true;
            
            return true;
        });
    </script>
</body>
</html>