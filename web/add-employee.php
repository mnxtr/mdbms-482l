<?php
require_once 'config/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize_input($_POST['role'] ?? 'operator');
    $status = sanitize_input($_POST['status'] ?? 'active');

    // Validate required fields
    if ($full_name && $username && $email && filter_var($email, FILTER_VALIDATE_EMAIL) && $password && $role) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $data = [
            'full_name' => $full_name,
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password,
            'role' => $role,
            'status' => $status
        ];
        $result = $db->insert('users', $data);
        if ($result) {
            set_flash_message('success', 'Employee added successfully!');
            header('Location: employee.php');
            exit;
        } else {
            set_flash_message('danger', 'Failed to add employee. Username or email may already exist.');
        }
    } else {
        set_flash_message('warning', 'Please fill all fields correctly.');
    }
}
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee - Manufacturing Database System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-label { color: #1976d2; font-weight: 600; }
        .form-control {
            background: #f5f5f6;
            color: #222;
            border: 2px solid #1976d2;
            border-radius: 8px;
        }
        .form-control:focus {
            background: #fff;
            color: #1976d2;
            border-color: #1565c0;
            box-shadow: 0 0 0 2px #1976d2;
        }
        .btn-primary {
            background: #1976d2;
            border: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-primary:hover, .btn-primary:focus {
            background: #1565c0;
        }
        .card {
            max-width: 600px;
            margin: 40px auto;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
        }
        .card-header {
            background: #1976d2;
            color: #fff;
            font-weight: 700;
            border-radius: 12px 12px 0 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow">
            <div class="card-header">
                <i class="fas fa-user-plus"></i> Add New Employee
            </div>
            <div class="card-body">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endif; ?>
                <form id="addEmployeeForm" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="operator" selected>Operator</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                    </div>
                    <div id="formAlert"></div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Submit</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#addEmployeeForm').on('submit', function(e) {
            var valid = true;
            var msg = '';
            var fullName = $('#full_name').val().trim();
            var username = $('#username').val().trim();
            var email = $('#email').val().trim();
            var password = $('#password').val();
            if (!fullName) {
                valid = false;
                msg += '<div class="alert alert-warning">Full name is required.</div>';
            }
            if (!username) {
                valid = false;
                msg += '<div class="alert alert-warning">Username is required.</div>';
            }
            if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
                valid = false;
                msg += '<div class="alert alert-warning">A valid email is required.</div>';
            }
            if (!password || password.length < 6) {
                valid = false;
                msg += '<div class="alert alert-warning">Password must be at least 6 characters.</div>';
            }
            if (!valid) {
                $('#formAlert').html(msg);
                e.preventDefault();
            } else {
                $('#formAlert').html('');
            }
        });
    });
    </script>
</body>
</html> 