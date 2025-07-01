<?php
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// Check if user is logged in for protected pages
if (isset($require_auth) && $require_auth && !is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Get current user for display
$currentUser = get_logged_in_user();
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'Manufacturing Database System') ?>">
    <meta name="author" content="<?= APP_NAME ?>">
    <meta name="csrf-token" content="<?= Security::generateCSRFToken() ?>">
    
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="css/style.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Additional CSS for specific pages -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link href="<?= htmlspecialchars($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    
    <!-- Security headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
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
                <li class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="<?= in_array($currentPage, ['products', 'materials']) ? 'active' : '' ?>">
                    <a href="#inventorySubmenu" data-bs-toggle="collapse" class="<?= in_array($currentPage, ['products', 'materials']) ? 'active' : '' ?>">
                        <i class="fas fa-boxes"></i> Inventory
                    </a>
                    <ul class="collapse <?= in_array($currentPage, ['products', 'materials']) ? 'show' : '' ?> list-unstyled" id="inventorySubmenu">
                        <li class="<?= $currentPage === 'products' ? 'active' : '' ?>">
                            <a href="products.php">Products</a>
                        </li>
                        <li class="<?= $currentPage === 'materials' ? 'active' : '' ?>">
                            <a href="materials.php">Raw Materials</a>
                        </li>
                    </ul>
                </li>
                <li class="<?= $currentPage === 'production' ? 'active' : '' ?>">
                    <a href="production.php"><i class="fas fa-industry"></i> Production</a>
                </li>
                <li class="<?= $currentPage === 'quality' ? 'active' : '' ?>">
                    <a href="quality.php"><i class="fas fa-check-circle"></i> Quality Control</a>
                </li>
                <li class="<?= $currentPage === 'suppliers' ? 'active' : '' ?>">
                    <a href="suppliers.php"><i class="fas fa-truck"></i> Suppliers</a>
                </li>
                <li class="<?= $currentPage === 'employees' ? 'active' : '' ?>">
                    <a href="employees.php"><i class="fas fa-users"></i> Employees</a>
                </li>
                <li class="<?= $currentPage === 'machines' ? 'active' : '' ?>">
                    <a href="machines.php"><i class="fas fa-cogs"></i> Machines</a>
                </li>
                <li class="<?= $currentPage === 'reports' ? 'active' : '' ?>">
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
                
                <?php if (isset($showBackButton) && $showBackButton): ?>
                    <a href="<?= htmlspecialchars($backUrl ?? 'dashboard.php') ?>" class="btn ms-3" 
                       style="background:linear-gradient(90deg, #43c6ac 0%, #191654 100%);color:#fff;border-radius:8px;border:none;font-weight:500;">
                        ← <?= htmlspecialchars($backText ?? 'Back') ?>
                    </a>
                <?php endif; ?>
                
                <div class="ms-auto">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> 
                            <?= htmlspecialchars($currentUser['username'] ?? 'Guest') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid mt-4">
                <!-- Flash message display -->
                <?php 
                $flash = get_flash_message();
                if ($flash): 
                ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Page Header -->
                <?php if (isset($pageHeader)): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2><?= htmlspecialchars($pageHeader) ?></h2>
                        <?php if (isset($pageActions)): ?>
                            <div class="page-actions">
                                <?= $pageActions ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html> 