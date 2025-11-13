<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Require customer session
if (!isset($_SESSION['customer_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$customer_id = (int)$_SESSION['customer_id'];

// Handle date range parameters
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

try {
    // Get current meter reading (latest reading from meter_readings table)
    // Get the highest current_reading value (most recent cumulative reading)
    $current_reading = fetchOne("SELECT current_reading, reading_date, created_at FROM meter_readings 
                                 WHERE customer_id = ? 
                                 ORDER BY current_reading DESC, created_at DESC 
                                 LIMIT 1", [$customer_id]);
    $current_reading_value = $current_reading ? $current_reading['current_reading'] : 0;
    
    // Debug: Log the current reading found (uncomment to debug)
    // error_log("Current reading for customer $customer_id: " . $current_reading_value . " (Date: " . $current_reading['reading_date'] . ", Created: " . $current_reading['created_at'] . ")");
    
    // Temporary debug: Get all readings for this customer to see what's in the database
    $all_readings = fetchAll("SELECT reading_id, reading_date, current_reading, consumption, created_at 
                              FROM meter_readings 
                              WHERE customer_id = ? 
                              ORDER BY current_reading DESC", [$customer_id]);
    // error_log("All readings for customer $customer_id: " . json_encode($all_readings));

    // Build query based on date range or default to last 6 months
    if ($start_date && $end_date) {
        $bills = fetchAll("SELECT bill_id, bill_number, billing_period_start, billing_period_end, consumption, total_amount
                           FROM bills
                           WHERE customer_id = ? 
                           AND billing_period_end >= ? 
                           AND billing_period_end <= ?
                           ORDER BY billing_period_end DESC", [$customer_id, $start_date, $end_date]);
    } else {
        // Default to last 6 months if no date range specified
        $bills = fetchAll("SELECT bill_id, bill_number, billing_period_start, billing_period_end, consumption, total_amount
                           FROM bills
                           WHERE customer_id = ?
                           AND billing_period_end >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                           ORDER BY billing_period_end DESC", [$customer_id]);
    }
    
    // Debug: Log the number of bills found (uncomment for debugging)
    // error_log("Analytics: Found " . count($bills) . " bills for customer $customer_id");

    // Prepare data in chronological order
    $bills = array_reverse($bills);

    $months = [];
    $kwh = [];
    $billAmounts = [];
    $changes = []; // Percentage changes in consumption
    $billChanges = []; // Bill amount changes
    $recent = [];

    foreach ($bills as $index => $b) {
        $label = date('M Y', strtotime($b['billing_period_end']));
        $months[] = $label;
        $kwh[] = (float)$b['consumption'];
        $billAmounts[] = (float)$b['total_amount'];

        // Calculate percentage change from previous month
        $change = 0;
        if ($index > 0) {
            $prevKwh = $kwh[$index - 1];
            $currentKwh = (float)$b['consumption'];
            if ($prevKwh > 0) {
                $change = (($currentKwh - $prevKwh) / $prevKwh) * 100;
            }
        }
        $changes[] = round($change, 1);

        // Calculate bill change from previous month
        $billChange = 0;
        if ($index > 0) {
            $prevBill = $billAmounts[$index - 1];
            $currentBill = (float)$b['total_amount'];
            $billChange = $currentBill - $prevBill;
        }
        $billChanges[] = round($billChange, 2);

        $recent[] = [
            'bill_id' => (int)$b['bill_id'],
            'bill_number' => $b['bill_number'],
            'period' => date('M d', strtotime($b['billing_period_start'])) . ' - ' . date('M d, Y', strtotime($b['billing_period_end'])),
            'amount' => (float)$b['total_amount'],
            'consumption' => (float)$b['consumption'],
            'change' => round($change, 1),
            'billChange' => round($billChange, 2)
        ];
    }

    $count = max(count($kwh), 1);
    $avg_kwh = $count ? array_sum($kwh) / $count : 0;
    $avg_bill = $count ? array_sum($billAmounts) / $count : 0;
    $last_kwh = $count ? end($kwh) : 0;
    $last_bill = $count ? end($billAmounts) : 0;

    $response = [
        'kpis' => [
            'avg_kwh' => round($avg_kwh, 2),
            'avg_bill' => round($avg_bill, 2),
            'last_kwh' => round($last_kwh, 2),
            'last_bill' => round($last_bill, 2)
        ],
        'months' => $months,
        'kwh' => $kwh,
        'bills' => $billAmounts,
        'changes' => $changes,
        'bill_changes' => $billChanges,
        'current_reading' => round($current_reading_value, 2),
        'debug_readings' => $all_readings, // Temporary debug - remove this later
        'recent' => array_reverse($recent) // most recent first in UI
    ];

    // Debug: Log the response data (uncomment for debugging)
    // error_log("Analytics response: " . json_encode($response));
    
    echo json_encode($response);
} catch (Exception $e) {
    error_log("Analytics error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

?>


