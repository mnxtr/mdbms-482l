<?php
require_once 'config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash_message('error', 'Invalid material ID provided.');
    header('Location: materials.php');
    exit();
}

$material_id = (int)$_GET['id'];

try {
    // Check if material exists
    $material = $db->getOne('SELECT * FROM materials WHERE material_id = ?', [$material_id]);
    
    if (!$material) {
        set_flash_message('error', 'Material not found.');
        header('Location: materials.php');
        exit();
    }
    
    // Check if material is used in production orders
    $usedInProduction = $db->getOne('SELECT COUNT(*) FROM production_orders WHERE material_id = ?', [$material_id]);
    
    if ($usedInProduction > 0) {
        set_flash_message('error', 'Cannot delete material. It is used in production orders.');
        header('Location: materials.php');
        exit();
    }
    
    // Delete the material
    $result = $db->execute('DELETE FROM materials WHERE material_id = ?', [$material_id]);
    
    if ($result) {
        // Log the action
        log_activity('Material deleted', 'Material ID: ' . $material_id . ', Name: ' . $material['name']);
        set_flash_message('success', 'Material "' . htmlspecialchars($material['name']) . '" has been deleted successfully.');
    } else {
        set_flash_message('error', 'Failed to delete material. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Delete material error: " . $e->getMessage());
    set_flash_message('error', 'An error occurred while deleting the material.');
}

header('Location: materials.php');
exit();
?> 