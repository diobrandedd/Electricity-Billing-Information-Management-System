<?php
/**
 * SMS Scheduler - Automated SMS notifications
 * This script should be run via cron job
 */

require_once __DIR__ . '/../functions/bill_notifications.php';

class SMSScheduler {
    private $billNotifications;
    
    public function __construct() {
        $this->billNotifications = new BillSMSNotifications();
    }
    
    /**
     * Run daily SMS notifications
     */
    public function runDailyNotifications() {
        $results = [
            'due_date' => [],
            'overdue' => [],
            'disconnection' => []
        ];
        
        // Send due date reminders
        echo "Sending due date reminders...\n";
        $results['due_date'] = $this->billNotifications->sendDueDateReminders();
        
        // Send overdue reminders
        echo "Sending overdue reminders...\n";
        $results['overdue'] = $this->billNotifications->sendOverdueReminders();
        
        // Send disconnection notices
        echo "Sending disconnection notices...\n";
        $results['disconnection'] = $this->billNotifications->sendDisconnectionNotices();
        
        return $results;
    }
    
    /**
     * Run specific notification type
     */
    public function runNotification($type) {
        switch ($type) {
            case 'due_date':
                return $this->billNotifications->sendDueDateReminders();
            case 'overdue':
                return $this->billNotifications->sendOverdueReminders();
            case 'disconnection':
                return $this->billNotifications->sendDisconnectionNotices();
            default:
                return ['error' => 'Invalid notification type'];
        }
    }
    
    /**
     * Get notification statistics
     */
    public function getNotificationStats() {
        $stats = [
            'due_date' => count($this->billNotifications->getBillsForNotification('due_date')),
            'overdue' => count($this->billNotifications->getBillsForNotification('overdue')),
            'disconnection' => count($this->billNotifications->getBillsForNotification('disconnection'))
        ];
        
        return $stats;
    }
}

// Run if called directly
if (php_sapi_name() === 'cli') {
    $scheduler = new SMSScheduler();
    
    $type = $argv[1] ?? 'all';
    
    if ($type === 'all') {
        $results = $scheduler->runDailyNotifications();
        echo "SMS Notifications Summary:\n";
        echo "Due Date Reminders: " . count($results['due_date']) . "\n";
        echo "Overdue Reminders: " . count($results['overdue']) . "\n";
        echo "Disconnection Notices: " . count($results['disconnection']) . "\n";
    } else {
        $results = $scheduler->runNotification($type);
        echo "Sent " . count($results) . " " . $type . " notifications\n";
    }
}
?>
