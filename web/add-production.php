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
    $product_id = sanitize_input($_POST['product_id'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $start_date = sanitize_input($_POST['start_date'] ?? '');
    $expected_completion = sanitize_input($_POST['expected_completion'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'pending');
    $priority = sanitize_input($_POST['priority'] ?? 'normal');
    $assigned_to = sanitize_input($_POST['assigned_to'] ?? '');
    $machine_id = sanitize_input($_POST['machine_id'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');

    $errors = [];

    // Validation
    if (empty($product_id)) {
        $errors[] = 'Product is required.';
    }
    if ($quantity <= 0) {
        $errors[] = 'Quantity must be greater than 0.';
    }
    if (empty($start_date)) {
        $errors[] = 'Start date is required.';
    }
    if (empty($expected_completion)) {
        $errors[] = 'Expected completion date is required.';
    }
    if (!empty($start_date) && !empty($expected_completion) && $start_date > $expected_completion) {
        $errors[] = 'Start date cannot be after expected completion date.';
    }

    if (empty($errors)) {
        $production_data = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'start_date' => $start_date,
            'expected_completion' => $expected_completion,
            'status' => $status,
            'priority' => $priority,
            'assigned_to' => $assigned_to ?: null,
            'machine_id' => $machine_id ?: null,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['user_id']
        ];

        $result = $db->insert('production_orders', $production_data);
        
        if ($result !== false) {
            set_flash_message('success', 'Production order added successfully!');
            header('Location: production.php');
            exit;
        } else {
            set_flash_message('danger', 'Failed to add production order. Please try again.');
        }
    } else {
        set_flash_message('warning', implode(' ', $errors));
    }
}

// Get data for dropdowns
$products = $db->getAll("SELECT product_id, product_name FROM products ORDER BY product_name");
$employees = $db->getAll("SELECT user_id, full_name FROM users WHERE role IN ('operator', 'supervisor', 'manager') ORDER BY full_name");
$machines = $db->getAll("SELECT machine_id, machine_name FROM machines WHERE status = 'active' ORDER BY machine_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Production Order - Manufacturing Database System</title>
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
                                <h5 class="mb-0"><i class="fas fa-plus"></i> Add New Production Order</h5>
                            </div>
                            <div class="card-body">
                                <form id="productionForm" method="POST" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="product_id" class="form-label">Product *</label>
                                                <select class="form-select" id="product_id" name="product_id" required>
                                                    <option value="">Select Product</option>
                                                    <?php foreach ($products as $product): ?>
                                                        <option value="<?= $product['product_id'] ?>" <?= ($_POST['product_id'] ?? '') == $product['product_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($product['product_name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="quantity" class="form-label">Quantity *</label>
                                                <input type="number" class="form-control" id="quantity" name="quantity" value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>" min="1" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="start_date" class="form-label">Start Date *</label>
                                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="expected_completion" class="form-label">Expected Completion *</label>
                                                <input type="date" class="form-control" id="expected_completion" name="expected_completion" value="<?= htmlspecialchars($_POST['expected_completion'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status">
                                                    <option value="pending" <?= ($_POST['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="in_progress" <?= ($_POST['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                                    <option value="completed" <?= ($_POST['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                    <option value="on_hold" <?= ($_POST['status'] ?? '') === 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                                                    <option value="cancelled" <?= ($_POST['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="priority" class="form-label">Priority</label>
                                                <select class="form-select" id="priority" name="priority">
                                                    <option value="low" <?= ($_POST['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                                                    <option value="normal" <?= ($_POST['priority'] ?? 'normal') === 'normal' ? 'selected' : '' ?>>Normal</option>
                                                    <option value="high" <?= ($_POST['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                                                    <option value="urgent" <?= ($_POST['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="assigned_to" class="form-label">Assigned To</label>
                                                <select class="form-select" id="assigned_to" name="assigned_to">
                                                    <option value="">Select Employee</option>
                                                    <?php foreach ($employees as $employee): ?>
                                                        <option value="<?= $employee['user_id'] ?>" <?= ($_POST['assigned_to'] ?? '') == $employee['user_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($employee['full_name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="machine_id" class="form-label">Machine</label>
                                                <select class="form-select" id="machine_id" name="machine_id">
                                                    <option value="">Select Machine</option>
                                                    <?php foreach ($machines as $machine): ?>
                                                        <option value="<?= $machine['machine_id'] ?>" <?= ($_POST['machine_id'] ?? '') == $machine['machine_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($machine['machine_name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about the production order"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                                    </div>

                                    <div id="formAlert"></div>
                                    <div class="d-flex justify-content-between">
                                        <a href="production.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Production</a>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Production Order</button>
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
        $('#productionForm').on('submit', function(e) {
            var valid = true;
            var msg = '';
            
            var productId = $('#product_id').val();
            var quantity = parseInt($('#quantity').val());
            var startDate = $('#start_date').val();
            var expectedCompletion = $('#expected_completion').val();
            
            if (!productId) {
                valid = false;
                msg += '<div class="alert alert-warning">Product is required.</div>';
            }
            if (!quantity || quantity <= 0) {
                valid = false;
                msg += '<div class="alert alert-warning">Quantity must be greater than 0.</div>';
            }
            if (!startDate) {
                valid = false;
                msg += '<div class="alert alert-warning">Start date is required.</div>';
            }
            if (!expectedCompletion) {
                valid = false;
                msg += '<div class="alert alert-warning">Expected completion date is required.</div>';
            }
            if (startDate && expectedCompletion && startDate > expectedCompletion) {
                valid = false;
                msg += '<div class="alert alert-warning">Start date cannot be after expected completion date.</div>';
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