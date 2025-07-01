<?php
require_once 'config/config.php';

// Fetch production statistics
try {
    $totalOrdersRow = $db->getOne('SELECT COUNT(*) as count FROM production_orders');
    $totalOrders = $totalOrdersRow ? $totalOrdersRow['count'] : 0;

    $activeOrdersRow = $db->getOne('SELECT COUNT(*) as count FROM production_orders WHERE status IN ("In Progress", "Pending")');
    $activeOrders = $activeOrdersRow ? $activeOrdersRow['count'] : 0;

    $completedOrdersRow = $db->getOne('SELECT COUNT(*) as count FROM production_orders WHERE status = "Completed"');
    $completedOrders = $completedOrdersRow ? $completedOrdersRow['count'] : 0;

    $totalProductsRow = $db->getOne('SELECT COUNT(*) as count FROM products');
    $totalProducts = $totalProductsRow ? $totalProductsRow['count'] : 0;
    
    // Fetch recent production orders
    $recentOrders = $db->getAll('
        SELECT po.*, p.name as product_name, u.username as created_by_name
        FROM production_orders po
        LEFT JOIN products p ON po.product_id = p.product_id
        LEFT JOIN users u ON po.created_by = u.user_id
        ORDER BY po.created_at DESC
        LIMIT 10
    ');
    
} catch (Exception $e) {
    error_log("Production page error: " . $e->getMessage());
    $totalOrders = 0;
    $activeOrders = 0;
    $completedOrders = 0;
    $totalProducts = 0;
    $recentOrders = [];
}

// Get and display flash message if present
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production - Manufacturing Database System</title>
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
                <li class="active"><a href="production.php"><i class="fas fa-industry"></i> Production</a></li>
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
                
                <!-- Production Statistics -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title text-muted">Total Orders</h5>
                                        <h2 class="card-text"><?= number_format($totalOrders) ?></h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-clipboard-list fa-2x text-primary"></i>
                                    </div>
                                </div>
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
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title text-muted">Completed</h5>
                                        <h2 class="card-text"><?= number_format($completedOrders) ?></h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title text-muted">Total Products</h5>
                                        <h2 class="card-text"><?= number_format($totalProducts) ?></h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-boxes fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Production Orders -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Production Orders</h5>
                        <button onclick="window.location.href='add-production.php'" class="btn btn-primary"><i class="fas fa-plus"></i> Create Order</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($recentOrders): ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td>#<?= $order['order_id'] ?></td>
                                        <td><?= htmlspecialchars($order['product_name'] ?? 'N/A') ?></td>
                                        <td><?= number_format($order['quantity']) ?></td>
                                        <td>
                                            <?php
                                            $statusClass = 'badge bg-secondary';
                                            switch ($order['status']) {
                                                case 'Completed':
                                                    $statusClass = 'badge bg-success';
                                                    break;
                                                case 'In Progress':
                                                    $statusClass = 'badge bg-warning';
                                                    break;
                                                case 'Pending':
                                                    $statusClass = 'badge bg-info';
                                                    break;
                                                case 'Cancelled':
                                                    $statusClass = 'badge bg-danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="<?= $statusClass ?>"><?= htmlspecialchars($order['status']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($order['created_by_name'] ?? 'System') ?></td>
                                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <a href="edit-production.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                            <a href="delete-production.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this order?');"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center">No production orders found.</td></tr>
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