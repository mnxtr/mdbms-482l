<?php
require_once 'config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$supplier = null;
$error = '';
$success = '';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash_message('error', 'Invalid supplier ID provided.');
    header('Location: suppliers.php');
    exit();
}

$supplier_id = (int)$_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $name = trim($_POST['name']);
        $contact_person = trim($_POST['contact_person']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $zip_code = trim($_POST['zip_code']);
        $country = trim($_POST['country']);
        $status = trim($_POST['status']);
        
        // Validation
        if (empty($name) || empty($contact_person) || empty($email)) {
            throw new Exception('Name, contact person, and email are required.');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }
        
        // Check if email exists (excluding current supplier)
        $existingEmail = $db->getOne('SELECT supplier_id FROM suppliers WHERE email = ? AND supplier_id != ?', [$email, $supplier_id]);
        if ($existingEmail) {
            throw new Exception('Email already exists. Please choose a different email.');
        }
        
        // Update supplier
        $result = $db->execute('
            UPDATE suppliers SET 
                name = ?, 
                contact_person = ?, 
                email = ?, 
                phone = ?, 
                address = ?, 
                city = ?, 
                state = ?, 
                zip_code = ?, 
                country = ?, 
                status = ?,
                updated_at = NOW()
            WHERE supplier_id = ?
        ', [$name, $contact_person, $email, $phone, $address, $city, $state, $zip_code, $country, $status, $supplier_id]);
        
        if ($result) {
            // Log the action
            log_activity('Supplier updated', 'Supplier ID: ' . $supplier_id . ', Name: ' . $name);
            set_flash_message('success', 'Supplier updated successfully.');
            header('Location: suppliers.php');
            exit();
        } else {
            throw new Exception('Failed to update supplier. Please try again.');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch supplier data
try {
    $supplier = $db->getOne('SELECT * FROM suppliers WHERE supplier_id = ?', [$supplier_id]);
    
    if (!$supplier) {
        set_flash_message('error', 'Supplier not found.');
        header('Location: suppliers.php');
        exit();
    }
    
} catch (Exception $e) {
    error_log("Edit supplier error: " . $e->getMessage());
    set_flash_message('error', 'An error occurred while loading supplier data.');
    header('Location: suppliers.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Supplier - Manufacturing Database System</title>
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
                <li class="active">
                    <a href="suppliers.php"><i class="fas fa-truck"></i> Suppliers</a>
                </li>
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
                <a href="suppliers.php" class="btn ms-3" style="background:linear-gradient(90deg, #43c6ac 0%, #191654 100%);color:#fff;border-radius:8px;border:none;font-weight:500;">← Back to Suppliers</a>
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
                                <h4 class="card-title mb-0">Edit Supplier</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($error): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?= htmlspecialchars($error) ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" id="editSupplierForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Company Name *</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?= htmlspecialchars($supplier['name']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="contact_person" class="form-label">Contact Person *</label>
                                            <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                                   value="<?= htmlspecialchars($supplier['contact_person']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email *</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?= htmlspecialchars($supplier['email']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?= htmlspecialchars($supplier['phone'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="2"><?= htmlspecialchars($supplier['address'] ?? '') ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city" 
                                                   value="<?= htmlspecialchars($supplier['city'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="state" class="form-label">State/Province</label>
                                            <input type="text" class="form-control" id="state" name="state" 
                                                   value="<?= htmlspecialchars($supplier['state'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="zip_code" class="form-label">ZIP/Postal Code</label>
                                            <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                                   value="<?= htmlspecialchars($supplier['zip_code'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="country" class="form-label">Country</label>
                                            <input type="text" class="form-control" id="country" name="country" 
                                                   value="<?= htmlspecialchars($supplier['country'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="Active" <?= $supplier['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                                <option value="Inactive" <?= $supplier['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                                <option value="Suspended" <?= $supplier['status'] === 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="suppliers.php" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary">Update Supplier</button>
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
            $('#editSupplierForm').on('submit', function(e) {
                let isValid = true;
                
                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                
                // Validate required fields
                const requiredFields = ['name', 'contact_person', 'email'];
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