<?php
require_once 'config/config.php';

// Fetch all quality control inspections with related data
$inspections = $db->getAll('
    SELECT qc.qc_id, qc.inspection_date, qc.passed_quantity, qc.failed_quantity, qc.notes,
           po.order_number, u.full_name as inspector_name
    FROM quality_control qc
    LEFT JOIN production_orders po ON qc.order_id = po.order_id
    LEFT JOIN users u ON qc.inspector_id = u.user_id
    ORDER BY qc.inspection_date DESC
');

// Get and display flash message if present
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Control - Manufacturing Database System</title>
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
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li>
                    <a href="#inventorySubmenu" data-bs-toggle="collapse"><i class="fas fa-boxes"></i> Inventory</a>
                    <ul class="collapse list-unstyled" id="inventorySubmenu">
                        <li><a href="products.php">Products</a></li>
                        <li><a href="materials.php">Raw Materials</a></li>
                    </ul>
                </li>
                <li><a href="production.php"><i class="fas fa-industry"></i> Production</a></li>
                <li class="active"><a href="quality.php"><i class="fas fa-check-circle"></i> Quality Control</a></li>
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
                <div class="ms-auto">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> Admin
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
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
                    <h2>Quality Control</h2>
                    <button class="btn btn-primary" onclick="window.location.href='add-inspection.php'"><i class="fas fa-plus"></i> Add Inspection</button>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Inspection ID</th>
                                        <th>Order Number</th>
                                        <th>Inspector</th>
                                        <th>Date</th>
                                        <th>Passed</th>
                                        <th>Failed</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($inspections): ?>
                                    <?php foreach ($inspections as $inspection): ?>
                                    <tr>
                                        <td>QC-<?= str_pad($inspection['qc_id'], 3, '0', STR_PAD_LEFT) ?></td>
                                        <td><?= htmlspecialchars($inspection['order_number'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($inspection['inspector_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($inspection['inspection_date']) ?></td>
                                        <td><?= (int)$inspection['passed_quantity'] ?></td>
                                        <td><?= (int)$inspection['failed_quantity'] ?></td>
                                        <td>
                                            <?php
                                            $total = $inspection['passed_quantity'] + $inspection['failed_quantity'];
                                            $passRate = $total > 0 ? ($inspection['passed_quantity'] / $total) * 100 : 0;
                                            $badgeClass = 'bg-success';
                                            if ($passRate < 90) $badgeClass = 'bg-warning';
                                            if ($passRate < 80) $badgeClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>">
                                                <?= number_format($passRate, 1) ?>% Pass Rate
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit-inspection.php?id=<?= $inspection['qc_id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                            <a href="delete-inspection.php?id=<?= $inspection['qc_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this inspection?');"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center">No inspections found.</td></tr>
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