<?php
/**
 * Priority Queue Management - Admin/Cashier Interface
 * Manages priority queue calling with categories and timer functionality
 */

require_once 'config/config.php';
require_once 'includes/PriorityNumberGeneratorV2.php';
requireRole(['admin', 'cashier']);

$priorityGenerator = new PriorityNumberGeneratorV2();
$message = '';
$messageType = '';

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $action = $_GET['action'];
    $category = $_GET['category'] ?? '';
    
    switch ($action) {
        case 'serve_next':
            $result = $priorityGenerator->serveNextPriorityNumber($category, $_SESSION['user_id']);
            echo json_encode($result);
            exit;
            
        case 'skip_current':
            $result = $priorityGenerator->skipCurrentPriorityNumber($category, $_SESSION['user_id']);
            echo json_encode($result);
            exit;
            
        case 'get_queue_data':
            $current = $priorityGenerator->getCurrentPriorityNumber($category);
            $next = $priorityGenerator->getNextPriorityNumbers($category, null, 3);
            $previous = $priorityGenerator->getPreviousPriorityNumbers($category, null, 1);
            echo json_encode([
                'current' => $current,
                'next' => $next,
                'previous' => $previous,
                'is_lunch_break' => $priorityGenerator->isLunchBreak()
            ]);
            exit;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $category = $_POST['category'] ?? '';
    
    switch ($action) {
        case 'serve_next':
            $result = $priorityGenerator->serveNextPriorityNumber($category, $_SESSION['user_id']);
            if ($result['success']) {
                $message = "Priority number {$result['priority_number']} served successfully!";
                $messageType = 'success';
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
            break;
            
        case 'skip_current':
            $result = $priorityGenerator->skipCurrentPriorityNumber($category, $_SESSION['user_id']);
            if ($result['success']) {
                $message = "Priority number {$result['priority_number']} skipped successfully!";
                $messageType = 'success';
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
            break;
    }
}

$page_title = 'Priority Queue Management';
require_once 'includes/header.php';
?>

<style>
.queue-window {
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    min-height: 400px;
}

.window-header {
    text-align: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #dee2e6;
}

.window-header h4 {
    color: #495057;
    margin-bottom: 5px;
}

.pipe-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 20px 0;
}

.pipe-section {
    width: 100%;
    text-align: center;
    padding: 15px;
    margin: 5px 0;
    border-radius: 8px;
    border: 2px solid #dee2e6;
}

.previous-numbers {
    background: #e9ecef;
    color: #6c757d;
    min-height: 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.current-number {
    background: #007bff;
    color: white;
    font-size: 1.5em;
    font-weight: bold;
    min-height: 80px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    border-color: #007bff !important;
}

.next-numbers {
    background: #f8f9fa;
    color: #495057;
    min-height: 100px;
}

.next-number-item {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 8px;
    margin: 3px 0;
    font-weight: bold;
}

.queue-actions {
    text-align: center;
    margin-top: 20px;
}

.queue-actions .btn {
    margin: 0 5px;
    min-width: 120px;
}

.category-stats {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}

.category-stats h5 {
    color: #495057;
    margin-bottom: 10px;
}

.stats-row {
    display: flex;
    justify-content: space-between;
    margin: 5px 0;
}

.stats-label {
    font-weight: bold;
    color: #6c757d;
}

.stats-value {
    color: #007bff;
    font-weight: bold;
}

.lunch-break-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lunch-break-content {
    background: white;
    padding: 40px;
    border-radius: 10px;
    text-align: center;
    max-width: 400px;
}

.lunch-break-content h2 {
    color: #dc3545;
    margin-bottom: 20px;
}

.auto-timer {
    background: #28a745;
    color: white;
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;
    text-align: center;
}

.timer-disabled {
    background: #6c757d;
}

@media (max-width: 768px) {
    .queue-window {
        margin-bottom: 15px;
    }
    
    .queue-actions .btn {
        margin: 5px;
        width: 100%;
    }
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-ticket-alt me-2"></i>Priority Queue Management</h1>
                <div>
                    <a href="<?php echo url('priority_display.php'); ?>" class="btn btn-info" target="_blank">
                        <i class="fas fa-desktop me-1"></i>View Display
                    </a>
                    <a href="<?php echo url('priority_settings.php'); ?>" class="btn btn-warning">
                        <i class="fas fa-cog me-1"></i>Settings
                    </a>
        </div>
    </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Queue Statistics -->
            <div class="row mb-4">
                <?php 
                $queueStats = $priorityGenerator->getQueueStatistics();
                foreach ($queueStats as $category => $stats): 
                ?>
                    <div class="col-md-4 mb-3">
                        <div class="category-stats">
                            <h5><?php echo ucfirst($category); ?> Queue</h5>
                            <div class="stats-row">
                                <span class="stats-label">Pending:</span>
                                <span class="stats-value"><?php echo $stats['pending']; ?></span>
                                </div>
                            <div class="stats-row">
                                <span class="stats-label">Served:</span>
                                <span class="stats-value"><?php echo $stats['served']; ?></span>
                            </div>
                            <div class="stats-row">
                                <span class="stats-label">Skipped:</span>
                                <span class="stats-value"><?php echo $stats['skipped']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
        </div>
        
            <!-- Queue Windows -->
            <div class="row">
                    <?php
                $categories = ['payment', 'claims', 'registration'];
                $windowNumbers = [1, 2, 3];
                $categoryNames = ['Payment', 'Claims', 'Registration'];
                
                for ($i = 0; $i < 3; $i++): 
                    $category = $categories[$i];
                    $windowNumber = $windowNumbers[$i];
                    $categoryName = $categoryNames[$i];
                ?>
                    <div class="col-md-4">
                        <div class="queue-window">
                            <div class="window-header">
                                <h4><i class="fas fa-window-maximize me-2"></i>Window <?php echo $windowNumber; ?></h4>
                                <p class="mb-0"><?php echo $categoryName; ?> Queue</p>
                        </div>
                            
                            <div class="pipe-container" id="pipe-<?php echo $category; ?>">
                                <!-- Previous Numbers (Top) -->
                                <div class="pipe-section previous-numbers" id="previous-<?php echo $category; ?>">
                                    <div class="small">Previously Served</div>
                                    <div id="previous-number-<?php echo $category; ?>">--</div>
                            </div>
                                
                                <!-- Current Number (Middle) -->
                                <div class="pipe-section current-number" id="current-<?php echo $category; ?>">
                                    <div id="current-number-<?php echo $category; ?>">No Active Number</div>
                                    <div class="small mt-1" id="current-customer-<?php echo $category; ?>"></div>
                    </div>
                    
                                <!-- Next Numbers (Bottom) -->
                                <div class="pipe-section next-numbers" id="next-<?php echo $category; ?>">
                                    <div class="small mb-2">Next Numbers</div>
                                    <div id="next-numbers-<?php echo $category; ?>">
                                        <div>No pending numbers</div>
                            </div>
                        </div>
                    </div>
                    
                            <div class="queue-actions">
                                <button class="btn btn-success" onclick="serveNext('<?php echo $category; ?>')">
                                    <i class="fas fa-check me-1"></i>Serve Next
                                </button>
                                <button class="btn btn-warning" onclick="skipCurrent('<?php echo $category; ?>')">
                                    <i class="fas fa-forward me-1"></i>Skip
                                </button>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>


<script>
let autoTimer = null;
let timerInterval = 5; // minutes

// Load initial queue data
document.addEventListener('DOMContentLoaded', function() {
    loadQueueData();
    startAutoRefresh();
    checkLunchBreak();
});

function loadQueueData() {
    const categories = ['payment', 'claims', 'registration'];
    
    categories.forEach(category => {
        fetch(`?action=get_queue_data&category=${category}`)
            .then(response => response.json())
            .then(data => {
                updateQueueDisplay(category, data);
            })
            .catch(error => {
                console.error('Error loading queue data:', error);
            });
    });
}

function updateQueueDisplay(category, data) {
    // Update current number
    const currentNumberEl = document.getElementById(`current-number-${category}`);
    const currentCustomerEl = document.getElementById(`current-customer-${category}`);
    
    if (data.current && data.current.priority_number) {
        currentNumberEl.textContent = data.current.priority_number;
        
        // Get customer name from first_name and last_name
        let customerName = '';
        if (data.current.first_name && data.current.last_name) {
            customerName = data.current.first_name + ' ' + data.current.last_name;
        } else if (data.current.customer_id) {
            customerName = 'Unknown Customer';
        }
        
        currentCustomerEl.textContent = customerName;
        currentCustomerEl.style.display = customerName ? 'block' : 'none';
    } else {
        currentNumberEl.textContent = 'No Active Number';
        currentCustomerEl.textContent = '';
        currentCustomerEl.style.display = 'none';
    }
    
    // Update previous number
    const previousNumberEl = document.getElementById(`previous-number-${category}`);
    if (data.previous && data.previous.length > 0) {
        previousNumberEl.textContent = data.previous[0].priority_number;
    } else {
        previousNumberEl.textContent = '--';
    }
    
    // Update next numbers
    const nextNumbersEl = document.getElementById(`next-numbers-${category}`);
    if (data.next && data.next.length > 0) {
        nextNumbersEl.innerHTML = data.next.map(item => {
            const name = (item.first_name && item.last_name) 
                ? ` - ${item.first_name} ${item.last_name}` 
                : '';
            return `<div class="next-number-item">${item.priority_number}${name}</div>`;
        }).join('');
                    } else {
        nextNumbersEl.innerHTML = '<div>No pending numbers</div>';
    }
}

function serveNext(category) {
    if (confirm(`Serve next priority number for ${category} queue?`)) {
        fetch(`?action=serve_next&category=${category}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=serve_next&category=${category}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(`Priority number ${data.priority_number} served successfully!`, 'success');
                loadQueueData();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while serving the priority number.', 'error');
        });
    }
}

function skipCurrent(category) {
    if (confirm(`Skip current priority number for ${category} queue?`)) {
        fetch(`?action=skip_current&category=${category}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=skip_current&category=${category}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = `Priority number ${data.skipped_number || data.priority_number} skipped successfully!`;
                if (data.next_number) {
                    message += ` Next number ${data.next_number} is now being served.`;
                }
                showMessage(message, 'success');
                loadQueueData();
            } else {
                showMessage(data.message || data.error || 'An error occurred while skipping the priority number.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while skipping the priority number.', 'error');
        });
    }
}

function showMessage(message, type) {
    const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Add new alert
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

function startAutoRefresh() {
    // Refresh queue data every 30 seconds
    setInterval(loadQueueData, 30000);
}

function checkLunchBreak() {
    const now = new Date();
    const currentTime = now.getHours() * 60 + now.getMinutes();
    const lunchStart = 12 * 60; // 12:00 PM
    const lunchEnd = 13 * 60;   // 1:00 PM
    
    if (currentTime >= lunchStart && currentTime < lunchEnd) {
        document.getElementById('lunchBreakModal').style.display = 'flex';
    } else {
        document.getElementById('lunchBreakModal').style.display = 'none';
    }
}

// Check lunch break every minute
setInterval(checkLunchBreak, 60000);
</script>

<?php require_once 'includes/footer.php'; ?>