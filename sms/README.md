# SMS Notification System

This SMS notification system provides automated billing notifications for the SOCOTECO billing system.

## Features

- **Due Date Reminders**: Send SMS 3 days before bill due date
- **Overdue Reminders**: Send SMS 7 days after due date
- **Disconnection Notices**: Send SMS 30 days after due date
- **Bulk SMS**: Send notifications to multiple customers at once
- **Individual SMS**: Send specific notifications to individual bills
- **Automated Scheduling**: Cron job support for automated notifications

## Configuration

### SMS Gateway Settings

Edit `config/sms_config.php` to configure your SMS gateway:

```php
define('SMS_USERNAME', 'M9U4MY');
define('SMS_PASSWORD', '0csafcb1qnqumo');
define('SMS_SERVER_URL', 'https://api.sms-gate.app/3rdparty/v1');
```

### Notification Settings

```php
define('SMS_DUE_DATE_REMINDER_DAYS', 3);     // Days before due date
define('SMS_OVERDUE_REMINDER_DAYS', 7);      // Days after due date
define('SMS_DISCONNECTION_DAYS', 30);        // Days for disconnection notice
```

## Usage

### Manual SMS Sending

1. **Individual Bill SMS**: Go to Bills page and click the SMS dropdown for any pending bill
2. **Bulk SMS**: Use the "Send SMS" dropdown in the Bills page header

### Automated SMS (Cron Jobs)

Set up cron jobs for automated notifications:

```bash
# Daily notifications at 9:00 AM
0 9 * * * cd /path/to/sms && php cron/sms_scheduler.php all

# Due date reminders at 10:00 AM
0 10 * * * cd /path/to/sms && php cron/sms_scheduler.php due_date

# Overdue reminders at 2:00 PM
0 14 * * * cd /path/to/sms && php cron/sms_scheduler.php overdue

# Disconnection notices at 4:00 PM
0 16 * * * cd /path/to/sms && php cron/sms_scheduler.php disconnection
```

### Testing

Test the SMS system:

```bash
# Test all notifications
php cron/sms_scheduler.php all

# Test specific notification type
php cron/sms_scheduler.php due_date
php cron/sms_scheduler.php overdue
php cron/sms_scheduler.php disconnection
```

## Message Templates

### Due Date Reminder
```
Dear {customer_name}, your electricity bill #{bill_number} amounting to {amount} is due on {due_date}. Please pay on time to avoid penalties. - SOCOTECO
```

### Overdue Reminder
```
Dear {customer_name}, your electricity bill #{bill_number} amounting to {amount} is now OVERDUE since {due_date}. Please pay immediately to avoid disconnection. - SOCOTECO
```

### Disconnection Notice
```
Dear {customer_name}, your electricity service will be DISCONNECTED due to unpaid bill #{bill_number} amounting to {amount}. Please pay immediately to restore service. - SOCOTECO
```

## File Structure

```
sms/
├── config/
│   └── sms_config.php          # SMS configuration
├── classes/
│   └── SMSNotification.php     # SMS notification class
├── functions/
│   └── bill_notifications.php   # Bill-specific SMS functions
├── cron/
│   └── sms_scheduler.php       # Automated scheduler
├── setup_cron.php              # Cron setup helper
└── README.md                   # This file
```

## Requirements

- PHP 7.4 or higher
- Composer dependencies installed
- Valid SMS gateway credentials
- Customer phone numbers in database

## Installation

1. Install Composer dependencies:
   ```bash
   composer install
   ```

2. Configure SMS settings in `config/sms_config.php`

3. Set up cron jobs using `setup_cron.php`

4. Test the system with manual SMS sending

## Logging

SMS activities are logged to `logs/sms_log.txt` with timestamps and results.

## Troubleshooting

1. **SMS not sending**: Check SMS gateway credentials and server URL
2. **Invalid phone numbers**: Ensure customer phone numbers are in correct format (9XXXXXXXXX)
3. **Cron jobs not running**: Check cron job syntax and file paths
4. **Database errors**: Ensure database connection is working

## Support

For issues with the SMS system, check:
1. SMS gateway service status
2. Database connectivity
3. Log files for error messages
4. Customer phone number formats
