<?php
require_once 'config/config.php';

// Fetch all production orders for dropdown
$orders = $db->getAll('SELECT order_id, order_number, status, created_at FROM production_orders ORDER BY created_at DESC');
// Fetch all users as potential inspectors
$inspectors = $db->getAll('SELECT user_id, full_name, role FROM users ORDER BY full_name');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $inspector_id = intval($_POST['inspector_id'] ?? 0);
    $inspection_date = sanitize_input($_POST['inspection_date'] ?? '');
    $passed_quantity = intval($_POST['passed_quantity'] ?? 0);
    $failed_quantity = intval($_POST['failed_quantity'] ?? 0);
    $notes = sanitize_input($_POST['notes'] ?? '');

    // Validate required fields
    if ($order_id > 0 && $inspector_id > 0 && $inspection_date && $passed_quantity >= 0 && $failed_quantity >= 0) {
        $data = [
            'order_id' => $order_id,
            'inspector_id' => $inspector_id,
            'inspection_date' => $inspection_date,
            'passed_quantity' => $passed_quantity,
            'failed_quantity' => $failed_quantity,
            'notes' => $notes
        ];
        $result = $db->insert('quality_control', $data);
        if ($result) {
            set_flash_message('success', 'Quality inspection added successfully!');
            header('Location: quality.php');
            exit;
        } else {
            set_flash_message('danger', 'Failed to add inspection. Please try again.');
        }
    } else {
        set_flash_message('warning', 'Please fill all required fields correctly.');
    }
}
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Quality Inspection - Manufacturing Database System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-label { color: #1976d2; font-weight: 600; }
        .form-control, .form-select {
            background: #f5f5f6;
            color: #222;
            border: 2px solid #1976d2;
            border-radius: 8px;
        }
        .form-control:focus, .form-select:focus {
            background: #fff;
            color: #1976d2;
            border-color: #1565c0;
            box-shadow: 0 0 0 2px #1976d2;
        }
        .btn-primary {
            background: #1976d2;
            border: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-primary:hover, .btn-primary:focus {
            background: #1565c0;
        }
        .card {
            max-width: 600px;
            margin: 40px auto;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
        }
        .card-header {
            background: #1976d2;
            color: #fff;
            font-weight: 700;
            border-radius: 12px 12px 0 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow">
            <div class="card-header">
                <i class="fas fa-clipboard-check"></i> Add Quality Inspection
            </div>
            <div class="card-body">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endif; ?>
                <form id="addInspectionForm" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="order_id" class="form-label">Production Order</label>
                        <select class="form-select" id="order_id" name="order_id" required>
                            <option value="">Select Production Order</option>
                            <?php if ($orders): ?>
                                <?php foreach ($orders as $order): ?>
                                    <option value="<?= $order['order_id'] ?>">
                                        <?= htmlspecialchars($order['order_number']) ?> 
                                        (<?= htmlspecialchars(ucfirst($order['status'])) ?> - <?= date('M d, Y', strtotime($order['created_at'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="inspector_id" class="form-label">Inspector</label>
                        <select class="form-select" id="inspector_id" name="inspector_id" required>
                            <option value="">Select Inspector</option>
                            <?php if ($inspectors): ?>
                                <?php foreach ($inspectors as $inspector): ?>
                                    <option value="<?= $inspector['user_id'] ?>"><?= htmlspecialchars($inspector['full_name']) ?> (<?= htmlspecialchars(ucfirst($inspector['role'])) ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="inspection_date" class="form-label">Inspection Date</label>
                        <input type="date" class="form-control" id="inspection_date" name="inspection_date" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="passed_quantity" class="form-label">Passed Quantity</label>
                                <input type="number" class="form-control" id="passed_quantity" name="passed_quantity" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="failed_quantity" class="form-label">Failed Quantity</label>
                                <input type="number" class="form-control" id="failed_quantity" name="failed_quantity" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about the inspection..."></textarea>
                    </div>
                    <div id="formAlert"></div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Submit Inspection</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Set default date to today
        $('#inspection_date').val(new Date().toISOString().split('T')[0]);
        
        $('#addInspectionForm').on('submit', function(e) {
            var valid = true;
            var msg = '';
            var orderId = $('#order_id').val();
            var inspectorId = $('#inspector_id').val();
            var inspectionDate = $('#inspection_date').val();
            var passedQty = parseInt($('#passed_quantity').val());
            var failedQty = parseInt($('#failed_quantity').val());
            
            if (!orderId) {
                valid = false;
                msg += '<div class="alert alert-warning">Please select a production order.</div>';
            }
            if (!inspectorId) {
                valid = false;
                msg += '<div class="alert alert-warning">Please select an inspector.</div>';
            }
            if (!inspectionDate) {
                valid = false;
                msg += '<div class="alert alert-warning">Inspection date is required.</div>';
            }
            if (isNaN(passedQty) || passedQty < 0) {
                valid = false;
                msg += '<div class="alert alert-warning">Passed quantity must be a valid number.</div>';
            }
            if (isNaN(failedQty) || failedQty < 0) {
                valid = false;
                msg += '<div class="alert alert-warning">Failed quantity must be a valid number.</div>';
            }
            if (passedQty === 0 && failedQty === 0) {
                valid = false;
                msg += '<div class="alert alert-warning">At least one quantity (passed or failed) must be greater than 0.</div>';
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