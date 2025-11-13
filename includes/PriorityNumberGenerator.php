<?php
/**
 * Priority Number Generator Class for SOCOTECO II Billing Management System
 * Handles priority number generation, queue management, and service day assignment
 */

require_once __DIR__ . '/../config/config.php';

class PriorityNumberGenerator {
    private $db;
    private $dailyCapacity;
    private $advanceDays;
    private $expiryHours;
    
    public function __construct() {
        $this->db = getDB();
        $this->dailyCapacity = (int)getSystemSetting('priority_daily_capacity', 1000);
        $this->advanceDays = (int)getSystemSetting('priority_advance_days', 7);
        $this->expiryHours = (int)getSystemSetting('priority_expiry_hours', 24);
    }
    
    /**
     * Generate a new priority number for a customer
     */
    public function generatePriorityNumber($customerId, $preferredDate = null) {
        try {
            $this->db->beginTransaction();
            
            // Validate customer exists and is active
            $customer = $this->validateCustomer($customerId);
            if (!$customer) {
                throw new Exception("Invalid customer ID");
            }
            
            // Check if customer already has a pending priority number
            $existingPriority = $this->getCustomerPendingPriority($customerId);
            if ($existingPriority) {
                throw new Exception("You already have a pending priority number: " . $existingPriority['priority_number']);
            }
            
            // Get next available priority number
            $priorityNumber = $this->getNextPriorityNumberInternal();
            
            // Calculate service date
            $serviceDate = $this->calculateServiceDate($priorityNumber, $preferredDate);
            
            // Check if service date is available
            if (!$this->isServiceDateAvailable($serviceDate)) {
                throw new Exception("Service date is not available. Please try again later.");
            }
            
            // Insert priority number
            $sql = "INSERT INTO priority_numbers (priority_number, customer_id, service_date, status) 
                    VALUES (?, ?, ?, 'pending')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$priorityNumber, $customerId, $serviceDate]);
            
            $priorityId = $this->db->lastInsertId();
            
            // Update service day count
            $this->updateServiceDayCount($serviceDate, 1);
            
            // Log the action
            $this->logPriorityAction($priorityId, 'generated', null, 'pending');
            
            $this->db->commit();
            
