<?php
require_once 'config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash_message('error', 'Invalid product ID provided.');
    header('Location: products.php');
    exit();
}

$product_id = (int)$_GET['id'];

try {
    // Check if product exists
    $product = $db->getOne('SELECT * FROM products WHERE product_id = ?', [$product_id]);
    
    if (!$product) {
        set_flash_message('error', 'Product not found.');
        header('Location: products.php');
        exit();
    }
    
    // Check if product is used in production orders
    $usedInProduction = $db->getOne('SELECT COUNT(*) FROM production_orders WHERE product_id = ?', [$product_id]);
    
    if ($usedInProduction > 0) {
        set_flash_message('error', 'Cannot delete product. It is used in production orders.');
        header('Location: products.php');
        exit();
    }
    
    // Delete the product
    $result = $db->execute('DELETE FROM products WHERE product_id = ?', [$product_id]);
    
    if ($result) {
        // Log the action
        log_activity('Product deleted', 'Product ID: ' . $product_id . ', Name: ' . $product['name']);
        set_flash_message('success', 'Product "' . htmlspecialchars($product['name']) . '" has been deleted successfully.');
    } else {
        set_flash_message('error', 'Failed to delete product. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Delete product error: " . $e->getMessage());
    set_flash_message('error', 'An error occurred while deleting the product.');
}

header('Location: products.php');
exit();
?> 