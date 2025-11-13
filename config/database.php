<?php
/**
 * Database Configuration for SOCOTECO II Billing Management System
 * Security Enhanced Version
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $conn;
    private $max_retries = 3;
    private $retry_delay = 1; // seconds

    public function __construct() {
        // constants from config.php for better security
        $this->host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $this->db_name = defined('DB_NAME') ? DB_NAME : 'socoteco_billing';
        $this->username = defined('DB_USER') ? DB_USER : 'root';
        $this->password = defined('DB_PASS') ? DB_PASS : '';
        $this->charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
    }

    public function getConnection() {
        $this->conn = null;
        $attempt = 0;
        
        while ($attempt < $this->max_retries) {
            try {
                $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false, // Disable persistent connections for security
                    PDO::ATTR_TIMEOUT => 5, // 5 second connection timeout
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $this->charset,
                    // SSL options (uncomment and configure if using SSL)
                    // PDO::MYSQL_ATTR_SSL_CA => '/path/to/ca-cert.pem',
                    // PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
                ];
                
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                
                // Connection successful, break retry loop
                break;
                
            } catch(PDOException $exception) {
                $attempt++;
                
                // Log error securely (don't expose sensitive info)
                $this->logError($exception, $attempt);
                
                // If this was the last attempt, handle the error
                if ($attempt >= $this->max_retries) {
                    $this->handleConnectionError($exception);
                    return null;
                }
                
                // Wait before retrying
                sleep($this->retry_delay);
            }
        }
        
        return $this->conn;
    }
    
    /**
     * Log database errors securely
     */
    private function logError($exception, $attempt) {
        $log_dir = __DIR__ . '/../logs';
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }
        
        $log_file = $log_dir . '/db_errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $error_msg = "[{$timestamp}] Attempt {$attempt}: " . $exception->getMessage() . "\n";
        
        // Don't log password or sensitive connection details
        @file_put_contents($log_file, $error_msg, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Handle connection errors based on environment
     */
    private function handleConnectionError($exception) {
        $is_production = defined('ENVIRONMENT') && ENVIRONMENT === 'production';
        
        if ($is_production) {
            // In production, show generic error message
            error_log("Database connection failed: " . $exception->getMessage());
            if (php_sapi_name() !== 'cli') {
                http_response_code(503);
                die("Service temporarily unavailable. Please try again later.");
            }
        } else {
            // In development, show detailed error
            die("Database Connection Error: " . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }
}

// Database connection instance
function getDB() {
    $database = new Database();
    return $database->getConnection();
}

// Helper function for prepared statements
function executeQuery($sql, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Helper function to execute query and return last insert ID
function executeQueryWithId($sql, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $db->lastInsertId();
}

// Helper function to get last inserted ID from a specific statement
function getLastInsertIdFromStmt($stmt) {
    return $stmt->getConnection()->lastInsertId();
}

// Helper function to get single record
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

// Helper function to get multiple records
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

// Helper function to get last inserted ID
function getLastInsertId() {
    $db = getDB();
    return $db->lastInsertId();
}
?>
