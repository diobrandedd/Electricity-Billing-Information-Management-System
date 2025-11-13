<?php
/**
 * SMS Gateway Configuration
 * Configuration file for SMS notifications
 */

// SMS Gateway Credentials
define('SMS_USERNAME', 'M9U4MY');
define('SMS_PASSWORD', '0csafcb1qnqumo');
define('SMS_SERVER_URL', 'https://api.sms-gate.app/3rdparty/v1');

// SMS Settings
define('SMS_ENABLED', true);
define('SMS_COUNTRY_CODE', '+63');
define('SMS_MAX_LENGTH', 160);

// Notification Settings
define('SMS_DUE_DATE_REMINDER_DAYS', 3); // Days before due date to send reminder
define('SMS_OVERDUE_REMINDER_DAYS', 7); // Days after due date to send overdue reminder
define('SMS_DISCONNECTION_DAYS', 30); // Days after due date to send disconnection notice

// Message Templates
define('SMS_DUE_DATE_TEMPLATE', 'Dear {customer_name}, your electricity bill #{bill_number} amounting to {amount} is due on {due_date}. Please pay on time to avoid penalties. - SOCOTECO');
define('SMS_OVERDUE_TEMPLATE', 'Dear {customer_name}, your electricity bill #{bill_number} amounting to {amount} is now OVERDUE since {due_date}. Please pay immediately to avoid disconnection. - SOCOTECO');
define('SMS_DISCONNECTION_TEMPLATE', 'Dear {customer_name}, your electricity service will be DISCONNECTED due to unpaid bill #{bill_number} amounting to {amount}. Please pay immediately to restore service. - SOCOTECO');

// Log Settings
define('SMS_LOG_ENABLED', true);
define('SMS_LOG_FILE', __DIR__ . '/../../logs/sms_log.txt');
?>
