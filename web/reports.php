<?php // reports.php - converted from reports.html ?>
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
                <h2>Reports</h2>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">Production Report</div>
                            <div class="card-body">
                                <canvas id="productionChart" width="100%" height="60"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">Inventory Report</div>
                            <div class="card-body">
                                <canvas id="inventoryChart" width="100%" height="60"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">Quality Report</div>
                            <div class="card-body">
                                <canvas id="qualityChart" width="100%" height="60"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info mt-4">Charts are for demonstration only. No real data is displayed.</div>
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
        // Example chart initialization for demonstration
        if (typeof Chart !== 'undefined') {
            var ctx1 = document.getElementById('productionChart');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Production Output',
                            data: [120, 190, 300, 500, 200, 300],
                            backgroundColor: muiColors.primary,
                            borderColor: muiColors.primary,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: { legend: { labels: { color: '#e0e0e0' } } },
                        scales: { x: { ticks: { color: '#e0e0e0' } }, y: { ticks: { color: '#e0e0e0' } } }
                    }
                });
            }
            var ctx2 = document.getElementById('inventoryChart');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'line',
                    data: {
                        labels: ['Widgets', 'Gadgets', 'Components'],
                        datasets: [{
                            label: 'Stock',
                            data: [120, 80, 200],
                            backgroundColor: muiColors.info,
                            borderColor: muiColors.info,
                            fill: false,
                            tension: 0.3
                        }]
                    },
                    options: {
                        plugins: { legend: { labels: { color: '#e0e0e0' } } },
                        scales: { x: { ticks: { color: '#e0e0e0' } }, y: { ticks: { color: '#e0e0e0' } } }
                    }
                });
            }
            var ctx3 = document.getElementById('qualityChart');
            if (ctx3) {
                new Chart(ctx3, {
                    type: 'pie',
                    data: {
                        labels: ['Passed', 'Failed', 'Warning'],
                        datasets: [{
                            data: [95, 5, 10],
                            backgroundColor: [muiColors.success, muiColors.error, muiColors.warning]
                        }]
                    },
                    options: {
                        plugins: { legend: { labels: { color: '#e0e0e0' } } }
                    }
                });
            }
        }
    </script>
</body>
</html> 