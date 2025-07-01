<?php
require_once 'config/config.php'; // This starts the session and loads settings
require_once 'config/database.php';

// Handle AJAX login requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_login'])) {
    header('Content-Type: application/json');
    
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Both username and password are required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare('SELECT user_id, username, password, role FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
            exit;
        }
    } catch (PDOException $e) {
        error_log('Login Database Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again later.']);
        exit;
    }
}

// Ensure default user exists (for development/demo)
try {
    $exists = $db->getOne("SELECT * FROM users WHERE username = ?", ['user']);
    if (!$exists) {
        $db->insert('users', [
            'username' => 'user',
            'password' => password_hash('root', PASSWORD_DEFAULT),
            'email' => 'user@example.com',
            'full_name' => 'Default User',
            'role' => 'admin'
        ]);
    }
} catch (Exception $e) {
    // Handle error (optional: log or display)
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Manufacturing Database System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
        }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8ffae 0%, #43c6ac 100%);
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
        }
        .login-card {
            background: linear-gradient(135deg, #f8ffae 0%, #43c6ac 100%);
            padding: 36px 32px;
            border-radius: 16px;
            color: #222;
            box-shadow: 0 4px 24px rgba(67,198,172,0.12);
            border: none;
        }
        .login-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .login-header h1 {
            color: #ff512f;
            font-size: 26px;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .login-form .form-label {
            color: #ff512f;
            font-weight: 600;
        }
        .login-form .form-control {
            background: #fffbe7;
            color: #222;
            border: 2px solid #43c6ac;
            border-radius: 8px;
            margin-bottom: 18px;
        }
        .login-form .form-control:focus {
            background: #f8ffae;
            color: #191654;
            border-color: #ff512f;
            box-shadow: 0 0 0 2px #43c6ac;
        }
        .login-form .btn {
            background: linear-gradient(90deg, #ff512f 0%, #dd2476 100%);
            color: #fff;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .login-form .btn:hover, .login-form .btn:focus {
            background: linear-gradient(90deg, #43c6ac 0%, #191654 100%);
            color: #fff;
        }
        .text-muted {
            color: #1976d2 !important;
        }
        #msg {
            margin-top: 16px;
            text-align: center;
            font-weight: 600;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Manufacturing Database System</h1>
                <p class="text-muted">Please login to continue</p>
            </div>
            <!-- Static error message example (remove or edit as needed) -->
            <!-- <div class="alert alert-danger" role="alert">Invalid username or password</div> -->
            <form class="login-form" id="loginForm" method="POST" action="#" onsubmit="return false;">
                <input type="hidden" name="ajax_login" value="1">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn">Login</button>
                </div>
            </form>
        </div>
    </div>
    <div id="msg" style="color:red;"></div>
    <script src="form-validation.js"></script>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 