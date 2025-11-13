<?php
/**
 * Priority Number Generator Class V2 for SOCOTECO II Billing Management System
 * Handles priority number generation with categories, queue management, and service day assignment
 */

require_once __DIR__ . '/../config/config.php';

class PriorityNumberGeneratorV2 {
    private $db;
    private $dailyCapacityPerCategory;
    private $timerInterval;
    private $lunchBreakStart;
    private $lunchBreakEnd;
    
    public function __construct() {
        $this->db = getDB();
        $this->dailyCapacityPerCategory = (int)getSystemSetting('priority_daily_capacity_per_category', 500);
        $this->timerInterval = (int)getSystemSetting('priority_timer_interval', 5);
        $this->lunchBreakStart = getSystemSetting('priority_lunch_start', '12:00');
        $this->lunchBreakEnd = getSystemSetting('priority_lunch_end', '13:00');
    }
    
    /**
     * Generate a new priority number for a customer with category
     */
    public function generatePriorityNumber($customerId, $category, $preferredDate = null) {
        try {
            $this->db->beginTransaction();
            
            // Validate category
            if (!in_array($category, ['payment', 'claims', 'registration'])) {
                throw new Exception("Invalid category");
            }
            
            // Check time restrictions (6 AM - 6 PM)
            // TEMPORARILY DISABLED FOR TESTING - Remove comments to re-enable
            /*
            $currentTime = date('H:i');
            $currentHour = (int)date('H');
            if ($currentHour < 6 || $currentHour >= 18) {
                throw new Exception("Priority numbers can only be generated between 6:00 AM and 6:00 PM");
            }
            */
            
            // Validate customer exists and is active
            $customer = $this->validateCustomer($customerId);
            if (!$customer) {
                throw new Exception("Invalid customer ID");
            }
            
            // Check if customer already has a pending priority number for THIS SPECIFIC CATEGORY today
            $existingPriority = $this->getCustomerPendingPriorityForCategory($customerId, $category);
            if ($existingPriority) {
                throw new Exception("You already have a pending priority number for {$category} queue today: " . $existingPriority['priority_number']);
            }
            
            // Get service date (today only)
            $serviceDate = date('Y-m-d');
            
            // Check if category has reached daily capacity
            if (!$this->isCategoryAvailable($category, $serviceDate)) {
                throw new Exception("Daily capacity for {$category} queue has been reached. Please try again tomorrow.");
            }
            
            // Get next available priority number for category
            $priorityNumber = $this->getNextPriorityNumberForCategory($category, $serviceDate);
            
            // Insert priority number
            $sql = "INSERT INTO priority_numbers (priority_number, category, customer_id, service_date, status) 
                    VALUES (?, ?, ?, ?, 'pending')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$priorityNumber, $category, $customerId, $serviceDate]);
            
            $priorityId = $this->db->lastInsertId();
            
            // Update queue status count
            $this->updateQueueStatusCount($category, $serviceDate, 1);
            
            // Log the action
            $this->logPriorityAction($priorityId, 'generated', null, 'pending');
            
            $this->db->commit();
            
            return [
                'success' => true,
                'priority_number' => $priorityNumber,
                'category' => $category,
                'service_date' => $serviceDate,
                'customer_name' => $customer['first_name'] . ' ' . $customer['last_name']
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get next priority number for a specific category and date
     */
    private function getNextPriorityNumberForCategory($category, $serviceDate) {
        // Get the last number for this category and date
        $sql = "SELECT priority_number FROM priority_numbers 
                WHERE category = ? AND service_date = ? 
                ORDER BY priority_id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category, $serviceDate]);
        $lastNumber = $stmt->fetchColumn();
        
