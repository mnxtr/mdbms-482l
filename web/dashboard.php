<?php
require_once 'config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Fetch dashboard statistics
try {
    // Total products
    $totalProductsRow = $db->getOne('SELECT COUNT(*) as count FROM products');
    $totalProducts = $totalProductsRow ? $totalProductsRow['count'] : 0;
    
    // Active production orders
    $activeOrdersRow = $db->getOne('SELECT COUNT(*) as count FROM production_orders WHERE status IN ("In Progress", "Pending")');
    $activeOrders = $activeOrdersRow ? $activeOrdersRow['count'] : 0;
    
    // Pending QC inspections
    $pendingQCRow = $db->getOne('SELECT COUNT(*) as count FROM quality_control WHERE status = "Pending"');
    $pendingQC = $pendingQCRow ? $pendingQCRow['count'] : 0;
    
    // Low stock items (products below minimum stock level)
    $lowStockItemsRow = $db->getOne('SELECT COUNT(*) as count FROM products WHERE current_stock <= min_stock_level');
    $lowStockItems = $lowStockItemsRow ? $lowStockItemsRow['count'] : 0;
    
    // Recent activity (last 10 activities)
    $recentActivity = $db->getAll('
        SELECT 
            "Production Order" as activity_type,
            po.order_id as id,
            po.created_at as date,
            CONCAT("Production Order #", po.order_id) as description,
            u.username as user,
            po.status
        FROM production_orders po
        LEFT JOIN users u ON po.created_by = u.user_id
        WHERE po.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        
        UNION ALL
        
        SELECT 
            "Quality Check" as activity_type,
            qc.qc_id as id,
            qc.inspection_date as date,
            CONCAT("Quality Check #", qc.qc_id) as description,
            u.username as user,
            qc.status
        FROM quality_control qc
        LEFT JOIN users u ON qc.inspector_id = u.user_id
        WHERE qc.inspection_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        
        ORDER BY date DESC
        LIMIT 10
    ');
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalProducts = 0;
    $activeOrders = 0;
    $pendingQC = 0;
    $lowStockItems = 0;
    $recentActivity = [];
}

// Get current user info
$currentUser = get_current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Manufacturing Database System</title>
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
                <li class="active">
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li>
                    <a href="#inventorySubmenu" data-bs-toggle="collapse"><i class="fas fa-boxes"></i> Inventory</a>
                    <ul class="collapse list-unstyled" id="inventorySubmenu">
                        <li><a href="products.php">Products</a></li>
                        <li><a href="materials.php">Raw Materials</a></li>
                    </ul>
                </li>
                <li>
                    <a href="production.php"><i class="fas fa-industry"></i> Production</a>
                </li>
                <li>
                    <a href="quality.php"><i class="fas fa-check-circle"></i> Quality Control</a>
                </li>
                <li>
                    <a href="suppliers.php"><i class="fas fa-truck"></i> Suppliers</a>
                </li>
                <li>
                    <a href="employees.php"><i class="fas fa-users"></i> Employees</a>
                </li>
                <li>
                    <a href="machines.php"><i class="fas fa-cogs"></i> Machines</a>
                </li>
                <li>
                    <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                </li>
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
                            <i class="fas fa-user"></i> <?= htmlspecialchars($currentUser['username'] ?? 'Admin') ?>
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

            <!-- Main Content Area -->
            <div class="container-fluid">
                <!-- Welcome Message -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="h3 mb-0">Welcome back, <?= htmlspecialchars($currentUser['username'] ?? 'Admin') ?>!</h1>
                        <p class="text-muted">Here's what's happening in your manufacturing system today.</p>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title text-muted">Total Products</h5>
                                        <h2 class="card-text"><?= number_format($totalProducts) ?></h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-boxes fa-2x text-primary"></i>
                                    </div>
                                </div>
                                <p class="card-text"><small class="text-muted">Last updated just now</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title text-muted">Active Orders</h5>
                                        <h2 class="card-text"><?= number_format($activeOrders) ?></h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-industry fa-2x text-warning"></i>
                                    </div>
                                </div>
                                <p class="card-text"><small class="text-muted">In progress or pending</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title text-muted">Pending QC</h5>
                                        <h2 class="card-text"><?= number_format($pendingQC) ?></h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x text-info"></i>
                                    </div>
                                </div>
                                <p class="card-text"><small class="text-muted">Awaiting inspection</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title text-muted">Low Stock Items</h5>
                                        <h2 class="card-text"><?= number_format($lowStockItems) ?></h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                                    </div>
                                </div>
                                <p class="card-text"><small class="text-muted">Below minimum level</small></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Activity</th>
                                        <th>User</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($recentActivity): ?>
                                    <?php foreach ($recentActivity as $activity): ?>
                                    <tr>
                                        <td><?= date('M j, Y', strtotime($activity['date'])) ?></td>
                                        <td><?= htmlspecialchars($activity['description']) ?></td>
                                        <td><?= htmlspecialchars($activity['user'] ?? 'System') ?></td>
                                        <td>
                                            <?php
                                            $statusClass = 'badge bg-secondary';
                                            switch ($activity['status']) {
                                                case 'Completed':
                                                    $statusClass = 'badge bg-success';
                                                    break;
                                                case 'In Progress':
                                                    $statusClass = 'badge bg-warning';
                                                    break;
                                                case 'Pending':
                                                    $statusClass = 'badge bg-info';
                                                    break;
                                                case 'Failed':
                                                    $statusClass = 'badge bg-danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="<?= $statusClass ?>"><?= htmlspecialchars($activity['status']) ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center">No recent activity found.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="add-product.php" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-plus"></i> Add Product
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="add-production.php" class="btn btn-outline-success w-100">
                                            <i class="fas fa-industry"></i> Create Order
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="add-inspection.php" class="btn btn-outline-info w-100">
                                            <i class="fas fa-check-circle"></i> Quality Check
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="reports.php" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-chart-bar"></i> View Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html> 