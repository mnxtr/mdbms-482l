<?php
require_once 'config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$order = null;
$error = '';
$success = '';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash_message('error', 'Invalid production order ID provided.');
    header('Location: production.php');
    exit();
}

$order_id = (int)$_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        $status = trim($_POST['status']);
        $priority = trim($_POST['priority']);
        $notes = trim($_POST['notes']);
        
        // Validation
        if ($product_id <= 0) {
            throw new Exception('Please select a valid product.');
        }
        
        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than 0.');
        }
        
        if (empty($status)) {
            throw new Exception('Status is required.');
        }
        
        // Update production order
        $result = $db->execute('
            UPDATE production_orders SET 
                product_id = ?, 
                quantity = ?, 
                status = ?, 
                priority = ?, 
                notes = ?,
                updated_at = NOW()
            WHERE order_id = ?
        ', [$product_id, $quantity, $status, $priority, $notes, $order_id]);
        
        if ($result) {
            // Log the action
            log_activity('Production order updated', 'Order ID: ' . $order_id);
            set_flash_message('success', 'Production order updated successfully.');
            header('Location: production.php');
            exit();
        } else {
            throw new Exception('Failed to update production order. Please try again.');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch order data
try {
    $order = $db->getOne('SELECT * FROM production_orders WHERE order_id = ?', [$order_id]);
    
    if (!$order) {
        set_flash_message('error', 'Production order not found.');
        header('Location: production.php');
        exit();
    }
    
    // Fetch products for dropdown
    $products = $db->getAll('SELECT product_id, name, product_code FROM products ORDER BY name');
    
} catch (Exception $e) {
    error_log("Edit production order error: " . $e->getMessage());
    set_flash_message('error', 'An error occurred while loading production order data.');
    header('Location: production.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Production Order - Manufacturing Database System</title>
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
                <li class="active">
                    <a href="production.php"><i class="fas fa-industry"></i> Production</a>
                </li>
                <li><a href="quality.php"><i class="fas fa-check-circle"></i> Quality Control</a></li>
                <li><a href="suppliers.php"><i class="fas fa-truck"></i> Suppliers</a></li>
                <li><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
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
                <a href="production.php" class="btn ms-3" style="background:linear-gradient(90deg, #43c6ac 0%, #191654 100%);color:#fff;border-radius:8px;border:none;font-weight:500;">← Back to Production</a>
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
                                <h4 class="card-title mb-0">Edit Production Order #<?= $order_id ?></h4>
                            </div>
                            <div class="card-body">
                                <?php if ($error): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?= htmlspecialchars($error) ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" id="editProductionForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="product_id" class="form-label">Product *</label>
                                            <select class="form-select" id="product_id" name="product_id" required>
                                                <option value="">Select Product</option>
                                                <?php foreach ($products as $product): ?>
                                                    <option value="<?= $product['product_id'] ?>" 
                                                            <?= $order['product_id'] == $product['product_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($product['name'] . ' (' . $product['product_code'] . ')') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="quantity" class="form-label">Quantity *</label>
                                            <input type="number" min="1" class="form-control" id="quantity" name="quantity" 
                                                   value="<?= htmlspecialchars($order['quantity']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="status" class="form-label">Status *</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="">Select Status</option>
                                                <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="In Progress" <?= $order['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                <option value="Completed" <?= $order['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                                <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="priority" class="form-label">Priority</label>
                                            <select class="form-select" id="priority" name="priority">
                                                <option value="">Select Priority</option>
                                                <option value="Low" <?= $order['priority'] === 'Low' ? 'selected' : '' ?>>Low</option>
                                                <option value="Medium" <?= $order['priority'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
                                                <option value="High" <?= $order['priority'] === 'High' ? 'selected' : '' ?>>High</option>
                                                <option value="Urgent" <?= $order['priority'] === 'Urgent' ? 'selected' : '' ?>>Urgent</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="production.php" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary">Update Order</button>
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
            $('#editProductionForm').on('submit', function(e) {
                let isValid = true;
                
                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                
                // Validate required fields
                const requiredFields = ['product_id', 'quantity', 'status'];
                requiredFields.forEach(function(field) {
                    const value = $('#' + field).val().trim();
                    if (!value) {
                        $('#' + field).addClass('is-invalid');
                        isValid = false;
                    }
                });
                
                // Validate quantity
                const quantity = parseInt($('#quantity').val());
                if (quantity <= 0) {
                    $('#quantity').addClass('is-invalid');
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