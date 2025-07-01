<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$machine_id = (int)($_GET['id'] ?? 0);
if (!$machine_id) {
    set_flash_message('danger', 'Machine ID is required.');
    header('Location: machines.php');
    exit;
}

// Get machine data
$machine = $db->getOne("SELECT * FROM machines WHERE machine_id = ?", [$machine_id]);
if (!$machine) {
    set_flash_message('danger', 'Machine not found.');
    header('Location: machines.php');
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $result = $db->delete('machines', 'machine_id = ?', [$machine_id]);
        
        if ($result !== false) {
            set_flash_message('success', 'Machine deleted successfully!');
        } else {
            set_flash_message('danger', 'Failed to delete machine. Please try again.');
        }
    } catch (Exception $e) {
        error_log('Delete Machine Error: ' . $e->getMessage());
        set_flash_message('danger', 'An error occurred while deleting the machine.');
    }
    
    header('Location: machines.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Machine - Manufacturing Database System</title>
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
                            <i class="fas fa-user"></i> User
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
                <div class="row">
                    <div class="col-md-6 mx-auto">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fas fa-trash"></i> Delete Machine</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Warning</h6>
                                    <p class="mb-0">Are you sure you want to delete this machine? This action cannot be undone.</p>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Machine Name:</strong><br>
                                        <?= htmlspecialchars($machine['machine_name']) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Machine Type:</strong><br>
                                        <?= htmlspecialchars($machine['machine_type']) ?>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Manufacturer:</strong><br>
                                        <?= htmlspecialchars($machine['manufacturer']) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Location:</strong><br>
                                        <?= htmlspecialchars($machine['location']) ?>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Status:</strong><br>
                                        <span class="badge bg-<?= $machine['status'] === 'active' ? 'success' : ($machine['status'] === 'maintenance' ? 'warning' : 'secondary') ?>">
                                            <?= ucfirst(htmlspecialchars($machine['status'])) ?>
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Serial Number:</strong><br>
                                        <?= htmlspecialchars($machine['serial_number'] ?: 'N/A') ?>
                                    </div>
                                </div>
                                
                                <form method="POST" class="mt-4">
                                    <div class="d-flex justify-content-between">
                                        <a href="machines.php" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Cancel
                                        </a>
                                        <button type="submit" name="confirm_delete" class="btn btn-danger" onclick="return confirm('Are you absolutely sure you want to delete this machine?')">
                                            <i class="fas fa-trash"></i> Delete Machine
                                        </button>
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
    <script src="js/main.js"></script>
</body>
</html> 