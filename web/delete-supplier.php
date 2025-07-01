<?php
require_once 'config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash_message('error', 'Invalid supplier ID provided.');
    header('Location: suppliers.php');
    exit();
}

$supplier_id = (int)$_GET['id'];

try {
    // Check if supplier exists
    $supplier = $db->getOne('SELECT * FROM suppliers WHERE supplier_id = ?', [$supplier_id]);
    
    if (!$supplier) {
        set_flash_message('error', 'Supplier not found.');
        header('Location: suppliers.php');
        exit();
    }
    
    // Check if supplier is used in materials
    $usedInMaterials = $db->getOne('SELECT COUNT(*) FROM materials WHERE supplier_id = ?', [$supplier_id]);
    
    if ($usedInMaterials > 0) {
        set_flash_message('error', 'Cannot delete supplier. They are associated with materials.');
        header('Location: suppliers.php');
        exit();
    }
    
    // Delete the supplier
    $result = $db->execute('DELETE FROM suppliers WHERE supplier_id = ?', [$supplier_id]);
    
    if ($result) {
        // Log the action
        log_activity('Supplier deleted', 'Supplier ID: ' . $supplier_id . ', Name: ' . $supplier['name']);
        set_flash_message('success', 'Supplier "' . htmlspecialchars($supplier['name']) . '" has been deleted successfully.');
    } else {
        set_flash_message('error', 'Failed to delete supplier. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Delete supplier error: " . $e->getMessage());
    set_flash_message('error', 'An error occurred while deleting the supplier.');
}

header('Location: suppliers.php');
exit();
?> 