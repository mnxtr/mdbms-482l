<?php
require_once 'config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash_message('error', 'Invalid production order ID provided.');
    header('Location: production.php');
    exit();
}

$order_id = (int)$_GET['id'];

try {
    // Check if order exists
    $order = $db->getOne('SELECT * FROM production_orders WHERE order_id = ?', [$order_id]);
    
    if (!$order) {
        set_flash_message('error', 'Production order not found.');
        header('Location: production.php');
        exit();
    }
    
    // Check if order is completed (prevent deletion of completed orders)
    if ($order['status'] === 'Completed') {
        set_flash_message('error', 'Cannot delete completed production orders.');
        header('Location: production.php');
        exit();
    }
    
    // Check if order is used in quality control
    $usedInQC = $db->getOne('SELECT COUNT(*) FROM quality_control WHERE production_order_id = ?', [$order_id]);
    
    if ($usedInQC > 0) {
        set_flash_message('error', 'Cannot delete production order. It is associated with quality inspections.');
        header('Location: production.php');
        exit();
    }
    
    // Delete the production order
    $result = $db->execute('DELETE FROM production_orders WHERE order_id = ?', [$order_id]);
    
    if ($result) {
        // Log the action
        log_activity('Production order deleted', 'Order ID: ' . $order_id);
        set_flash_message('success', 'Production order #' . $order_id . ' has been deleted successfully.');
    } else {
        set_flash_message('error', 'Failed to delete production order. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Delete production order error: " . $e->getMessage());
    set_flash_message('error', 'An error occurred while deleting the production order.');
}

header('Location: production.php');
exit();
?> 