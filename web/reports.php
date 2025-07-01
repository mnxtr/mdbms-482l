<?php
require_once 'config/config.php';

// Fetch report data
try {
    // Production statistics
    $totalOrders = $db->getOne('SELECT COUNT(*) FROM production_orders');
    $completedOrders = $db->getOne('SELECT COUNT(*) FROM production_orders WHERE status = "Completed"');
    $pendingOrders = $db->getOne('SELECT COUNT(*) FROM production_orders WHERE status = "Pending"');
    $inProgressOrders = $db->getOne('SELECT COUNT(*) FROM production_orders WHERE status = "In Progress"');
    
    // Inventory statistics
    $totalProducts = $db->getOne('SELECT COUNT(*) FROM products');
    $lowStockProducts = $db->getOne('SELECT COUNT(*) FROM products WHERE current_stock <= min_stock_level');
    $totalMaterials = $db->getOne('SELECT COUNT(*) FROM materials');
    $lowStockMaterials = $db->getOne('SELECT COUNT(*) FROM materials WHERE current_stock <= min_stock_level');
    
    // Quality statistics
    $totalInspections = $db->getOne('SELECT COUNT(*) FROM quality_control');
    $passedInspections = $db->getOne('SELECT COUNT(*) FROM quality_control WHERE result = "Pass"');
    $failedInspections = $db->getOne('SELECT COUNT(*) FROM quality_control WHERE result = "Fail"');
    $pendingInspections = $db->getOne('SELECT COUNT(*) FROM quality_control WHERE status = "Pending"');
    
    // Monthly production data for chart
    $monthlyProduction = $db->getAll('
        SELECT 
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as count,
            SUM(quantity) as total_quantity
        FROM production_orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, "%Y-%m")
        ORDER BY month
    ');
    
    // Top products by production
    $topProducts = $db->getAll('
        SELECT 
            p.name,
            COUNT(po.order_id) as order_count,
            SUM(po.quantity) as total_quantity
        FROM products p
        LEFT JOIN production_orders po ON p.product_id = po.product_id
        GROUP BY p.product_id, p.name
        ORDER BY total_quantity DESC
        LIMIT 5
    ');
    
} catch (Exception $e) {
    error_log("Reports page error: " . $e->getMessage());
    $totalOrders = 0;
    $completedOrders = 0;
    $pendingOrders = 0;
    $inProgressOrders = 0;
    $totalProducts = 0;
    $lowStockProducts = 0;
    $totalMaterials = 0;
    $lowStockMaterials = 0;
    $totalInspections = 0;
    $passedInspections = 0;
    $failedInspections = 0;
    $pendingInspections = 0;
    $monthlyProduction = [];
    $topProducts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Manufacturing Database System</title>
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
                <li><a href="suppliers.php"><i class="fas fa-truck"></i> Suppliers</a></li>
                <li><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
                <li><a href="machines.php"><i class="fas fa-cogs"></i> Machines</a></li>
                <li class="active"><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
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
                <h2>Reports & Analytics</h2>
                
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted">Total Orders</h6>
                                        <h3 class="card-text"><?= number_format($totalOrders) ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-clipboard-list fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted">Completed</h6>
                                        <h3 class="card-text"><?= number_format($completedOrders) ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted">Quality Pass Rate</h6>
                                        <h3 class="card-text">
                                            <?= $totalInspections > 0 ? round(($passedInspections / $totalInspections) * 100, 1) : 0 ?>%
                                        </h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-chart-line fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted">Low Stock Items</h6>
                                        <h3 class="card-text"><?= number_format($lowStockProducts + $lowStockMaterials) ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Production Orders by Status</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="productionStatusChart" width="100%" height="60"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Quality Control Results</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="qualityChart" width="100%" height="60"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Top Products by Production Volume</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Order Count</th>
                                        <th>Total Quantity</th>
                                        <th>Average Quantity per Order</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($topProducts): ?>
                                    <?php foreach ($topProducts as $product): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= number_format($product['order_count']) ?></td>
                                        <td><?= number_format($product['total_quantity'] ?? 0) ?></td>
                                        <td>
                                            <?= $product['order_count'] > 0 ? number_format(($product['total_quantity'] ?? 0) / $product['order_count'], 1) : 0 ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center">No production data available.</td></tr>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Material UI color palette
        const muiColors = {
            primary: '#1976d2',
            secondary: '#9c27b0',
            error: '#d32f2f',
            warning: '#ffa000',
            info: '#0288d1',
            success: '#388e3c',
        };

        // Production Status Chart
        if (typeof Chart !== 'undefined') {
            var ctx1 = document.getElementById('productionStatusChart');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed', 'In Progress', 'Pending'],
                        datasets: [{
                            data: [<?= $completedOrders ?>, <?= $inProgressOrders ?>, <?= $pendingOrders ?>],
                            backgroundColor: [muiColors.success, muiColors.warning, muiColors.info],
                            borderWidth: 2,
                            borderColor: '#2c3e50'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#e0e0e0' }
                            }
                        }
                    }
                });
            }

            // Quality Chart
            var ctx2 = document.getElementById('qualityChart');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: ['Passed', 'Failed', 'Pending'],
                        datasets: [{
                            data: [<?= $passedInspections ?>, <?= $failedInspections ?>, <?= $pendingInspections ?>],
                            backgroundColor: [muiColors.success, muiColors.error, muiColors.warning]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#e0e0e0' }
                            }
                        }
                    }
                });
            }
        }
    </script>
</body>
</html> 