<?php
$page_title = 'Meter Readings';
require_once 'includes/header.php';
requireRole(['admin', 'meter_reader']);

$action = $_GET['action'] ?? 'list';
$reading_id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        if ($action == 'add' || $action == 'edit') {
            $customer_id = (int)$_POST['customer_id'];
            $reading_date = $_POST['reading_date'];
            $current_reading = (float)$_POST['current_reading'];
            $reading_type = $_POST['reading_type'];
            $notes = sanitizeInput($_POST['notes']);
            
            // Validation
            if (empty($customer_id) || empty($reading_date) || $current_reading < 0) {
                $error = 'Please fill in all required fields with valid values.';
            } else {
                // Get previous reading (latest cumulative reading)
                $prev_reading_sql = "SELECT current_reading FROM meter_readings 
                                   WHERE customer_id = ? 
                                   ORDER BY current_reading DESC, created_at DESC 
                                   LIMIT 1";
                $prev_reading_result = fetchOne($prev_reading_sql, [$customer_id]);
                $previous_reading = $prev_reading_result ? $prev_reading_result['current_reading'] : 0;
                
                // Calculate consumption
                $consumption = max(0, $current_reading - $previous_reading);
                
                if ($action == 'add') {
                    // Check if user_id exists in users table
                    $meter_reader_id = null;
                    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
                        $user_check = fetchOne("SELECT user_id FROM users WHERE user_id = ?", [$_SESSION['user_id']]);
                        if ($user_check) {
                            $meter_reader_id = $_SESSION['user_id'];
                        }
                    }
                    
                    $sql = "INSERT INTO meter_readings (customer_id, reading_date, previous_reading, 
                            current_reading, consumption, reading_type, meter_reader_id, notes) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    executeQuery($sql, [
                        $customer_id, $reading_date, $previous_reading, $current_reading, 
                        $consumption, $reading_type, $meter_reader_id, $notes
                    ]);
                    
                    logActivity('Meter reading created', 'meter_readings', getLastInsertId());
                    $message = 'Meter reading recorded successfully.';
                    $action = 'list';
                } else {
                    $sql = "UPDATE meter_readings SET customer_id = ?, reading_date = ?, 
                            previous_reading = ?, current_reading = ?, consumption = ?, 
                            reading_type = ?, notes = ? WHERE reading_id = ?";
                    
                    executeQuery($sql, [
                        $customer_id, $reading_date, $previous_reading, $current_reading, 
                        $consumption, $reading_type, $notes, $reading_id
                    ]);
                    
                    logActivity('Meter reading updated', 'meter_readings', $reading_id);
                    $message = 'Meter reading updated successfully.';
                    $action = 'list';
                }
            }
        }
    }
}

// Get reading data for editing
$reading = null;
if ($action == 'edit' && $reading_id) {
    $reading = fetchOne("SELECT * FROM meter_readings WHERE reading_id = ?", [$reading_id]);
    if (!$reading) {
        $error = 'Meter reading not found.';
        $action = 'list';
    }
}

