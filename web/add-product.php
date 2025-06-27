<?php
require_once 'config/config.php';

// Initialize alert variable (not used after flash refactor)
$alert = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = sanitize_input($_POST['productName'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $unit_price = floatval($_POST['unitPrice'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $min_stock = intval($_POST['minStock'] ?? 0);
    $description = sanitize_input($_POST['description'] ?? '');

    // Validate required fields
    if ($name && $category && $unit_price > 0 && $stock >= 0 && $min_stock >= 0) {
        // Prepare data for insertion
        $data = [
            'product_code' => generate_random_string(8),
            'name' => $name,
            'category' => $category,
            'unit_price' => $unit_price,
            'current_stock' => $stock,
            'min_stock_level' => $min_stock,
            'description' => $description
        ];
        // Insert product into database
        $result = $db->insert('products', $data);
        if ($result) {
            // Set success flash message and redirect
            set_flash_message('success', 'Product added successfully!');
            header('Location: products.php');
            exit;
        } else {
            // Set error flash message and redirect
            set_flash_message('danger', 'Failed to add product. Please try again.');
            header('Location: products.php');
            exit;
        }
    } else {
        // Set warning flash message (stay on page)
        $alert = '<div class="alert alert-warning">Please fill all fields correctly.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Manufacturing Database System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-label { color: #ff512f; font-weight: 600; }
        .form-control, .form-select {
            background: #fffbe7;
            color: #222;
            border: 2px solid #43c6ac;
            border-radius: 8px;
        }
        .form-control:focus, .form-select:focus {
            background: #f8ffae;
            color: #191654;
            border-color: #ff512f;
            box-shadow: 0 0 0 2px #43c6ac;
        }
        .btn-primary {
            background: linear-gradient(90deg, #ff512f 0%, #dd2476 100%);
            border: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(90deg, #43c6ac 0%, #191654 100%);
        }
        .card {
            max-width: 600px;
            margin: 40px auto;
            border-radius: 16px;
            background: linear-gradient(135deg, #f8ffae 0%, #43c6ac 100%);
            box-shadow: 0 4px 24px rgba(67,198,172,0.12);
        }
        .card-header {
            background: linear-gradient(90deg, #ff512f 0%, #dd2476 100%);
            color: #fff;
            font-weight: 700;
            border-radius: 16px 16px 0 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow">
            <div class="card-header" style="background:#1976d2;color:#fff;font-weight:600;">
                <i class="fas fa-plus"></i> Add New Product
            </div>
            <div class="card-body">
                <?php 
                // Show warning alert if validation failed (not a redirect case)
                if ($alert) echo $alert; 
                ?>
                <form id="addProductForm" method="POST" novalidate>
                    <!-- Product Name -->
                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="productName" name="productName" required>
                    </div>
                    <!-- Category -->
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" required>
                    </div>
                    <!-- Unit Price -->
                    <div class="mb-3">
                        <label for="unitPrice" class="form-label">Unit Price</label>
                        <input type="number" class="form-control" id="unitPrice" name="unitPrice" min="0.01" step="0.01" required>
                    </div>
                    <!-- Stock -->
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                    </div>
                    <!-- Min Stock -->
                    <div class="mb-3">
                        <label for="minStock" class="form-label">Min Stock</label>
                        <input type="number" class="form-control" id="minStock" name="minStock" min="0" required>
                    </div>
                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div id="formAlert"></div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Submit</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Client-side validation for add product form
    $(document).ready(function() {
        $('#addProductForm').on('submit', function(e) {
            var valid = true;
            var msg = '';
            var name = $('#productName').val().trim();
            var category = $('#category').val().trim();
            var unitPrice = parseFloat($('#unitPrice').val());
            var stock = parseInt($('#stock').val());
            var minStock = parseInt($('#minStock').val());
            if (!name || !category || isNaN(unitPrice) || unitPrice <= 0 || isNaN(stock) || stock < 0 || isNaN(minStock) || minStock < 0) {
                valid = false;
                msg = '<div class="alert alert-warning">Please fill all fields correctly.</div>';
            }
            if (!valid) {
                $('#formAlert').html(msg);
                e.preventDefault();
            } else {
                $('#formAlert').html('');
            }
        });
    });
    </script>
</body>
</html> 