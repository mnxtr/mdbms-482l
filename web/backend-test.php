<?php
require_once 'config/config.php';

echo "<h2>Backend Logic and Setup Test</h2>";
echo "<style>
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; }
</style>";

// Test 1: Database Connection
echo "<div class='test-section'>";
echo "<h3>1. Database Connection Test</h3>";
try {
    if ($conn) {
        echo "<p class='success'>✓ Database connection successful</p>";
        echo "<p class='info'>Connected to: " . $db_name . "</p>";
    } else {
        echo "<p class='error'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 2: Check Required Tables
echo "<div class='test-section'>";
echo "<h3>2. Database Tables Check</h3>";
$required_tables = ['users', 'products', 'suppliers', 'quality_control', 'production_orders'];
$missing_tables = [];

foreach ($required_tables as $table) {
    try {
        $result = $db->getOne("SHOW TABLES LIKE '$table'");
        if ($result) {
            echo "<p class='success'>✓ Table '$table' exists</p>";
        } else {
            echo "<p class='error'>✗ Table '$table' missing</p>";
            $missing_tables[] = $table;
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error checking table '$table': " . $e->getMessage() . "</p>";
        $missing_tables[] = $table;
    }
}

if (!empty($missing_tables)) {
    echo "<p class='warning'>⚠ Missing tables: " . implode(', ', $missing_tables) . "</p>";
    echo "<p><a href='setup-database.php'>Run Database Setup</a></p>";
}
echo "</div>";

// Test 3: Test CRUD Operations
echo "<div class='test-section'>";
echo "<h3>3. CRUD Operations Test</h3>";

// Test Insert
try {
    $test_data = [
        'product_code' => 'TEST' . time(),
        'name' => 'Test Product',
        'category' => 'Test Category',
        'unit_price' => 99.99,
        'current_stock' => 10,
        'min_stock_level' => 5,
        'description' => 'Test product for backend testing'
    ];
    
    $insert_result = $db->insert('products', $test_data);
    if ($insert_result) {
        echo "<p class='success'>✓ Insert operation successful (ID: $insert_result)</p>";
        
        // Test Select
        $select_result = $db->getOne("SELECT * FROM products WHERE product_id = ?", [$insert_result]);
        if ($select_result) {
            echo "<p class='success'>✓ Select operation successful</p>";
            echo "<pre>Retrieved: " . json_encode($select_result, JSON_PRETTY_PRINT) . "</pre>";
            
            // Test Update
            $update_data = ['name' => 'Updated Test Product'];
            $update_result = $db->update('products', $update_data, 'product_id = ?', [$insert_result]);
            if ($update_result !== false) {
                echo "<p class='success'>✓ Update operation successful</p>";
                
                // Test Delete
                $delete_result = $db->delete('products', 'product_id = ?', [$insert_result]);
                if ($delete_result) {
                    echo "<p class='success'>✓ Delete operation successful</p>";
                } else {
                    echo "<p class='error'>✗ Delete operation failed</p>";
                }
            } else {
                echo "<p class='error'>✗ Update operation failed</p>";
            }
        } else {
            echo "<p class='error'>✗ Select operation failed</p>";
        }
    } else {
        echo "<p class='error'>✗ Insert operation failed</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ CRUD test error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 4: Utility Functions
echo "<div class='test-section'>";
echo "<h3>4. Utility Functions Test</h3>";

// Test sanitize_input
$test_input = "  <script>alert('test')</script>  ";
$sanitized = sanitize_input($test_input);
echo "<p class='info'>Sanitize Input Test:</p>";
echo "<pre>Original: '$test_input'</pre>";
echo "<pre>Sanitized: '$sanitized'</pre>";

// Test generate_random_string
$random_string = generate_random_string(8);
echo "<p class='info'>Random String: $random_string</p>";

// Test format_currency
$currency_test = format_currency(1234.56);
echo "<p class='info'>Currency Format: $currency_test</p>";

// Test flash messages
set_flash_message('success', 'Test flash message');
$flash = get_flash_message();
if ($flash) {
    echo "<p class='success'>✓ Flash message system working</p>";
    echo "<pre>Flash: " . json_encode($flash) . "</pre>";
}
echo "</div>";

// Test 5: Session and Configuration
echo "<div class='test-section'>";
echo "<h3>5. Session and Configuration Test</h3>";
echo "<p class='info'>Session ID: " . session_id() . "</p>";
echo "<p class='info'>App Name: " . APP_NAME . "</p>";
echo "<p class='info'>Base URL: " . BASE_URL . "</p>";
echo "<p class='info'>Error Reporting: " . (error_reporting() ? 'Enabled' : 'Disabled') . "</p>";
echo "</div>";

// Test 6: Error Logging
echo "<div class='test-section'>";
echo "<h3>6. Error Logging Test</h3>";
$log_test_message = "Backend test log message - " . date('Y-m-d H:i:s');
error_log($log_test_message);
echo "<p class='success'>✓ Error log test message sent</p>";
echo "<p class='info'>Check XAMPP error log for: '$log_test_message'</p>";
echo "</div>";

// Test 7: Database Schema Validation
echo "<div class='test-section'>";
echo "<h3>7. Database Schema Validation</h3>";
try {
    $products_schema = $db->getAll("DESCRIBE products");
    echo "<p class='success'>✓ Products table schema:</p>";
    echo "<pre>";
    foreach ($products_schema as $column) {
        echo "{$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']}\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Schema validation error: " . $e->getMessage() . "</p>";
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h3>Test Summary</h3>";
echo "<p><a href='index.php'>← Back to Dashboard</a></p>";
echo "<p><a href='add-product.php'>← Test Add Product</a></p>";
echo "<p><a href='setup-database.php'>← Run Database Setup</a></p>";
echo "</div>";
?> 