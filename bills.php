<?php
$page_title = 'Billing Management';
require_once 'includes/header.php';
require_once 'sms/functions/bill_notifications.php';
requireRole(['admin', 'cashier']);

$action = $_GET['action'] ?? 'list';
$bill_id = $_GET['id'] ?? null;
$sms_action = $_GET['sms_action'] ?? '';
$message = '';
$error = '';

// Initialize SMS notifications
$smsNotifications = new BillSMSNotifications();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        if ($action == 'generate') {
            $reading_id = (int)$_POST['reading_id'];
            
            if (empty($reading_id)) {
                $error = 'Please select a meter reading.';
            } else {
                // Get reading details
                $reading = fetchOne("
                    SELECT mr.*, c.*, cc.category_name, cc.base_rate
                    FROM meter_readings mr
                    JOIN customers c ON mr.customer_id = c.customer_id
                    JOIN customer_categories cc ON c.category_id = cc.category_id
                    WHERE mr.reading_id = ?
                ", [$reading_id]);
                
                if (!$reading) {
                    $error = 'Meter reading not found.';
                } else {
                    // Check if bill already exists for this reading
                    $existing_bill = fetchOne("SELECT bill_id FROM bills WHERE reading_id = ?", [$reading_id]);
                    
                    if ($existing_bill) {
                        $error = 'Bill already exists for this meter reading.';
                    } else {
                        // Calculate billing amounts
                        $consumption = $reading['consumption'];
                        $generation_rate = (float)getSystemSetting('generation_rate', 4.5000);
                        $distribution_rate = (float)getSystemSetting('distribution_rate', 1.2000);
                        $transmission_rate = (float)getSystemSetting('transmission_rate', 0.8000);
                        $system_loss_rate = (float)getSystemSetting('system_loss_rate', 0.5000);
                        $vat_rate = (float)getSystemSetting('vat_rate', 12) / 100;
                        
                        $generation_charge = $consumption * $generation_rate;
                        $distribution_charge = $consumption * $distribution_rate;
                        $transmission_charge = $consumption * $transmission_rate;
                        $system_loss_charge = $consumption * $system_loss_rate;
                        
                        $subtotal = $generation_charge + $distribution_charge + $transmission_charge + $system_loss_charge;
                        $vat = $subtotal * $vat_rate;
                        $total_amount = $subtotal + $vat;
                        
                        // Generate bill number
                        $bill_number = generateBillNumber();
                        
                        // Calculate due date
                        $due_days = (int)getSystemSetting('due_days', 15);
                        $due_date = date('Y-m-d', strtotime("+{$due_days} days"));
                        
                        // Calculate billing period
                        $billing_period_start = date('Y-m-01', strtotime($reading['reading_date']));
                        $billing_period_end = date('Y-m-t', strtotime($reading['reading_date']));
                        
                        $sql = "INSERT INTO bills (customer_id, reading_id, bill_number, billing_period_start, 
                                billing_period_end, consumption, generation_charge, distribution_charge, 
                                transmission_charge, system_loss_charge, vat, total_amount, due_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        
                        executeQuery($sql, [
                            $reading['customer_id'], $reading_id, $bill_number, $billing_period_start,
                            $billing_period_end, $consumption, $generation_charge, $distribution_charge,
                            $transmission_charge, $system_loss_charge, $vat, $total_amount, $due_date
                        ]);
                        
                        logActivity('Bill generated', 'bills', getLastInsertId());
                        $message = 'Bill generated successfully. Bill Number: ' . $bill_number;
                        $action = 'list';
                    }
                }
            }
        }
    }
}

