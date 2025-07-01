<?php
define('SECURE_ACCESS', true);
require_once 'config/config.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validator = InputValidator::getInstance();
    
    // Sanitize inputs
    $username = $validator->sanitize($_POST['username'] ?? '', 'string');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    $errors = $validator->validate([
        'username' => $username,
        'password' => $password
    ], [
        'username' => 'required|min:3|max:50',
        'password' => 'required|min:6'
    ]);
    
    if (empty($errors)) {
        try {
            // Get user from database
            $user = $db->getOne('SELECT * FROM users WHERE username = ?', [$username]);
            
            if ($user && Security::verifyPassword($password, $user['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // Log successful login
                log_activity('user_login', "User {$username} logged in successfully");
                
                // Set success message
                set_flash_message('success', 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!');
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit();
                
            } else {
                $error = 'Invalid username or password.';
                Logger::warning("Failed login attempt for username: {$username}", ['ip' => $_SERVER['REMOTE_ADDR']]);
            }
        } catch (Exception $e) {
            Logger::error("Login error: " . $e->getMessage());
            $error = 'A server error occurred. Please try again later.';
        }
    } else {
        $error = implode(', ', $errors);
    }
}

// Ensure default admin user exists (for development/demo)
if (!IS_PRODUCTION) {
    try {
        $exists = $db->exists('users', 'username = ?', ['admin']);
        if (!$exists) {
            $db->insert('users', [
                'username' => 'admin',
                'password' => Security::hashPassword('admin123'),
                'email' => 'admin@example.com',
                'full_name' => 'Administrator',
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            Logger::info("Default admin user created");
        }
    } catch (Exception $e) {
        Logger::error("Failed to create default user: " . $e->getMessage());
    }
}

$pageTitle = 'Login - ' . APP_NAME;
$pageDescription = 'Login to access the Manufacturing Database System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="author" content="<?= APP_NAME ?>">
    <meta name="csrf-token" content="<?= Security::generateCSRFToken() ?>">
    
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="css/style.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    
    <!-- Security headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    
    <style>
        html, body {
            height: 100%;
            overflow: hidden;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header .logo {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .login-header h1 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .login-header p {
            color: #6c757d;
            font-size: 0.95rem;
            margin: 0;
        }
        
        .form-label {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: var(--border-radius);
            border: 2px solid #e9ecef;
            padding: 12px 16px;
            font-size: 1rem;
            transition: var(--transition);
            background-color: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 198, 172, 0.25);
            background-color: white;
        }
        
        .form-control.is-invalid {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 198, 172, 0.3);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-login .spinner-border {
            width: 1rem;
            height: 1rem;
        }
        
        .alert {
            border-radius: var(--border-radius);
            border: none;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        
        .demo-credentials {
            background: rgba(67, 198, 172, 0.1);
            border: 1px solid rgba(67, 198, 172, 0.2);
            border-radius: var(--border-radius);
            padding: 15px;
            margin-top: 20px;
            text-align: center;
        }
        
        .demo-credentials h6 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .demo-credentials p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .demo-credentials strong {
            color: var(--dark-color);
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 10px;
            }
            
            .login-card {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-industry"></i>
                </div>
                <h1><?= htmlspecialchars(APP_NAME) ?></h1>
                <p>Please sign in to continue</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-2"></i>Username
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           value="<?= htmlspecialchars($username ?? '') ?>"
                           required 
                           autocomplete="username"
                           autofocus>
                    <div class="invalid-feedback">
                        Please enter a valid username.
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           required 
                           autocomplete="current-password">
                    <div class="invalid-feedback">
                        Please enter your password.
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    <span class="btn-text">Sign In</span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Signing in...
                    </span>
                </button>
            </form>
            
            <?php if (!IS_PRODUCTION): ?>
                <div class="demo-credentials">
                    <h6><i class="fas fa-info-circle me-2"></i>Demo Credentials</h6>
                    <p><strong>Username:</strong> admin</p>
                    <p><strong>Password:</strong> admin123</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            const form = $('#loginForm');
            const loginBtn = $('#loginBtn');
            const btnText = $('.btn-text');
            const btnLoading = $('.btn-loading');
            
            // Form validation
            form.on('submit', function(e) {
                e.preventDefault();
                
                const username = $('#username').val().trim();
                const password = $('#password').val().trim();
                
                // Clear previous validation
                $('.form-control').removeClass('is-invalid');
                $('.alert').remove();
                
                // Validate fields
                let isValid = true;
                
                if (!username) {
                    $('#username').addClass('is-invalid');
                    isValid = false;
                }
                
                if (!password) {
                    $('#password').addClass('is-invalid');
                    isValid = false;
                }
                
                if (!isValid) {
                    return false;
                }
                
                // Show loading state
                loginBtn.prop('disabled', true);
                btnText.addClass('d-none');
                btnLoading.removeClass('d-none');
                
                // Submit form
                form[0].submit();
            });
            
            // Clear validation on input
            $('.form-control').on('input', function() {
                $(this).removeClass('is-invalid');
            });
            
            // Enter key submission
            $('.form-control').on('keypress', function(e) {
                if (e.which === 13) {
                    form.submit();
                }
            });
            
            // Auto-focus username field
            $('#username').focus();
        });
    </script>
</body>
</html> 