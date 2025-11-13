<?php
/**
 * Priority System Settings V2
 * Allows administrators to configure priority number system settings with categories
 */

require_once 'config/config.php';
requireRole(['admin']);

$pageTitle = "Priority System Settings V2";
include 'includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $settings = [
        'priority_daily_capacity_per_category' => $_POST['daily_capacity_per_category'] ?? 500,
        'priority_timer_interval' => $_POST['timer_interval'] ?? 5,
        'priority_lunch_start' => $_POST['lunch_start'] ?? '12:00',
        'priority_lunch_end' => $_POST['lunch_end'] ?? '13:00',
        'priority_service_start' => $_POST['service_start'] ?? '06:00',
        'priority_service_end' => $_POST['service_end'] ?? '18:00',
        'priority_calling_start' => $_POST['calling_start'] ?? '07:00',
        'priority_calling_end' => $_POST['calling_end'] ?? '18:00',
        'priority_notification_enabled' => $_POST['notification_enabled'] ?? 0,
        'priority_weekend_service' => $_POST['weekend_service'] ?? 0,
        'priority_auto_reset_daily' => $_POST['auto_reset_daily'] ?? 1
    ];
    
    foreach ($settings as $key => $value) {
        setSystemSetting($key, $value);
    }
    
    logActivity('Priority system settings V2 updated', 'system_settings');
    
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> Settings updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}

