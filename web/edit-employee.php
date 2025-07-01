<?php
require_once 'config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$employee = null;
$error = '';
$success = '';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash_message('error', 'Invalid employee ID provided.');
    header('Location: employees.php');
    exit();
}

$employee_id = (int)$_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $role = trim($_POST['role']);
        $department = trim($_POST['department']);
        $phone = trim($_POST['phone']);
        $hire_date = trim($_POST['hire_date']);
        $status = trim($_POST['status']);
        
        // Validation
        if (empty($username) || empty($email) || empty($first_name) || empty($last_name)) {
            throw new Exception('All required fields must be filled.');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }
        
        // Check if username exists (excluding current employee)
        $existingUser = $db->getOne('SELECT user_id FROM users WHERE username = ? AND user_id != ?', [$username, $employee_id]);
        if ($existingUser) {
            throw new Exception('Username already exists. Please choose a different username.');
        }
        
        // Check if email exists (excluding current employee)
        $existingEmail = $db->getOne('SELECT user_id FROM users WHERE email = ? AND user_id != ?', [$email, $employee_id]);
        if ($existingEmail) {
            throw new Exception('Email already exists. Please choose a different email.');
        }
        
        // Update employee
        $result = $db->execute('
            UPDATE users SET 
                username = ?, 
                email = ?, 
                first_name = ?, 
                last_name = ?, 
                role = ?, 
                department = ?, 
                phone = ?, 
                hire_date = ?, 
                status = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ', [$username, $email, $first_name, $last_name, $role, $department, $phone, $hire_date, $status, $employee_id]);
        
        if ($result) {
            // Log the action
            log_activity('Employee updated', 'Employee ID: ' . $employee_id . ', Name: ' . $first_name . ' ' . $last_name);
            set_flash_message('success', 'Employee updated successfully.');
            header('Location: employees.php');
            exit();
        } else {
            throw new Exception('Failed to update employee. Please try again.');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch employee data
try {
    $employee = $db->getOne('SELECT * FROM users WHERE user_id = ?', [$employee_id]);
    
    if (!$employee) {
        set_flash_message('error', 'Employee not found.');
        header('Location: employees.php');
        exit();
    }
    
} catch (Exception $e) {
    error_log("Edit employee error: " . $e->getMessage());
    set_flash_message('error', 'An error occurred while loading employee data.');
    header('Location: employees.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee - Manufacturing Database System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>MDS</h3>
                <strong>MDS</strong>
                <p>Manufacturing Database System</p>
            </div>
            <ul class="components">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li>
                    <a href="#inventorySubmenu" data-bs-toggle="collapse"><i class="fas fa-boxes"></i> Inventory</a>
                    <ul class="collapse list-unstyled" id="inventorySubmenu">
                        <li><a href="products.php">Products</a></li>
                        <li><a href="materials.php">Raw Materials</a></li>
                    </ul>
                </li>
                <li><a href="production.php"><i class="fas fa-industry"></i> Production</a></li>
                <li><a href="quality.php"><i class="fas fa-check-circle"></i> Quality Control</a></li>
                <li><a href="suppliers.php"><i class="fas fa-truck"></i> Suppliers</a></li>
                <li class="active">
                    <a href="employees.php"><i class="fas fa-users"></i> Employees</a>
                </li>
                <li><a href="machines.php"><i class="fas fa-cogs"></i> Machines</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar">
                <button type="button" id="sidebarCollapse" class="btn">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="employees.php" class="btn ms-3" style="background:linear-gradient(90deg, #43c6ac 0%, #191654 100%);color:#fff;border-radius:8px;border:none;font-weight:500;">← Back to Employees</a>
                <div class="ms-auto">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> Admin
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid mt-4">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Edit Employee</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($error): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?= htmlspecialchars($error) ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" id="editEmployeeForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="username" class="form-label">Username *</label>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?= htmlspecialchars($employee['username']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email *</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?= htmlspecialchars($employee['email']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="first_name" class="form-label">First Name *</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                                   value="<?= htmlspecialchars($employee['first_name']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="last_name" class="form-label">Last Name *</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                                   value="<?= htmlspecialchars($employee['last_name']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select" id="role" name="role">
                                                <option value="Employee" <?= $employee['role'] === 'Employee' ? 'selected' : '' ?>>Employee</option>
                                                <option value="Supervisor" <?= $employee['role'] === 'Supervisor' ? 'selected' : '' ?>>Supervisor</option>
                                                <option value="Manager" <?= $employee['role'] === 'Manager' ? 'selected' : '' ?>>Manager</option>
                                                <option value="Admin" <?= $employee['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="department" class="form-label">Department</label>
                                            <select class="form-select" id="department" name="department">
                                                <option value="Production" <?= $employee['department'] === 'Production' ? 'selected' : '' ?>>Production</option>
                                                <option value="Quality Control" <?= $employee['department'] === 'Quality Control' ? 'selected' : '' ?>>Quality Control</option>
                                                <option value="Maintenance" <?= $employee['department'] === 'Maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                                <option value="Logistics" <?= $employee['department'] === 'Logistics' ? 'selected' : '' ?>>Logistics</option>
                                                <option value="Administration" <?= $employee['department'] === 'Administration' ? 'selected' : '' ?>>Administration</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?= htmlspecialchars($employee['phone'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="hire_date" class="form-label">Hire Date</label>
                                            <input type="date" class="form-control" id="hire_date" name="hire_date" 
                                                   value="<?= htmlspecialchars($employee['hire_date'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="Active" <?= $employee['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                            <option value="Inactive" <?= $employee['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                            <option value="On Leave" <?= $employee['status'] === 'On Leave' ? 'selected' : '' ?>>On Leave</option>
                                        </select>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="employees.php" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary">Update Employee</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/form-validation.js"></script>
    <script>
        $(document).ready(function() {
            // Form validation
            $('#editEmployeeForm').on('submit', function(e) {
                let isValid = true;
                
                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                
                // Validate required fields
                const requiredFields = ['username', 'email', 'first_name', 'last_name'];
                requiredFields.forEach(function(field) {
                    const value = $('#' + field).val().trim();
                    if (!value) {
                        $('#' + field).addClass('is-invalid');
                        isValid = false;
                    }
                });
                
                // Validate email format
                const email = $('#email').val().trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (email && !emailRegex.test(email)) {
                    $('#email').addClass('is-invalid');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields correctly.');
                }
            });
        });
    </script>
</body>
</html> 