<?php
require_once 'config/config.php';

// Fetch all materials with supplier information
$materials = $db->getAll('
    SELECT m.*, s.name as supplier_name 
    FROM materials m 
    LEFT JOIN suppliers s ON m.supplier_id = s.supplier_id 
    ORDER BY m.name
');

// Get and display flash message if present
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raw Materials - Manufacturing Database System</title>
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
                <a href="dashboard.php" class="btn ms-3" style="background:linear-gradient(90deg, #43c6ac 0%, #191654 100%);color:#fff;border-radius:8px;border:none;font-weight:500;">← Dashboard</a>
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
                <!-- Flash message display -->
                <?php if ($flash): ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Raw Materials</h2>
                    <button class="btn btn-primary" onclick="window.location.href='add-material.php'"><i class="fas fa-plus"></i> Add Material</button>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Material Code</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Unit</th>
                                        <th>Unit Price</th>
                                        <th>Stock</th>
                                        <th>Min Stock</th>
                                        <th>Supplier</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($materials): ?>
                                    <?php foreach ($materials as $material): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($material['material_code']) ?></td>
                                        <td><?= htmlspecialchars($material['name']) ?></td>
                                        <td><?= htmlspecialchars($material['category']) ?></td>
                                        <td><?= htmlspecialchars($material['unit_of_measure']) ?></td>
                                        <td>$<?= number_format($material['unit_price'], 2) ?></td>
                                        <td>
                                            <span class="<?= (int)$material['current_stock'] <= (int)$material['min_stock_level'] ? 'text-danger fw-bold' : '' ?>">
                                                <?= (int)$material['current_stock'] ?>
                                            </span>
                                        </td>
                                        <td><?= (int)$material['min_stock_level'] ?></td>
                                        <td><?= htmlspecialchars($material['supplier_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <a href="edit-material.php?id=<?= $material['material_id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                            <a href="delete-material.php?id=<?= $material['material_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this material?');"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-center">No materials found.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
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