        if ($lastNumber) {
            // Extract number from format like P001, C001, R001
            $number = (int)substr($lastNumber, 1);
            $nextNumber = $number + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Check if we've reached daily capacity
        if ($nextNumber > $this->dailyCapacityPerCategory) {
            throw new Exception("Daily capacity for {$category} queue has been reached");
        }
        
        // Format the number with category prefix
        $categoryPrefix = strtoupper(substr($category, 0, 1));
        return $categoryPrefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Check if category is available (hasn't reached daily capacity)
     */
    private function isCategoryAvailable($category, $serviceDate) {
        $sql = "SELECT COUNT(*) FROM priority_numbers 
                WHERE category = ? AND service_date = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category, $serviceDate]);
        $count = $stmt->fetchColumn();
        
        return $count < $this->dailyCapacityPerCategory;
    }
    
    /**
     * Get current priority number being served for a category
     */
    public function getCurrentPriorityNumber($category, $serviceDate = null) {
        if (!$serviceDate) {
            $serviceDate = date('Y-m-d');
        }
        
        // First, get the current priority number from queue status
        $statusSql = "SELECT current_priority_number FROM priority_queue_status 
                      WHERE category = ? AND queue_date = ? AND is_active = 1";
        $statusStmt = $this->db->prepare($statusSql);
        $statusStmt->execute([$category, $serviceDate]);
        $currentNumber = $statusStmt->fetchColumn();
        
        if (!$currentNumber) {
            return false;
        }
        
        // Get the priority number details with customer info
        // Use LEFT JOIN to handle cases where customer might be deleted/inactive
        $sql = "SELECT p.*, c.first_name, c.last_name, c.account_number 
                FROM priority_numbers p 
                LEFT JOIN customers c ON p.customer_id = c.customer_id 
                WHERE p.priority_number = ? AND p.category = ? AND p.service_date = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$currentNumber, $category, $serviceDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return false;
        }
        
        // If customer data is missing, try to fetch it directly
        if (!$result['first_name'] || !$result['last_name']) {
            $customerSql = "SELECT first_name, last_name, account_number FROM customers WHERE customer_id = ?";
            $customerStmt = $this->db->prepare($customerSql);
            $customerStmt->execute([$result['customer_id']]);
            $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($customer) {
                $result['first_name'] = $customer['first_name'];
                $result['last_name'] = $customer['last_name'];
                $result['account_number'] = $customer['account_number'];
            }
        }
        
