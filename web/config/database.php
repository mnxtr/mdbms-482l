<?php
class Database {
    private $host = "localhost";
    private $db_name = "manufacturing_db";
    private $username = "root";
    private $password = "";
    private $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }

    // Execute query with parameters
    public function executeQuery($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            echo "Query Error: " . $e->getMessage();
            return false;
        }
    }

    // Get single record
    public function getOne($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt ? $stmt->fetch() : false;
    }

    // Get multiple records
    public function getAll($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt ? $stmt->fetchAll() : false;
    }

    // Insert record
    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $values = implode(", ", array_fill(0, count($data), "?"));
        
        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(array_values($data));
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            echo "Insert Error: " . $e->getMessage();
            return false;
        }
    }

    // Update record
    public function update($table, $data, $where, $whereParams = []) {
        $set = implode(" = ?, ", array_keys($data)) . " = ?";
        $query = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(array_merge(array_values($data), $whereParams));
            return $stmt->rowCount();
        } catch(PDOException $e) {
            echo "Update Error: " . $e->getMessage();
            return false;
        }
    }

    // Delete record
    public function delete($table, $where, $params = []) {
        $query = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch(PDOException $e) {
            echo "Delete Error: " . $e->getMessage();
            return false;
        }
    }

    // Begin transaction
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    // Commit transaction
    public function commit() {
        return $this->conn->commit();
    }

    // Rollback transaction
    public function rollback() {
        return $this->conn->rollBack();
    }
}
?> 