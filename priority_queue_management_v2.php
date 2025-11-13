<?php
/**
 * Priority Queue Management V2 - Admin/Cashier Interface
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
            
            // Debug: log what we're returning
            error_log("get_queue_data - Category: $category");
            error_log("Current: " . json_encode($current));
            
            echo json_encode([
                'current' => $current ?: null, // Ensure false becomes null in JSON
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
    
    if ($action === 'serve_next' && $category) {
        $result = $priorityGenerator->serveNextPriorityNumber($category, $_SESSION['user_id']);
        if ($result['success']) {
            $message = "Priority number {$result['priority_number']} served successfully!";
            $messageType = 'success';
        } else {
            $message = $result['error'];
            $messageType = 'danger';
        }
    } elseif ($action === 'skip_current' && $category) {
        $result = $priorityGenerator->skipCurrentPriorityNumber($category, $_SESSION['user_id']);
        if ($result['success']) {
            $message = "Priority number {$result['priority_number']} skipped";
            $messageType = 'warning';
        } else {
            $message = $result['error'];
            $messageType = 'danger';
        }
    }
}

$page_title = 'Priority Queue Management V2';
include 'includes/header.php';
?>

<style>
    .queue-window {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .window-header {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-yellow));
        color: white;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        text-align: center;
    }
    
    .pipe-container {
        position: relative;
        height: 400px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .pipe-section {
        width: 200px;
        padding: 1rem;
        margin: 0.5rem 0;
        border-radius: 10px;
        text-align: center;
        position: relative;
    }
    
    .previous-numbers {
        background: #6c757d;
        color: white;
        min-height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .current-number {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        min-height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        border: 3px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .next-numbers {
        background: #007bff;
        color: white;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .queue-actions {
        margin-top: 1rem;
        display: flex;
        gap: 1rem;
        justify-content: center;
    }
    
    .lunch-break-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    .lunch-break-content {
        background: linear-gradient(135deg, #fd7e14, #e8590c);
        color: white;
        padding: 3rem;
        border-radius: 20px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    .timer-display {
        background: #343a40;
        color: #fff;
        padding: 1rem;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 1rem;
        font-family: 'Courier New', monospace;
        font-size: 1.2rem;
    }
    
    .category-stats {
        background: rgba(255,255,255,0.1);
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-tasks me-2"></i>Priority Queue Management</h2>
                <div class="timer-display" id="timerDisplay">
                    Next Call: <span id="nextCallTime">--:--</span>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
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
                            <h6 class="text-capitalize"><?php echo $category; ?> Queue</h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="text-warning"><?php echo $stats['pending']; ?></div>
                                    <small>Pending</small>
                                </div>
                                <div class="col-4">
                                    <div class="text-success"><?php echo $stats['served']; ?></div>
                                    <small>Served</small>
                                </div>
                                <div class="col-4">
                                    <div class="text-danger"><?php echo $stats['skipped']; ?></div>
                                    <small>Skipped</small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
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
                                      <div class="small mt-1 fw-bold" id="current-customer-<?php echo $category; ?>" style="opacity: 0.9; font-size: 0.85rem;"></div>
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

<!-- Lunch Break Modal -->
<div class="lunch-break-modal" id="lunchBreakModal" style="display: none;">
    <div class="lunch-break-content">
        <h2><i class="fas fa-utensils me-2"></i>Lunch Break</h2>
        <p class="mb-0">Queue management is paused during lunch break</p>
        <p class="mb-0"><strong>12:00 PM - 1:00 PM</strong></p>
    </div>
</div>

<script>
let nextCallTimes = {
    payment: null,
    claims: null,
    registration: null
};

// Initialize queue data for all categories
function initializeQueueData() {
    ['payment', 'claims', 'registration'].forEach(category => {
        updateQueueDisplay(category);
    });
}

  // Update queue display for a specific category
  function updateQueueDisplay(category) {
      fetch(`?action=get_queue_data&category=${category}`)
          .then(response => response.json())
          .then(data => {
              // Update current number
              const currentEl = document.getElementById(`current-number-${category}`);
              const customerEl = document.getElementById(`current-customer-${category}`);
              
              if (data.current && data.current.priority_number) {
                  if (currentEl) {
                      currentEl.textContent = data.current.priority_number;
                  }
                  
                  // Get customer name
                  let customerName = '';
                  if (data.current.first_name && data.current.last_name) {
                      customerName = data.current.first_name + ' ' + data.current.last_name;
                  } else if (data.current.customer_id) {
                      customerName = 'Unknown Customer';
                  }
                  
                  if (customerEl) {
                      customerEl.textContent = customerName;
                      customerEl.style.display = customerName ? 'block' : 'none';
                  }
              } else {
                  if (currentEl) {
                      currentEl.textContent = 'No Active Number';
                  }
                  if (customerEl) {
                      customerEl.textContent = '';
                      customerEl.style.display = 'none';
                  }
              }
            
            // Update next numbers
            const nextEl = document.getElementById(`next-numbers-${category}`);
            if (nextEl) {
                if (data.next && data.next.length > 0) {
                    nextEl.innerHTML = data.next.map(item => {
                        const name = (item.first_name && item.last_name) 
                            ? `${item.first_name} ${item.last_name}` 
                            : '';
                        return `<div class="small">${item.priority_number}${name ? ' - ' + name : ''}</div>`;
                    }).join('');
                } else {
                    nextEl.innerHTML = '<div class="small">No pending numbers</div>';
                }
            }
            
            // Update previous number
            const prevEl = document.getElementById(`previous-number-${category}`);
            if (prevEl) {
                if (data.previous && data.previous.length > 0) {
                    prevEl.textContent = data.previous[0].priority_number;
                } else {
                    prevEl.textContent = '--';
                }
            }
            
            // Handle lunch break
            const lunchBreakModal = document.getElementById('lunchBreakModal');
            if (lunchBreakModal) {
                lunchBreakModal.style.display = data.is_lunch_break ? 'flex' : 'none';
            }
        })
        .catch(error => {
            console.error('Error updating queue display:', error);
        });
  }

// Serve next number
function serveNext(category) {
    fetch(`?action=serve_next&category=${category}`, {method: 'POST'})
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateQueueDisplay(category);
                setNextCallTime(category);
                showMessage(`Priority number ${data.priority_number} served successfully!`, 'success');
            } else {
                showMessage(data.error, 'danger');
            }
        })
        .catch(error => {
            console.error('Error serving next number:', error);
            showMessage('Error serving priority number', 'danger');
        });
}

// Skip current number
function skipCurrent(category) {
    fetch(`?action=skip_current&category=${category}`, {method: 'POST'})
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateQueueDisplay(category);
                showMessage(`Priority number ${data.priority_number} skipped`, 'warning');
            } else {
                showMessage(data.error, 'danger');
            }
        })
        .catch(error => {
            console.error('Error skipping number:', error);
            showMessage('Error skipping priority number', 'danger');
        });
}

// Set next call time
function setNextCallTime(category) {
    const now = new Date();
    const nextCall = new Date(now.getTime() + (5 * 60 * 1000)); // 5 minutes
    nextCallTimes[category] = nextCall;
}

// Update timer display
function updateTimerDisplay() {
    const now = new Date();
    const nextCallTime = Math.min(...Object.values(nextCallTimes).filter(time => time !== null));
    
    if (nextCallTime) {
        const timeLeft = Math.max(0, nextCallTime - now);
        const minutes = Math.floor(timeLeft / 60000);
        const seconds = Math.floor((timeLeft % 60000) / 1000);
        
        document.getElementById('nextCallTime').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    } else {
        document.getElementById('nextCallTime').textContent = '--:--';
    }
}

// Show message
function showMessage(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeQueueData();
    
    // Update queue data every 10 seconds
    setInterval(initializeQueueData, 10000);
    
    // Update timer every second
    setInterval(updateTimerDisplay, 1000);
});
</script>

<?php include 'includes/footer.php'; ?>
