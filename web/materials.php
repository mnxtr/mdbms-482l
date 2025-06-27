<?php // materials.php - converted from materials.html ?>
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
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
                                        <th>Unit</th>
                                        <th>Unit Price</th>
                                        <th>Stock</th>
                                        <th>Min Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>M-2001</td>
                                        <td>Steel Rod</td>
                                        <td>kg</td>
                                        <td>$2.50</td>
                                        <td>500</td>
                                        <td>100</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" disabled><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-danger" disabled><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>M-2002</td>
                                        <td>Plastic Sheet</td>
                                        <td>m2</td>
                                        <td>$1.20</td>
                                        <td>300</td>
                                        <td>50</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" disabled><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-danger" disabled><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>M-2003</td>
                                        <td>Copper Wire</td>
                                        <td>m</td>
                                        <td>$0.80</td>
                                        <td>1000</td>
                                        <td>200</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" disabled><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-danger" disabled><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
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