<?php
/**
 * Priority Number Generator V2 - Customer Interface
 * Allows customers to generate priority numbers with category selection
 */

require_once 'config/config.php';
require_once 'includes/PriorityNumberGeneratorV2.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_id'])) {
    redirect('auth/customer_login.php');
}

$priorityGenerator = new PriorityNumberGeneratorV2();
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'] ?? '';
    
    if ($category) {
        $result = $priorityGenerator->generatePriorityNumber($_SESSION['customer_id'], $category);
        
        if ($result['success']) {
            $message = "Priority number {$result['priority_number']} generated successfully for {$category} queue!";
            $messageType = 'success';
        } else {
            $message = $result['error'];
            $messageType = 'danger';
        }
    } else {
        $message = "Please select a queue category";
        $messageType = 'danger';
    }
}

// Get current queue statistics
$queueStats = $priorityGenerator->getQueueStatistics();
$isLunchBreak = $priorityGenerator->isLunchBreak();

// Check if current time allows priority number generation
// TEMPORARILY DISABLED FOR TESTING - Remove comments to re-enable
$currentHour = (int)date('H');
// $canGenerate = $currentHour >= 6 && $currentHour < 18 && !$isLunchBreak;
$canGenerate = true; // Always allow generation for testing
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Priority Number Generator - <?php echo getSystemSetting('company_name', 'SOCOTECO II'); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-orange: #FF9A00;
            --secondary-yellow: #FFD93D;
            --dark-blue: #1e3a8a;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-yellow) 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--dark-blue), #2563eb);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }
        
        .category-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.2);
        }
        
        .category-card.selected {
            border-color: var(--primary-orange);
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        }
        
        .category-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .payment { color: #28a745; }
        .claims { color: #dc3545; }
        .registration { color: #007bff; }
        
        .queue-stats {
            background: rgba(255,255,255,0.9);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .time-restriction {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .lunch-break {
            background: linear-gradient(135deg, #fd7e14, #e8590c);
            color: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h2><i class="fas fa-ticket-alt me-2"></i>Priority Number Generator</h2>
                        <p class="mb-0">Get your priority number for today's service</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Time Restrictions -->
                        <?php if (!$canGenerate): ?>
                            <?php if ($isLunchBreak): ?>
                                <div class="lunch-break text-center">
                                    <h5><i class="fas fa-utensils me-2"></i>Lunch Break</h5>
                                    <p class="mb-0">Priority number generation is temporarily unavailable during lunch break (12:00 PM - 1:00 PM)</p>
                                </div>
                            <?php elseif ($currentHour < 6): ?>
                                <div class="time-restriction text-center">
                                    <h5><i class="fas fa-clock me-2"></i>Service Not Available</h5>
                                    <p class="mb-0">Priority numbers can only be generated between 6:00 AM and 6:00 PM</p>
                                </div>
                            <?php else: ?>
                                <div class="time-restriction text-center">
                                    <h5><i class="fas fa-clock me-2"></i>Service Hours Ended</h5>
                                    <p class="mb-0">Priority number generation is only available between 6:00 AM and 6:00 PM</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Queue Statistics -->
                        <div class="queue-stats">
                            <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Today's Queue Status</h5>
                            <div class="row">
                                <?php foreach ($queueStats as $category => $stats): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold text-capitalize"><?php echo $category; ?>:</span>
                                            <span><?php echo $stats['pending']; ?>/<?php echo $stats['capacity']; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <?php if ($canGenerate): ?>
                            <!-- Category Selection -->
                            <form method="POST" id="priorityForm">
                                <h5 class="mb-3"><i class="fas fa-list me-2"></i>Select Service Category</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="category-card card h-100 text-center p-3" data-category="payment">
                                            <div class="category-icon payment">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <h5>Payment</h5>
                                            <p class="small text-muted">Bill payments and transactions</p>
                                            <div class="queue-info">
                                                <small class="text-muted">
                                                    Pending: <?php echo $queueStats['payment']['pending']; ?><br>
                                                    Window 1
                                                </small>
                                            </div>
                                            <input type="radio" name="category" value="payment" class="d-none" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="category-card card h-100 text-center p-3" data-category="claims">
                                            <div class="category-icon claims">
                                                <i class="fas fa-file-alt"></i>
                                            </div>
                                            <h5>Claims</h5>
                                            <p class="small text-muted">Service claims and complaints</p>
                                            <div class="queue-info">
                                                <small class="text-muted">
                                                    Pending: <?php echo $queueStats['claims']['pending']; ?><br>
                                                    Window 2
                                                </small>
                                            </div>
                                            <input type="radio" name="category" value="claims" class="d-none" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="category-card card h-100 text-center p-3" data-category="registration">
                                            <div class="category-icon registration">
                                                <i class="fas fa-user-plus"></i>
                                            </div>
                                            <h5>Registration</h5>
                                            <p class="small text-muted">New customer registration</p>
                                            <div class="queue-info">
                                                <small class="text-muted">
                                                    Pending: <?php echo $queueStats['registration']['pending']; ?><br>
                                                    Window 3
                                                </small>
                                            </div>
                                            <input type="radio" name="category" value="registration" class="d-none" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-ticket-alt me-2"></i>Generate Priority Number
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                        
                        <!-- Service Hours Info -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6><i class="fas fa-info-circle me-2"></i>Service Information</h6>
                            <ul class="mb-0 small">
                                <li><strong>Service Hours:</strong> 6:00 AM - 6:00 PM</li>
                                <li><strong>Priority Calling:</strong> 7:00 AM - 6:00 PM</li>
                                <li><strong>Lunch Break:</strong> 12:00 PM - 1:00 PM</li>
                                <li><strong>Daily Capacity:</strong> 500 numbers per category</li>
                                <li><strong>Timer Interval:</strong> 5 minutes between calls</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Category selection
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                document.querySelectorAll('.category-card').forEach(c => c.classList.remove('selected'));
                
                // Add selected class to clicked card
                this.classList.add('selected');
                
                // Check the radio button
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
            });
        });
        
        // Form validation
        document.getElementById('priorityForm').addEventListener('submit', function(e) {
            const selectedCategory = document.querySelector('input[name="category"]:checked');
            if (!selectedCategory) {
                e.preventDefault();
                alert('Please select a service category');
                return false;
            }
        });
    </script>
</body>
</html>
