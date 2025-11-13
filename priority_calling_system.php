<?php
/**
 * Priority Calling System - Admin Interface
 * Real-time interface for calling customers by priority number
 */

require_once 'config/config.php';
// Legacy page: redirect to unified Priority Management
header('Location: priority_queue_management.php');
exit;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Priority Calling System</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Calling System</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Customer Being Served -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-microphone me-2"></i>Currently Being Served
                    </h5>
                </div>
                <div class="card-body text-center py-4">
                    <?php if ($currentCustomer): ?>
                        <div class="row">
                            <div class="col-md-4">
                                <h1 class="display-1 text-primary fw-bold"><?php echo $currentCustomer['priority_number']; ?></h1>
                                <p class="fs-4 text-muted">Priority Number</p>
                            </div>
                            <div class="col-md-8">
                                <h2 class="text-primary mb-2"><?php echo htmlspecialchars($currentCustomer['first_name'] . ' ' . $currentCustomer['last_name']); ?></h2>
                                <p class="fs-5 mb-1"><strong>Account:</strong> <?php echo htmlspecialchars($currentCustomer['account_number']); ?></p>
                                <?php if ($currentCustomer['contact_number']): ?>
                                    <p class="fs-5 mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($currentCustomer['contact_number']); ?></p>
                                <?php endif; ?>
                                <p class="text-muted mb-0">Generated: <?php echo date('M j, Y g:i A', strtotime($currentCustomer['generated_at'])); ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <h3 class="text-muted">No customer currently being served</h3>
                            <p class="text-muted">Click "Call Next Customer" to start serving</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Next Customer to Call -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bell me-2"></i>Next Customer to Call
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($nextCustomer): ?>
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <h1 class="display-4 text-warning fw-bold"><?php echo $nextCustomer['priority_number']; ?></h1>
                                <p class="text-muted">Next Priority</p>
                            </div>
                            <div class="col-md-6">
                                <h4 class="text-dark mb-2"><?php echo htmlspecialchars($nextCustomer['first_name'] . ' ' . $nextCustomer['last_name']); ?></h4>
                                <p class="mb-1"><strong>Account:</strong> <?php echo htmlspecialchars($nextCustomer['account_number']); ?></p>
                                <?php if ($nextCustomer['contact_number']): ?>
                                    <p class="mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($nextCustomer['contact_number']); ?></p>
                                <?php endif; ?>
                                <p class="text-muted mb-0">Waiting since: <?php echo date('g:i A', strtotime($nextCustomer['generated_at'])); ?></p>
                            </div>
                            <div class="col-md-3 text-center">
                                <button type="button" class="btn btn-success btn-lg" onclick="callCustomer(<?php echo $nextCustomer['priority_number']; ?>)">
                                    <i class="fas fa-microphone me-2"></i>Call Customer
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <h4 class="text-muted">No customers waiting</h4>
                            <p class="text-muted">All customers have been served or no priority numbers generated yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Quick Actions</h5>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="callNextCustomer()">
                            <i class="fas fa-forward me-2"></i>Call Next Customer
                        </button>
                        <button type="button" class="btn btn-warning" onclick="skipCurrentCustomer()">
                            <i class="fas fa-step-forward me-2"></i>Skip Current
                        </button>
                        <button type="button" class="btn btn-info" onclick="refreshCallingSystem()">
                            <i class="fas fa-sync me-2"></i>Refresh Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Queue Statistics</h5>
                    <div class="row">
                        <div class="col-6">
                            <h3 class="text-primary" id="served-today"><?php echo $currentStatus['served_count'] ?? 0; ?></h3>
                            <p class="text-muted mb-0">Served Today</p>
                        </div>
                        <div class="col-6">
                            <h3 class="text-warning" id="pending-today">0</h3>
                            <p class="text-muted mb-0">Pending Today</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">System Status</h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Queue System</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Calling System</span>
                        <span class="badge bg-success">Running</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Last Update</span>
                        <small class="text-muted" id="last-update"><?php echo date('g:i A'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Customers -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upcoming Customers (Next 10)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="upcomingTable">
                            <thead>
                                <tr>
                                    <th>Priority #</th>
                                    <th>Customer Name</th>
                                    <th>Account Number</th>
                                    <th>Contact</th>
                                    <th>Generated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingNumbers as $number): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary fs-6"><?php echo $number['priority_number']; ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($number['first_name'] . ' ' . $number['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($number['account_number']); ?></td>
                                    <td><?php echo htmlspecialchars($number['contact_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('g:i A', strtotime($number['generated_at'])); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success" onclick="callCustomer(<?php echo $number['priority_number']; ?>)">
                                            <i class="fas fa-microphone"></i> Call
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audio Alert Modal -->
<div class="modal fade" id="callingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-microphone me-2"></i>Calling Customer
                </h5>
            </div>
            <div class="modal-body text-center py-4">
                <div id="calling-content">
                    <h2 class="text-success mb-3">Calling Priority Number</h2>
                    <h1 class="display-1 text-primary fw-bold" id="calling-number">0</h1>
                    <h3 class="text-dark mb-3" id="calling-name">Customer Name</h3>
                    <p class="fs-5 text-muted" id="calling-account">Account Number</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="markAsServed()">
                    <i class="fas fa-check me-2"></i>Mark as Served
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-refresh every 30 seconds
    setInterval(refreshCallingSystem, 30000);
    
    // Initial load
    refreshCallingSystem();
});

// Call specific customer
function callCustomer(priorityNumber) {
    $.ajax({
        url: 'ajax/priority_number.php',
        type: 'GET',
        data: { action: 'get_details', priority_number: priorityNumber },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#calling-number').text(response.data.priority_number);
                $('#calling-name').text(response.data.first_name + ' ' + response.data.last_name);
                $('#calling-account').text('Account: ' + response.data.account_number);
                
                $('#callingModal').modal('show');
                
                // Play audio alert (if supported)
                playCallingSound();
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to get customer details', 'error');
        }
    });
}

// Call next customer
function callNextCustomer() {
    $.ajax({
        url: 'ajax/priority_number.php',
        type: 'GET',
        data: { action: 'get_upcoming', limit: 1 },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                callCustomer(response.data[0].priority_number);
            } else {
                Swal.fire('Info', 'No customers waiting', 'info');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to get next customer', 'error');
        }
    });
}

// Skip current customer
function skipCurrentCustomer() {
    Swal.fire({
        title: 'Skip Current Customer',
        text: 'Are you sure you want to skip the current customer?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, skip!'
    }).then((result) => {
        if (result.isConfirmed) {
            callNextCustomer();
        }
    });
}

// Mark customer as served
function markAsServed() {
    var priorityNumber = $('#calling-number').text();
    
    $.ajax({
        url: 'ajax/priority_number.php',
        type: 'POST',
        data: {
            action: 'update_current',
            priority_number: priorityNumber,
            csrf_token: '<?php echo generateCSRFToken(); ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#callingModal').modal('hide');
                Swal.fire('Success!', response.message, 'success');
                refreshCallingSystem();
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to update customer status', 'error');
        }
    });
}

// Refresh calling system
function refreshCallingSystem() {
    location.reload(); // Simple refresh for now
}

// Play calling sound
function playCallingSound() {
    // Create audio element for calling sound
    var audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
    audio.play().catch(function() {
        // Audio play failed, continue silently
    });
}
</script>

<style>
.display-1 {
    font-size: 4rem;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}

.border-primary {
    border: 2px solid #007bff !important;
}

.border-warning {
    border: 2px solid #ffc107 !important;
}

#callingModal .modal-content {
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}
</style>

<?php include 'includes/footer.php'; ?>
