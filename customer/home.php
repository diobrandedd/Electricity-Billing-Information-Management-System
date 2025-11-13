<?php
require_once __DIR__ . '/../config/config.php';

// Require customer session
if (!isset($_SESSION['customer_id'])) {
    redirect('auth/customer_login.php');
}

$customer_name = $_SESSION['customer_name'] ?? 'Customer';
$customer_id = (int)($_SESSION['customer_id'] ?? 0);

// Fetch recent bills and payments for overview
$recent_bills = fetchAll(
    "SELECT bill_id, bill_number, billing_period_start, billing_period_end, total_amount, status, due_date
     FROM bills WHERE customer_id = ?
     ORDER BY billing_period_end DESC, bill_id DESC LIMIT 5",
    [$customer_id]
);

$recent_payments = fetchAll(
    "SELECT p.payment_id, p.payment_date, p.amount_paid, p.payment_method, p.or_number, b.bill_number
     FROM payments p
     JOIN bills b ON p.bill_id = b.bill_id
     WHERE b.customer_id = ?
     ORDER BY p.payment_date DESC, p.payment_id DESC LIMIT 5",
    [$customer_id]
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Portal - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo url('css/socoteco-theme.css'); ?>" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #FF9A00;
            --secondary-yellow: #FFD93D;
            --dark-blue: #1e3a8a;
            --light-gray: #f8f9fa;
        }

        .btn-primary {
            background-color: var(--primary-orange);
            border-color: var(--primary-orange);
        }

        .btn-primary:hover { background-color: #e68900; border-color: #e68900; }

        .section-card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.08); }
        .kpi-box { border: 1px solid #eee; border-radius: 10px; }

        /* Match secondary navbar styles from users/userindex.php */
        .navbar-nav .nav-link {
            color: #333 !important;
            font-weight: 500;
            margin: 0 10px;
        }
        .navbar-nav .nav-link:hover { color: var(--primary-orange) !important; }

        /* Sticky navbars (two-tier) */
        body { padding-top: 150px; }
        .navbar.fixed-top { z-index: 1040; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        .fixed-top-2 { position: fixed; top: 80px; left: 0; right: 0; z-index: 1035; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        @media (min-width: 992px) {
            body { padding-top: 160px; }
            .fixed-top-2 { top: 80px; }
        }
        @media (max-width: 991.98px) {
            body { padding-top: 120px; }
            .fixed-top-2 { top: 60px; }
        }

        /* Page background image with 60-90% opacity to match userindex */
        body { position: relative; }
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url('../img/socotecobg.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.9;
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <!-- Top Navigation (copied from users/userindex.php) -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" style="background-color: var(--primary-orange)">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="../img/socotecoLogo.png" alt="SOCOTECO II" class="d-inline-block align-text-top" style="max-height: 60px;">
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white">
                    <i class="fas fa-phone me-2"></i>(083) 553-5848 to 50
                </span>
            </div>
        </div>
    </nav>

    <!-- Main Navigation (copied from users/userindex.php) -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top-2" style="background-color: var(--secondary-yellow)">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../users/userindex.php"><i class="fas fa-home me-1"></i>Home</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <?php if (!empty($_SESSION['customer_id'])): ?>
                            <div class="dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['customer_name']); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo url('auth/customer_logout.php'); ?>">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a></li>
                                </ul>
                            </div>
                        <?php elseif (!empty($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                            <div class="dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-shield me-1"></i><?php echo htmlspecialchars($_SESSION['full_name']); ?> (Admin)
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="../dashboard.php">
                                        <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="../feedback_management.php">
                                        <i class="fas fa-comments me-2"></i>Feedback Management
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo url('auth/logout.php'); ?>">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a class="nav-link" href="<?php echo url('auth/customer_login.php'); ?>"><i class="fas fa-user me-1"></i>Members Portal</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success">
                    Welcome, <?php echo htmlspecialchars($customer_name); ?>!
                </div>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card section-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Account</h5>
                        <p class="card-text">Account Number: <?php echo htmlspecialchars($_SESSION['customer_account_number'] ?? ''); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card section-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Overview</h5>
                            <a href="<?php echo url('../bills.php'); ?>" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="accordion" id="overviewAccordionBills">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingBills">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBills" aria-expanded="false" aria-controls="collapseBills">
                                                <i class="fas fa-file-invoice me-2"></i>Recent Bills
                                            </button>
                                        </h2>
                                        <div id="collapseBills" class="accordion-collapse collapse" aria-labelledby="headingBills" data-bs-parent="#overviewAccordionBills">
                                            <div class="accordion-body p-0">
                                                <div class="list-group list-group-flush small">
                                                    <?php if (!empty($recent_bills)): ?>
                                                        <?php foreach ($recent_bills as $b): ?>
                                                            <a href="<?php echo url('../bill_details.php?id=' . $b['bill_id']); ?>" class="list-group-item d-flex justify-content-between align-items-center">
                                                                <span>
                                                                    <strong><?php echo htmlspecialchars($b['bill_number']); ?></strong><br>
                                                                    <small class="text-muted"><?php echo date('M d', strtotime($b['billing_period_start'])); ?> - <?php echo date('M d, Y', strtotime($b['billing_period_end'])); ?></small>
                                                                </span>
                                                                <span class="text-end">
                                                                    <span class="badge bg-primary">₱<?php echo number_format($b['total_amount'], 2); ?></span><br>
                                                                    <?php $status_class = ($b['status'] === 'paid') ? 'bg-success' : (($b['status'] === 'overdue') ? 'bg-danger' : 'bg-warning'); ?>
                                                                    <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($b['status']); ?></span>
                                                                </span>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <div class="list-group-item text-muted">No recent bills.</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="accordion" id="overviewAccordionPayments">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingPayments">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayments" aria-expanded="false" aria-controls="collapsePayments">
                                                <i class="fas fa-credit-card me-2"></i>Recent Payments
                                            </button>
                                        </h2>
                                        <div id="collapsePayments" class="accordion-collapse collapse" aria-labelledby="headingPayments" data-bs-parent="#overviewAccordionPayments">
                                            <div class="accordion-body p-0">
                                                <div class="list-group list-group-flush small">
                                                    <?php if (!empty($recent_payments)): ?>
                                                        <?php foreach ($recent_payments as $p): ?>
                                                            <a href="<?php echo url('../payment_receipt.php?id=' . $p['payment_id']); ?>" target="_blank" class="list-group-item d-flex justify-content-between align-items-center">
                                                                <span>
                                                                    <strong><?php echo htmlspecialchars($p['or_number']); ?></strong> <small class="text-muted">(<?php echo htmlspecialchars($p['bill_number']); ?>)</small><br>
                                                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($p['payment_date'])); ?> • <?php echo ucfirst(str_replace('_', ' ', $p['payment_method'])); ?></small>
                                                                </span>
                                                                <span class="badge bg-success">₱<?php echo number_format($p['amount_paid'], 2); ?></span>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <div class="list-group-item text-muted">No recent payments.</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div class="row mt-4" id="analytics">
            <div class="col-12">
                <div class="card section-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Your Usage Analytics</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <label for="dateRange" class="form-label mb-0 small">Date Range:</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="date" id="startDate" class="form-control form-control-sm" style="width: auto;">
                                    <span class="text-muted">to</span>
                                    <input type="date" id="endDate" class="form-control form-control-sm" style="width: auto;">
                                </div>
                                <button id="applyDateRange" class="btn btn-sm btn-primary">Apply</button>
                                <button id="resetDateRange" class="btn btn-sm btn-outline-secondary" title="Reset to latest data">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="currentRange">All available data</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-6 col-md-3">
                                <div class="kpi-box p-3 h-100">
                                    <div class="small text-muted">Avg. Monthly kWh</div>
                                    <div class="fs-5 fw-bold" id="kpiAvgKwh">—</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="kpi-box p-3 h-100">
                                    <div class="small text-muted">Avg. Bill</div>
                                    <div class="fs-5 fw-bold" id="kpiAvgBill">—</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="kpi-box p-3 h-100">
                                    <div class="small text-muted">Last Month kWh</div>
                                    <div class="fs-5 fw-bold" id="kpiLastKwh">—</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="kpi-box p-3 h-100">
                                    <div class="small text-muted">Last Bill</div>
                                    <div class="fs-5 fw-bold" id="kpiLastBill">—</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-lg-8">
                                <div class="kpi-box p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="text-muted mb-0">Consumption Trend</h6>
                                        <div class="text-end">
                                            <small class="text-muted d-block">Current Meter Reading</small>
                                            <span id="currentMeterReading" class="fw-bold text-primary">--</span>
                                            <small class="text-muted">(Actual meter number)</small>
                                        </div>
                                    </div>
                                    <div class="position-relative">
                                        <canvas id="chartConsumption" height="160"></canvas>
                                        <div class="chart-loading d-none" id="chartLoading">
                                            <div class="d-flex justify-content-center align-items-center h-100">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Chart Legend -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="d-flex flex-wrap gap-3 justify-content-center">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="legend-color" style="width: 20px; height: 20px; background: #0d6efd; border-radius: 3px;"></div>
                                                    <small class="text-muted">kWh Consumption</small>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-arrow-up text-danger"></i>
                                                    <small class="text-muted">Higher than previous</small>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-arrow-down text-success"></i>
                                                    <small class="text-muted">Lower than previous</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="kpi-box p-3 h-100">
                                    <h6 class="text-muted mb-2">Recent Bills</h6>
                                    <div id="recentBills" class="list-group small"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // If navigated with #analytics, scroll to section
        if (window.location.hash === '#analytics') {
            const el = document.getElementById('analytics');
            if (el && el.scrollIntoView) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // Fetch analytics data
        fetch('../ajax/get_bill_details.php?bill_id=0', { cache: 'no-store' })
            .catch(() => null);

        fetch('../ajax/priority_user.php?analytics=1', { cache: 'no-store' })
            .catch(() => null);

        // Initialize date range picker with default values
        initializeDateRange();

        // Load analytics data
        loadAnalyticsData();

        // Date range apply handler
        document.getElementById('applyDateRange').addEventListener('click', function() {
            loadAnalyticsData();
        });

        // Date range reset handler
        document.getElementById('resetDateRange').addEventListener('click', function() {
            // Clear the date inputs
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            
            // Reset the range display
            document.getElementById('currentRange').textContent = 'All available data';
            
            // Load data without date filters (latest data)
            loadLatestAnalyticsData();
        });

        // Enter key handler for date inputs
        document.getElementById('startDate').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') loadAnalyticsData();
        });
        document.getElementById('endDate').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') loadAnalyticsData();
        });

        function initializeDateRange() {
            const today = new Date();
            const sixMonthsAgo = new Date();
            sixMonthsAgo.setMonth(today.getMonth() - 6);
            
            document.getElementById('endDate').value = today.toISOString().split('T')[0];
            document.getElementById('startDate').value = sixMonthsAgo.toISOString().split('T')[0];
        }

        function loadAnalyticsData() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }
            
            if (new Date(startDate) > new Date(endDate)) {
                alert('Start date cannot be after end date');
                return;
            }
            
            const startFormatted = new Date(startDate).toLocaleDateString();
            const endFormatted = new Date(endDate).toLocaleDateString();
            document.getElementById('currentRange').textContent = `${startFormatted} to ${endFormatted}`;
            
            // Show loading
            document.getElementById('chartLoading').classList.remove('d-none');
            
            fetch(`../ajax/customer_analytics.php?start_date=${startDate}&end_date=${endDate}`, { cache: 'no-store' })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => populateAnalytics(data))
                .catch(() => populateAnalytics({
                    kpis: { avg_kwh: 0, avg_bill: 0, last_kwh: 0, last_bill: 0 },
                    months: [], kwh: [], bills: [], recent: [], changes: [], current_reading: 0
                }))
                .finally(() => {
                    document.getElementById('chartLoading').classList.add('d-none');
                });
        }

        function loadLatestAnalyticsData() {
            // Show loading
            document.getElementById('chartLoading').classList.remove('d-none');
            
            // Load data without date filters (gets latest 6 months by default)
            fetch('../ajax/customer_analytics.php', { cache: 'no-store' })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => populateAnalytics(data))
                .catch(() => populateAnalytics({
                    kpis: { avg_kwh: 0, avg_bill: 0, last_kwh: 0, last_bill: 0 },
                    months: [], kwh: [], bills: [], recent: [], changes: [], current_reading: 0
                }))
                .finally(() => {
                    document.getElementById('chartLoading').classList.add('d-none');
                });
        }

        function populateAnalytics(data) {
            document.getElementById('kpiAvgKwh').textContent = formatNumber(data.kpis?.avg_kwh) + ' kWh';
            document.getElementById('kpiAvgBill').textContent = formatCurrency(data.kpis?.avg_bill);
            document.getElementById('kpiLastKwh').textContent = formatNumber(data.kpis?.last_kwh) + ' kWh';
            document.getElementById('kpiLastBill').textContent = formatCurrency(data.kpis?.last_bill);
            
            // Display current meter reading (actual meter number)
            const currentReading = data.current_reading || 0;
            document.getElementById('currentMeterReading').textContent = formatNumber(currentReading) + ' kWh';

            // Chart
            const ctx = document.getElementById('chartConsumption');
            if (ctx) {
                // Destroy existing chart if it exists
                if (window.consumptionChart) {
                    window.consumptionChart.destroy();
                }
                
                // Prepare data points with enhanced information
                const chartData = (data.kwh || []).map((kwh, index) => {
                    const change = data.changes?.[index] || 0;
                    const bill = data.bills?.[index] || 0;
                    const billChange = data.bill_changes?.[index] || 0;
                    
                    return {
                        y: kwh,
                        kwh: kwh,
                        change: change,
                        bill: bill,
                        billChange: billChange,
                        month: data.months?.[index] || ''
                    };
                });
                
                // Also prepare simple data array for Chart.js
                const simpleData = data.kwh || [];
                
                console.log('Chart data:', chartData); // Debug log
                
                // Check if we have data to display
                if (chartData.length === 0) {
                    ctx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-chart-line fa-3x mb-3"></i><p>No consumption data available for the selected period.</p></div>';
                    return;
                }
                
                try {
                    window.consumptionChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.months || [],
                        datasets: [{
                            label: 'kWh Consumption',
                            data: simpleData,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13,110,253,0.1)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            pointBackgroundColor: chartData.map(point => {
                                if (point.change > 0) return '#dc3545'; // Red for increase
                                if (point.change < 0) return '#198754'; // Green for decrease
                                return '#0d6efd'; // Blue for no change
                            }),
                            pointBorderColor: chartData.map(point => {
                                if (point.change > 0) return '#dc3545';
                                if (point.change < 0) return '#198754';
                                return '#0d6efd';
                            })
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: function(context) {
                                        return context[0].label;
                                    },
                                    label: function(context) {
                                        const index = context.dataIndex;
                                        const point = chartData[index];
                                        const kwh = simpleData[index];
                                        
                                        if (!point) return `${kwh} kWh`;
                                        
                                        let tooltip = `Consumption: ${kwh} kWh`;
                                        
                                        if (point.change !== 0) {
                                            const changeIcon = point.change > 0 ? '↗' : '↘';
                                            const changeColor = point.change > 0 ? 'red' : 'green';
                                            tooltip += `\n${changeIcon} ${Math.abs(point.change).toFixed(1)}% change from previous month`;
                                        } else {
                                            tooltip += `\n→ No change from previous month`;
                                        }
                                        
                                        if (point.bill > 0) {
                                            tooltip += `\n\nCurrent Bill: ₱${point.bill.toFixed(2)}`;
                                            if (point.billChange !== 0) {
                                                const billIcon = point.billChange > 0 ? '↗' : '↘';
                                                const billChangeText = point.billChange > 0 ? 'higher' : 'lower';
                                                tooltip += `\n${billIcon} ₱${Math.abs(point.billChange).toFixed(2)} ${billChangeText} than previous month`;
                                            } else {
                                                tooltip += `\n→ Same as previous month`;
                                            }
                                        }
                                        
                                        return tooltip;
                                    }
                                },
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: '#0d6efd',
                                borderWidth: 1
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'kWh Consumption'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value + ' kWh';
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month'
                                }
                            }
                        }
                    }
                });
                
                console.log('Chart created successfully');
                } catch (error) {
                    console.error('Chart creation error:', error);
                    ctx.parentElement.innerHTML = '<div class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><p>Error creating chart: ' + error.message + '</p></div>';
                }

                // Export controls (PNG & CSV) - only add once
                if (!document.getElementById('exportControls')) {
                    const controls = document.createElement('div');
                    controls.id = 'exportControls';
                    controls.className = 'd-flex gap-2 mt-2';
                    controls.innerHTML = `
                        <button class="btn btn-sm btn-outline-secondary" id="exportPng"><i class="fas fa-image me-1"></i>Export PNG</button>
                        <button class="btn btn-sm btn-outline-secondary" id="exportCsv"><i class="fas fa-file-csv me-1"></i>Export CSV</button>
                    `;
                    ctx.parentElement.appendChild(controls);

                    document.getElementById('exportPng').addEventListener('click', function(){
                        const link = document.createElement('a');
                        link.href = window.consumptionChart.toBase64Image('image/png', 1);
                        link.download = 'consumption_chart.png';
                        link.click();
                    });
                    document.getElementById('exportCsv').addEventListener('click', function(){
                        const rows = [['Month','kWh']];
                        const labels = window.consumptionChart.data.labels || [];
                        const values = (window.consumptionChart.data.datasets[0] && window.consumptionChart.data.datasets[0].data) || [];
                        for (let i=0;i<labels.length;i++) rows.push([labels[i], values[i]]);
                        const csv = rows.map(r => r.map(v => '"'+(v ?? '')+'"').join(',')).join('\n');
                        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                        const url = URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url; link.download = 'consumption.csv'; link.click();
                        URL.revokeObjectURL(url);
                    });
                }
            }

            // Recent bills list
            const recent = data.recent || [];
            const list = document.getElementById('recentBills');
            list.innerHTML = recent.length ? '' : '<div class="text-muted">No recent bills.</div>';
            recent.forEach(b => {
                const a = document.createElement('a');
                a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                a.href = '../bill_details.php?id=' + (b.bill_id || '#');
                a.innerHTML = `<span><strong>${b.bill_number || ''}</strong><br><small class="text-muted">${b.period || ''}</small></span><span class="badge bg-primary">${formatCurrency(b.amount || 0)}</span>`;
                list.appendChild(a);
            });
        }

        function formatCurrency(n) {
            const num = Number(n || 0);
            return '₱' + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        function formatNumber(n) {
            const num = Number(n || 0);
            return num.toLocaleString(undefined, { maximumFractionDigits: 2 });
        }
    });
    </script>
</body>
</html>


