<?php
require_once 'config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$material = null;
$error = '';
$success = '';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash_message('error', 'Invalid material ID provided.');
    header('Location: materials.php');
    exit();
}

$material_id = (int)$_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $material_code = trim($_POST['material_code']);
        $name = trim($_POST['name']);
        $category = trim($_POST['category']);
        $unit_price = (float)$_POST['unit_price'];
        $current_stock = (int)$_POST['current_stock'];
        $min_stock_level = (int)$_POST['min_stock_level'];
        $supplier_id = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
        $description = trim($_POST['description']);
        $unit_of_measure = trim($_POST['unit_of_measure']);
        
        // Validation
        if (empty($material_code) || empty($name) || empty($category)) {
            throw new Exception('Material code, name, and category are required.');
        }
        
        if ($unit_price < 0) {
            throw new Exception('Unit price cannot be negative.');
        }
        
        if ($current_stock < 0) {
            throw new Exception('Current stock cannot be negative.');
        }
        
        if ($min_stock_level < 0) {
            throw new Exception('Minimum stock level cannot be negative.');
        }
        
        // Check if material code exists (excluding current material)
        $existingMaterial = $db->getOne('SELECT material_id FROM materials WHERE material_code = ? AND material_id != ?', [$material_code, $material_id]);
        if ($existingMaterial) {
            throw new Exception('Material code already exists. Please choose a different code.');
        }
        
        // Update material
        $result = $db->execute('
            UPDATE materials SET 
                material_code = ?, 
                name = ?, 
                category = ?, 
                unit_price = ?, 
                current_stock = ?, 
                min_stock_level = ?, 
                supplier_id = ?, 
                description = ?, 
                unit_of_measure = ?,
                updated_at = NOW()
            WHERE material_id = ?
        ', [$material_code, $name, $category, $unit_price, $current_stock, $min_stock_level, $supplier_id, $description, $unit_of_measure, $material_id]);
        
        if ($result) {
            // Log the action
            log_activity('Material updated', 'Material ID: ' . $material_id . ', Name: ' . $name);
            set_flash_message('success', 'Material updated successfully.');
            header('Location: materials.php');
            exit();
        } else {
            throw new Exception('Failed to update material. Please try again.');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch material data
try {
    $material = $db->getOne('SELECT * FROM materials WHERE material_id = ?', [$material_id]);
    
    if (!$material) {
        set_flash_message('error', 'Material not found.');
        header('Location: materials.php');
        exit();
    }
    
    // Fetch suppliers for dropdown
    $suppliers = $db->getAll('SELECT supplier_id, name FROM suppliers ORDER BY name');
    
} catch (Exception $e) {
    error_log("Edit material error: " . $e->getMessage());
    set_flash_message('error', 'An error occurred while loading material data.');
    header('Location: materials.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Material - Manufacturing Database System</title>
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
                <li class="active">
                    <a href="#inventorySubmenu" data-bs-toggle="collapse" class="active"><i class="fas fa-boxes"></i> Inventory</a>
                    <ul class="collapse show list-unstyled" id="inventorySubmenu">
                        <li><a href="products.php">Products</a></li>
                        <li class="active"><a href="materials.php">Raw Materials</a></li>
                    </ul>
                </li>
                <li><a href="production.php"><i class="fas fa-industry"></i> Production</a></li>
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
                <a href="materials.php" class="btn ms-3" style="background:linear-gradient(90deg, #43c6ac 0%, #191654 100%);color:#fff;border-radius:8px;border:none;font-weight:500;">← Back to Materials</a>
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
                                <h4 class="card-title mb-0">Edit Material</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($error): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?= htmlspecialchars($error) ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" id="editMaterialForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="material_code" class="form-label">Material Code *</label>
                                            <input type="text" class="form-control" id="material_code" name="material_code" 
                                                   value="<?= htmlspecialchars($material['material_code']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Name *</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?= htmlspecialchars($material['name']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="category" class="form-label">Category *</label>
                                            <select class="form-select" id="category" name="category" required>
                                                <option value="">Select Category</option>
                                                <option value="Raw Material" <?= $material['category'] === 'Raw Material' ? 'selected' : '' ?>>Raw Material</option>
                                                <option value="Component" <?= $material['category'] === 'Component' ? 'selected' : '' ?>>Component</option>
                                                <option value="Packaging" <?= $material['category'] === 'Packaging' ? 'selected' : '' ?>>Packaging</option>
                                                <option value="Chemical" <?= $material['category'] === 'Chemical' ? 'selected' : '' ?>>Chemical</option>
                                                <option value="Other" <?= $material['category'] === 'Other' ? 'selected' : '' ?>>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="unit_of_measure" class="form-label">Unit of Measure</label>
                                            <select class="form-select" id="unit_of_measure" name="unit_of_measure">
                                                <option value="kg" <?= $material['unit_of_measure'] === 'kg' ? 'selected' : '' ?>>Kilograms (kg)</option>
                                                <option value="g" <?= $material['unit_of_measure'] === 'g' ? 'selected' : '' ?>>Grams (g)</option>
                                                <option value="l" <?= $material['unit_of_measure'] === 'l' ? 'selected' : '' ?>>Liters (L)</option>
                                                <option value="ml" <?= $material['unit_of_measure'] === 'ml' ? 'selected' : '' ?>>Milliliters (mL)</option>
                                                <option value="pcs" <?= $material['unit_of_measure'] === 'pcs' ? 'selected' : '' ?>>Pieces (pcs)</option>
                                                <option value="m" <?= $material['unit_of_measure'] === 'm' ? 'selected' : '' ?>>Meters (m)</option>
                                                <option value="cm" <?= $material['unit_of_measure'] === 'cm' ? 'selected' : '' ?>>Centimeters (cm)</option>
                                                <option value="mm" <?= $material['unit_of_measure'] === 'mm' ? 'selected' : '' ?>>Millimeters (mm)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="unit_price" class="form-label">Unit Price ($)</label>
                                            <input type="number" step="0.01" min="0" class="form-control" id="unit_price" name="unit_price" 
                                                   value="<?= htmlspecialchars($material['unit_price']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="current_stock" class="form-label">Current Stock</label>
                                            <input type="number" min="0" class="form-control" id="current_stock" name="current_stock" 
                                                   value="<?= htmlspecialchars($material['current_stock']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="min_stock_level" class="form-label">Min Stock Level</label>
                                            <input type="number" min="0" class="form-control" id="min_stock_level" name="min_stock_level" 
                                                   value="<?= htmlspecialchars($material['min_stock_level']) ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="supplier_id" class="form-label">Supplier</label>
                                        <select class="form-select" id="supplier_id" name="supplier_id">
                                            <option value="">Select Supplier</option>
                                            <?php foreach ($suppliers as $supplier): ?>
                                                <option value="<?= $supplier['supplier_id'] ?>" 
                                                        <?= $material['supplier_id'] == $supplier['supplier_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($supplier['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($material['description'] ?? '') ?></textarea>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="materials.php" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary">Update Material</button>
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
            $('#editMaterialForm').on('submit', function(e) {
                let isValid = true;
                
                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                
                // Validate required fields
                const requiredFields = ['material_code', 'name', 'category'];
                requiredFields.forEach(function(field) {
                    const value = $('#' + field).val().trim();
                    if (!value) {
                        $('#' + field).addClass('is-invalid');
                        isValid = false;
                    }
                });
                
                // Validate numeric fields
                const numericFields = ['unit_price', 'current_stock', 'min_stock_level'];
                numericFields.forEach(function(field) {
                    const value = parseFloat($('#' + field).val());
                    if (value < 0) {
                        $('#' + field).addClass('is-invalid');
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields correctly.');
                }
            });
        });
    </script>
</body>
</html> 