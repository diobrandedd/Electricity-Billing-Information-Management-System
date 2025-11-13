<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/sms_config.php';

use AndroidSmsGateway\Client;
use AndroidSmsGateway\Domain\Message;

class SMSNotification {
    private $client;
    private $logFile;
    
    public function __construct() {
        $this->client = new Client(SMS_USERNAME, SMS_PASSWORD, SMS_SERVER_URL);
        $this->logFile = SMS_LOG_FILE;
    }
    
    /**
     * Send SMS notification
     */
    public function sendSMS($phoneNumber, $message) {
        if (!SMS_ENABLED) {
            $this->log("SMS is disabled");
            return false;
        }
        
        try {
            // Format phone number
            $formattedNumber = $this->formatPhoneNumber($phoneNumber);
            
            if (!$formattedNumber) {
                $this->log("Invalid phone number: " . $phoneNumber);
                return false;
            }
            
            // Create message
            $smsMessage = new Message($message, [$formattedNumber]);
            
            // Send message
            $messageState = $this->client->Send($smsMessage);
            
            $this->log("SMS sent successfully. ID: " . $messageState->ID() . " to: " . $formattedNumber);
            
            return [
                'success' => true,
                'message_id' => $messageState->ID(),
                'phone' => $formattedNumber
            ];
            
        } catch (Exception $e) {
            $this->log("Error sending SMS: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phoneNumber) {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If it starts with 63, remove it
        if (substr($phoneNumber, 0, 2) == '63') {
            $phoneNumber = substr($phoneNumber, 2);
        }
        
        // If it starts with 0, remove it
        if (substr($phoneNumber, 0, 1) == '0') {
            $phoneNumber = substr($phoneNumber, 1);
        }
        
        // Check if it's a valid Philippine mobile number (starts with 9 and is 10 digits)
        if (strlen($phoneNumber) == 10 && substr($phoneNumber, 0, 1) == '9') {
            return SMS_COUNTRY_CODE . $phoneNumber;
        }
        
        // Log invalid phone number for debugging
        $this->log("Invalid phone number format: " . $phoneNumber . " (length: " . strlen($phoneNumber) . ")");
        
        return false;
    }
    
    /**
     * Send due date reminder
     */
    public function sendDueDateReminder($customerName, $billNumber, $amount, $dueDate) {
        $message = str_replace(
            ['{customer_name}', '{bill_number}', '{amount}', '{due_date}'],
            [$customerName, $billNumber, $this->formatCurrency($amount), $this->formatDate($dueDate)],
            SMS_DUE_DATE_TEMPLATE
        );
        
        return $message;
    }
    
    /**
     * Send overdue reminder
     */
    public function sendOverdueReminder($customerName, $billNumber, $amount, $dueDate) {
        $message = str_replace(
            ['{customer_name}', '{bill_number}', '{amount}', '{due_date}'],
            [$customerName, $billNumber, $this->formatCurrency($amount), $this->formatDate($dueDate)],
            SMS_OVERDUE_TEMPLATE
        );
        
        return $message;
    }
    
    /**
     * Send disconnection notice
     */
    public function sendDisconnectionNotice($customerName, $billNumber, $amount) {
        $message = str_replace(
            ['{customer_name}', '{bill_number}', '{amount}'],
            [$customerName, $billNumber, $this->formatCurrency($amount)],
            SMS_DISCONNECTION_TEMPLATE
        );
        
        return $message;
    }
    
    /**
     * Format currency
     */
    private function formatCurrency($amount) {
        return '₱' . number_format($amount, 2);
    }
    
    /**
     * Format date
     */
    private function formatDate($date) {
        return date('M d, Y', strtotime($date));
    }
    
    /**
     * Log SMS activities
     */
    private function log($message) {
        if (SMS_LOG_ENABLED) {
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[$timestamp] $message" . PHP_EOL;
            
            // Ensure log directory exists
            $logDir = dirname($this->logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Get message status
     */
    public function getMessageStatus($messageId) {
        try {
            $messageState = $this->client->GetState($messageId);
            return $messageState->State();
        } catch (Exception $e) {
            $this->log("Error getting message status: " . $e->getMessage());
            return false;
        }
    }
}
?>
