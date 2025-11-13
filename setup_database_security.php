<?php
/**
 * Database Security Setup Script
 * This script applies security configurations to the database
 * 
 * IMPORTANT: Run this AFTER importing the main database schema (socoteco_billing.sql)
 */

// Database configuration (using root for initial setup)
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'socoteco_billing';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Security Setup - SOCOTECO II</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #34495e;
            margin-top: 30px;
        }
        .success {
            color: #27ae60;
            background: #d5f4e6;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            color: #e74c3c;
            background: #fadbd8;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .warning {
            color: #f39c12;
            background: #fef5e7;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            color: #3498db;
            background: #ebf5fb;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .step {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #3498db;
        }
        code {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .credentials {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .credentials strong {
            color: #856404;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔒 Database Security Setup</h1>
        <p>This script will apply security configurations to your database.</p>";

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='success'>✓ Connected to MySQL server</div>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$database'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Database '$database' does not exist. Please import socoteco_billing.sql first!");
    }
    
    echo "<div class='success'>✓ Database '$database' found</div>";
    
    // Select the database
    $pdo->exec("USE `$database`");
    
    // Read and execute the security config file
    $security_file = __DIR__ . '/database/security_config.sql';
    if (!file_exists($security_file)) {
        throw new Exception("Security configuration file not found: $security_file");
    }
    
    echo "<div class='info'>📄 Reading security configuration file...</div>";
    
    $sql = file_get_contents($security_file);
    
    // Split SQL into individual statements
    // Remove comments and split by semicolon
    $sql = preg_replace('/--.*$/m', '', $sql); // Remove single-line comments
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove multi-line comments
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    $warnings = [];
    
    echo "<div class='step'><h2>Applying Security Configurations...</h2>";
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            // Skip SET GLOBAL commands that might not work in all environments
            if (stripos($statement, 'SET GLOBAL') !== false) {
                $warnings[] = "Skipped: " . substr($statement, 0, 50) . "... (may require SUPER privilege)";
                continue;
            }
            
            $pdo->exec($statement);
            $success_count++;
        } catch (PDOException $e) {
            // Some statements might fail (like CREATE USER IF NOT EXISTS on older MySQL versions)
            $error_code = $e->getCode();
            if ($error_code == 1396) {
                // User already exists - this is okay
                $warnings[] = "User already exists (skipped): " . substr($statement, 0, 50) . "...";
                $success_count++;
            } else {
                $error_count++;
                echo "<div class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
    
    echo "</div>";
    
    echo "<div class='success'>✓ Applied $success_count security configurations</div>";
    
    if ($error_count > 0) {
        echo "<div class='error'>✗ $error_count errors encountered</div>";
    }
    
    if (!empty($warnings)) {
        echo "<div class='warning'><strong>Warnings:</strong><ul>";
        foreach ($warnings as $warning) {
            echo "<li>" . htmlspecialchars($warning) . "</li>";
        }
        echo "</ul></div>";
    }
    
    // Verify users were created
    echo "<div class='step'><h2>Verifying Created Users...</h2>";
    
    $users = ['socoteco_app', 'socoteco_readonly', 'socoteco_backup', 'socoteco_admin'];
    foreach ($users as $user) {
        $stmt = $pdo->query("SELECT User, Host FROM mysql.user WHERE User = '$user'");
        $user_count = $stmt->rowCount();
        if ($user_count > 0) {
            echo "<div class='success'>✓ User '$user' created successfully</div>";
        } else {
            echo "<div class='error'>✗ User '$user' not found</div>";
        }
    }
    
    echo "</div>";
    
    // Display credentials
    echo "<div class='credentials'>
        <h2>⚠️ IMPORTANT: Default Credentials</h2>
        <p><strong>Application User:</strong><br>
        Username: <code>socoteco_app</code><br>
        Password: <code>Socoteco@App2025!Secure</code></p>
        
        <p><strong>Read-Only User:</strong><br>
        Username: <code>socoteco_readonly</code><br>
        Password: <code>Socoteco@ReadOnly2025!Secure</code></p>
        
        <p><strong>Backup User:</strong><br>
        Username: <code>socoteco_backup</code><br>
        Password: <code>Socoteco@Backup2025!Secure</code></p>
        
        <p><strong>Admin User:</strong><br>
        Username: <code>socoteco_admin</code><br>
        Password: <code>Socoteco@Admin2025!Secure</code></p>
        
        <p style='color: #856404;'><strong>⚠️ CHANGE THESE PASSWORDS BEFORE PRODUCTION USE!</strong></p>
    </div>";
    
    // Next steps
    echo "<div class='step'>
        <h2>Next Steps:</h2>
        <ol>
            <li>Update <code>config/database.php</code> to use the <code>socoteco_app</code> user</li>
            <li>Change all default passwords (see <code>database/SECURITY_SETUP.md</code>)</li>
            <li>Test the application connection with the new user</li>
            <li>Review security settings in <code>database/SECURITY_SETUP.md</code></li>
        </ol>
    </div>";
    
    echo "<div class='info'>
        <strong>📚 Documentation:</strong> See <code>database/SECURITY_SETUP.md</code> for detailed information about the security configuration.
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>
        <h2>❌ Error</h2>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
        <p>Please ensure:</p>
        <ul>
            <li>MySQL/MariaDB is running</li>
            <li>Root credentials are correct</li>
            <li>Database 'socoteco_billing' has been imported</li>
            <li>You have sufficient privileges to create users</li>
        </ul>
    </div>";
}

echo "    </div>
</body>
</html>";
?>

