<?php
// Database setup script
echo "<h2>Manufacturing Database Setup</h2>";

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "manufacturing_db";

try {
    // Connect to MySQL without selecting a database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Connected to MySQL server</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$database'");
    $dbExists = $stmt->fetch();
    
    if (!$dbExists) {
        echo "<p>Creating database '$database'...</p>";
        $pdo->exec("CREATE DATABASE `$database`");
        echo "<p style='color: green;'>✓ Database created successfully</p>";
    } else {
        echo "<p style='color: green;'>✓ Database '$database' already exists</p>";
    }
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if products table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "<p>Creating tables...</p>";
        
        // Create products table
        $sql = "CREATE TABLE products (
            product_id INT PRIMARY KEY AUTO_INCREMENT,
            product_code VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            category VARCHAR(50),
            unit_price DECIMAL(10,2) NOT NULL,
            min_stock_level INT NOT NULL,
            current_stock INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✓ Products table created</p>";
        
        // Create users table if it doesn't exist
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if (!$stmt->fetch()) {
            $sql = "CREATE TABLE users (
                user_id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                role ENUM('admin', 'manager', 'supervisor', 'operator') NOT NULL,
                status ENUM('active', 'inactive', 'on_leave') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Users table created</p>";
            
            // Insert default admin user
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['admin', $hashedPassword, 'admin@example.com', 'System Administrator', 'admin']);
            echo "<p style='color: green;'>✓ Default admin user created (username: admin, password: admin123)</p>";
        }
        
        // Create suppliers table if it doesn't exist
        $stmt = $pdo->query("SHOW TABLES LIKE 'suppliers'");
        if (!$stmt->fetch()) {
            $sql = "CREATE TABLE suppliers (
                supplier_id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                contact_person VARCHAR(100),
                email VARCHAR(100),
                phone VARCHAR(20),
                address TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Suppliers table created</p>";
        }
        
        // Create production_orders table if it doesn't exist
        $stmt = $pdo->query("SHOW TABLES LIKE 'production_orders'");
        if (!$stmt->fetch()) {
            $sql = "CREATE TABLE production_orders (
                order_id INT PRIMARY KEY AUTO_INCREMENT,
                order_number VARCHAR(50) UNIQUE NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL,
                status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL,
                start_date DATE,
                end_date DATE,
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Production orders table created</p>";
        }
        
        // Create quality_control table if it doesn't exist
        $stmt = $pdo->query("SHOW TABLES LIKE 'quality_control'");
        if (!$stmt->fetch()) {
            $sql = "CREATE TABLE quality_control (
                qc_id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT NOT NULL,
                inspector_id INT NOT NULL,
                inspection_date DATE NOT NULL,
                passed_quantity INT NOT NULL,
                failed_quantity INT NOT NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Quality control table created</p>";
        }
        
    } else {
        echo "<p style='color: green;'>✓ Tables already exist</p>";
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p>Your database is now ready to use.</p>";
    echo "<p><a href='index.php'>← Go to Dashboard</a></p>";
    echo "<p><a href='add-product.php'>← Try Adding a Product</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure:</p>";
    echo "<ul>";
    echo "<li>XAMPP is running</li>";
    echo "<li>MySQL service is started</li>";
    echo "<li>Database credentials are correct</li>";
    echo "</ul>";
}
?> 