        return $result;
    }
    
    /**
     * Get next priority numbers in queue for a category
     */
    public function getNextPriorityNumbers($category, $serviceDate = null, $limit = 3) {
        if (!$serviceDate) {
            $serviceDate = date('Y-m-d');
        }
        
        $sql = "SELECT p.*, c.first_name, c.last_name, c.account_number 
                FROM priority_numbers p 
                JOIN customers c ON p.customer_id = c.customer_id 
                WHERE p.category = ? AND p.service_date = ? AND p.status = 'pending'
                ORDER BY p.priority_id ASC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category, $serviceDate, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get previously served priority numbers for a category
     * Excludes the current priority number being served
     */
    public function getPreviousPriorityNumbers($category, $serviceDate = null, $limit = 1) {
        if (!$serviceDate) {
            $serviceDate = date('Y-m-d');
        }
        
        // Get the current priority number to exclude it from previous numbers
        $statusSql = "SELECT current_priority_number FROM priority_queue_status 
                      WHERE category = ? AND queue_date = ? AND is_active = 1";
        $statusStmt = $this->db->prepare($statusSql);
        $statusStmt->execute([$category, $serviceDate]);
        $currentNumber = $statusStmt->fetchColumn();
        
        // Build query - exclude current number if it exists
        $sql = "SELECT p.*, c.first_name, c.last_name, c.account_number 
                FROM priority_numbers p 
                JOIN customers c ON p.customer_id = c.customer_id 
                WHERE p.category = ? AND p.service_date = ? AND p.status = 'served'";
        
        $params = [$category, $serviceDate];
        
        // Exclude current number if it exists
        if ($currentNumber) {
            $sql .= " AND p.priority_number != ?";
            $params[] = $currentNumber;
        }
        
        $sql .= " ORDER BY p.served_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Serve the next priority number for a category
     */
    public function serveNextPriorityNumber($category, $userId, $serviceDate = null) {
        if (!$serviceDate) {
            $serviceDate = date('Y-m-d');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Get the next pending priority number
            $sql = "SELECT * FROM priority_numbers 
                    WHERE category = ? AND service_date = ? AND status = 'pending'
                    ORDER BY priority_id ASC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$category, $serviceDate]);
            $priority = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$priority) {
                throw new Exception("No pending priority numbers in queue");
            }
            
            // Update priority number status to served
            $updateSql = "UPDATE priority_numbers 
                          SET status = 'served', served_at = NOW(), served_by = ?
                          WHERE priority_id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([$userId, $priority['priority_id']]);
            
            // Update queue status
            $this->updateQueueStatusCurrent($category, $serviceDate, $priority['priority_number']);
            
            // Log the action
            $this->logPriorityAction($priority['priority_id'], 'served', 'pending', 'served', $userId);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'priority_number' => $priority['priority_number'],
                'customer_id' => $priority['customer_id']
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Skip the current priority number for a category
     * This skips the currently displayed/served priority number, not the next one
     */
    public function skipCurrentPriorityNumber($category, $userId, $serviceDate = null) {
        if (!$serviceDate) {
            $serviceDate = date('Y-m-d');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Get the CURRENT priority number being served (from queue status)
            $statusSql = "SELECT current_priority_number FROM priority_queue_status 
                          WHERE category = ? AND queue_date = ? AND is_active = 1";
            $statusStmt = $this->db->prepare($statusSql);
            $statusStmt->execute([$category, $serviceDate]);
            $currentNumber = $statusStmt->fetchColumn();
            
            if (!$currentNumber) {
                throw new Exception("No priority number is currently being served for this category");
            }
            
            // Get the priority number record
            $sql = "SELECT * FROM priority_numbers 
                    WHERE priority_number = ? AND category = ? AND service_date = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$currentNumber, $category, $serviceDate]);
            $priority = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$priority) {
                throw new Exception("Priority number not found");
            }
            
            // Don't skip if already skipped or cancelled
            if ($priority['status'] === 'skipped' || $priority['status'] === 'cancelled') {
                throw new Exception("Current priority number cannot be skipped (status: {$priority['status']})");
            }
            
            // Update priority number status to skipped
            $updateSql = "UPDATE priority_numbers 
                          SET status = 'skipped', served_at = NOW(), served_by = ?
                          WHERE priority_id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([$userId, $priority['priority_id']]);
            
            // Log the action
            $this->logPriorityAction($priority['priority_id'], 'skipped', $priority['status'], 'skipped', $userId);
            
            // Automatically serve the next pending number (if available)
            $nextPriority = $this->getNextPendingPriority($category, $serviceDate);
            if ($nextPriority) {
                // Update the next priority to served
                $nextUpdateSql = "UPDATE priority_numbers 
                                  SET status = 'served', served_at = NOW(), served_by = ?
                                  WHERE priority_id = ?";
                $nextUpdateStmt = $this->db->prepare($nextUpdateSql);
                $nextUpdateStmt->execute([$userId, $nextPriority['priority_id']]);
                
                // Update queue status to the next number
                $this->updateQueueStatusCurrent($category, $serviceDate, $nextPriority['priority_number']);
                
                // Log the action
                $this->logPriorityAction($nextPriority['priority_id'], 'served', 'pending', 'served', $userId);
            } else {
                // No more pending numbers - clear current_priority_number
                $clearSql = "UPDATE priority_queue_status 
                            SET current_priority_number = NULL
                            WHERE category = ? AND queue_date = ?";
                $clearStmt = $this->db->prepare($clearSql);
                $clearStmt->execute([$category, $serviceDate]);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'priority_number' => $priority['priority_number'],
                'skipped_number' => $priority['priority_number'],
                'next_number' => $nextPriority ? $nextPriority['priority_number'] : null
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get the next pending priority number for a category
     */
    private function getNextPendingPriority($category, $serviceDate) {
        $sql = "SELECT * FROM priority_numbers 
                WHERE category = ? AND service_date = ? AND status = 'pending'
                ORDER BY priority_id ASC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category, $serviceDate]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if system is in lunch break
     */
    public function isLunchBreak() {
        $currentTime = date('H:i');
        return $currentTime >= $this->lunchBreakStart && $currentTime < $this->lunchBreakEnd;
    }
    
    /**
     * Get queue statistics for all categories
     */
    public function getQueueStatistics($serviceDate = null) {
        if (!$serviceDate) {
            $serviceDate = date('Y-m-d');
        }
        
        $categories = ['payment', 'claims', 'registration'];
        $stats = [];
        
        foreach ($categories as $category) {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'served' THEN 1 ELSE 0 END) as served,
                        SUM(CASE WHEN status = 'skipped' THEN 1 ELSE 0 END) as skipped,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                    FROM priority_numbers 
                    WHERE category = ? AND service_date = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$category, $serviceDate]);
            $categoryStats = $stmt->fetch(PDO::FETCH_ASSOC);
            $categoryStats['category'] = $category;
            $categoryStats['capacity'] = $this->dailyCapacityPerCategory;
            $stats[$category] = $categoryStats;
        }
        
        return $stats;
    }
    
    /**
     * Validate customer exists and is active
     */
    private function validateCustomer($customerId) {
        $sql = "SELECT * FROM customers WHERE customer_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer's pending priority for a specific category today
     * Only checks for PENDING status (not served), allowing customers to generate new numbers after being served
     */
    public function getCustomerPendingPriorityForCategory($customerId, $category) {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM priority_numbers 
                WHERE customer_id = ? AND category = ? AND service_date = ? AND status = 'pending'
                ORDER BY priority_id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $category, $today]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer's pending priority for today (for backward compatibility)
     * @deprecated Use getCustomerPendingPriorityForCategory instead
     */
    private function getCustomerPendingPriorityToday($customerId) {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM priority_numbers 
                WHERE customer_id = ? AND service_date = ? AND status = 'pending'
                ORDER BY priority_id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $today]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all active priority numbers for a customer today (one per category)
     */
    public function getCustomerActivePriorityNumbers($customerId) {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM priority_numbers 
                WHERE customer_id = ? AND service_date = ? AND status = 'pending'
                ORDER BY category, priority_id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $today]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update queue status count
     */
    private function updateQueueStatusCount($category, $serviceDate, $increment) {
        $sql = "INSERT INTO priority_queue_status (category, queue_date, daily_capacity, served_count, timer_interval, lunch_break_start, lunch_break_end)
                VALUES (?, ?, ?, 0, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                served_count = served_count + ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category, $serviceDate, $this->dailyCapacityPerCategory, $this->timerInterval, $this->lunchBreakStart, $this->lunchBreakEnd, $increment]);
    }
    
    /**
     * Update queue status current number
     */
    private function updateQueueStatusCurrent($category, $serviceDate, $priorityNumber) {
        $sql = "UPDATE priority_queue_status 
                SET current_priority_number = ?, last_served_number = ?, served_count = served_count + 1
                WHERE category = ? AND queue_date = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$priorityNumber, $priorityNumber, $category, $serviceDate]);
    }
    
    /**
     * Log priority action
     */
    private function logPriorityAction($priorityId, $action, $oldStatus = null, $newStatus = null, $userId = null) {
        $sql = "INSERT INTO priority_number_history (priority_id, action, old_status, new_status, user_id, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $priorityId, 
            $action, 
            $oldStatus, 
            $newStatus, 
            $userId, 
            $_SERVER['REMOTE_ADDR'] ?? null, 
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
}
?>
