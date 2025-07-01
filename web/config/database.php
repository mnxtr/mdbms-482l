<?php

class Database {
    private $host = "localhost";
    private $db_name = "manufacturing_db";          
    private $username = "root";
    private $password = "";
    private $conn;
    private static $instance = null;
    private $preparedStatements = [];
    private $transactionLevel = 0;

    // Singleton pattern for connection pooling
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->getConnection();
    }

    // Prevent cloning
    private function __clone() {}

    // Get database connection with connection pooling
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false, // Use native prepared statements
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    PDO::ATTR_PERSISTENT => true, // Enable persistent connections
                    PDO::ATTR_TIMEOUT => 5, // Connection timeout
                ];
                
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                
                // Set session variables for better performance
                $this->conn->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
                $this->conn->exec("SET SESSION time_zone = '+00:00'");
                
            } catch(PDOException $e) {
                error_log("[ERROR] Database Connection Error: " . $e->getMessage());
                throw new Exception("Database connection failed");
            }
        }
        return $this->conn;
    }

    // Execute query with parameters and statement caching
    public function executeQuery($query, $params = []) {
        $hash = md5($query);
        
        try {
            if (!isset($this->preparedStatements[$hash])) {
                $this->preparedStatements[$hash] = $this->conn->prepare($query);
            }
            
            $stmt = $this->preparedStatements[$hash];
            $stmt->execute($params);
            return $stmt;
            
        } catch(PDOException $e) {
            error_log("[ERROR] Query Error: " . $e->getMessage() . " Query: " . $query);
            throw new Exception("Database query failed");
        }
    }

    // Get single record with error handling
    public function getOne($query, $params = []) {
        try {
            $stmt = $this->executeQuery($query, $params);
            return $stmt ? $stmt->fetch() : false;
        } catch (Exception $e) {
            error_log("[ERROR] getOne failed: " . $e->getMessage());
            return false;
        }
    }

    // Get multiple records with error handling
    public function getAll($query, $params = []) {
        try {
            $stmt = $this->executeQuery($query, $params);
            return $stmt ? $stmt->fetchAll() : false;
        } catch (Exception $e) {
            error_log("[ERROR] getAll failed: " . $e->getMessage());
            return false;
        }
    }

    // Insert record with validation
    public function insert($table, $data) {
        if (empty($data) || !is_array($data)) {
            throw new Exception("Invalid data for insert");
        }
        
        $columns = array_keys($data);
        $values = array_fill(0, count($data), "?");
        $query = "INSERT INTO {$table} (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(array_values($data));
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            error_log("[ERROR] Insert Error: " . $e->getMessage());
            throw new Exception("Insert operation failed");
        }
    }

    // Update record with validation
    public function update($table, $data, $where, $whereParams = []) {
        if (empty($data) || !is_array($data)) {
            throw new Exception("Invalid data for update");
        }
        
        $setClause = implode(" = ?, ", array_keys($data)) . " = ?";
        $query = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(array_merge(array_values($data), $whereParams));
            return $stmt->rowCount();
        } catch(PDOException $e) {
            error_log("[ERROR] Update Error: " . $e->getMessage());
            throw new Exception("Update operation failed");
        }
    }

    // Delete record with validation
    public function delete($table, $where, $params = []) {
        if (empty($where)) {
            throw new Exception("Where clause is required for delete operation");
        }
        
        $query = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch(PDOException $e) {
            error_log("[ERROR] Delete Error: " . $e->getMessage());
            throw new Exception("Delete operation failed");
        }
    }

    // Enhanced transaction handling with nested transactions
    public function beginTransaction() {
        if ($this->transactionLevel === 0) {
            $this->conn->beginTransaction();
        }
        $this->transactionLevel++;
        return true;
    }

    public function commit() {
        $this->transactionLevel--;
        if ($this->transactionLevel === 0) {
            return $this->conn->commit();
        }
        return true;
    }

    public function rollback() {
        $this->transactionLevel--;
        if ($this->transactionLevel === 0) {
            return $this->conn->rollBack();
        }
        return true;
    }

    // Execute multiple queries in a transaction
    public function executeTransaction($queries) {
        try {
            $this->beginTransaction();
            
            foreach ($queries as $query) {
                if (is_array($query)) {
                    $this->executeQuery($query['sql'], $query['params'] ?? []);
                } else {
                    $this->executeQuery($query);
                }
            }
            
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    // Get table row count
    public function getCount($table, $where = '', $params = []) {
        $query = "SELECT COUNT(*) as count FROM {$table}";
        if (!empty($where)) {
            $query .= " WHERE {$where}";
        }
        
        $result = $this->getOne($query, $params);
        return $result ? (int)$result['count'] : 0;
    }

    // Check if record exists
    public function exists($table, $where, $params = []) {
        return $this->getCount($table, $where, $params) > 0;
    }

    // Get paginated results
    public function getPaginated($query, $params = [], $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $countQuery = preg_replace('/SELECT .* FROM/', 'SELECT COUNT(*) as total FROM', $query);
        $countQuery = preg_replace('/ORDER BY .*/', '', $countQuery);
        
        $total = $this->getOne($countQuery, $params);
        $total = $total ? (int)$total['total'] : 0;
        
        $dataQuery = $query . " LIMIT {$limit} OFFSET {$offset}";
        $data = $this->getAll($dataQuery, $params);
        
        return [
            'data' => $data ?: [],
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    // Close connection and cleanup
    public function close() {
        $this->preparedStatements = [];
        $this->conn = null;
    }

    // Destructor to ensure cleanup
    public function __destruct() {
        $this->close();
    }
}

// Global database instance
$db = Database::getInstance();

// Ensure default user exists (for development/demo)
try {
    $exists = $db->getOne("SELECT * FROM users WHERE username = ?", ['user']);
    if (!$exists) {
        $db->insert('users', [
            'username' => 'user',
            'password' => password_hash('root', PASSWORD_DEFAULT),
            'email' => 'user@example.com',
            'full_name' => 'Default User',
            'role' => 'admin'
        ]);
    }
} catch (Exception $e) {
    // Handle error (optional: log or display)
}
?> 