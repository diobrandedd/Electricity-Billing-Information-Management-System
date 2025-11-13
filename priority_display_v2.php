<?php
/**
 * Priority Queue Display V2 - Public Display
 * Shows priority queue status with pipe-like UI for all categories
 */

require_once 'config/config.php';
require_once 'includes/PriorityNumberGeneratorV2.php';

$priorityGenerator = new PriorityNumberGeneratorV2();

// Set page to auto-refresh every 10 seconds
header('Refresh: 10');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Priority Queue Display - <?php echo getSystemSetting('company_name', 'SOCOTECO II'); ?></title>
    
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
        
        html, body {
            height: 100%;
            overflow: hidden;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-yellow) 100%);
            font-family: 'Arial', sans-serif;
            color: white;
        }
        
        .display-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: rgba(30, 58, 138, 0.9);
            color: white;
            padding: 1rem 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .header p {
            margin: 0.5rem 0 0 0;
            font-size: 1.2rem;
        }
        
        .content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .queue-windows {
            display: flex;
            gap: 3rem;
            width: 100%;
            max-width: 1400px;
        }
        
        .queue-window {
            flex: 1;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            color: #333;
            min-height: 500px;
            display: flex;
            flex-direction: column;
        }
        
        .window-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--primary-orange);
        }
        
        .window-header h3 {
            color: var(--dark-blue);
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
        }
        
        .window-header p {
            color: #666;
            margin: 0.5rem 0 0 0;
            font-size: 1.1rem;
        }
        
        .pipe-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }
        
        .pipe-section {
            width: 100%;
            max-width: 300px;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            margin: 0.5rem 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .previous-numbers {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .current-number {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            border: 4px solid #fff;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            position: relative;
        }
        
        .current-number::before {
            content: '';
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            background: linear-gradient(45deg, #28a745, #20c997, #28a745);
            border-radius: 20px;
            z-index: -1;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 0.7; }
            50% { opacity: 1; }
            100% { opacity: 0.7; }
        }
        
        .next-numbers {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .number-display {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .customer-name {
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .section-label {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .next-number-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            margin: 0.25rem 0;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .lunch-break-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .lunch-break-content {
            background: linear-gradient(135deg, #fd7e14, #e8590c);
            color: white;
            padding: 4rem;
            border-radius: 25px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .lunch-break-content h2 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .lunch-break-content p {
            font-size: 1.5rem;
            margin: 0;
        }
        
        .status-bar {
            background: rgba(0, 0, 0, 0.1);
            padding: 1rem;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        @media (max-width: 1200px) {
            .queue-windows {
                flex-direction: column;
                gap: 2rem;
            }
            
            .queue-window {
                min-height: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="display-container">
        <div class="header">
            <h1><i class="fas fa-ticket-alt me-3"></i><?php echo getSystemSetting('company_name', 'SOCOTECO II'); ?></h1>
            <p>Priority Queue Display - <?php echo date('F j, Y'); ?></p>
            <div class="status-bar">
                <i class="fas fa-clock me-2"></i>
                Current Time: <span id="currentTime"><?php echo date('h:i A'); ?></span>
                <?php if ($priorityGenerator->isLunchBreak()): ?>
                    <span class="ms-3 text-warning">
                        <i class="fas fa-utensils me-1"></i>Lunch Break (12:00 PM - 1:00 PM)
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="content">
            <div class="queue-windows">
                <?php 
                $categories = ['payment', 'claims', 'registration'];
                $categoryNames = ['Payment', 'Claims', 'Registration'];
                $windowNumbers = [1, 2, 3];
                
                for ($i = 0; $i < 3; $i++): 
                    $category = $categories[$i];
                    $categoryName = $categoryNames[$i];
                    $windowNumber = $windowNumbers[$i];
                    
                    $current = $priorityGenerator->getCurrentPriorityNumber($category);
                    $next = $priorityGenerator->getNextPriorityNumbers($category, null, 3);
                    $previous = $priorityGenerator->getPreviousPriorityNumbers($category, null, 1);
                ?>
                    <div class="queue-window">
                        <div class="window-header">
                            <h3>Window <?php echo $windowNumber; ?></h3>
                            <p><?php echo $categoryName; ?> Queue</p>
                        </div>
                        
                        <div class="pipe-container">
                            <!-- Previous Numbers (Top) -->
                            <div class="pipe-section previous-numbers">
                                <div class="section-label">Previously Served</div>
                                <div class="number-display">
                                    <?php if ($previous && count($previous) > 0): ?>
                                        <?php echo $previous[0]['priority_number']; ?>
                                    <?php else: ?>
                                        --
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Current Number (Middle) -->
                            <div class="pipe-section current-number">
                                <div class="section-label">Now Serving</div>
                                <div class="number-display">
                                    <?php if ($current): ?>
                                        <?php echo $current['priority_number']; ?>
                                    <?php else: ?>
                                        --
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Next Numbers (Bottom) -->
                            <div class="pipe-section next-numbers">
                                <div class="section-label">Next Numbers</div>
                                <?php if ($next && count($next) > 0): ?>
                                    <?php foreach ($next as $nextItem): ?>
                                        <div class="next-number-item">
                                            <?php echo $nextItem['priority_number']; ?>
                                        </div>
                                    <?php endfor; ?>
                                <?php else: ?>
                                    <div class="next-number-item">No pending numbers</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    
    <!-- Lunch Break Overlay -->
    <?php if ($priorityGenerator->isLunchBreak()): ?>
        <div class="lunch-break-overlay">
            <div class="lunch-break-content">
                <h2><i class="fas fa-utensils me-3"></i>Lunch Break</h2>
                <p>Service will resume at 1:00 PM</p>
            </div>
        </div>
    <?php endif; ?>
    
    <script>
        // Update current time every second
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            document.getElementById('currentTime').textContent = timeString;
        }
        
        // Update time on page load and every second
        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);
    </script>
</body>
</html>