            $message = null;
            // Inform user if scheduled for tomorrow due to closing time
            if (date('Y-m-d') !== $serviceDate) {
                $message = 'Branch is currently closed. Your priority number is scheduled for tomorrow.';
            }
            return [
                'success' => true,
                'priority_number' => $priorityNumber,
                'service_date' => $serviceDate,
                'priority_id' => $priorityId,
                'estimated_wait_time' => $this->calculateEstimatedWaitTime($priorityNumber, $serviceDate),
                'message' => $message
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get the next available priority number
     */
    private function getNextPriorityNumberInternal() {
        $sql = "SELECT MAX(priority_number) as max_number FROM priority_numbers";
        $result = $this->db->query($sql)->fetch();
        return ($result['max_number'] ?? 0) + 1;
    }
    
    /**
     * Get the next available priority number (public method)
     */
    public function getNextPriorityNumber() {
        return $this->getNextPriorityNumberInternal();
    }
    
    /**
     * Calculate service date based on priority number and daily capacity
     */
    private function calculateServiceDate($priorityNumber, $preferredDate = null) {
        // If preferred date is provided and valid, try to use it
        if ($preferredDate) {
            $preferredDate = date('Y-m-d', strtotime($preferredDate));
            if ($this->isServiceDateAvailable($preferredDate)) {
                return $preferredDate;
            }
        }
        
        // Calculate which day this priority number falls on
        // Map priority numbers 1..dailyCapacity to today, next batch to tomorrow, etc.
        $dayOffset = (int)floor(($priorityNumber - 1) / $this->dailyCapacity);
        $serviceDate = date('Y-m-d', strtotime("+{$dayOffset} days"));

        // If current local time is 5:00 PM or later, move to next day
        $currentHour = (int)date('G'); // 0-23
        if ($currentHour >= 17) {
            $serviceDate = date('Y-m-d', strtotime($serviceDate . ' +1 day'));
        }
        
        // Ensure we don't exceed advance days limit
        $maxDate = date('Y-m-d', strtotime("+{$this->advanceDays} days"));
        if ($serviceDate > $maxDate) {
            throw new Exception("All available service days are fully booked. Please try again later.");
        }
        
        return $serviceDate;
    }
    
    /**
     * Check if a service date is available
     */
    private function isServiceDateAvailable($serviceDate) {
        // Check if it's not a weekend (optional - can be configured)
        $dayOfWeek = date('N', strtotime($serviceDate));
        if ($dayOfWeek > 5) { // Saturday = 6, Sunday = 7
            return false;
        }
        
        // Check current count for the date
        $sql = "SELECT current_count, max_capacity FROM service_days WHERE service_date = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$serviceDate]);
        $result = $stmt->fetch();
        
        if ($result) {
            return $result['current_count'] < $result['max_capacity'];
        } else {
            // Create new service day record
            $sql = "INSERT INTO service_days (service_date, max_capacity, current_count) VALUES (?, ?, 0)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$serviceDate, $this->dailyCapacity]);
            return true;
        }
    }
    
    /**
     * Update service day count
     */
    private function updateServiceDayCount($serviceDate, $increment) {
        $sql = "UPDATE service_days SET current_count = current_count + ? WHERE service_date = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$increment, $serviceDate]);
    }
    
    /**
     * Validate customer exists and is active
     */
    private function validateCustomer($customerId) {
        $sql = "SELECT * FROM customers WHERE customer_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetch();
    }
    
    /**
     * Get customer's pending priority number
     */
    private function getCustomerPendingPriority($customerId) {
        $sql = "SELECT * FROM priority_numbers 
                WHERE customer_id = ? AND status = 'pending' 
                ORDER BY generated_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetch();
    }
    
    /**
     * Calculate estimated wait time
     */
    private function calculateEstimatedWaitTime($priorityNumber, $serviceDate) {
        $dayNumber = ceil($priorityNumber / $this->dailyCapacity);
        $positionInDay = (($priorityNumber - 1) % $this->dailyCapacity) + 1;
        
        return [
            'day_number' => $dayNumber,
            'position_in_day' => $positionInDay,
            'estimated_serving_time' => $this->estimateServingTime($positionInDay)
        ];
    }
    
    /**
     * Estimate serving time based on position in queue
     */
    private function estimateServingTime($position) {
        // Assuming 5 minutes per customer on average
        $minutesPerCustomer = 5;
        $totalMinutes = $position * $minutesPerCustomer;
        
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } else {
            return "{$minutes} minutes";
        }
    }
    
    /**
     * Get current priority number being served
     */
    public function getCurrentPriorityNumber() {
        // Ensure a row exists for today; if not, insert a default one
        $sql = "SELECT current_priority_number, last_served_number, served_count, daily_capacity 
                FROM priority_queue_status 
                WHERE queue_date = CURDATE() AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        if (!$row) {
            $insert = $this->db->prepare("INSERT INTO priority_queue_status (current_priority_number, last_served_number, queue_date, daily_capacity, served_count) VALUES (0, 0, CURDATE(), ?, 0)");
            $insert->execute([$this->dailyCapacity]);
            // Re-read after insert
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch();
        }
        return $row;
    }
    
    /**
     * Update current priority number (for staff use)
     */
    public function updateCurrentPriorityNumber($newNumber, $userId = null) {
        try {
            $this->db->beginTransaction();
            
            // Validate the new number
            $sql = "SELECT * FROM priority_numbers WHERE priority_number = ? AND status = 'pending'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newNumber]);
            $priority = $stmt->fetch();
            
            if (!$priority) {
                throw new Exception("Invalid priority number");
            }
            
            // Update priority number status
            $sql = "UPDATE priority_numbers SET status = 'served', served_at = NOW(), served_by = ? 
                    WHERE priority_number = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $newNumber]);
            
