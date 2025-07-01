<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$flash = get_flash_message();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $machine_name = sanitize_input($_POST['machine_name'] ?? '');
    $machine_type = sanitize_input($_POST['machine_type'] ?? '');
    $model_number = sanitize_input($_POST['model_number'] ?? '');
    $manufacturer = sanitize_input($_POST['manufacturer'] ?? '');
    $serial_number = sanitize_input($_POST['serial_number'] ?? '');
    $purchase_date = sanitize_input($_POST['purchase_date'] ?? '');
    $warranty_expiry = sanitize_input($_POST['warranty_expiry'] ?? '');
    $location = sanitize_input($_POST['location'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'active');
    $capacity = sanitize_input($_POST['capacity'] ?? '');
    $maintenance_schedule = sanitize_input($_POST['maintenance_schedule'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');

    $errors = [];

    // Validation
    if (empty($machine_name)) {
        $errors[] = 'Machine name is required.';
    }
    if (empty($machine_type)) {
        $errors[] = 'Machine type is required.';
    }
    if (empty($manufacturer)) {
        $errors[] = 'Manufacturer is required.';
    }
    if (empty($location)) {
        $errors[] = 'Location is required.';
    }

    // Check for duplicate serial number
    if (!empty($serial_number)) {
        $existing = $db->getOne("SELECT * FROM machines WHERE serial_number = ?", [$serial_number]);
        if ($existing) {
            $errors[] = 'A machine with this serial number already exists.';
        }
    }

    if (empty($errors)) {
        $machine_data = [
            'machine_name' => $machine_name,
            'machine_type' => $machine_type,
            'model_number' => $model_number,
            'manufacturer' => $manufacturer,
            'serial_number' => $serial_number,
            'purchase_date' => $purchase_date ?: null,
            'warranty_expiry' => $warranty_expiry ?: null,
            'location' => $location,
            'status' => $status,
            'capacity' => $capacity,
            'maintenance_schedule' => $maintenance_schedule,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['user_id']
        ];

        $result = $db->insert('machines', $machine_data);
        
        if ($result !== false) {
            set_flash_message('success', 'Machine added successfully!');
            header('Location: machines.php');
            exit;
        } else {
            set_flash_message('danger', 'Failed to add machine. Please try again.');
        }
    } else {
        set_flash_message('warning', implode(' ', $errors));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Machine - Manufacturing Database System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-label { color: #1976d2; font-weight: 600; }
        .form-control, .form-select {
            background: #f5f5f6;
            color: #222;
            border: 2px solid #1976d2;
            border-radius: 8px;
        }
        .form-control:focus, .form-select:focus {
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
                <!-- Flash message display -->
                <?php if ($flash): ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-plus"></i> Add New Machine</h5>
                            </div>
                            <div class="card-body">
                                <form id="machineForm" method="POST" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="machine_name" class="form-label">Machine Name *</label>
                                                <input type="text" class="form-control" id="machine_name" name="machine_name" value="<?= htmlspecialchars($_POST['machine_name'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="machine_type" class="form-label">Machine Type *</label>
                                                <select class="form-select" id="machine_type" name="machine_type" required>
                                                    <option value="">Select Type</option>
                                                    <option value="CNC Machine" <?= ($_POST['machine_type'] ?? '') === 'CNC Machine' ? 'selected' : '' ?>>CNC Machine</option>
                                                    <option value="Injection Molding" <?= ($_POST['machine_type'] ?? '') === 'Injection Molding' ? 'selected' : '' ?>>Injection Molding</option>
                                                    <option value="Assembly Line" <?= ($_POST['machine_type'] ?? '') === 'Assembly Line' ? 'selected' : '' ?>>Assembly Line</option>
                                                    <option value="Packaging Machine" <?= ($_POST['machine_type'] ?? '') === 'Packaging Machine' ? 'selected' : '' ?>>Packaging Machine</option>
                                                    <option value="Testing Equipment" <?= ($_POST['machine_type'] ?? '') === 'Testing Equipment' ? 'selected' : '' ?>>Testing Equipment</option>
                                                    <option value="Other" <?= ($_POST['machine_type'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="model_number" class="form-label">Model Number</label>
                                                <input type="text" class="form-control" id="model_number" name="model_number" value="<?= htmlspecialchars($_POST['model_number'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="manufacturer" class="form-label">Manufacturer *</label>
                                                <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="<?= htmlspecialchars($_POST['manufacturer'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="serial_number" class="form-label">Serial Number</label>
                                                <input type="text" class="form-control" id="serial_number" name="serial_number" value="<?= htmlspecialchars($_POST['serial_number'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="location" class="form-label">Location *</label>
                                                <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="purchase_date" class="form-label">Purchase Date</label>
                                                <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="<?= htmlspecialchars($_POST['purchase_date'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="warranty_expiry" class="form-label">Warranty Expiry</label>
                                                <input type="date" class="form-control" id="warranty_expiry" name="warranty_expiry" value="<?= htmlspecialchars($_POST['warranty_expiry'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status">
                                                    <option value="active" <?= ($_POST['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                                    <option value="maintenance" <?= ($_POST['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                                    <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                    <option value="retired" <?= ($_POST['status'] ?? '') === 'retired' ? 'selected' : '' ?>>Retired</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="capacity" class="form-label">Capacity</label>
                                                <input type="text" class="form-control" id="capacity" name="capacity" value="<?= htmlspecialchars($_POST['capacity'] ?? '') ?>" placeholder="e.g., 1000 units/hour">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="maintenance_schedule" class="form-label">Maintenance Schedule</label>
                                        <input type="text" class="form-control" id="maintenance_schedule" name="maintenance_schedule" value="<?= htmlspecialchars($_POST['maintenance_schedule'] ?? '') ?>" placeholder="e.g., Every 6 months">
                                    </div>

                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about the machine"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                                    </div>

                                    <div id="formAlert"></div>
                                    <div class="d-flex justify-content-between">
                                        <a href="machines.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Machines</a>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Machine</button>
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
    <script>
    $(document).ready(function() {
        $('#machineForm').on('submit', function(e) {
            var valid = true;
            var msg = '';
            
            var machineName = $('#machine_name').val().trim();
            var machineType = $('#machine_type').val();
            var manufacturer = $('#manufacturer').val().trim();
            var location = $('#location').val().trim();
            
            if (!machineName) {
                valid = false;
                msg += '<div class="alert alert-warning">Machine name is required.</div>';
            }
            if (!machineType) {
                valid = false;
                msg += '<div class="alert alert-warning">Machine type is required.</div>';
            }
            if (!manufacturer) {
                valid = false;
                msg += '<div class="alert alert-warning">Manufacturer is required.</div>';
            }
            if (!location) {
                valid = false;
                msg += '<div class="alert alert-warning">Location is required.</div>';
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