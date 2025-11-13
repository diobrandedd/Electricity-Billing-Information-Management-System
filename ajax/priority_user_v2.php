<?php
/**
 * Priority User V2 AJAX Handler
 * Handles priority number operations for customers with categories
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/PriorityNumberGeneratorV2.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $priorityGenerator = new PriorityNumberGeneratorV2();
    
    switch ($action) {
        case 'get_queue_stats':
            // Get queue statistics for all categories
            $stats = $priorityGenerator->getQueueStatistics();
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;
            
        case 'check_existing_today':
            // Check if customer has existing priority number for today
            $customerId = (int)($_GET['customer_id'] ?? $_POST['customer_id'] ?? 0);
            
            if (!$customerId) {
                echo json_encode(['success' => false, 'error' => 'Customer ID required']);
                break;
            }
            
            // Get all active priority numbers for customer today
            $activePriorities = $priorityGenerator->getCustomerActivePriorityNumbers($customerId);
            
            // Organize by category
            $byCategory = [];
            foreach ($activePriorities as $priority) {
                $byCategory[$priority['category']] = $priority;
            }
            
            echo json_encode([
                'success' => true,
                'has_existing' => !empty($activePriorities),
                'active_priorities' => $byCategory,
                'all_active' => $activePriorities
            ]);
            break;
            
        case 'check_category_availability':
            // Check if customer can generate priority number for a specific category
            $customerId = (int)($_GET['customer_id'] ?? $_POST['customer_id'] ?? 0);
            $category = $_GET['category'] ?? $_POST['category'] ?? '';
            
            if (!$customerId || !$category) {
                echo json_encode(['success' => false, 'error' => 'Customer ID and category required']);
                break;
            }
            
            // Check for existing pending priority in this category
            $existing = $priorityGenerator->getCustomerPendingPriorityForCategory($customerId, $category);
            
            echo json_encode([
                'success' => true,
                'category' => $category,
                'has_existing' => !empty($existing),
                'existing_priority' => $existing
            ]);
            break;
            
        case 'generate_priority':
            // Generate new priority number with category
            $customerId = (int)($_POST['customer_id'] ?? 0);
            $category = $_POST['category'] ?? '';
            
            if (!$customerId || !$category) {
                echo json_encode(['success' => false, 'error' => 'Customer ID and category required']);
                break;
            }
            
            // Validate customer session
            if (!isset($_SESSION['customer_id']) || $_SESSION['customer_id'] != $customerId) {
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                break;
            }
            
            $result = $priorityGenerator->generatePriorityNumber($customerId, $category);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
