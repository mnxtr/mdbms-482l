<?php
require_once 'config/config.php';

// Get inspection ID from URL
$inspection_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($inspection_id <= 0) {
    set_flash_message('danger', 'Invalid inspection ID.');
    header('Location: quality.php');
    exit;
}

// Check if inspection exists
$inspection = $db->getOne('SELECT * FROM quality_control WHERE qc_id = ?', [$inspection_id]);

if (!$inspection) {
    set_flash_message('danger', 'Inspection not found.');
    header('Location: quality.php');
    exit;
}

// Delete the inspection
$result = $db->delete('quality_control', 'qc_id = ?', [$inspection_id]);

if ($result) {
    set_flash_message('success', 'Quality inspection deleted successfully!');
} else {
    set_flash_message('danger', 'Failed to delete inspection. Please try again.');
}

header('Location: quality.php');
exit;
?> 