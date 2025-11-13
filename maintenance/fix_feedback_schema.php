<?php
// Quick fixer for feedback schema: ensure AUTO_INCREMENT primary keys
require_once __DIR__ . '/../config/config.php';

header('Content-Type: text/plain');

function run_migration($sql, $params = []) {
    try {
        executeQuery($sql, $params);
        echo "OK: $sql\n";
    } catch (Throwable $e) {
        echo "ERR: $sql\n  -> " . $e->getMessage() . "\n";
    }
}

echo "Fixing feedback schema...\n";

// Ensure feedback table has proper PK and auto increment
run_migration("ALTER TABLE feedback ADD COLUMN IF NOT EXISTS feedback_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT", []);
// Some MySQL versions don't support IF NOT EXISTS on column; fallback attempts:
run_migration("ALTER TABLE feedback MODIFY COLUMN feedback_id INT UNSIGNED NOT NULL AUTO_INCREMENT", []);

// Ensure replies table
run_migration("ALTER TABLE feedback_replies ADD COLUMN IF NOT EXISTS reply_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT", []);
run_migration("ALTER TABLE feedback_replies MODIFY COLUMN reply_id INT UNSIGNED NOT NULL AUTO_INCREMENT", []);

// Add indexes commonly needed
run_migration("ALTER TABLE feedback ADD INDEX idx_feedback_created_at (created_at)", []);
run_migration("ALTER TABLE feedback_replies ADD INDEX idx_replies_feedback_id (feedback_id)", []);
run_migration("ALTER TABLE feedback_replies ADD INDEX idx_replies_created_at (created_at)", []);

echo "Done.\n";
?>