// Handle SMS actions
if ($sms_action) {
    switch ($sms_action) {
        case 'send_due_reminder':
            if ($bill_id) {
                $result = $smsNotifications->sendBillSMS($bill_id, 'due_date');
                if ($result['success']) {
                    $message = 'Due date reminder sent successfully to ' . $result['customer'];
                } else {
                    $error = 'Failed to send SMS: ' . ($result['error'] ?? 'Unknown error');
                }
            }
            break;
            
        case 'send_overdue_reminder':
            if ($bill_id) {
                $result = $smsNotifications->sendBillSMS($bill_id, 'overdue');
                if ($result['success']) {
                    $message = 'Overdue reminder sent successfully to ' . $result['customer'];
                } else {
                    $error = 'Failed to send SMS: ' . ($result['error'] ?? 'Unknown error');
                }
            }
            break;
            
        case 'send_disconnection_notice':
            if ($bill_id) {
                $result = $smsNotifications->sendBillSMS($bill_id, 'disconnection');
                if ($result['success']) {
                    $message = 'Disconnection notice sent successfully to ' . $result['customer'];
                } else {
                    $error = 'Failed to send SMS: ' . ($result['error'] ?? 'Unknown error');
                }
            }
            break;
            
        case 'send_bulk_due_reminders':
            $results = $smsNotifications->sendDueDateReminders();
            $successCount = count(array_filter($results, function($r) { return $r['result']['success']; }));
            $message = "Bulk due date reminders sent: {$successCount} successful out of " . count($results) . " total";
            break;
            
        case 'send_bulk_overdue_reminders':
            $results = $smsNotifications->sendOverdueReminders();
            $successCount = count(array_filter($results, function($r) { return $r['result']['success']; }));
            $message = "Bulk overdue reminders sent: {$successCount} successful out of " . count($results) . " total";
            break;
            
        case 'send_bulk_disconnection_notices':
            $results = $smsNotifications->sendDisconnectionNotices();
            $successCount = count(array_filter($results, function($r) { return $r['result']['success']; }));
            $message = "Bulk disconnection notices sent: {$successCount} successful out of " . count($results) . " total";
            break;
    }
}

