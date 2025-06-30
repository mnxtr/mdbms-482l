<?php
require_once 'config/config.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test database connection
    $test = $db->getOne("SELECT 1 as test");
    if ($test) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Check if required tables exist
$tables = ['users', 'products', 'raw_materials', 'suppliers', 'quality_control', 'production_orders'];

echo "<h3>Table Status:</h3>";
foreach ($tables as $table) {
    try {
        $result = $db->getOne("SHOW TABLES LIKE '$table'");
        if ($result) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
            
            // Show table structure
            $columns = $db->getAll("DESCRIBE $table");
            echo "<details><summary>Structure of $table:</summary><ul>";
            foreach ($columns as $column) {
                echo "<li>{$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']}</li>";
            }
            echo "</ul></details>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error checking table '$table': " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Sample Data Check:</h3>";
try {
    $userCount = $db->getOne("SELECT COUNT(*) as count FROM users");
    echo "<p>Users: " . $userCount['count'] . "</p>";
    
    $productCount = $db->getOne("SELECT COUNT(*) as count FROM products");
    echo "<p>Products: " . $productCount['count'] . "</p>";
    
    $supplierCount = $db->getOne("SELECT COUNT(*) as count FROM suppliers");
    echo "<p>Suppliers: " . $supplierCount['count'] . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking data: " . $e->getMessage() . "</p>";
}

echo "<h3>Database Schema Import Instructions:</h3>";
echo "<p>If tables are missing, please import the schema:</p>";
echo "<ol>";
echo "<li>Go to phpMyAdmin</li>";
echo "<li>Select your 'manufacturing_db' database</li>";
echo "<li>Go to Import tab</li>";
echo "<li>Upload the file: <code>web/database/schema.sql</code></li>";
echo "<li>Click Go to import</li>";
echo "</ol>";

echo "<p><a href='index.php'>← Back to Dashboard</a></p>";
?> 