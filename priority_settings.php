<?php
/**
 * Priority System Settings
 * Allows administrators to configure priority number system settings with categories
 */

require_once 'config/config.php';
requireRole(['admin']);

$pageTitle = "Priority System Settings";
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
    
    logActivity('Priority system settings updated', 'system_settings');
    
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
    'notification_enabled' => getSystemSetting('priority_notification_enabled', 0),
    'weekend_service' => getSystemSetting('priority_weekend_service', 0),
    'auto_reset_daily' => getSystemSetting('priority_auto_reset_daily', 1)
];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-cog me-2"></i>Priority System Settings</h1>
                <div>
                    <a href="<?php echo url('priority_queue_management.php'); ?>" class="btn btn-primary">
                        <i class="fas fa-tasks me-1"></i>Manage Queue
                    </a>
                    <a href="<?php echo url('priority_display.php'); ?>" class="btn btn-info" target="_blank">
                        <i class="fas fa-desktop me-1"></i>View Display
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-sliders-h me-2"></i>System Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="settingsForm">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <!-- Daily Capacity -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="daily_capacity_per_category" class="form-label">
                                            <i class="fas fa-users me-1"></i>Daily Capacity Per Category
                                        </label>
                                        <input type="number" class="form-control" id="daily_capacity_per_category" 
                                               name="daily_capacity_per_category" 
                                               value="<?php echo htmlspecialchars($currentSettings['daily_capacity_per_category']); ?>"
                                               min="1" max="1000" required>
                                        <div class="form-text">Maximum priority numbers that can be generated per category per day</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="timer_interval" class="form-label">
                                            <i class="fas fa-clock me-1"></i>Timer Interval (Minutes)
                                        </label>
                                        <input type="number" class="form-control" id="timer_interval" 
                                               name="timer_interval" 
                                               value="<?php echo htmlspecialchars($currentSettings['timer_interval']); ?>"
                                               min="1" max="60" required>
                                        <div class="form-text">Interval between automatic priority number calls</div>
                                    </div>
                                </div>

                                <!-- Service Hours -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-clock me-2"></i>Service Hours
                                        </h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="service_start" class="form-label">Service Start Time</label>
                                        <input type="time" class="form-control" id="service_start" 
                                               name="service_start" 
                                               value="<?php echo htmlspecialchars($currentSettings['service_start']); ?>" required>
                                        <div class="form-text">When customers can start getting priority numbers</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="service_end" class="form-label">Service End Time</label>
                                        <input type="time" class="form-control" id="service_end" 
                                               name="service_end" 
                                               value="<?php echo htmlspecialchars($currentSettings['service_end']); ?>" required>
                                        <div class="form-text">When customers can no longer get priority numbers</div>
                                    </div>
                                </div>

                                <!-- Calling Hours -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-phone me-2"></i>Calling Hours
                                        </h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="calling_start" class="form-label">Calling Start Time</label>
                                        <input type="time" class="form-control" id="calling_start" 
                                               name="calling_start" 
                                               value="<?php echo htmlspecialchars($currentSettings['calling_start']); ?>" required>
                                        <div class="form-text">When admin/cashier can start calling priority numbers</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="calling_end" class="form-label">Calling End Time</label>
                                        <input type="time" class="form-control" id="calling_end" 
                                               name="calling_end" 
                                               value="<?php echo htmlspecialchars($currentSettings['calling_end']); ?>" required>
                                        <div class="form-text">When admin/cashier stops calling priority numbers</div>
                                    </div>
                                </div>

                                <!-- Lunch Break -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-warning mb-3">
                                            <i class="fas fa-utensils me-2"></i>Lunch Break
                                        </h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lunch_start" class="form-label">Lunch Start Time</label>
                                        <input type="time" class="form-control" id="lunch_start" 
                                               name="lunch_start" 
                                               value="<?php echo htmlspecialchars($currentSettings['lunch_start']); ?>" required>
                                        <div class="form-text">When lunch break starts (queue paused)</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lunch_end" class="form-label">Lunch End Time</label>
                                        <input type="time" class="form-control" id="lunch_end" 
                                               name="lunch_end" 
                                               value="<?php echo htmlspecialchars($currentSettings['lunch_end']); ?>" required>
                                        <div class="form-text">When lunch break ends (queue resumes)</div>
                                    </div>
                                </div>

                                <!-- Additional Options -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-info mb-3">
                                            <i class="fas fa-cog me-2"></i>Additional Options
                                        </h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="notification_enabled" 
                                                   name="notification_enabled" value="1"
                                                   <?php echo $currentSettings['notification_enabled'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notification_enabled">
                                                Enable Notifications
                                            </label>
                                            <div class="form-text">Send notifications for priority number updates</div>
                                        </div>
                                        
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="weekend_service" 
                                                   name="weekend_service" value="1"
                                                   <?php echo $currentSettings['weekend_service'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="weekend_service">
                                                Weekend Service
                                            </label>
                                            <div class="form-text">Allow priority numbers on weekends</div>
                                        </div>
                                        
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="auto_reset_daily" 
                                                   name="auto_reset_daily" value="1"
                                                   <?php echo $currentSettings['auto_reset_daily'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="auto_reset_daily">
                                                Auto Reset Daily
                                            </label>
                                            <div class="form-text">Automatically reset priority numbers daily</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save me-2"></i>Save Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- System Status -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>System Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $now = new DateTime();
                            $currentTime = $now->format('H:i');
                            $isServiceTime = $currentTime >= $currentSettings['service_start'] && $currentTime <= $currentSettings['service_end'];
                            $isCallingTime = $currentTime >= $currentSettings['calling_start'] && $currentTime <= $currentSettings['calling_end'];
                            $isLunchBreak = $currentTime >= $currentSettings['lunch_start'] && $currentTime <= $currentSettings['lunch_end'];
                            ?>
                            
                            <div class="mb-3">
                                <strong>Current Time:</strong> <?php echo $now->format('M j, Y g:i A'); ?>
                            </div>
                            
                            <div class="mb-2">
                                <span class="badge <?php echo $isServiceTime ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $isServiceTime ? 'Service Active' : 'Service Inactive'; ?>
                                </span>
                            </div>
                            
                            <div class="mb-2">
                                <span class="badge <?php echo $isCallingTime ? 'bg-primary' : 'bg-secondary'; ?>">
                                    <?php echo $isCallingTime ? 'Calling Active' : 'Calling Inactive'; ?>
                                </span>
                            </div>
                            
                            <div class="mb-2">
                                <span class="badge <?php echo $isLunchBreak ? 'bg-warning' : 'bg-success'; ?>">
                                    <?php echo $isLunchBreak ? 'Lunch Break' : 'Normal Hours'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Queue Statistics -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Today's Statistics
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            require_once 'includes/PriorityNumberGeneratorV2.php';
                            $priorityGenerator = new PriorityNumberGeneratorV2();
                            $stats = $priorityGenerator->getQueueStatistics();
                            ?>
                            
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border-end">
                                        <h4 class="text-primary"><?php echo $stats['payment']['pending']; ?></h4>
                                        <small>Payment Pending</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <h4 class="text-danger"><?php echo $stats['claims']['pending']; ?></h4>
                                        <small>Claims Pending</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-info"><?php echo $stats['registration']['pending']; ?></h4>
                                    <small>Registration Pending</small>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border-end">
                                        <h5 class="text-success"><?php echo $stats['payment']['served']; ?></h5>
                                        <small>Payment Served</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <h5 class="text-success"><?php echo $stats['claims']['served']; ?></h5>
                                        <small>Claims Served</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <h5 class="text-success"><?php echo $stats['registration']['served']; ?></h5>
                                    <small>Registration Served</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('settingsForm');
    form.addEventListener('submit', function(e) {
        const serviceStart = document.getElementById('service_start').value;
        const serviceEnd = document.getElementById('service_end').value;
        const callingStart = document.getElementById('calling_start').value;
        const callingEnd = document.getElementById('calling_end').value;
        const lunchStart = document.getElementById('lunch_start').value;
        const lunchEnd = document.getElementById('lunch_end').value;
        
        // Validate time ranges
        if (serviceStart >= serviceEnd) {
            alert('Service end time must be after service start time');
            e.preventDefault();
            return;
        }
        
        if (callingStart >= callingEnd) {
            alert('Calling end time must be after calling start time');
            e.preventDefault();
            return;
        }
        
        if (lunchStart >= lunchEnd) {
            alert('Lunch end time must be after lunch start time');
            e.preventDefault();
            return;
        }
        
        // Validate calling hours are within service hours
        if (callingStart < serviceStart || callingEnd > serviceEnd) {
            alert('Calling hours must be within service hours');
            e.preventDefault();
            return;
        }
        
        // Validate lunch break is within service hours
        if (lunchStart < serviceStart || lunchEnd > serviceEnd) {
            alert('Lunch break must be within service hours');
            e.preventDefault();
            return;
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>