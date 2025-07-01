<?php
require_once 'config/config.php';

// Fetch all suppliers
$suppliers = $db->getAll('SELECT * FROM suppliers ORDER BY name');

// Get and display flash message if present
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers - Manufacturing Database System</title>
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
                <li class="active"><a href="suppliers.php"><i class="fas fa-truck"></i> Suppliers</a></li>
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
                    <h2>Suppliers</h2>
                    <button class="btn btn-primary" onclick="window.location.href='add-supplier.php'"><i class="fas fa-plus"></i> Add Supplier</button>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Contact Person</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($suppliers): ?>
                                    <?php foreach ($suppliers as $supplier): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($supplier['name']) ?></td>
                                        <td><?= htmlspecialchars($supplier['contact_person']) ?></td>
                                        <td><?= htmlspecialchars($supplier['email']) ?></td>
                                        <td><?= htmlspecialchars($supplier['phone'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php 
                                            $location = [];
                                            if (!empty($supplier['city'])) $location[] = $supplier['city'];
                                            if (!empty($supplier['state'])) $location[] = $supplier['state'];
                                            if (!empty($supplier['country'])) $location[] = $supplier['country'];
                                            echo htmlspecialchars(implode(', ', $location) ?: 'N/A');
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = 'badge bg-secondary';
                                            switch ($supplier['status']) {
                                                case 'Active':
                                                    $statusClass = 'badge bg-success';
                                                    break;
                                                case 'Inactive':
                                                    $statusClass = 'badge bg-danger';
                                                    break;
                                                case 'Suspended':
                                                    $statusClass = 'badge bg-warning';
                                                    break;
                                            }
                                            ?>
                                            <span class="<?= $statusClass ?>"><?= htmlspecialchars($supplier['status']) ?></span>
                                        </td>
                                        <td>
                                            <a href="edit-supplier.php?id=<?= $supplier['supplier_id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                            <a href="delete-supplier.php?id=<?= $supplier['supplier_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this supplier?');"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center">No suppliers found.</td></tr>
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