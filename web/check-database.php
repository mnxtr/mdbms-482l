<?php
define('SECURE_ACCESS', true);
require_once 'config/config.php';

echo "<h1>Database Structure Check</h1>";

// Check database connection
echo "<h2>1. Database Connection</h2>";
try {
    $testQuery = $db->getOne("SELECT 1 as test");
    if ($testQuery) {
        echo "✅ Database connection successful<br>";
    } else {
        echo "❌ Database connection failed<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "<br>";
    exit;
}

// Check if database exists
echo "<h2>2. Database Check</h2>";
try {
    $currentDb = $db->getOne("SELECT DATABASE() as db_name");
    echo "Current database: <strong>{$currentDb['db_name']}</strong><br>";
    
    if ($currentDb['db_name'] === 'manufacturing_db') {
        echo "✅ Correct database selected<br>";
    } else {
        echo "⚠️ Wrong database selected. Expected: manufacturing_db, Got: {$currentDb['db_name']}<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking database: " . $e->getMessage() . "<br>";
}

// Check users table structure
echo "<h2>3. Users Table Structure</h2>";
try {
    $tableExists = $db->getOne("SHOW TABLES LIKE 'users'");
    if ($tableExists) {
        echo "✅ Users table exists<br>";
        
        // Get table structure
        $columns = $db->getAll("DESCRIBE users");
        echo "Table structure:<br>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check if required columns exist
        $requiredColumns = ['user_id', 'username', 'password', 'email', 'full_name', 'role'];
        $existingColumns = array_column($columns, 'Field');
        
        foreach ($requiredColumns as $required) {
            if (in_array($required, $existingColumns)) {
                echo "✅ Column '{$required}' exists<br>";
            } else {
                echo "❌ Column '{$required}' missing<br>";
            }
        }
        
    } else {
        echo "❌ Users table does not exist<br>";
        echo "Creating users table...<br>";
        
        $createTable = "CREATE TABLE users (
            user_id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('admin', 'manager', 'supervisor', 'operator') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $db->executeQuery($createTable);
        echo "✅ Users table created successfully<br>";
    }
} catch (Exception $e) {
    echo "❌ Error with users table: " . $e->getMessage() . "<br>";
}

// Check for existing users
echo "<h2>4. Existing Users</h2>";
try {
    $users = $db->getAll("SELECT user_id, username, email, role, LENGTH(password) as pass_length FROM users");
    if ($users) {
        echo "Found " . count($users) . " users:<br>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Password Length</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['user_id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>{$user['pass_length']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No users found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error getting users: " . $e->getMessage() . "<br>";
}

// Test password hashing
echo "<h2>5. Password Hashing Test</h2>";
try {
    $testPassword = 'admin123';
    $hashedPassword = Security::hashPassword($testPassword);
    $verifyResult = Security::verifyPassword($testPassword, $hashedPassword);
    
    echo "Test password: {$testPassword}<br>";
    echo "Hashed password: " . substr($hashedPassword, 0, 20) . "...<br>";
    echo "Hash length: " . strlen($hashedPassword) . " characters<br>";
    
    if ($verifyResult) {
        echo "✅ Password hashing and verification working correctly<br>";
    } else {
        echo "❌ Password hashing and verification failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Password hashing error: " . $e->getMessage() . "<br>";
}

// Test login query
echo "<h2>6. Login Query Test</h2>";
try {
    $testUsername = 'admin';
    $user = $db->getOne('SELECT * FROM users WHERE username = ?', [$testUsername]);
    
    if ($user) {
        echo "✅ Found user with username '{$testUsername}'<br>";
        echo "User ID: {$user['user_id']}<br>";
        echo "Email: {$user['email']}<br>";
        echo "Role: {$user['role']}<br>";
        echo "Password hash: " . substr($user['password'], 0, 20) . "...<br>";
        
        // Test password verification
        if (Security::verifyPassword('admin123', $user['password'])) {
            echo "✅ Password verification successful<br>";
        } else {
            echo "❌ Password verification failed<br>";
            echo "This is likely the cause of your login issue!<br>";
        }
    } else {
        echo "❌ User '{$testUsername}' not found<br>";
        echo "This is the cause of your login issue!<br>";
    }
} catch (Exception $e) {
    echo "❌ Login query error: " . $e->getMessage() . "<br>";
}

echo "<h2>Next Steps</h2>";
echo "<p>If you see any ❌ errors above, they need to be fixed.</p>";
echo "<p><a href='setup-admin.php'>Run Admin Setup</a> to create/fix the admin user</p>";
echo "<p><a href='login.php'>Try Login Again</a></p>";
echo "<p><a href='test-login.php'>Run Full Test</a></p>";
?> 