            // Update queue status; ensure today's row exists
            $sql = "UPDATE priority_queue_status 
                    SET current_priority_number = ?, last_served_number = ?, served_count = served_count + 1 
                    WHERE queue_date = CURDATE() AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newNumber, $newNumber]);
            if ($stmt->rowCount() === 0) {
                $ins = $this->db->prepare("INSERT INTO priority_queue_status (current_priority_number, last_served_number, queue_date, daily_capacity, served_count, is_active) VALUES (?, ?, CURDATE(), ?, 1, 1)");
                $ins->execute([$newNumber, $newNumber, $this->dailyCapacity]);
            }
            
            // Log the action
            $this->logPriorityAction($priority['priority_id'], 'served', 'pending', 'served', $userId);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => "Priority number {$newNumber} has been served"
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get priority number details
     */
    public function getPriorityNumberDetails($priorityNumber) {
        $sql = "SELECT p.*, c.first_name, c.last_name, c.account_number, c.contact_number 
                FROM priority_numbers p 
                JOIN customers c ON p.customer_id = c.customer_id 
                WHERE p.priority_number = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$priorityNumber]);
        return $stmt->fetch();
    }
    
    /**
     * Get queue statistics
     */
    public function getQueueStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_pending,
                    COUNT(CASE WHEN service_date = CURDATE() THEN 1 END) as today_pending,
                    COUNT(CASE WHEN service_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 1 END) as tomorrow_pending
                FROM priority_numbers 
                WHERE status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats = $stmt->fetch();
        
        $current = $this->getCurrentPriorityNumber();
        
        return [
            'total_pending' => $stats['total_pending'],
            'today_pending' => $stats['today_pending'],
            'tomorrow_pending' => $stats['tomorrow_pending'],
            'current_serving' => $current['current_priority_number'] ?? 0,
            'served_today' => $current['served_count'] ?? 0,
            'daily_capacity' => $current['daily_capacity'] ?? $this->dailyCapacity
        ];
    }
    
    /**
     * Get upcoming priority numbers for today
     */
    public function getUpcomingPriorityNumbers($limit = 10) {
        $limit = (int)$limit;
        if ($limit <= 0) { $limit = 10; }
        $sql = "SELECT p.*, c.first_name, c.last_name, c.account_number, c.contact_number 
                FROM priority_numbers p 
                JOIN customers c ON p.customer_id = c.customer_id 
                WHERE p.service_date = CURDATE() AND p.status = 'pending' 
                ORDER BY p.priority_number ASC 
                LIMIT {$limit}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get current priority number with customer information
     */
    public function getCurrentPriorityWithCustomer() {
        $sql = "SELECT p.*, c.first_name, c.last_name, c.account_number, c.contact_number 
                FROM priority_numbers p 
                JOIN customers c ON p.customer_id = c.customer_id 
                WHERE p.priority_number = (
                    SELECT current_priority_number 
                    FROM priority_queue_status 
                    WHERE queue_date = CURDATE() AND is_active = 1
                ) AND p.status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Get next priority number with customer information
     */
    public function getNextPriorityWithCustomer() {
        $current = $this->getCurrentPriorityNumber();
        $nextNumber = ($current['current_priority_number'] ?? 0) + 1;
        
        $sql = "SELECT p.*, c.first_name, c.last_name, c.account_number, c.contact_number 
                FROM priority_numbers p 
                JOIN customers c ON p.customer_id = c.customer_id 
                WHERE p.priority_number = ? AND p.status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nextNumber]);
        return $stmt->fetch();
    }
    
    /**
     * Log priority number actions
     */
    private function logPriorityAction($priorityId, $action, $oldStatus = null, $newStatus = null, $userId = null) {
        $sql = "INSERT INTO priority_number_history 
                (priority_id, action, old_status, new_status, user_id, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $priorityId,
            $action,
            $oldStatus,
            $newStatus,
            $userId,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
    
    /**
     * Cancel a priority number
     */
    public function cancelPriorityNumber($priorityNumber, $reason = null) {
        try {
            $this->db->beginTransaction();
            
            $sql = "SELECT * FROM priority_numbers WHERE priority_number = ? AND status = 'pending'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$priorityNumber]);
            $priority = $stmt->fetch();
            
            if (!$priority) {
                throw new Exception("Priority number not found or already processed");
            }
            
            // Update status to cancelled
            $sql = "UPDATE priority_numbers SET status = 'cancelled', notes = ? WHERE priority_number = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reason, $priorityNumber]);
            
            // Update service day count
            $this->updateServiceDayCount($priority['service_date'], -1);
            
            // Log the action
            $this->logPriorityAction($priority['priority_id'], 'cancelled', 'pending', 'cancelled');
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => "Priority number {$priorityNumber} has been cancelled"
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get customer's priority number history
     */
    public function getCustomerPriorityHistory($customerId, $limit = 10) {
        $sql = "SELECT * FROM priority_numbers 
                WHERE customer_id = ? 
                ORDER BY generated_at DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $limit]);
        return $stmt->fetchAll();
    }
}
?>
