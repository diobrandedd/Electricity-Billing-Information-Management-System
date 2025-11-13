<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';

// Get dashboard statistics
$stats = [];

// Total customers
$stats['total_customers'] = fetchOne("SELECT COUNT(*) as count FROM customers WHERE is_active = 1")['count'];

// Total bills this month
$stats['bills_this_month'] = fetchOne("
    SELECT COUNT(*) as count FROM bills 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
")['count'];

// Total payments this month
$stats['payments_this_month'] = fetchOne("
    SELECT COALESCE(SUM(amount_paid), 0) as total FROM payments 
    WHERE MONTH(payment_date) = MONTH(CURRENT_DATE()) 
    AND YEAR(payment_date) = YEAR(CURRENT_DATE())
")['total'];

// Overdue bills
$stats['overdue_bills'] = fetchOne("
    SELECT COUNT(*) as count FROM bills 
    WHERE status = 'overdue' OR (status = 'pending' AND due_date < CURDATE())
")['count'];

// Priority system statistics
require_once 'includes/PriorityNumberGeneratorV2.php';
$priorityGenerator = new PriorityNumberGeneratorV2();
$priorityStats = $priorityGenerator->getQueueStatistics();

// Total pending priority numbers
$stats['total_pending_priority'] = $priorityStats['payment']['pending'] + 
                                  $priorityStats['claims']['pending'] + 
                                  $priorityStats['registration']['pending'];

// Total served today
$stats['total_served_today'] = $priorityStats['payment']['served'] + 
                              $priorityStats['claims']['served'] + 
                              $priorityStats['registration']['served'];

// Recent activities
$recent_activities = fetchAll("
    SELECT 
        a.action,
        a.table_name,
        a.created_at,
        u.full_name as user_name
    FROM audit_trail a
    JOIN users u ON a.user_id = u.user_id
    ORDER BY a.created_at DESC
    LIMIT 10
");

// Monthly revenue chart data
$monthly_revenue = fetchAll("
    SELECT 
        DATE_FORMAT(payment_date, '%Y-%m') as month,
        SUM(amount_paid) as total
    FROM payments 
    WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY month
");

?>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Customers</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo number_format($stats['total_customers']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Bills This Month</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo number_format($stats['bills_this_month']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Revenue This Month</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo formatCurrency($stats['payments_this_month']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-peso-sign stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Overdue Bills</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo number_format($stats['overdue_bills']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Pending Priority</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo number_format($stats['total_pending_priority']); ?></div>
                        <div class="small text-muted">
                            P: <?php echo $priorityStats['payment']['pending']; ?> | 
                            C: <?php echo $priorityStats['claims']['pending']; ?> | 
                            R: <?php echo $priorityStats['registration']['pending']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock stats-icon text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Served Today</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo number_format($stats['total_served_today']); ?></div>
                        <div class="small text-muted">
                            P: <?php echo $priorityStats['payment']['served']; ?> | 
                            C: <?php echo $priorityStats['claims']['served']; ?> | 
                            R: <?php echo $priorityStats['registration']['served']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle stats-icon text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Revenue Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Monthly Revenue Trend
                </h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Recent Activities -->
    <div class="col-xl-4 col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Recent Activities
                </h5>
            </div>
            <div class="card-body">
                <?php $max_visible = 5; $total_acts = count($recent_activities); ?>
                <div class="list-group list-group-flush">
                    <?php $i = 0; foreach ($recent_activities as $activity): if ($i++ >= $max_visible) break; ?>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold"><?php echo ucfirst(str_replace('_', ' ', $activity['action'])); ?></div>
                            <small class="text-muted"><?php echo $activity['user_name']; ?></small>
                        </div>
                        <small class="text-muted"><?php echo formatDate($activity['created_at'], 'M j, g:i A'); ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_acts > $max_visible): ?>
                <div class="collapse mt-1" id="recentActivitiesCollapse">
                    <div class="list-group list-group-flush">
                        <?php $j = 0; foreach ($recent_activities as $activity): if ($j++ < $max_visible) continue; ?>
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold"><?php echo ucfirst(str_replace('_', ' ', $activity['action'])); ?></div>
                                <small class="text-muted"><?php echo $activity['user_name']; ?></small>
                            </div>
                            <small class="text-muted"><?php echo formatDate($activity['created_at'], 'M j, g:i A'); ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="text-center mt-2">
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#recentActivitiesCollapse" aria-expanded="false" aria-controls="recentActivitiesCollapse" id="toggleRecentBtn">
                        Show more
                    </button>
                </div>
                <script>
                (function(){
                    var btn = document.getElementById('toggleRecentBtn');
                    var target = document.getElementById('recentActivitiesCollapse');
                    if (!btn || !target) return;
                    target.addEventListener('shown.bs.collapse', function(){ btn.textContent = 'Show less'; });
                    target.addEventListener('hidden.bs.collapse', function(){ btn.textContent = 'Show more'; });
                })();
                </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Priority Queue Status Widget -->
<?php if (in_array($_SESSION['role'], ['admin', 'cashier'])): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-ticket-alt me-2"></i>Priority Queue Status
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Payment Queue</h5>
                                <h3 class="mb-2"><?php echo $priorityStats['payment']['pending']; ?></h3>
                                <small>Pending</small>
                                <div class="mt-2">
                                    <small>Served: <?php echo $priorityStats['payment']['served']; ?></small>
                                </div>
                                <div class="mt-2">
                                    <small>Window 1</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Claims Queue</h5>
                                <h3 class="mb-2"><?php echo $priorityStats['claims']['pending']; ?></h3>
                                <small>Pending</small>
                                <div class="mt-2">
                                    <small>Served: <?php echo $priorityStats['claims']['served']; ?></small>
                                </div>
                                <div class="mt-2">
                                    <small>Window 2</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Registration Queue</h5>
                                <h3 class="mb-2"><?php echo $priorityStats['registration']['pending']; ?></h3>
                                <small>Pending</small>
                                <div class="mt-2">
                                    <small>Served: <?php echo $priorityStats['registration']['served']; ?></small>
                                </div>
                                <div class="mt-2">
                                    <small>Window 3</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="<?php echo url('priority_queue_management.php'); ?>" class="btn btn-success me-2">
                        <i class="fas fa-tasks me-2"></i>Manage Queue
                    </a>
                    <a href="<?php echo url('priority_display.php'); ?>" class="btn btn-info me-2">
                        <i class="fas fa-desktop me-2"></i>View Display
                    </a>
                    <a href="<?php echo url('priority_settings.php'); ?>" class="btn btn-warning">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row mt-4">
    <!-- Quick Actions -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (in_array($_SESSION['role'], ['admin', 'cashier'])): ?>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo url('customers.php?action=add'); ?>" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus me-2"></i>Add Customer
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (in_array($_SESSION['role'], ['admin', 'meter_reader'])): ?>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo url('meter_readings.php?action=add'); ?>" class="btn btn-success w-100">
                            <i class="fas fa-tachometer me-2"></i>Record Reading
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (in_array($_SESSION['role'], ['admin', 'cashier'])): ?>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo url('payments.php?action=add'); ?>" class="btn btn-warning w-100">
                            <i class="fas fa-credit-card me-2"></i>Process Payment
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (in_array($_SESSION['role'], ['admin', 'cashier'])): ?>
					<div class="col-md-3 mb-3">
						<a href="<?php echo url('priority_queue_management.php'); ?>" class="btn btn-success w-100">
							<i class="fas fa-ticket-alt me-2"></i>Priority Management
						</a>
					</div>
                    <?php endif; ?>
                    
                    <?php if (in_array($_SESSION['role'], ['admin'])): ?>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo url('reports.php'); ?>" class="btn btn-info w-100">
                            <i class="fas fa-chart-bar me-2"></i>View Reports
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Defer chart init until after Chart.js is loaded (footer includes Chart.js)
$chart_labels = [];
foreach ($monthly_revenue as $revenue) {
    $chart_labels[] = date('M Y', strtotime($revenue['month'] . '-01'));
}
$chart_values = array_map(function($r){ return (float)$r['total']; }, $monthly_revenue);

$additional_scripts = '<script>' .
"(function(){\n" .
"  var el = document.getElementById('revenueChart'); if(!el) return;\n" .
"  var ctx = el.getContext('2d');\n" .
"  new Chart(ctx, {\n" .
"    type: 'line',\n" .
"    data: {\n" .
"      labels: " . json_encode($chart_labels) . ",\n" .
"      datasets: [{ label: 'Monthly Revenue', data: " . json_encode($chart_values) . ",\n" .
"        borderColor: 'rgb(102, 126, 234)', backgroundColor: 'rgba(102, 126, 234, 0.1)', tension: 0.4, fill: true }]\n" .
"    },\n" .
"    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: function(v){ return '₱' + Number(v).toLocaleString(); } } } },\n" .
"      plugins: { tooltip: { callbacks: { label: function(ctx){ return 'Revenue: ₱' + Number(ctx.parsed.y||0).toLocaleString(); } } } } }\n" .
"  });\n" .
"})();\n" .
"</script>";

require_once 'includes/footer.php';
?>
