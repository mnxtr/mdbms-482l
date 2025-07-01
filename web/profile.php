<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch current user data
$user = $db->getOne('SELECT * FROM users WHERE user_id = ?', [$_SESSION['user_id']]);

if (!$user) {
    set_flash_message('danger', 'User profile not found.');
    header('Location: index.php');
    exit;
}

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];
    $update_data = [];

    // Validate required fields
    if (empty($full_name)) {
        $errors[] = 'Full name is required.';
    } else {
        $update_data['full_name'] = $full_name;
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    } else {
        $update_data['email'] = $email;
    }

    // Handle password change if requested
    if (!empty($current_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (empty($new_password)) {
            $errors[] = 'New password is required.';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match.';
        } else {
            $update_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }

    // Update profile if no errors
    if (empty($errors) && !empty($update_data)) {
        $result = $db->update('users', $update_data, 'user_id = ?', [$_SESSION['user_id']]);
        if ($result !== false) {
            set_flash_message('success', 'Profile updated successfully!');
            // Refresh user data
            $user = $db->getOne('SELECT * FROM users WHERE user_id = ?', [$_SESSION['user_id']]);
        } else {
            set_flash_message('danger', 'Failed to update profile. Please try again.');
        }
    } elseif (!empty($errors)) {
        set_flash_message('warning', implode(' ', $errors));
    }
}

// Get user statistics
$user_stats = [
    'total_products' => $db->getOne("SELECT COUNT(*) as count FROM products")['count'] ?? 0,
    'total_suppliers' => $db->getOne("SELECT COUNT(*) as count FROM suppliers")['count'] ?? 0,
    'total_inspections' => $db->getOne("SELECT COUNT(*) as count FROM quality_control WHERE inspector_id = ?", [$_SESSION['user_id']])['count'] ?? 0,
    'recent_activity' => $db->getAll("SELECT * FROM quality_control WHERE inspector_id = ? ORDER BY created_at DESC LIMIT 5", [$_SESSION['user_id']])
];

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Manufacturing Database System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
            margin-bottom: 1rem;
        }
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
        .btn-primary:hover {
            background: #1565c0;
        }
    </style>
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
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
                <li><a href="employee.php"><i class="fas fa-users"></i> Employees</a></li>
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
                <div class="ms-auto">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($user['full_name']) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item active" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
            <div class="container-fluid mt-4">
                <!-- Flash message display -->
                <?php if ($flash): ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <h2><?= htmlspecialchars($user['full_name']) ?></h2>
                            <p class="mb-1"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                            <p class="mb-1"><i class="fas fa-user-tag"></i> <?= htmlspecialchars(ucfirst($user['role'])) ?></p>
                            <p class="mb-0"><i class="fas fa-calendar"></i> Member since <?= date('M Y', strtotime($user['created_at'])) ?></p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Profile Form -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Profile</h5>
                            </div>
                            <div class="card-body">
                                <form id="profileForm" method="POST" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="full_name" class="form-label">Full Name</label>
                                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    <h6>Change Password (Optional)</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">Current Password</label>
                                                <input type="password" class="form-control" id="current_password" name="current_password">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">New Password</label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="formAlert"></div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- User Statistics -->
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h6><i class="fas fa-chart-bar"></i> Your Statistics</h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-primary"><?= $user_stats['total_products'] ?></h4>
                                    <small>Products</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success"><?= $user_stats['total_suppliers'] ?></h4>
                                    <small>Suppliers</small>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <h4 class="text-info"><?= $user_stats['total_inspections'] ?></h4>
                                <small>Inspections Conducted</small>
                            </div>
                        </div>

                        <?php if (!empty($user_stats['recent_activity'])): ?>
                        <div class="stats-card">
                            <h6><i class="fas fa-history"></i> Recent Activity</h6>
                            <div class="list-group list-group-flush">
                                <?php foreach ($user_stats['recent_activity'] as $activity): ?>
                                <div class="list-group-item border-0 px-0">
                                    <small class="text-muted"><?= date('M d, Y', strtotime($activity['inspection_date'])) ?></small>
                                    <p class="mb-1">Quality inspection completed</p>
                                    <small class="text-success"><?= $activity['passed_quantity'] ?> passed, <?= $activity['failed_quantity'] ?> failed</small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script>
    $(document).ready(function() {
        $('#profileForm').on('submit', function(e) {
            var valid = true;
            var msg = '';
            var fullName = $('#full_name').val().trim();
            var email = $('#email').val().trim();
            var currentPassword = $('#current_password').val();
            var newPassword = $('#new_password').val();
            var confirmPassword = $('#confirm_password').val();
            
            if (!fullName) {
                valid = false;
                msg += '<div class="alert alert-warning">Full name is required.</div>';
            }
            if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
                valid = false;
                msg += '<div class="alert alert-warning">Valid email is required.</div>';
            }
            
            // Password validation
            if (currentPassword || newPassword || confirmPassword) {
                if (!currentPassword) {
                    valid = false;
                    msg += '<div class="alert alert-warning">Current password is required to change password.</div>';
                }
                if (!newPassword) {
                    valid = false;
                    msg += '<div class="alert alert-warning">New password is required.</div>';
                } else if (newPassword.length < 6) {
                    valid = false;
                    msg += '<div class="alert alert-warning">New password must be at least 6 characters.</div>';
                }
                if (newPassword !== confirmPassword) {
                    valid = false;
                    msg += '<div class="alert alert-warning">New passwords do not match.</div>';
                }
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