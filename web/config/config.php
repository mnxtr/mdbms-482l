<?php
// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// Environment detection
define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');
define('IS_PRODUCTION', ENVIRONMENT === 'production');

// Error reporting based on environment
if (IS_PRODUCTION) {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Session security configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', IS_PRODUCTION ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600); // 1 hour
ini_set('session.cookie_lifetime', 3600);

// Start session with security
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application settings
define('APP_NAME', 'Manufacturing Database System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/web/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('CACHE_PATH', __DIR__ . '/../cache/');

// Security settings
define('HASH_COST', 12);
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600); // 1 hour

// Cache settings
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 300); // 5 minutes

// Input validation and sanitization
class InputValidator {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function sanitize($data, $type = 'string') {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        
        switch ($type) {
            case 'email':
                return filter_var($data, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($data, FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'string':
            default:
                return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
    }
    
    public function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if (!isset($data[$field]) || empty($data[$field])) {
                if (strpos($rule, 'required') !== false) {
                    $errors[$field] = ucfirst($field) . ' is required';
                }
                continue;
            }
            
            $value = $data[$field];
            
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'Invalid email format';
            }
            
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $min = (int)$matches[1];
                if (strlen($value) < $min) {
                    $errors[$field] = ucfirst($field) . " must be at least {$min} characters";
                }
            }
            
            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                $max = (int)$matches[1];
                if (strlen($value) > $max) {
                    $errors[$field] = ucfirst($field) . " must not exceed {$max} characters";
                }
            }
        }
        
        return $errors;
    }
}

// Security utilities
class Security {
    public static function generateCSRFToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    public static function validateCSRFToken($token) {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
    }
    
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public static function checkSessionTimeout() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            session_unset();
            session_destroy();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
}

// Caching system
class Cache {
    private static $cache = [];
    
    public static function get($key) {
        if (!CACHE_ENABLED) return false;
        
        $file = CACHE_PATH . md5($key) . '.cache';
        if (file_exists($file) && (time() - filemtime($file)) < CACHE_DURATION) {
            return unserialize(file_get_contents($file));
        }
        return false;
    }
    
    public static function set($key, $data, $duration = CACHE_DURATION) {
        if (!CACHE_ENABLED) return false;
        
        if (!is_dir(CACHE_PATH)) {
            mkdir(CACHE_PATH, 0755, true);
        }
        
        $file = CACHE_PATH . md5($key) . '.cache';
        return file_put_contents($file, serialize($data));
    }
    
    public static function delete($key) {
        $file = CACHE_PATH . md5($key) . '.cache';
        return file_exists($file) ? unlink($file) : true;
    }
    
    public static function clear() {
        $files = glob(CACHE_PATH . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}

// Utility functions with caching
function format_date($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

function format_currency($amount, $currency = 'USD') {
    return number_format($amount, 2, '.', ',');
}

function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Global input sanitization helper
function sanitize_input($data, $type = 'string') {
    return InputValidator::getInstance()->sanitize($data, $type);
}

// Authentication functions
function is_logged_in() {
    return Security::checkSessionTimeout() && isset($_SESSION['user_id']);
}

function get_logged_in_user() {
    if (!is_logged_in()) return null;
    
    $cacheKey = 'user_' . $_SESSION['user_id'];
    $user = Cache::get($cacheKey);
    
    if (!$user) {
        global $db;
        $user = $db->getOne('SELECT * FROM users WHERE user_id = ?', [$_SESSION['user_id']]);
        if ($user) {
            Cache::set($cacheKey, $user, 300); // Cache for 5 minutes
        }
    }
    
    return $user;
}

function check_role($allowed_roles) {
    $user = get_logged_in_user();
    return $user && in_array($user['role'], (array)$allowed_roles);
}

// Logging system
class Logger {
    private static $logFile = __DIR__ . '/../logs/app.log';
    
    public static function log($level, $message, $context = []) {
        if (!is_dir(dirname(self::$logFile))) {
            mkdir(dirname(self::$logFile), 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    public static function debug($message, $context = []) {
        if (!IS_PRODUCTION) {
            self::log('DEBUG', $message, $context);
        }
    }
    
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
}

// Activity logging
function log_activity($action, $details = '') {
    if (!is_logged_in()) return false;
    
    global $db;
    try {
        $data = [
            'user_id' => $_SESSION['user_id'],
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $db->insert('activity_log', $data);
        Logger::info("Activity logged: {$action}", ['user_id' => $_SESSION['user_id']]);
        return $result;
    } catch (Exception $e) {
        Logger::error("Failed to log activity: " . $e->getMessage());
        return false;
    }
}

// Flash message system
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
        'timestamp' => time()
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

// Business logic functions
function get_stock_level_status($current_stock, $min_stock_level) {
    if ($current_stock <= 0) return 'out_of_stock';
    if ($current_stock <= $min_stock_level) return 'low_stock';
    return 'in_stock';
}

function calculate_production_cost($product_id) {
    $cacheKey = "production_cost_{$product_id}";
    $cost = Cache::get($cacheKey);
    
    if ($cost === false) {
        global $db;
        $query = "SELECT SUM(bom.quantity * rm.unit_price) as total_cost 
                  FROM bill_of_materials bom 
                  JOIN raw_materials rm ON bom.material_id = rm.material_id 
                  WHERE bom.product_id = ?";
        $result = $db->getOne($query, [$product_id]);
        $cost = $result ? $result['total_cost'] : 0;
        Cache::set($cacheKey, $cost, 600); // Cache for 10 minutes
    }
    
    return $cost;
}

function generate_order_number() {
    $prefix = 'PO';
    $date = date('Ymd');
    $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    return $prefix . $date . $random;
}

// Initialize database connection
require_once 'database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

// Ensure default user exists (for development/demo)
if (!IS_PRODUCTION) {
    try {
        $exists = $db->exists('users', 'username = ?', ['admin']);
        if (!$exists) {
            $db->insert('users', [
                'username' => 'admin',
                'password' => Security::hashPassword('admin123'),
                'email' => 'admin@example.com',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'role' => 'Admin',
                'status' => 'Active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            Logger::info("Default admin user created");
        }
    } catch (Exception $e) {
        Logger::error("Failed to create default user: " . $e->getMessage());
    }
}

// Set CSRF token for forms
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    Security::generateCSRFToken();
}
?> 