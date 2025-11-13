<?php
require_once __DIR__ . '/../classes/SMSNotification.php';
require_once __DIR__ . '/../../config/database.php';

class BillSMSNotifications {
    private $sms;
    
    public function __construct() {
        $this->sms = new SMSNotification();
    }
    
    /**
     * Send due date reminders
     */
    public function sendDueDateReminders() {
        $reminderDays = SMS_DUE_DATE_REMINDER_DAYS;
        $today = date('Y-m-d');
        $reminderDate = date('Y-m-d', strtotime("+{$reminderDays} days"));
        
        $sql = "SELECT b.*, c.first_name, c.last_name, c.contact_number, c.account_number
                FROM bills b
                JOIN customers c ON b.customer_id = c.customer_id
                WHERE b.status = 'pending' 
                AND b.due_date = ?
                AND c.contact_number IS NOT NULL 
                AND c.contact_number != ''
                AND c.is_active = 1";
        
        $bills = fetchAll($sql, [$reminderDate]);
        
        $results = [];
        foreach ($bills as $bill) {
            $customerName = $bill['first_name'] . ' ' . $bill['last_name'];
            $message = $this->sms->sendDueDateReminder(
                $customerName,
                $bill['bill_number'],
                $bill['total_amount'],
                $bill['due_date']
            );
            
            $result = $this->sms->sendSMS($bill['contact_number'], $message);
            $results[] = [
                'bill_id' => $bill['bill_id'],
                'customer' => $customerName,
                'phone' => $bill['contact_number'],
                'result' => $result
            ];
        }
        
        return $results;
    }
    
    /**
     * Send overdue reminders
     */
    public function sendOverdueReminders() {
        $overdueDays = SMS_OVERDUE_REMINDER_DAYS;
        $today = date('Y-m-d');
        $overdueDate = date('Y-m-d', strtotime("-{$overdueDays} days"));
        
        $sql = "SELECT b.*, c.first_name, c.last_name, c.contact_number, c.account_number
                FROM bills b
                JOIN customers c ON b.customer_id = c.customer_id
                WHERE b.status = 'pending' 
                AND b.due_date < ?
                AND c.contact_number IS NOT NULL 
                AND c.contact_number != ''
                AND c.is_active = 1";
        
        $bills = fetchAll($sql, [$today]);
        
        $results = [];
        foreach ($bills as $bill) {
            $customerName = $bill['first_name'] . ' ' . $bill['last_name'];
            $message = $this->sms->sendOverdueReminder(
                $customerName,
                $bill['bill_number'],
                $bill['total_amount'],
                $bill['due_date']
            );
            
            $result = $this->sms->sendSMS($bill['contact_number'], $message);
            $results[] = [
                'bill_id' => $bill['bill_id'],
                'customer' => $customerName,
                'phone' => $bill['contact_number'],
                'result' => $result
            ];
        }
        
        return $results;
    }
    
    /**
     * Send disconnection notices
     */
    public function sendDisconnectionNotices() {
        $disconnectionDays = SMS_DISCONNECTION_DAYS;
        $today = date('Y-m-d');
        $disconnectionDate = date('Y-m-d', strtotime("-{$disconnectionDays} days"));
        
        $sql = "SELECT b.*, c.first_name, c.last_name, c.contact_number, c.account_number
                FROM bills b
                JOIN customers c ON b.customer_id = c.customer_id
                WHERE b.status = 'pending' 
                AND b.due_date <= ?
                AND c.contact_number IS NOT NULL 
                AND c.contact_number != ''
                AND c.is_active = 1";
        
        $bills = fetchAll($sql, [$disconnectionDate]);
        
        $results = [];
        foreach ($bills as $bill) {
            $customerName = $bill['first_name'] . ' ' . $bill['last_name'];
            $message = $this->sms->sendDisconnectionNotice(
                $customerName,
                $bill['bill_number'],
                $bill['total_amount']
            );
            
            $result = $this->sms->sendSMS($bill['contact_number'], $message);
            $results[] = [
                'bill_id' => $bill['bill_id'],
                'customer' => $customerName,
                'phone' => $bill['contact_number'],
                'result' => $result
            ];
        }
        
        return $results;
    }
    
