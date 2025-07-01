<?php
require_once 'config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash_message('error', 'Invalid employee ID provided.');
    header('Location: employees.php');
    exit();
}

$employee_id = (int)$_GET['id'];

try {
    // Check if employee exists
    $employee = $db->getOne('SELECT * FROM users WHERE user_id = ?', [$employee_id]);
    
    if (!$employee) {
        set_flash_message('error', 'Employee not found.');
        header('Location: employees.php');
        exit();
    }
    
    // Prevent deletion of admin users
    if ($employee['role'] === 'Admin') {
        set_flash_message('error', 'Cannot delete admin users.');
        header('Location: employees.php');
        exit();
    }
    
    // Check if employee is used in any related tables
    $usedInProduction = $db->getOne('SELECT COUNT(*) FROM production_orders WHERE created_by = ?', [$employee_id]);
    $usedInQC = $db->getOne('SELECT COUNT(*) FROM quality_control WHERE inspector_id = ?', [$employee_id]);
    
    if ($usedInProduction > 0 || $usedInQC > 0) {
        set_flash_message('error', 'Cannot delete employee. They are associated with production orders or quality inspections.');
        header('Location: employees.php');
        exit();
    }
    
    // Delete the employee
    $result = $db->execute('DELETE FROM users WHERE user_id = ?', [$employee_id]);
    
    if ($result) {
        // Log the action
        log_activity('Employee deleted', 'Employee ID: ' . $employee_id . ', Name: ' . $employee['first_name'] . ' ' . $employee['last_name']);
        set_flash_message('success', 'Employee "' . htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) . '" has been deleted successfully.');
    } else {
        set_flash_message('error', 'Failed to delete employee. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Delete employee error: " . $e->getMessage());
    set_flash_message('error', 'An error occurred while deleting the employee.');
}

header('Location: employees.php');
exit();
?> 