// Get bills list
if ($action == 'list') {
    $search = $_GET['search'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $customer_filter = $_GET['customer_id'] ?? '';
    
    $sql = "SELECT b.*, c.account_number, c.first_name, c.last_name, c.meter_number, 
                   mr.reading_date, mr.consumption
            FROM bills b
            JOIN customers c ON b.customer_id = c.customer_id
            JOIN meter_readings mr ON b.reading_id = mr.reading_id";
    $params = [];
    $conditions = [];
    
    if ($search) {
        $conditions[] = "(b.bill_number LIKE ? OR c.account_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
        $search_param = "%{$search}%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    if ($status_filter) {
        $conditions[] = "b.status = ?";
        $params[] = $status_filter;
    }
    
    if ($customer_filter) {
        $conditions[] = "b.customer_id = ?";
        $params[] = $customer_filter;
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY b.created_at DESC";
    
    $bills = fetchAll($sql, $params);
}

// Get customers for filter
$customers = fetchAll("SELECT customer_id, account_number, first_name, last_name 
                      FROM customers WHERE is_active = 1 ORDER BY last_name, first_name");

// Get unprocessed readings for bill generation
if ($action == 'generate') {
    $unprocessed_readings = fetchAll("
        SELECT mr.*, c.account_number, c.first_name, c.last_name, c.meter_number
        FROM meter_readings mr
        JOIN customers c ON mr.customer_id = c.customer_id
        LEFT JOIN bills b ON mr.reading_id = b.reading_id
        WHERE b.reading_id IS NULL
        ORDER BY mr.reading_date DESC
    ");
}
?>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($action == 'list'): ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-invoice me-2"></i>Billing Management
            </h5>
            <div>
                <a href="?action=generate" class="btn btn-success me-2">
                    <i class="fas fa-plus me-2"></i>Generate Bill
                </a>
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-sms me-2"></i>Send SMS
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="?action=list&sms_action=send_bulk_due_reminders">
                                <i class="fas fa-calendar-check me-2"></i>Due Date Reminders
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="?action=list&sms_action=send_bulk_overdue_reminders">
                                <i class="fas fa-exclamation-triangle me-2"></i>Overdue Reminders
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="?action=list&sms_action=send_bulk_disconnection_notices">
                                <i class="fas fa-power-off me-2"></i>Disconnection Notices
                            </a>
                        </li>
                    </ul>
                </div>
                <button class="btn btn-outline-primary" onclick="printBills()">
                    <i class="fas fa-print me-2"></i>Print Bills
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <form method="GET" class="d-flex">
                        <input type="hidden" name="action" value="list">
                        <input type="text" class="form-control me-2" name="search" 
                               placeholder="Search bills..." 
                               value="<?php echo htmlspecialchars($search ?? ''); ?>">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <select class="form-select" onchange="filterBills()">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo $status_filter == 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="overdue" <?php echo $status_filter == 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" onchange="filterBills()">
                        <option value="">All Customers</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['customer_id']; ?>" 
                                    <?php echo $customer_filter == $customer['customer_id'] ? 'selected' : ''; ?>>
                                <?php echo $customer['account_number'] . ' - ' . $customer['last_name'] . ', ' . $customer['first_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
            
            <!-- Bills Table -->
            <div class="table-responsive">
                <table class="table table-striped data-table">
                    <thead>
                        <tr>
                            <th>Bill #</th>
                            <th>Customer</th>
                            <th>Period</th>
                            <th>Consumption</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills as $bill): ?>
                        <tr>
                            <td><strong><?php echo $bill['bill_number']; ?></strong></td>
                            <td>
                                <strong><?php echo $bill['account_number']; ?></strong><br>
                                <small><?php echo $bill['last_name'] . ', ' . $bill['first_name']; ?></small>
                            </td>
                            <td>
                                <?php echo formatDate($bill['billing_period_start'], 'M d') . ' - ' . formatDate($bill['billing_period_end'], 'M d, Y'); ?>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo number_format($bill['consumption'], 2); ?> kWh
                                </span>
                            </td>
                            <td><strong><?php echo formatCurrency($bill['total_amount']); ?></strong></td>
                            <td><?php echo formatDate($bill['due_date'], 'M d, Y'); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                switch ($bill['status']) {
                                    case 'paid':
                                        $status_class = 'bg-success';
                                        break;
                                    case 'overdue':
                                        $status_class = 'bg-danger';
                                        break;
                                    case 'pending':
                                        $status_class = 'bg-warning';
                                        break;
                                    default:
                                        $status_class = 'bg-secondary';
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($bill['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="bill_details.php?id=<?php echo $bill['bill_id']; ?>" 
                                       class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="bill_print.php?id=<?php echo $bill['bill_id']; ?>" 
                                       class="btn btn-sm btn-outline-primary" target="_blank" title="Print Bill">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <?php if ($bill['status'] == 'pending'): ?>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle" 
                                                    data-bs-toggle="dropdown" title="Send SMS">
                                                <i class="fas fa-sms"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="?action=list&sms_action=send_due_reminder&id=<?php echo $bill['bill_id']; ?>">
                                                        <i class="fas fa-calendar-check me-2"></i>Due Date Reminder
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="?action=list&sms_action=send_overdue_reminder&id=<?php echo $bill['bill_id']; ?>">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>Overdue Reminder
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="?action=list&sms_action=send_disconnection_notice&id=<?php echo $bill['bill_id']; ?>">
                                                        <i class="fas fa-power-off me-2"></i>Disconnection Notice
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action == 'generate'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-plus me-2"></i>Generate New Bill
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($unprocessed_readings)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>No Unprocessed Readings</h5>
                    <p class="text-muted">All meter readings have been processed into bills.</p>
                    <a href="meter_readings.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Record New Reading
                    </a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="reading_id" class="form-label">Select Meter Reading *</label>
                        <select class="form-select" id="reading_id" name="reading_id" required>
                            <option value="">Select a meter reading to generate bill</option>
                            <?php foreach ($unprocessed_readings as $reading): ?>
                                <option value="<?php echo $reading['reading_id']; ?>">
                                    <?php echo $reading['account_number'] . ' - ' . $reading['last_name'] . ', ' . $reading['first_name']; ?>
                                    (<?php echo formatDate($reading['reading_date'], 'M d, Y'); ?> - 
                                    <?php echo number_format($reading['consumption'], 2); ?> kWh)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> The system will automatically calculate the bill amount based on current rates and consumption.
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="bills.php" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-file-invoice me-2"></i>Generate Bill
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script>
function filterBills() {
    const status = document.querySelector('select[onchange="filterBills()"]').value;
    const customer = document.querySelector('select[onchange="filterBills()"]').value;
    const search = document.querySelector('input[name="search"]').value;
    
    let url = 'bills.php?action=list';
    if (search) url += '&search=' + encodeURIComponent(search);
    if (status) url += '&status=' + status;
    if (customer) url += '&customer_id=' + customer;
    
    window.location.href = url;
}

function clearFilters() {
    window.location.href = 'bills.php?action=list';
}

function printBills() {
    // Implementation for bulk printing
    alert('Bulk printing functionality will be implemented');
}
</script>

<?php require_once 'includes/footer.php'; ?>
