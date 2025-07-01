<?php
define('SECURE_ACCESS', true);
require_once 'config/config.php';

echo "<h1>Login System Test</h1>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    $testQuery = $db->getOne("SELECT 1 as test");
    if ($testQuery) {
        echo "✅ Database connection successful<br>";
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "<br>";
}

// Test 2: Users Table
echo "<h2>2. Users Table Test</h2>";
try {
    $userCount = $db->getCount('users');
    echo "✅ Users table exists. Total users: {$userCount}<br>";
    
    if ($userCount > 0) {
        $users = $db->getAll("SELECT username, email, role FROM users LIMIT 5");
        echo "Sample users:<br>";
        foreach ($users as $user) {
            echo "- Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Users table error: " . $e->getMessage() . "<br>";
}

// Test 3: Default User Creation
echo "<h2>3. Default User Test</h2>";
try {
    $adminExists = $db->exists('users', 'username = ?', ['admin']);
    if ($adminExists) {
        echo "✅ Default admin user exists<br>";
        
        // Test password verification
        $admin = $db->getOne("SELECT * FROM users WHERE username = ?", ['admin']);
        if ($admin && Security::verifyPassword('admin123', $admin['password'])) {
            echo "✅ Admin password verification successful<br>";
        } else {
            echo "❌ Admin password verification failed<br>";
        }
    } else {
        echo "⚠️ Default admin user does not exist<br>";
        
        // Try to create it
        $result = $db->insert('users', [
            'username' => 'admin',
            'password' => Security::hashPassword('admin123'),
            'email' => 'admin@example.com',
            'full_name' => 'Administrator',
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            echo "✅ Default admin user created successfully<br>";
        } else {
            echo "❌ Failed to create default admin user<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Default user test error: " . $e->getMessage() . "<br>";
}

// Test 4: Session Test
echo "<h2>4. Session Test</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session is active<br>";
    echo "Session ID: " . session_id() . "<br>";
} else {
    echo "❌ Session is not active<br>";
}

// Test 5: Security Functions
echo "<h2>5. Security Functions Test</h2>";
try {
    $testPassword = "test123";
    $hashedPassword = Security::hashPassword($testPassword);
    $verifyResult = Security::verifyPassword($testPassword, $hashedPassword);
    
    if ($verifyResult) {
        echo "✅ Password hashing and verification working<br>";
    } else {
        echo "❌ Password hashing and verification failed<br>";
    }
    
    $csrfToken = Security::generateCSRFToken();
    if ($csrfToken) {
        echo "✅ CSRF token generation working<br>";
    } else {
        echo "❌ CSRF token generation failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Security functions error: " . $e->getMessage() . "<br>";
}

// Test 6: Input Validation
echo "<h2>6. Input Validation Test</h2>";
try {
    $validator = InputValidator::getInstance();
    
    $testData = [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password123'
    ];
    
    $sanitized = $validator->sanitize($testData);
    if ($sanitized) {
        echo "✅ Input sanitization working<br>";
    } else {
        echo "❌ Input sanitization failed<br>";
    }
    
    $validationRules = [
        'username' => 'required|min:3',
        'email' => 'required|email',
        'password' => 'required|min:6'
    ];
    
    $errors = $validator->validate($testData, $validationRules);
    if (empty($errors)) {
        echo "✅ Input validation working<br>";
    } else {
        echo "❌ Input validation errors: " . implode(', ', $errors) . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Input validation error: " . $e->getMessage() . "<br>";
}

// Test 7: Login Simulation
echo "<h2>7. Login Simulation Test</h2>";
try {
    $testUsername = 'admin';
    $testPassword = 'admin123';
    
    $user = $db->getOne('SELECT * FROM users WHERE username = ?', [$testUsername]);
    
    if ($user && Security::verifyPassword($testPassword, $user['password'])) {
        echo "✅ Login simulation successful<br>";
        echo "User found: {$user['full_name']} ({$user['role']})<br>";
    } else {
        echo "❌ Login simulation failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Login simulation error: " . $e->getMessage() . "<br>";
}

// Test 8: Cache Test
echo "<h2>8. Cache Test</h2>";
try {
    $testKey = 'test_cache_key';
    $testData = ['test' => 'data', 'timestamp' => time()];
    
    $cacheSet = Cache::set($testKey, $testData, 60);
    if ($cacheSet) {
        echo "✅ Cache set successful<br>";
    } else {
        echo "❌ Cache set failed<br>";
    }
    
    $cachedData = Cache::get($testKey);
    if ($cachedData && $cachedData['test'] === 'data') {
        echo "✅ Cache get successful<br>";
    } else {
        echo "❌ Cache get failed<br>";
    }
    
    $cacheDelete = Cache::delete($testKey);
    if ($cacheDelete) {
        echo "✅ Cache delete successful<br>";
    } else {
        echo "❌ Cache delete failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Cache test error: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Summary</h2>";
echo "<p>If all tests show ✅, your login system should be working properly.</p>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
?> 