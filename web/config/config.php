<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration (must be before session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_start();

// Application settings
define('APP_NAME', 'Manufacturing Database System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/web/');
define('UPLOAD_PATH', 'uploads/');

// Security settings
define('HASH_COST', 12); // For password hashing

// Utility functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function format_date($date) {
    return date('Y-m-d H:i:s', strtotime($date));
}

function format_currency($amount) {
    return number_format($amount, 2, '.', ',');
}

function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

function check_role($allowed_roles) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: ' . BASE_URL . 'unauthorized.php');
        exit();
    }
}

function log_activity($user_id, $action, $details = '') {
    global $db;
    $data = [
        'user_id' => $user_id,
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ];
    return $db->insert('activity_log', $data);
}

function send_notification($user_id, $title, $message, $type = 'info') {
    global $db;
    $data = [
        'user_id' => $user_id,
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'is_read' => 0
    ];
    return $db->insert('notifications', $data);
}

function get_stock_level_status($current_stock, $min_stock_level) {
    if ($current_stock <= 0) {
        return 'out_of_stock';
    } elseif ($current_stock <= $min_stock_level) {
        return 'low_stock';
    } else {
        return 'in_stock';
    }
}

function calculate_production_cost($product_id) {
    global $db;
    $query = "SELECT SUM(bom.quantity * rm.unit_price) as total_cost 
              FROM bill_of_materials bom 
              JOIN raw_materials rm ON bom.material_id = rm.material_id 
              WHERE bom.product_id = ?";
    $result = $db->getOne($query, [$product_id]);
    return $result ? $result['total_cost'] : 0;
}

function generate_order_number() {
    $prefix = 'PO';
    $date = date('Ymd');
    $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    return $prefix . $date . $random;
}

function validate_date_range($start_date, $end_date) {
    $start = strtotime($start_date);
    $end = strtotime($end_date);
    return ($start && $end && $start <= $end);
}

function get_machine_status($machine_id) {
    global $db;
    $query = "SELECT status FROM machines WHERE machine_id = ?";
    $result = $db->getOne($query, [$machine_id]);
    return $result ? $result['status'] : null;
}

function check_machine_availability($machine_id, $start_time, $end_time) {
    global $db;
    $query = "SELECT COUNT(*) as count FROM production_schedule 
              WHERE machine_id = ? 
              AND ((start_time BETWEEN ? AND ?) 
              OR (end_time BETWEEN ? AND ?))";
    $params = [$machine_id, $start_time, $end_time, $start_time, $end_time];
    $result = $db->getOne($query, $params);
    return $result && $result['count'] == 0;
}

// Flash message utilities
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

// Initialize database connection
require_once 'database.php';
$db = new Database();
$conn = $db->getConnection();

// Ensure default user exists (for development/demo)
try {
    $exists = $db->getOne("SELECT * FROM users WHERE username = ?", ['user']);
    if (!$exists) {
        $db->insert('users', [
            'username' => 'user',
            'password' => password_hash('root', PASSWORD_DEFAULT), // Store hashed password
            'email' => 'user@example.com',
            'full_name' => 'Default User',
            'role' => 'admin'
        ]);
    }
} catch (Exception $e) {
    // Optionally log or handle error
}
?> 