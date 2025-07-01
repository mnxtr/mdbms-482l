<?php
define('SECURE_ACCESS', true);
require_once 'config/config.php';

// Log the logout activity if user was logged in
if (is_logged_in()) {
    $username = $_SESSION['username'] ?? 'Unknown';
    log_activity('user_logout', "User {$username} logged out");
}

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Set logout message
set_flash_message('info', 'You have been successfully logged out.');

// Redirect to login page
header('Location: login.php');
exit();
