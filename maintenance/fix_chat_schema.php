<?php
require_once __DIR__ . '/../config/config.php';
requireRole(['admin']);

header('Content-Type: text/plain');

try {
    $db = getDB();

    echo "Checking chat schema...\n";

    // Ensure chat_sessions table exists
    $db->exec("CREATE TABLE IF NOT EXISTS chat_sessions (
        session_id INT PRIMARY KEY AUTO_INCREMENT,
        customer_id INT NOT NULL,
        status ENUM('open','closed') DEFAULT 'open',
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Ensure chat_messages table exists
    $db->exec("CREATE TABLE IF NOT EXISTS chat_messages (
        message_id INT PRIMARY KEY AUTO_INCREMENT,
        session_id INT NOT NULL,
        sender_type ENUM('customer','admin') NOT NULL,
        sender_customer_id INT NULL,
        sender_user_id INT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Fix any session rows with id 0 (should be > 0)
    $zeroCount = fetchOne("SELECT COUNT(*) AS c FROM chat_sessions WHERE session_id = 0");
    if (($zeroCount['c'] ?? 0) > 0) {
        $maxRow = fetchOne("SELECT MAX(session_id) AS m FROM chat_sessions WHERE session_id > 0");
        $newId = (int)(($maxRow['m'] ?? 0) + 1);
        // Ensure there are no child messages referencing 0 before updating
        $msgCount = fetchOne("SELECT COUNT(*) AS c FROM chat_messages WHERE session_id = 0");
        if (($msgCount['c'] ?? 0) > 0) {
            echo "Cannot fix: found chat_messages referencing session_id=0. Please contact support.\n";
            exit;
        }
        executeQuery("UPDATE chat_sessions SET session_id = ? WHERE session_id = 0", [$newId]);
        echo "Updated chat_sessions session_id 0 -> {$newId}\n";
    }

    // Enforce AUTO_INCREMENT on keys
    $db->exec("ALTER TABLE chat_sessions MODIFY session_id INT NOT NULL AUTO_INCREMENT");
    $db->exec("ALTER TABLE chat_messages MODIFY message_id INT NOT NULL AUTO_INCREMENT");

    echo "Schema check complete.\n";
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage() . "\n";
}
?>


