<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

header('Content-Type: application/json');

$customer_id = $_GET['customer_id'] ?? null;

if (!$customer_id) {
    echo json_encode(['success' => false, 'message' => 'Customer ID required']);
    exit;
}

try {
    $sql = "SELECT current_reading FROM meter_readings 
            WHERE customer_id = ? 
            ORDER BY current_reading DESC, created_at DESC 
            LIMIT 1";
    
    $result = fetchOne($sql, [$customer_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'previous_reading' => number_format($result['current_reading'], 2)
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'previous_reading' => '0.00'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
