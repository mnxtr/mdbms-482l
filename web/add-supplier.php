<?php
require_once 'config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $contact_person = sanitize_input($_POST['contact_person'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');

    // Basic validation
    if ($name && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $data = [
            'name' => $name,
            'contact_person' => $contact_person,
            'email' => $email,
            'phone' => $phone,
            'address' => $address
        ];
        $result = $db->insert('suppliers', $data);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Supplier added successfully!']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add supplier. Please try again.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields correctly.']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier - Manufacturing Database System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-label { color: #ff512f; font-weight: 600; }
        .form-control {
            background: #fffbe7;
            color: #222;
            border: 2px solid #43c6ac;
            border-radius: 8px;
        }
        .form-control:focus {
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
                <i class="fas fa-plus"></i> Add New Supplier
            </div>
            <div class="card-body">
                <form id="addSupplierForm" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
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
    $(document).ready(function() {
        $('#addSupplierForm').on('submit', function(e) {
            e.preventDefault();
            var valid = true;
            var msg = '';
            var name = $('#name').val().trim();
            var email = $('#email').val().trim();
            if (!name) {
                valid = false;
                msg += '<div class="alert alert-warning">Supplier name is required.</div>';
            }
            if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
                valid = false;
                msg += '<div class="alert alert-warning">A valid email is required.</div>';
            }
            if (!valid) {
                $('#formAlert').html(msg);
                return;
            }
            // AJAX submit
            $.ajax({
                url: '',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#formAlert').html('<div class="alert alert-success">' + response.message + ' Redirecting...</div>');
                        setTimeout(function() { window.location.href = 'suppliers.php'; }, 1500);
                    } else {
                        $('#formAlert').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#formAlert').html('<div class="alert alert-danger">Server error. Please try again.</div>');
                }
            });
        });
    });
    </script>
</body>
</html> 