    /**
     * Send SMS to specific bill
     */
    public function sendBillSMS($billId, $type = 'due_date') {
        $sql = "SELECT b.*, c.first_name, c.last_name, c.contact_number, c.account_number
                FROM bills b
                JOIN customers c ON b.customer_id = c.customer_id
                WHERE b.bill_id = ?";
        
        $bill = fetchOne($sql, [$billId]);
        
        if (!$bill) {
            return ['success' => false, 'error' => 'Bill not found'];
        }
        
        if (empty($bill['contact_number'])) {
            return ['success' => false, 'error' => 'Customer contact number not available'];
        }
        
        $customerName = $bill['first_name'] . ' ' . $bill['last_name'];
        $message = '';
        
        switch ($type) {
            case 'due_date':
                $message = $this->sms->sendDueDateReminder(
                    $customerName,
                    $bill['bill_number'],
                    $bill['total_amount'],
                    $bill['due_date']
                );
                break;
            case 'overdue':
                $message = $this->sms->sendOverdueReminder(
                    $customerName,
                    $bill['bill_number'],
                    $bill['total_amount'],
                    $bill['due_date']
                );
                break;
            case 'disconnection':
                $message = $this->sms->sendDisconnectionNotice(
                    $customerName,
                    $bill['bill_number'],
                    $bill['total_amount']
                );
                break;
        }
        
        $result = $this->sms->sendSMS($bill['contact_number'], $message);
        
        return [
            'success' => $result['success'],
            'bill_id' => $billId,
            'customer' => $customerName,
            'phone' => $bill['contact_number'],
            'message' => $message,
            'result' => $result
        ];
    }
    
    /**
     * Get bills that need SMS notifications
     */
    public function getBillsForNotification($type = 'due_date') {
        $today = date('Y-m-d');
        
        switch ($type) {
            case 'due_date':
                $reminderDays = SMS_DUE_DATE_REMINDER_DAYS;
                $reminderDate = date('Y-m-d', strtotime("+{$reminderDays} days"));
                $sql = "SELECT b.*, c.first_name, c.last_name, c.contact_number, c.account_number
                        FROM bills b
                        JOIN customers c ON b.customer_id = c.customer_id
                        WHERE b.status = 'pending' 
                        AND b.due_date = ?
                        AND c.contact_number IS NOT NULL 
                        AND c.contact_number != ''
                        AND c.is_active = 1";
                $params = [$reminderDate];
                break;
                
            case 'overdue':
                $sql = "SELECT b.*, c.first_name, c.last_name, c.contact_number, c.account_number
                        FROM bills b
                        JOIN customers c ON b.customer_id = c.customer_id
                        WHERE b.status = 'pending' 
                        AND b.due_date < ?
                        AND c.contact_number IS NOT NULL 
                        AND c.contact_number != ''
                        AND c.is_active = 1";
                $params = [$today];
                break;
                
            case 'disconnection':
                $disconnectionDays = SMS_DISCONNECTION_DAYS;
                $disconnectionDate = date('Y-m-d', strtotime("-{$disconnectionDays} days"));
                $sql = "SELECT b.*, c.first_name, c.last_name, c.contact_number, c.account_number
                        FROM bills b
                        JOIN customers c ON b.customer_id = c.customer_id
                        WHERE b.status = 'pending' 
                        AND b.due_date <= ?
                        AND c.contact_number IS NOT NULL 
                        AND c.contact_number != ''
                        AND c.is_active = 1";
                $params = [$disconnectionDate];
                break;
        }
        
        return fetchAll($sql, $params);
    }
}
?>
