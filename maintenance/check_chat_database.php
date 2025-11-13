<?php
/**
 * Chat System Database Check
 * Ensures chat tables exist and are properly configured
 */

require_once __DIR__ . '/config/config.php';

try {
    $db = getDB();
    
    // Check if chat_sessions table exists
    $result = $db->query("SHOW TABLES LIKE 'chat_sessions'");
    if ($result->rowCount() == 0) {
        echo "Creating chat_sessions table...\n";
        $db->exec("
            CREATE TABLE chat_sessions (
                session_id INT PRIMARY KEY AUTO_INCREMENT,
                customer_id INT NOT NULL,
                status ENUM('open', 'closed') DEFAULT 'open',
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
            )
        ");
        echo "✓ chat_sessions table created\n";
    } else {
        echo "✓ chat_sessions table exists\n";
    }
    
    // Check if chat_messages table exists
    $result = $db->query("SHOW TABLES LIKE 'chat_messages'");
    if ($result->rowCount() == 0) {
        echo "Creating chat_messages table...\n";
        $db->exec("
            CREATE TABLE chat_messages (
                message_id INT PRIMARY KEY AUTO_INCREMENT,
                session_id INT NOT NULL,
                sender_type ENUM('customer', 'admin') NOT NULL,
                sender_customer_id INT NULL,
                sender_user_id INT NULL,
                message TEXT NOT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES chat_sessions(session_id),
                FOREIGN KEY (sender_customer_id) REFERENCES customers(customer_id),
                FOREIGN KEY (sender_user_id) REFERENCES users(user_id),
                INDEX idx_session_created_at (session_id, created_at),
                INDEX idx_unread (session_id, is_read)
            )
        ");
        echo "✓ chat_messages table created\n";
    } else {
        echo "✓ chat_messages table exists\n";
    }
    
    // Check if content_moderation table exists
    $result = $db->query("SHOW TABLES LIKE 'content_moderation'");
    if ($result->rowCount() == 0) {
        echo "Creating content_moderation table...\n";
        $db->exec("
            CREATE TABLE content_moderation (
                moderation_id INT PRIMARY KEY AUTO_INCREMENT,
                session_id INT NULL,
                customer_id INT NULL,
                content TEXT NOT NULL,
                action ENUM('allow', 'warn', 'block') NOT NULL,
                severity ENUM('low', 'medium', 'high') NOT NULL,
                flagged_words TEXT NULL,
                moderation_details JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES chat_sessions(session_id) ON DELETE SET NULL,
                FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL
            )
        ");
        echo "✓ content_moderation table created\n";
    } else {
        echo "✓ content_moderation table exists\n";
    }
    
    echo "\n✅ Chat system database check completed successfully!\n";
    echo "All required tables are present and properly configured.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
