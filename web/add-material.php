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
    $material_name = sanitize_input($_POST['material_name'] ?? '');
    $material_type = sanitize_input($_POST['material_type'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $unit = sanitize_input($_POST['unit'] ?? '');
    $current_stock = (float)($_POST['current_stock'] ?? 0);
    $minimum_stock = (float)($_POST['minimum_stock'] ?? 0);
    $unit_cost = (float)($_POST['unit_cost'] ?? 0);
    $supplier_id = sanitize_input($_POST['supplier_id'] ?? '');
    $location = sanitize_input($_POST['location'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');

    $errors = [];

    // Validation
    if (empty($material_name)) {
        $errors[] = 'Material name is required.';
    }
    if (empty($material_type)) {
        $errors[] = 'Material type is required.';
    }
    if (empty($unit)) {
        $errors[] = 'Unit is required.';
    }
    if ($current_stock < 0) {
        $errors[] = 'Current stock cannot be negative.';
    }
    if ($minimum_stock < 0) {
        $errors[] = 'Minimum stock cannot be negative.';
    }
    if ($unit_cost < 0) {
        $errors[] = 'Unit cost cannot be negative.';
    }

    // Check for duplicate material name
    $existing = $db->getOne("SELECT * FROM materials WHERE material_name = ?", [$material_name]);
    if ($existing) {
        $errors[] = 'A material with this name already exists.';
    }

    if (empty($errors)) {
        $material_data = [
            'material_name' => $material_name,
            'material_type' => $material_type,
            'description' => $description,
            'unit' => $unit,
            'current_stock' => $current_stock,
            'minimum_stock' => $minimum_stock,
            'unit_cost' => $unit_cost,
            'supplier_id' => $supplier_id ?: null,
            'location' => $location,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['user_id']
        ];

        $result = $db->insert('materials', $material_data);
        
        if ($result !== false) {
            set_flash_message('success', 'Material added successfully!');
            header('Location: materials.php');
            exit;
        } else {
            set_flash_message('danger', 'Failed to add material. Please try again.');
        }
    } else {
        set_flash_message('warning', implode(' ', $errors));
    }
}

// Get suppliers for dropdown
$suppliers = $db->getAll("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Material - Manufacturing Database System</title>
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
                                <h5 class="mb-0"><i class="fas fa-plus"></i> Add New Material</h5>
                            </div>
                            <div class="card-body">
                                <form id="materialForm" method="POST" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="material_name" class="form-label">Material Name *</label>
                                                <input type="text" class="form-control" id="material_name" name="material_name" value="<?= htmlspecialchars($_POST['material_name'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="material_type" class="form-label">Material Type *</label>
                                                <select class="form-select" id="material_type" name="material_type" required>
                                                    <option value="">Select Type</option>
                                                    <option value="Raw Material" <?= ($_POST['material_type'] ?? '') === 'Raw Material' ? 'selected' : '' ?>>Raw Material</option>
                                                    <option value="Component" <?= ($_POST['material_type'] ?? '') === 'Component' ? 'selected' : '' ?>>Component</option>
                                                    <option value="Packaging" <?= ($_POST['material_type'] ?? '') === 'Packaging' ? 'selected' : '' ?>>Packaging</option>
                                                    <option value="Chemical" <?= ($_POST['material_type'] ?? '') === 'Chemical' ? 'selected' : '' ?>>Chemical</option>
                                                    <option value="Other" <?= ($_POST['material_type'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Material description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="unit" class="form-label">Unit *</label>
                                                <select class="form-select" id="unit" name="unit" required>
                                                    <option value="">Select Unit</option>
                                                    <option value="kg" <?= ($_POST['unit'] ?? '') === 'kg' ? 'selected' : '' ?>>Kilograms (kg)</option>
                                                    <option value="g" <?= ($_POST['unit'] ?? '') === 'g' ? 'selected' : '' ?>>Grams (g)</option>
                                                    <option value="l" <?= ($_POST['unit'] ?? '') === 'l' ? 'selected' : '' ?>>Liters (L)</option>
                                                    <option value="ml" <?= ($_POST['unit'] ?? '') === 'ml' ? 'selected' : '' ?>>Milliliters (mL)</option>
                                                    <option value="pcs" <?= ($_POST['unit'] ?? '') === 'pcs' ? 'selected' : '' ?>>Pieces (pcs)</option>
                                                    <option value="m" <?= ($_POST['unit'] ?? '') === 'm' ? 'selected' : '' ?>>Meters (m)</option>
                                                    <option value="cm" <?= ($_POST['unit'] ?? '') === 'cm' ? 'selected' : '' ?>>Centimeters (cm)</option>
                                                    <option value="mm" <?= ($_POST['unit'] ?? '') === 'mm' ? 'selected' : '' ?>>Millimeters (mm)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="current_stock" class="form-label">Current Stock</label>
                                                <input type="number" step="0.01" class="form-control" id="current_stock" name="current_stock" value="<?= htmlspecialchars($_POST['current_stock'] ?? '0') ?>" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="minimum_stock" class="form-label">Minimum Stock</label>
                                                <input type="number" step="0.01" class="form-control" id="minimum_stock" name="minimum_stock" value="<?= htmlspecialchars($_POST['minimum_stock'] ?? '0') ?>" min="0">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="unit_cost" class="form-label">Unit Cost</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.01" class="form-control" id="unit_cost" name="unit_cost" value="<?= htmlspecialchars($_POST['unit_cost'] ?? '0') ?>" min="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="supplier_id" class="form-label">Supplier</label>
                                                <select class="form-select" id="supplier_id" name="supplier_id">
                                                    <option value="">Select Supplier</option>
                                                    <?php foreach ($suppliers as $supplier): ?>
                                                        <option value="<?= $supplier['supplier_id'] ?>" <?= ($_POST['supplier_id'] ?? '') == $supplier['supplier_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($supplier['supplier_name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="location" class="form-label">Storage Location</label>
                                        <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" placeholder="e.g., Warehouse A, Shelf 3">
                                    </div>

                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about the material"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                                    </div>

                                    <div id="formAlert"></div>
                                    <div class="d-flex justify-content-between">
                                        <a href="materials.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Materials</a>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Material</button>
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
        $('#materialForm').on('submit', function(e) {
            var valid = true;
            var msg = '';
            
            var materialName = $('#material_name').val().trim();
            var materialType = $('#material_type').val();
            var unit = $('#unit').val();
            var currentStock = parseFloat($('#current_stock').val());
            var minimumStock = parseFloat($('#minimum_stock').val());
            var unitCost = parseFloat($('#unit_cost').val());
            
            if (!materialName) {
                valid = false;
                msg += '<div class="alert alert-warning">Material name is required.</div>';
            }
            if (!materialType) {
                valid = false;
                msg += '<div class="alert alert-warning">Material type is required.</div>';
            }
            if (!unit) {
                valid = false;
                msg += '<div class="alert alert-warning">Unit is required.</div>';
            }
            if (currentStock < 0) {
                valid = false;
                msg += '<div class="alert alert-warning">Current stock cannot be negative.</div>';
            }
            if (minimumStock < 0) {
                valid = false;
                msg += '<div class="alert alert-warning">Minimum stock cannot be negative.</div>';
            }
            if (unitCost < 0) {
                valid = false;
                msg += '<div class="alert alert-warning">Unit cost cannot be negative.</div>';
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