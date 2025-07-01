<?php
require_once 'config/config.php';

echo "=== Database Connection Test ===\n";

try {
    // Test database connection
    $pdo = new PDO('mysql:host=localhost;dbname=manufacturing_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists\n";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE users");
        echo "Users table structure:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['Field']}: {$row['Type']}\n";
        }
        
        // Check if admin user exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            echo "✅ Admin user already exists\n";
        } else {
            echo "❌ Admin user does not exist\n";
            
            // Try to create admin user
            echo "Attempting to create admin user...\n";
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
                $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt->execute(['admin', $hashedPassword, 'admin@example.com', 'Administrator', 'admin']);
                echo "✅ Admin user created successfully\n";
            } catch (PDOException $e) {
                echo "❌ Failed to create admin user: " . $e->getMessage() . "\n";
            }
        }
        
    } else {
        echo "❌ Users table does not exist\n";
        echo "Please run the database setup script first.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Make sure:\n";
    echo "1. XAMPP is running\n";
    echo "2. MySQL service is started\n";
    echo "3. Database 'manufacturing_db' exists\n";
    echo "4. User 'root' with no password can connect\n";
}

echo "\n=== Test Complete ===\n";
?> 