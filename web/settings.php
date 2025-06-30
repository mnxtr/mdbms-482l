<?php
require_once 'config/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user is admin
$user = $db->getOne('SELECT * FROM users WHERE user_id = ?', [$_SESSION['user_id']]);
if (!$user || $user['role'] !== 'admin') {
    set_flash_message('danger', 'Access denied. Admin privileges required.');
    header('Location: index.php');
    exit;
}

// Handle form submission for settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $update_data = [];
    
    // System Settings
    $company_name = sanitize_input($_POST['company_name'] ?? '');
    $system_email = sanitize_input($_POST['system_email'] ?? '');
    $timezone = sanitize_input($_POST['timezone'] ?? '');
    $date_format = sanitize_input($_POST['date_format'] ?? '');
    $currency = sanitize_input($_POST['currency'] ?? '');
    
    // Quality Control Settings
    $default_inspection_threshold = (int)($_POST['default_inspection_threshold'] ?? 95);
    $auto_alert_failed_inspections = isset($_POST['auto_alert_failed_inspections']) ? 1 : 0;
    $require_inspection_notes = isset($_POST['require_inspection_notes']) ? 1 : 0;
    
    // Production Settings
    $default_production_status = sanitize_input($_POST['default_production_status'] ?? 'pending');
    $auto_calculate_costs = isset($_POST['auto_calculate_costs']) ? 1 : 0;
    $enable_batch_tracking = isset($_POST['enable_batch_tracking']) ? 1 : 0;
    
    // Notification Settings
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $low_stock_alerts = isset($_POST['low_stock_alerts']) ? 1 : 0;
    $quality_alert_threshold = (int)($_POST['quality_alert_threshold'] ?? 90);
    
    // Validation
    if (empty($company_name)) {
        $errors[] = 'Company name is required.';
    }
    
    if (!empty($system_email) && !filter_var($system_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid system email is required.';
    }
    
    if ($default_inspection_threshold < 0 || $default_inspection_threshold > 100) {
        $errors[] = 'Inspection threshold must be between 0 and 100.';
    }
    
    if ($quality_alert_threshold < 0 || $quality_alert_threshold > 100) {
        $errors[] = 'Quality alert threshold must be between 0 and 100.';
    }
    
    // Update settings if no errors
    if (empty($errors)) {
        $settings = [
            'company_name' => $company_name,
            'system_email' => $system_email,
            'timezone' => $timezone,
            'date_format' => $date_format,
            'currency' => $currency,
            'default_inspection_threshold' => $default_inspection_threshold,
            'auto_alert_failed_inspections' => $auto_alert_failed_inspections,
            'require_inspection_notes' => $require_inspection_notes,
            'default_production_status' => $default_production_status,
            'auto_calculate_costs' => $auto_calculate_costs,
            'enable_batch_tracking' => $enable_batch_tracking,
            'email_notifications' => $email_notifications,
            'low_stock_alerts' => $low_stock_alerts,
            'quality_alert_threshold' => $quality_alert_threshold,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $_SESSION['user_id']
        ];
        
        // Check if settings table exists, if not create it
        try {
            $db->query("CREATE TABLE IF NOT EXISTS system_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            
            // Insert or update settings
            foreach ($settings as $key => $value) {
                $existing = $db->getOne("SELECT * FROM system_settings WHERE setting_key = ?", [$key]);
                if ($existing) {
                    $db->update('system_settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
                } else {
                    $db->insert('system_settings', [
                        'setting_key' => $key,
                        'setting_value' => $value,
                        'setting_type' => is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string'),
                        'description' => ucwords(str_replace('_', ' ', $key))
                    ]);
                }
            }
            
            set_flash_message('success', 'Settings updated successfully!');
        } catch (Exception $e) {
            error_log('Settings Update Error: ' . $e->getMessage());
            set_flash_message('danger', 'Failed to update settings. Please try again.');
        }
    } else {
        set_flash_message('warning', implode(' ', $errors));
    }
}

// Load current settings
$current_settings = [];
try {
    $settings_data = $db->getAll("SELECT setting_key, setting_value FROM system_settings");
    foreach ($settings_data as $setting) {
        $current_settings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (Exception $e) {
    // Settings table might not exist yet, use defaults
}

// Set default values if not in database
$settings = [
    'company_name' => $current_settings['company_name'] ?? 'Manufacturing Database System',
    'system_email' => $current_settings['system_email'] ?? '',
    'timezone' => $current_settings['timezone'] ?? 'UTC',
    'date_format' => $current_settings['date_format'] ?? 'Y-m-d',
    'currency' => $current_settings['currency'] ?? 'USD',
    'default_inspection_threshold' => $current_settings['default_inspection_threshold'] ?? 95,
    'auto_alert_failed_inspections' => $current_settings['auto_alert_failed_inspections'] ?? 1,
    'require_inspection_notes' => $current_settings['require_inspection_notes'] ?? 1,
    'default_production_status' => $current_settings['default_production_status'] ?? 'pending',
    'auto_calculate_costs' => $current_settings['auto_calculate_costs'] ?? 1,
    'enable_batch_tracking' => $current_settings['enable_batch_tracking'] ?? 1,
    'email_notifications' => $current_settings['email_notifications'] ?? 1,
    'low_stock_alerts' => $current_settings['low_stock_alerts'] ?? 1,
    'quality_alert_threshold' => $current_settings['quality_alert_threshold'] ?? 90
];

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Manufacturing Database System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .settings-header {
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        .settings-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
            margin-bottom: 1.5rem;
        }
        .form-label { 
            color: #1976d2; 
            font-weight: 600; 
        }
        .form-control, .form-select {
            background: #f5f5f6;
            color: #222;
            border: 2px solid #1976d2;
            border-radius: 8px;
        }
        .form-control:focus, .form-select:focus {
            background: #fff;
            color: #1976d2;
            border-color: #1565c0;
            box-shadow: 0 0 0 2px #1976d2;
        }
        .form-check-input:checked {
            background-color: #1976d2;
            border-color: #1976d2;
        }
        .btn-primary {
            background: #1976d2;
            border: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #1565c0;
        }
        .settings-section {
            border-left: 4px solid #1976d2;
            padding-left: 1rem;
            margin-bottom: 2rem;
        }
    </style>
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
                <li><a href="employee.php"><i class="fas fa-users"></i> Employees</a></li>
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
                            <i class="fas fa-user"></i> <?= htmlspecialchars($user['full_name']) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item active" href="settings.php">Settings</a></li>
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

                <!-- Settings Header -->
                <div class="settings-header">
                    <h2><i class="fas fa-cog"></i> System Settings</h2>
                    <p class="mb-0">Configure system preferences and parameters</p>
                </div>

                <form id="settingsForm" method="POST">
                    <!-- System Settings -->
                    <div class="settings-section">
                        <h5><i class="fas fa-building"></i> System Configuration</h5>
                        <div class="settings-card">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">Company Name</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?= htmlspecialchars($settings['company_name']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="system_email" class="form-label">System Email</label>
                                        <input type="email" class="form-control" id="system_email" name="system_email" value="<?= htmlspecialchars($settings['system_email']) ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="timezone" class="form-label">Timezone</label>
                                        <select class="form-select" id="timezone" name="timezone">
                                            <option value="UTC" <?= $settings['timezone'] === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                            <option value="America/New_York" <?= $settings['timezone'] === 'America/New_York' ? 'selected' : '' ?>>Eastern Time</option>
                                            <option value="America/Chicago" <?= $settings['timezone'] === 'America/Chicago' ? 'selected' : '' ?>>Central Time</option>
                                            <option value="America/Denver" <?= $settings['timezone'] === 'America/Denver' ? 'selected' : '' ?>>Mountain Time</option>
                                            <option value="America/Los_Angeles" <?= $settings['timezone'] === 'America/Los_Angeles' ? 'selected' : '' ?>>Pacific Time</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="date_format" class="form-label">Date Format</label>
                                        <select class="form-select" id="date_format" name="date_format">
                                            <option value="Y-m-d" <?= $settings['date_format'] === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                            <option value="m/d/Y" <?= $settings['date_format'] === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                            <option value="d/m/Y" <?= $settings['date_format'] === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="currency" class="form-label">Currency</label>
                                        <select class="form-select" id="currency" name="currency">
                                            <option value="USD" <?= $settings['currency'] === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                            <option value="EUR" <?= $settings['currency'] === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                                            <option value="GBP" <?= $settings['currency'] === 'GBP' ? 'selected' : '' ?>>GBP (£)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quality Control Settings -->
                    <div class="settings-section">
                        <h5><i class="fas fa-check-circle"></i> Quality Control Settings</h5>
                        <div class="settings-card">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="default_inspection_threshold" class="form-label">Default Inspection Threshold (%)</label>
                                        <input type="number" class="form-control" id="default_inspection_threshold" name="default_inspection_threshold" value="<?= $settings['default_inspection_threshold'] ?>" min="0" max="100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="quality_alert_threshold" class="form-label">Quality Alert Threshold (%)</label>
                                        <input type="number" class="form-control" id="quality_alert_threshold" name="quality_alert_threshold" value="<?= $settings['quality_alert_threshold'] ?>" min="0" max="100">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="auto_alert_failed_inspections" name="auto_alert_failed_inspections" <?= $settings['auto_alert_failed_inspections'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="auto_alert_failed_inspections">
                                            Auto-alert on failed inspections
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="require_inspection_notes" name="require_inspection_notes" <?= $settings['require_inspection_notes'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="require_inspection_notes">
                                            Require notes for failed inspections
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Production Settings -->
                    <div class="settings-section">
                        <h5><i class="fas fa-industry"></i> Production Settings</h5>
                        <div class="settings-card">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="default_production_status" class="form-label">Default Production Status</label>
                                        <select class="form-select" id="default_production_status" name="default_production_status">
                                            <option value="pending" <?= $settings['default_production_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="in_progress" <?= $settings['default_production_status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="completed" <?= $settings['default_production_status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="auto_calculate_costs" name="auto_calculate_costs" <?= $settings['auto_calculate_costs'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="auto_calculate_costs">
                                            Auto-calculate production costs
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="enable_batch_tracking" name="enable_batch_tracking" <?= $settings['enable_batch_tracking'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enable_batch_tracking">
                                            Enable batch tracking
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="settings-section">
                        <h5><i class="fas fa-bell"></i> Notification Settings</h5>
                        <div class="settings-card">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?= $settings['email_notifications'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="email_notifications">
                                            Enable email notifications
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="low_stock_alerts" name="low_stock_alerts" <?= $settings['low_stock_alerts'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="low_stock_alerts">
                                            Low stock alerts
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="formAlert"></div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script>
    $(document).ready(function() {
        $('#settingsForm').on('submit', function(e) {
            var valid = true;
            var msg = '';
            
            var companyName = $('#company_name').val().trim();
            var systemEmail = $('#system_email').val().trim();
            var inspectionThreshold = $('#default_inspection_threshold').val();
            var qualityThreshold = $('#quality_alert_threshold').val();
            
            if (!companyName) {
                valid = false;
                msg += '<div class="alert alert-warning">Company name is required.</div>';
            }
            
            if (systemEmail && !/^\S+@\S+\.\S+$/.test(systemEmail)) {
                valid = false;
                msg += '<div class="alert alert-warning">Valid system email is required.</div>';
            }
            
            if (inspectionThreshold < 0 || inspectionThreshold > 100) {
                valid = false;
                msg += '<div class="alert alert-warning">Inspection threshold must be between 0 and 100.</div>';
            }
            
            if (qualityThreshold < 0 || qualityThreshold > 100) {
                valid = false;
                msg += '<div class="alert alert-warning">Quality alert threshold must be between 0 and 100.</div>';
            }
            
            if (!valid) {
                $('#formAlert').html(msg);
                e.preventDefault();
            } else {
                $('#formAlert').html('');
            }
        });
    });
    </script>
</body>
</html> 