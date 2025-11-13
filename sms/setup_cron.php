<?php
/**
 * SMS Cron Job Setup
 * This script helps set up automated SMS notifications
 */

require_once 'config/sms_config.php';

echo "SMS Cron Job Setup\n";
echo "==================\n\n";

// Check if SMS is enabled
if (!SMS_ENABLED) {
    echo "WARNING: SMS is disabled in configuration.\n";
    echo "Please enable SMS in sms/config/sms_config.php\n\n";
}

// Display cron job commands
echo "Add these cron jobs to your system:\n\n";

echo "1. Daily SMS Notifications (run every day at 9:00 AM):\n";
echo "0 9 * * * cd " . __DIR__ . " && php cron/sms_scheduler.php all\n\n";

echo "2. Due Date Reminders (run every day at 10:00 AM):\n";
echo "0 10 * * * cd " . __DIR__ . " && php cron/sms_scheduler.php due_date\n\n";

echo "3. Overdue Reminders (run every day at 2:00 PM):\n";
echo "0 14 * * * cd " . __DIR__ . " && php cron/sms_scheduler.php overdue\n\n";

echo "4. Disconnection Notices (run every day at 4:00 PM):\n";
echo "0 16 * * * cd " . __DIR__ . " && php cron/sms_scheduler.php disconnection\n\n";

echo "To add these cron jobs:\n";
echo "1. Run: crontab -e\n";
echo "2. Add the desired cron job lines\n";
echo "3. Save and exit\n\n";

echo "Test the SMS system:\n";
echo "php cron/sms_scheduler.php all\n\n";

echo "Configuration Summary:\n";
echo "- SMS Username: " . SMS_USERNAME . "\n";
echo "- SMS Server: " . SMS_SERVER_URL . "\n";
echo "- Due Date Reminder Days: " . SMS_DUE_DATE_REMINDER_DAYS . "\n";
echo "- Overdue Reminder Days: " . SMS_OVERDUE_REMINDER_DAYS . "\n";
echo "- Disconnection Days: " . SMS_DISCONNECTION_DAYS . "\n";
echo "- Log File: " . SMS_LOG_FILE . "\n";
?>