// Get customers list
$customers = fetchAll("SELECT customer_id, account_number, first_name, last_name, meter_number 
                      FROM customers WHERE is_active = 1 ORDER BY last_name, first_name");

// Get readings list
if ($action == 'list') {
    $search = $_GET['search'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    
    $sql = "SELECT mr.*, c.account_number, c.first_name, c.last_name, c.meter_number, u.full_name as reader_name
            FROM meter_readings mr
            JOIN customers c ON mr.customer_id = c.customer_id
            LEFT JOIN users u ON mr.meter_reader_id = u.user_id";
    $params = [];
    $conditions = [];
    
    if ($search) {
        $conditions[] = "(c.account_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.meter_number LIKE ?)";
        $search_param = "%{$search}%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    if ($date_from) {
        $conditions[] = "mr.reading_date >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $conditions[] = "mr.reading_date <= ?";
        $params[] = $date_to;
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY mr.reading_date DESC, mr.reading_id DESC";
    
    $readings = fetchAll($sql, $params);
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
                <i class="fas fa-tachometer me-2"></i>Meter Readings
            </h5>
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Record Reading
            </a>
        </div>
        <div class="card-body">
            <!-- Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <form method="GET" class="d-flex">
                        <input type="hidden" name="action" value="list">
                        <input type="text" class="form-control me-2" name="search" 
                               placeholder="Search by customer..." 
                               value="<?php echo htmlspecialchars($search ?? ''); ?>">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date_from" 
                           placeholder="From Date" value="<?php echo $date_from ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date_to" 
                           placeholder="To Date" value="<?php echo $date_to ?? ''; ?>">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="filterReadings()">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>
            
            <!-- Readings Table -->
            <div class="table-responsive">
                <table class="table table-striped data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Meter #</th>
                            <th>Previous</th>
                            <th>Current</th>
                            <th>Consumption</th>
                            <th>Type</th>
                            <th>Reader</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($readings as $reading): ?>
                        <tr>
                            <td><?php echo formatDate($reading['reading_date'], 'M d, Y'); ?></td>
                            <td>
                                <strong><?php echo $reading['account_number']; ?></strong><br>
                                <small><?php echo $reading['last_name'] . ', ' . $reading['first_name']; ?></small>
                            </td>
                            <td><?php echo $reading['meter_number']; ?></td>
                            <td><?php echo number_format($reading['previous_reading'], 2); ?></td>
                            <td><strong><?php echo number_format($reading['current_reading'], 2); ?></strong></td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo number_format($reading['consumption'], 2); ?> kWh
                                </span>
                            </td>
                            <td>
                                <?php
                                $type_class = '';
                                switch ($reading['reading_type']) {
                                    case 'actual':
                                        $type_class = 'bg-success';
                                        break;
                                    case 'estimated':
                                        $type_class = 'bg-warning';
                                        break;
                                    case 'adjusted':
                                        $type_class = 'bg-info';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $type_class; ?>">
                                    <?php echo ucfirst($reading['reading_type']); ?>
                                </span>
                            </td>
                            <td><?php echo $reading['reader_name'] ?? 'N/A'; ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="?action=edit&id=<?php echo $reading['reading_id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action == 'add' || $action == 'edit'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-tachometer me-2"></i>
                <?php echo $action == 'add' ? 'Record New Meter Reading' : 'Edit Meter Reading'; ?>
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" id="readingForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Customer *</label>
                            <select class="form-select" id="customer_id" name="customer_id" required>
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['customer_id']; ?>" 
                                            <?php echo ($reading['customer_id'] ?? '') == $customer['customer_id'] ? 'selected' : ''; ?>>
                                        <?php echo $customer['account_number'] . ' - ' . $customer['last_name'] . ', ' . $customer['first_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="reading_date" class="form-label">Reading Date *</label>
                            <input type="date" class="form-control" id="reading_date" name="reading_date" 
                                   value="<?php echo $reading['reading_date'] ?? date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="current_reading" class="form-label">Current Meter Reading (kWh) *</label>
                            <input type="number" class="form-control" id="current_reading" name="current_reading" 
                                   step="0.01" min="0" 
                                   value="<?php echo $reading['current_reading'] ?? ''; ?>" required>
                            <div class="form-text">Enter the current cumulative meter reading (the number on the physical meter)</div>
                            <div class="mt-3 p-3 bg-light rounded">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Previous Meter Reading</small>
                                        <span id="previousReadingDisplay" class="fw-bold text-primary">0.00 kWh</span>
                                        <small class="text-muted">(Cumulative)</small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Current Meter Reading</small>
                                        <span id="currentReadingDisplay" class="fw-bold text-info">0.00 kWh</span>
                                        <small class="text-muted">(Cumulative)</small>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="text-center">
                                    <small class="text-muted d-block">Calculated Consumption</small>
                                    <span id="consumptionDisplay" class="fw-bold fs-5">0.00 kWh</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="reading_type" class="form-label">Reading Type *</label>
                            <select class="form-select" id="reading_type" name="reading_type" required>
                                <option value="actual" <?php echo ($reading['reading_type'] ?? '') == 'actual' ? 'selected' : ''; ?>>Actual</option>
                                <option value="estimated" <?php echo ($reading['reading_type'] ?? '') == 'estimated' ? 'selected' : ''; ?>>Estimated</option>
                                <option value="adjusted" <?php echo ($reading['reading_type'] ?? '') == 'adjusted' ? 'selected' : ''; ?>>Adjusted</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($reading['notes'] ?? ''); ?></textarea>
                </div>
                
                <!-- Previous Reading Display -->
                <div class="alert alert-info" id="previousReadingInfo" style="display: none;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Previous Reading:</strong> <span id="previousReadingValue">0</span> kWh
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="meter_readings.php" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        <?php echo $action == 'add' ? 'Record Reading' : 'Update Reading'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
function filterReadings() {
    const dateFrom = document.querySelector('input[name="date_from"]').value;
    const dateTo = document.querySelector('input[name="date_to"]').value;
    const search = document.querySelector('input[name="search"]').value;
    
    let url = 'meter_readings.php?action=list';
    if (search) url += '&search=' + encodeURIComponent(search);
    if (dateFrom) url += '&date_from=' + dateFrom;
    if (dateTo) url += '&date_to=' + dateTo;
    
    window.location.href = url;
}

// Load previous reading when customer is selected
document.getElementById('customer_id').addEventListener('change', function() {
    const customerId = this.value;
    if (customerId) {
        fetch('ajax/get_previous_reading.php?customer_id=' + customerId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update both display elements
                    document.getElementById('previousReadingValue').textContent = data.previous_reading;
                    document.getElementById('previousReadingDisplay').textContent = data.previous_reading + ' kWh';
                    document.getElementById('previousReadingInfo').style.display = 'block';
                    
                    // Update the calculation display
                    updateCalculationDisplay();
                } else {
                    document.getElementById('previousReadingInfo').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('previousReadingInfo').style.display = 'none';
            });
    } else {
        document.getElementById('previousReadingInfo').style.display = 'none';
    }
});

// Function to update calculation display
function updateCalculationDisplay() {
    const currentReading = parseFloat(document.getElementById('current_reading').value) || 0;
    const previousReading = parseFloat(document.getElementById('previousReadingValue').textContent.replace(/,/g, '')) || 0;
    const consumption = Math.max(0, currentReading - previousReading);
    
    // Update displays
    const previousReadingDisplay = document.getElementById('previousReadingDisplay');
    const currentReadingDisplay = document.getElementById('currentReadingDisplay');
    const consumptionDisplay = document.getElementById('consumptionDisplay');
    
    if (previousReadingDisplay) {
        previousReadingDisplay.textContent = previousReading.toFixed(2) + ' kWh';
    }
    
    if (currentReadingDisplay) {
        currentReadingDisplay.textContent = currentReading.toFixed(2) + ' kWh';
    }
    
    if (consumptionDisplay) {
        consumptionDisplay.textContent = consumption.toFixed(2) + ' kWh';
        consumptionDisplay.style.color = consumption > 0 ? '#198754' : '#6c757d';
    }
}

// Calculate consumption on current reading change
document.getElementById('current_reading').addEventListener('input', updateCalculationDisplay);

// Initialize displays on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCalculationDisplay();
});
</script>

<?php require_once 'includes/footer.php'; ?>
