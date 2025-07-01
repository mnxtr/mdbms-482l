<?php
define('SECURE_ACCESS', true);
require_once '../config/config.php';

// Set JSON content type
header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get product ID
$productId = $_GET['id'] ?? null;

if (!$productId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit();
}

try {
    // Get product details
    $product = $db->getOne('SELECT * FROM products WHERE product_id = ?', [$productId]);
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }
    
    // Calculate production cost
    $productionCost = calculate_production_cost($productId);
    
    // Get stock status
    $stockStatus = get_stock_level_status($product['current_stock'], $product['min_stock_level']);
    
    // Return HTML for modal
    $html = "
    <div class='row'>
        <div class='col-md-6'>
            <h5>Product Information</h5>
            <table class='table table-borderless'>
                <tr>
                    <td><strong>Product Code:</strong></td>
                    <td>{$product['product_code']}</td>
                </tr>
                <tr>
                    <td><strong>Name:</strong></td>
                    <td>{$product['name']}</td>
                </tr>
                <tr>
                    <td><strong>Category:</strong></td>
                    <td><span class='badge bg-info'>{$product['category']}</span></td>
                </tr>
                <tr>
                    <td><strong>Description:</strong></td>
                    <td>" . htmlspecialchars($product['description'] ?? 'No description available') . "</td>
                </tr>
            </table>
        </div>
        <div class='col-md-6'>
            <h5>Stock & Pricing</h5>
            <table class='table table-borderless'>
                <tr>
                    <td><strong>Current Stock:</strong></td>
                    <td>{$product['current_stock']}</td>
                </tr>
                <tr>
                    <td><strong>Min Stock Level:</strong></td>
                    <td>{$product['min_stock_level']}</td>
                </tr>
                <tr>
                    <td><strong>Unit Price:</strong></td>
                    <td>$" . number_format($product['unit_price'], 2) . "</td>
                </tr>
                <tr>
                    <td><strong>Production Cost:</strong></td>
                    <td>$" . number_format($productionCost, 2) . "</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td><span class='badge bg-" . ($stockStatus === 'out_of_stock' ? 'danger' : ($stockStatus === 'low_stock' ? 'warning' : 'success')) . "'>" . ucwords(str_replace('_', ' ', $stockStatus)) . "</span></td>
                </tr>
            </table>
        </div>
    </div>
    <div class='row mt-3'>
        <div class='col-12'>
            <div class='d-flex justify-content-end gap-2'>
                <a href='edit-product.php?id={$productId}' class='btn btn-primary'>
                    <i class='fas fa-edit'></i> Edit Product
                </a>
                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>
                    Close
                </button>
            </div>
        </div>
    </div>";
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    Logger::error("Failed to get product details: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?> 