// Get current settings
$currentSettings = [
    'daily_capacity_per_category' => getSystemSetting('priority_daily_capacity_per_category', 500),
    'timer_interval' => getSystemSetting('priority_timer_interval', 5),
    'lunch_start' => getSystemSetting('priority_lunch_start', '12:00'),
    'lunch_end' => getSystemSetting('priority_lunch_end', '13:00'),
    'service_start' => getSystemSetting('priority_service_start', '06:00'),
    'service_end' => getSystemSetting('priority_service_end', '18:00'),
    'calling_start' => getSystemSetting('priority_calling_start', '07:00'),
    'calling_end' => getSystemSetting('priority_calling_end', '18:00'),
    'notification_enabled' => getSystemSetting('priority_notification_enabled', 1),
    'weekend_service' => getSystemSetting('priority_weekend_service', 0),
    'auto_reset_daily' => getSystemSetting('priority_auto_reset_daily', 1)
];
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-cog me-2"></i>Priority System Settings V2</h2>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Queue Configuration</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php echo generateCSRFToken(); ?>
                        
                        <div class="row">
                            <!-- Daily Capacity -->
                            <div class="col-md-6 mb-3">
                                <label for="daily_capacity_per_category" class="form-label">
                                    <i class="fas fa-users me-1"></i>Daily Capacity Per Category
                                </label>
                                <input type="number" class="form-control" id="daily_capacity_per_category" 
                                       name="daily_capacity_per_category" 
                                       value="<?php echo htmlspecialchars($currentSettings['daily_capacity_per_category']); ?>"
                                       min="100" max="1000" required>
                                <div class="form-text">
                                    Maximum number of priority numbers that can be generated per category per day
                                </div>
                            </div>
                            
                            <!-- Timer Interval -->
                            <div class="col-md-6 mb-3">
                                <label for="timer_interval" class="form-label">
                                    <i class="fas fa-clock me-1"></i>Timer Interval (Minutes)
                                </label>
                                <input type="number" class="form-control" id="timer_interval" 
                                       name="timer_interval" 
                                       value="<?php echo htmlspecialchars($currentSettings['timer_interval']); ?>"
                                       min="1" max="30" required>
                                <div class="form-text">
                                    Time interval between priority number calls
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3"><i class="fas fa-clock me-2"></i>Service Hours Configuration</h6>
                        
                        <div class="row">
                            <!-- Service Start Time -->
                            <div class="col-md-3 mb-3">
                                <label for="service_start" class="form-label">Service Start Time</label>
                                <input type="time" class="form-control" id="service_start" 
                                       name="service_start" 
                                       value="<?php echo htmlspecialchars($currentSettings['service_start']); ?>" required>
                                <div class="form-text">When customers can get priority numbers</div>
                            </div>
                            
                            <!-- Service End Time -->
                            <div class="col-md-3 mb-3">
                                <label for="service_end" class="form-label">Service End Time</label>
                                <input type="time" class="form-control" id="service_end" 
                                       name="service_end" 
                                       value="<?php echo htmlspecialchars($currentSettings['service_end']); ?>" required>
                                <div class="form-text">When customers can no longer get priority numbers</div>
                            </div>
                            
                            <!-- Calling Start Time -->
                            <div class="col-md-3 mb-3">
                                <label for="calling_start" class="form-label">Calling Start Time</label>
                                <input type="time" class="form-control" id="calling_start" 
                                       name="calling_start" 
                                       value="<?php echo htmlspecialchars($currentSettings['calling_start']); ?>" required>
                                <div class="form-text">When priority calling begins</div>
                            </div>
                            
                            <!-- Calling End Time -->
                            <div class="col-md-3 mb-3">
                                <label for="calling_end" class="form-label">Calling End Time</label>
                                <input type="time" class="form-control" id="calling_end" 
                                       name="calling_end" 
                                       value="<?php echo htmlspecialchars($currentSettings['calling_end']); ?>" required>
                                <div class="form-text">When priority calling ends</div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3"><i class="fas fa-utensils me-2"></i>Lunch Break Configuration</h6>
                        
                        <div class="row">
                            <!-- Lunch Start Time -->
                            <div class="col-md-6 mb-3">
                                <label for="lunch_start" class="form-label">Lunch Break Start Time</label>
                                <input type="time" class="form-control" id="lunch_start" 
                                       name="lunch_start" 
                                       value="<?php echo htmlspecialchars($currentSettings['lunch_start']); ?>" required>
                                <div class="form-text">When lunch break begins</div>
                            </div>
                            
                            <!-- Lunch End Time -->
                            <div class="col-md-6 mb-3">
                                <label for="lunch_end" class="form-label">Lunch Break End Time</label>
                                <input type="time" class="form-control" id="lunch_end" 
                                       name="lunch_end" 
                                       value="<?php echo htmlspecialchars($currentSettings['lunch_end']); ?>" required>
                                <div class="form-text">When lunch break ends</div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3"><i class="fas fa-bell me-2"></i>System Options</h6>
                        
                        <div class="row">
                            <!-- Notification Enabled -->
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notification_enabled" 
                                           name="notification_enabled" value="1"
                                           <?php echo $currentSettings['notification_enabled'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="notification_enabled">
                                        Enable Notifications
                                    </label>
                                </div>
                                <div class="form-text">Enable SMS/Email notifications for priority numbers</div>
                            </div>
                            
                            <!-- Weekend Service -->
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="weekend_service" 
                                           name="weekend_service" value="1"
                                           <?php echo $currentSettings['weekend_service'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="weekend_service">
                                        Weekend Service
                                    </label>
                                </div>
                                <div class="form-text">Allow priority number generation on weekends</div>
                            </div>
                            
                            <!-- Auto Reset Daily -->
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="auto_reset_daily" 
                                           name="auto_reset_daily" value="1"
                                           <?php echo $currentSettings['auto_reset_daily'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="auto_reset_daily">
                                        Auto Reset Daily
                                    </label>
                                </div>
                                <div class="form-text">Automatically reset priority numbers daily</div>
                            </div>
                        </div>
                        
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- System Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Queue Categories</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-credit-card text-success me-2"></i><strong>Payment:</strong> Window 1</li>
                                <li><i class="fas fa-file-alt text-danger me-2"></i><strong>Claims:</strong> Window 2</li>
                                <li><i class="fas fa-user-plus text-primary me-2"></i><strong>Registration:</strong> Window 3</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Number Format</h6>
                            <ul class="list-unstyled">
                                <li><strong>Payment:</strong> P001, P002, P003...</li>
                                <li><strong>Claims:</strong> C001, C002, C003...</li>
                                <li><strong>Registration:</strong> R001, R002, R003...</li>
                            </ul>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Current Configuration</h6>
                            <ul class="list-unstyled">
                                <li><strong>Daily Capacity:</strong> <?php echo $currentSettings['daily_capacity_per_category']; ?> per category</li>
                                <li><strong>Timer Interval:</strong> <?php echo $currentSettings['timer_interval']; ?> minutes</li>
                                <li><strong>Service Hours:</strong> <?php echo $currentSettings['service_start']; ?> - <?php echo $currentSettings['service_end']; ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Lunch Break</h6>
                            <ul class="list-unstyled">
                                <li><strong>Lunch Hours:</strong> <?php echo $currentSettings['lunch_start']; ?> - <?php echo $currentSettings['lunch_end']; ?></li>
                                <li><strong>Calling Hours:</strong> <?php echo $currentSettings['calling_start']; ?> - <?php echo $currentSettings['calling_end']; ?></li>
                                <li><strong>Notifications:</strong> <?php echo $currentSettings['notification_enabled'] ? 'Enabled' : 'Disabled'; ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const serviceStart = document.getElementById('service_start').value;
    const serviceEnd = document.getElementById('service_end').value;
    const callingStart = document.getElementById('calling_start').value;
    const callingEnd = document.getElementById('calling_end').value;
    const lunchStart = document.getElementById('lunch_start').value;
    const lunchEnd = document.getElementById('lunch_end').value;
    
    // Validate service hours
    if (serviceStart >= serviceEnd) {
        e.preventDefault();
        alert('Service end time must be after service start time');
        return false;
    }
    
    // Validate calling hours
    if (callingStart >= callingEnd) {
        e.preventDefault();
        alert('Calling end time must be after calling start time');
        return false;
    }
    
    // Validate lunch break
    if (lunchStart >= lunchEnd) {
        e.preventDefault();
        alert('Lunch end time must be after lunch start time');
        return false;
    }
    
    // Validate calling hours are within service hours
    if (callingStart < serviceStart || callingEnd > serviceEnd) {
        e.preventDefault();
        alert('Calling hours must be within service hours');
        return false;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
