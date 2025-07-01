<?php
define('SECURE_ACCESS', true);
require_once 'config/config.php';

echo "<h1>Admin User Setup</h1>";

// Check if users table exists
echo "<h2>1. Checking Users Table</h2>";
try {
    $tableExists = $db->getOne("SHOW TABLES LIKE 'users'");
    if ($tableExists) {
        echo "✅ Users table exists<br>";
    } else {
        echo "❌ Users table does not exist. Creating it...<br>";
        
        // Create users table
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

// Check current users
echo "<h2>2. Current Users</h2>";
try {
    $users = $db->getAll("SELECT user_id, username, email, role FROM users");
    if ($users) {
        echo "Found " . count($users) . " users:<br>";
        foreach ($users as $user) {
            echo "- ID: {$user['user_id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}<br>";
        }
    } else {
        echo "No users found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error getting users: " . $e->getMessage() . "<br>";
}

// Create or update admin user
echo "<h2>3. Setting Up Admin User</h2>";
try {
    $adminExists = $db->exists('users', 'username = ?', ['admin']);
    
    if ($adminExists) {
        echo "✅ Admin user already exists<br>";
        
        // Update admin password to ensure it's correct
        $newPassword = Security::hashPassword('admin123');
        $updateResult = $db->update('users', 
            ['password' => $newPassword], 
            'username = ?', 
            ['admin']
        );
        
        if ($updateResult) {
            echo "✅ Admin password updated successfully<br>";
        } else {
            echo "❌ Failed to update admin password<br>";
        }
    } else {
        echo "⚠️ Admin user does not exist. Creating...<br>";
        
        $adminData = [
            'username' => 'admin',
            'password' => Security::hashPassword('admin123'),
            'email' => 'admin@example.com',
            'full_name' => 'Administrator',
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $db->insert('users', $adminData);
        
        if ($result) {
            echo "✅ Admin user created successfully with ID: {$result}<br>";
        } else {
            echo "❌ Failed to create admin user<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error with admin user: " . $e->getMessage() . "<br>";
}

// Test login functionality
echo "<h2>4. Testing Login</h2>";
try {
    $testUsername = 'admin';
    $testPassword = 'admin123';
    
    $user = $db->getOne('SELECT * FROM users WHERE username = ?', [$testUsername]);
    
    if ($user) {
        echo "✅ Found user: {$user['username']}<br>";
        echo "User details: ID={$user['user_id']}, Email={$user['email']}, Role={$user['role']}<br>";
        
        // Test password verification
        if (Security::verifyPassword($testPassword, $user['password'])) {
            echo "✅ Password verification successful<br>";
        } else {
            echo "❌ Password verification failed<br>";
            echo "Stored password hash: " . substr($user['password'], 0, 20) . "...<br>";
            
            // Create new hash and test
            $newHash = Security::hashPassword($testPassword);
            echo "New password hash: " . substr($newHash, 0, 20) . "...<br>";
            
            if (Security::verifyPassword($testPassword, $newHash)) {
                echo "✅ New hash verification successful<br>";
                
                // Update the password
                $db->update('users', ['password' => $newHash], 'username = ?', ['admin']);
                echo "✅ Password updated in database<br>";
            } else {
                echo "❌ New hash verification also failed<br>";
            }
        }
    } else {
        echo "❌ User 'admin' not found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error testing login: " . $e->getMessage() . "<br>";
}

// Test database connection
echo "<h2>5. Database Connection Test</h2>";
try {
    $testQuery = $db->getOne("SELECT 1 as test");
    if ($testQuery) {
        echo "✅ Database connection working<br>";
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "<br>";
}

echo "<h2>Setup Complete</h2>";
echo "<p><strong>Login Credentials:</strong></p>";
echo "<p>Username: <strong>admin</strong></p>";
echo "<p>Password: <strong>admin123</strong></p>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
echo "<p><a href='test-login.php'>Run Full Test</a></p>";
?> 