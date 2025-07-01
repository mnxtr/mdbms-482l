<?php
require_once 'config/config.php';

// Fetch all employees (users)
$employees = $db->getAll('SELECT * FROM users ORDER BY first_name, last_name');

// Get and display flash message if present
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - Manufacturing Database System</title>
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
                <li class="active"><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
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
                    <h2>Employees</h2>
                    <button class="btn btn-primary" onclick="window.location.href='add-employee.php'"><i class="fas fa-plus"></i> Add Employee</button>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Department</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($employees): ?>
                                    <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></td>
                                        <td><?= htmlspecialchars($employee['username']) ?></td>
                                        <td><?= htmlspecialchars($employee['role']) ?></td>
                                        <td><?= htmlspecialchars($employee['department'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($employee['email']) ?></td>
                                        <td><?= htmlspecialchars($employee['phone'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php
                                            $statusClass = 'badge bg-secondary';
                                            switch ($employee['status']) {
                                                case 'Active':
                                                    $statusClass = 'badge bg-success';
                                                    break;
                                                case 'Inactive':
                                                    $statusClass = 'badge bg-danger';
                                                    break;
                                                case 'On Leave':
                                                    $statusClass = 'badge bg-warning';
                                                    break;
                                            }
                                            ?>
                                            <span class="<?= $statusClass ?>"><?= htmlspecialchars($employee['status']) ?></span>
                                        </td>
                                        <td>
                                            <a href="edit-employee.php?id=<?= $employee['user_id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                            <?php if ($employee['role'] !== 'Admin'): ?>
                                                <a href="delete-employee.php?id=<?= $employee['user_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this employee?');"><i class="fas fa-trash"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center">No employees found.</td></tr>
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