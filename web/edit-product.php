<?php
require_once 'config/config.php';

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    header('Location: products.php');
    exit;
}

// Fetch product data
$product = $db->getOne('SELECT * FROM products WHERE product_id = ?', [$product_id]);
if (!$product) {
    header('Location: products.php');
    exit;
}

$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = sanitize_input($_POST['productName'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $unit_price = floatval($_POST['unitPrice'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $min_stock = intval($_POST['minStock'] ?? 0);
    $description = sanitize_input($_POST['description'] ?? '');

    if ($name && $category && $unit_price > 0 && $stock >= 0 && $min_stock >= 0) {
        $data = [
            'name' => $name,
            'category' => $category,
            'unit_price' => $unit_price,
            'current_stock' => $stock,
            'min_stock_level' => $min_stock,
            'description' => $description
        ];
        $result = $db->update('products', $data, 'product_id = ?', [$product_id]);
        if ($result !== false) {
            $alert = '<div class="alert alert-success" style="background:#388e3c;color:#fff;">Product updated successfully! Redirecting...</div>';
            echo '<script>setTimeout(function(){ window.location.href = "products.php"; }, 1500);</script>';
        } else {
            $alert = '<div class="alert alert-danger" style="background:#d32f2f;color:#fff;">Failed to update product. Please try again.</div>';
        }
    } else {
        $alert = '<div class="alert alert-warning" style="background:#ffa000;color:#fff;">Please fill all fields correctly.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Manufacturing Database System</title>
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
            <div class="card-header">
                <i class="fas fa-edit"></i> Edit Product
            </div>
            <div class="card-body">
                <?php if ($alert) echo $alert; ?>
                <form id="editProductForm" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="productName" name="productName" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="unitPrice" class="form-label">Unit Price</label>
                        <input type="number" class="form-control" id="unitPrice" name="unitPrice" min="0.01" step="0.01" value="<?= htmlspecialchars($product['unit_price']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?= htmlspecialchars($product['current_stock']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="minStock" class="form-label">Min Stock</label>
                        <input type="number" class="form-control" id="minStock" name="minStock" min="0" value="<?= htmlspecialchars($product['min_stock_level']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>
                    <div id="formAlert"></div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Update</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#editProductForm').on('submit', function(e) {
            var valid = true;
            var msg = '';
            var name = $('#productName').val().trim();
            var category = $('#category').val().trim();
            var unitPrice = parseFloat($('#unitPrice').val());
            var stock = parseInt($('#stock').val());
            var minStock = parseInt($('#minStock').val());
            if (!name || !category || isNaN(unitPrice) || unitPrice <= 0 || isNaN(stock) || stock < 0 || isNaN(minStock) || minStock < 0) {
                valid = false;
                msg = '<div class="alert alert-warning" style="background:#ffa000;color:#fff;">Please fill all fields correctly.</div>';
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