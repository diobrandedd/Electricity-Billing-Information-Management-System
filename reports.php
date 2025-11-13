<?php
$page_title = 'Reports & Analytics';
require_once 'includes/header.php';
requireRole(['admin']);

$report_type = $_GET['report'] ?? 'dashboard';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$message = '';

// Get report data based on type
switch ($report_type) {
    case 'collection':
        $collection_data = fetchAll("
            SELECT 
                DATE(p.payment_date) as payment_date,
                COUNT(*) as payment_count,
                SUM(p.amount_paid) as total_collection
            FROM payments p
            WHERE p.payment_date BETWEEN ? AND ?
            GROUP BY DATE(p.payment_date)
            ORDER BY payment_date DESC
        ", [$date_from, $date_to]);
        
        $total_collection = array_sum(array_column($collection_data, 'total_collection'));
        $total_payments = array_sum(array_column($collection_data, 'payment_count'));
        break;
        
    case 'aging':
        $aging_data = fetchAll("
            SELECT 
                c.account_number,
                c.first_name,
                c.last_name,
                b.bill_number,
                b.total_amount,
                b.due_date,
                DATEDIFF(CURDATE(), b.due_date) as days_overdue,
                CASE 
                    WHEN DATEDIFF(CURDATE(), b.due_date) <= 0 THEN 'Current'
                    WHEN DATEDIFF(CURDATE(), b.due_date) <= 30 THEN '1-30 Days'
                    WHEN DATEDIFF(CURDATE(), b.due_date) <= 60 THEN '31-60 Days'
                    WHEN DATEDIFF(CURDATE(), b.due_date) <= 90 THEN '61-90 Days'
                    ELSE 'Over 90 Days'
                END as aging_category
            FROM bills b
            JOIN customers c ON b.customer_id = c.customer_id
            WHERE b.status IN ('pending', 'overdue')
            ORDER BY days_overdue DESC
        ");
        
        $aging_summary = [];
        foreach ($aging_data as $row) {
            $category = $row['aging_category'];
            if (!isset($aging_summary[$category])) {
                $aging_summary[$category] = ['count' => 0, 'amount' => 0];
            }
            $aging_summary[$category]['count']++;
            $aging_summary[$category]['amount'] += $row['total_amount'];
        }
        break;
        
    case 'revenue':
        $revenue_data = fetchAll("
            SELECT 
                DATE_FORMAT(p.payment_date, '%Y-%m') as month,
                SUM(p.amount_paid) as total_revenue,
                COUNT(*) as payment_count
            FROM payments p
            WHERE p.payment_date BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
            ORDER BY month DESC
        ", [$date_from, $date_to]);
        
        $total_revenue = array_sum(array_column($revenue_data, 'total_revenue'));
        break;
        
    case 'usage':
        $usage_data = fetchAll("
            SELECT 
                c.barangay,
                c.municipality,
                cc.category_name,
                COUNT(DISTINCT c.customer_id) as customer_count,
                AVG(mr.consumption) as avg_consumption,
                SUM(mr.consumption) as total_consumption
            FROM meter_readings mr
            JOIN customers c ON mr.customer_id = c.customer_id
            JOIN customer_categories cc ON c.category_id = cc.category_id
            WHERE mr.reading_date BETWEEN ? AND ?
            GROUP BY c.barangay, c.municipality, cc.category_name
            ORDER BY total_consumption DESC
        ", [$date_from, $date_to]);
        break;
        
    case 'customers':
        $customer_data = fetchAll("
            SELECT 
                cc.category_name,
                COUNT(*) as customer_count,
                COUNT(CASE WHEN c.is_active = 1 THEN 1 END) as active_customers
            FROM customers c
            JOIN customer_categories cc ON c.category_id = cc.category_id
            GROUP BY cc.category_name
            ORDER BY customer_count DESC
        ");
        break;
    
    case 'support':
        // KPIs for customer support (chat + feedback)
        $support_kpis = [];
        $row = fetchOne("SELECT COUNT(*) as c FROM chat_sessions WHERE DATE(created_at) BETWEEN ? AND ?", [$date_from, $date_to]);
        $support_kpis['chat_sessions'] = $row ? (int)$row['c'] : 0;
        $row = fetchOne("SELECT COUNT(*) as c FROM chat_messages WHERE DATE(created_at) BETWEEN ? AND ?", [$date_from, $date_to]);
        $support_kpis['chat_msgs_total'] = $row ? (int)$row['c'] : 0;
        $row = fetchOne("SELECT COUNT(*) as c FROM chat_messages WHERE sender_type='admin' AND DATE(created_at) BETWEEN ? AND ?", [$date_from, $date_to]);
        $support_kpis['chat_msgs_admin'] = $row ? (int)$row['c'] : 0;
        $row = fetchOne("SELECT COUNT(*) as c FROM chat_messages WHERE sender_type='customer' AND DATE(created_at) BETWEEN ? AND ?", [$date_from, $date_to]);
        $support_kpis['chat_msgs_customer'] = $row ? (int)$row['c'] : 0;
        $row = fetchOne("SELECT COUNT(*) as c FROM feedback WHERE DATE(created_at) BETWEEN ? AND ?", [$date_from, $date_to]);
        $support_kpis['feedback_count'] = $row ? (int)$row['c'] : 0;
        $row = fetchOne("SELECT COUNT(*) as c FROM feedback_replies WHERE DATE(created_at) BETWEEN ? AND ?", [$date_from, $date_to]);
        $support_kpis['feedback_replies'] = $row ? (int)$row['c'] : 0;

        // Average first response time (minutes) per session
        $avg_resp = fetchOne("
            SELECT AVG(TIMESTAMPDIFF(MINUTE, first_cust, first_admin)) as avg_mins FROM (
                SELECT s.session_id,
                       MIN(CASE WHEN m.sender_type='customer' THEN m.created_at END) as first_cust,
                       MIN(CASE WHEN m.sender_type='admin' THEN m.created_at END) as first_admin
                FROM chat_sessions s
                JOIN chat_messages m ON m.session_id = s.session_id
                WHERE DATE(s.created_at) BETWEEN ? AND ?
                GROUP BY s.session_id
            ) t
            WHERE first_cust IS NOT NULL AND first_admin IS NOT NULL
        ", [$date_from, $date_to]);
        $support_kpis['avg_first_response_mins'] = ($avg_resp && $avg_resp['avg_mins'] !== null) ? (float)$avg_resp['avg_mins'] : 0.0;

        // Daily breakdown (up to 31 days span)
        $support_daily = fetchAll("
            SELECT d.dt as day,
                   COALESCE(cs.cnt,0) as sessions,
                   COALESCE(cm.cnt,0) as messages,
                   COALESCE(fb.cnt,0) as feedback
            FROM (
                SELECT DATE_ADD(?, INTERVAL seq DAY) dt
                FROM (
                    SELECT 0 seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL
                    SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL
                    SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL
                    SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL
                    SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30
                ) s
                WHERE DATE_ADD(?, INTERVAL seq DAY) <= ?
            ) d
            LEFT JOIN (SELECT DATE(created_at) d, COUNT(*) cnt FROM chat_sessions WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at)) cs ON cs.d = d.dt
            LEFT JOIN (SELECT DATE(created_at) d, COUNT(*) cnt FROM chat_messages WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at)) cm ON cm.d = d.dt
            LEFT JOIN (SELECT DATE(created_at) d, COUNT(*) cnt FROM feedback WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at)) fb ON fb.d = d.dt
            ORDER BY d.dt DESC
        ", [$date_from, $date_from, $date_to, $date_from, $date_to, $date_from, $date_to, $date_from, $date_to]);
        break;
}

// Get system statistics
$stats = [
    'total_customers' => fetchOne("SELECT COUNT(*) as count FROM customers WHERE is_active = 1")['count'],
    'total_bills' => fetchOne("SELECT COUNT(*) as count FROM bills")['count'],
    'total_payments' => fetchOne("SELECT COUNT(*) as count FROM payments")['count'],
    'total_revenue' => fetchOne("SELECT COALESCE(SUM(amount_paid), 0) as total FROM payments")['total'],
    'overdue_bills' => fetchOne("SELECT COUNT(*) as count FROM bills WHERE status = 'overdue' OR (status = 'pending' AND due_date < CURDATE())")['count'],
    'pending_bills' => fetchOne("SELECT COUNT(*) as count FROM bills WHERE status = 'pending'")['count']
];
?>

<?php
// Resolve report title for print header
switch ($report_type) {
    case 'collection':
        $report_title = 'Daily Collection Report';
        break;
    case 'aging':
        $report_title = 'Aging Report - Outstanding Bills';
        break;
    case 'revenue':
        $report_title = 'Monthly Revenue Report';
        break;
    case 'usage':
        $report_title = 'Usage Report by Location and Category';
        break;
    case 'customers':
        $report_title = 'Customer Distribution by Category';
        break;
    case 'support':
        $report_title = 'Customer Support Report';
        break;
    default:
        $report_title = 'Reports Dashboard';
}
?>

<style>
@media print {
    @page { size: A4; margin: 12mm; }
    body { background: #fff !important; color: #000 !important; }
    .sidebar, .navbar, .btn, .nav, .nav-tabs, .dataTables_filter, .dataTables_length, .dataTables_info, .dataTables_paginate, .footer { display: none !important; }
    .card { box-shadow: none !important; border: none !important; }
    .card-header { border: none !important; }
    .card-body { padding: 0 !important; }
    .table { width: 100%; border-collapse: collapse !important; }
    .table th, .table td { border: 1px solid #333 !important; padding: 6px !important; }
    thead { display: table-header-group; }
    .screen-only { display: none !important; }
    .print-only { display: block !important; }
}
.print-only { display: none; }
.print-header { margin-bottom: 12px; }
.print-header .logo { height: 40px; vertical-align: middle; margin-right: 8px; }
.print-header .title { font-size: 1.25rem; font-weight: 700; }
.print-header .meta { color: #6c757d; font-size: 0.9rem; }
</style>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Reports & Analytics
                </h5>
                <div class="screen-only">
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="print-only print-header">
                    <div>
                        <img src="img/logo1.png" class="logo" alt="<?php echo htmlspecialchars(getSystemSetting('company_name', 'SOCOTECO II')); ?>">
                        <span class="title"><?php echo htmlspecialchars(getSystemSetting('company_name', 'SOCOTECO II')); ?></span>
                    </div>
                    <div class="meta">
                        <?php echo htmlspecialchars($report_title); ?> — Date Range: <?php echo htmlspecialchars($date_from); ?> to <?php echo htmlspecialchars($date_to); ?>
                    </div>
                    <hr>
                </div>
                <!-- Report Navigation -->
                <ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $report_type == 'dashboard' ? 'active' : ''; ?>" 
                                onclick="loadReport('dashboard')">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $report_type == 'collection' ? 'active' : ''; ?>" 
                                onclick="loadReport('collection')">
                            <i class="fas fa-money-bill-wave me-2"></i>Collection Report
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $report_type == 'aging' ? 'active' : ''; ?>" 
                                onclick="loadReport('aging')">
                            <i class="fas fa-clock me-2"></i>Aging Report
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $report_type == 'revenue' ? 'active' : ''; ?>" 
                                onclick="loadReport('revenue')">
                            <i class="fas fa-chart-line me-2"></i>Revenue Report
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $report_type == 'usage' ? 'active' : ''; ?>" 
                                onclick="loadReport('usage')">
                            <i class="fas fa-bolt me-2"></i>Usage Report
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $report_type == 'customers' ? 'active' : ''; ?>" 
                                onclick="loadReport('customers')">
                            <i class="fas fa-users me-2"></i>Customer Report
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $report_type == 'support' ? 'active' : ''; ?>" 
                                onclick="loadReport('support')">
                            <i class="fas fa-headset me-2"></i>Customer Support
                        </button>
                    </li>
                </ul>
                
                <!-- Date Range Filter -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary d-block" onclick="applyDateFilter()">
                            <i class="fas fa-filter me-2"></i>Apply Filter
                        </button>
                    </div>
                </div>
                
                <!-- Report Content -->
                <div id="reportContent">
                    <?php if ($report_type == 'dashboard'): ?>
                        <!-- Dashboard Overview -->
                        <div class="row">
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
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">Total Bills</div>
                                                <div class="h5 mb-0 font-weight-bold"><?php echo number_format($stats['total_bills']); ?></div>
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
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">Total Revenue</div>
                                                <div class="h5 mb-0 font-weight-bold"><?php echo formatCurrency($stats['total_revenue']); ?></div>
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
                        </div>
                        
                    <?php elseif ($report_type == 'collection'): ?>
                        <!-- Collection Report -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">Daily Collection Report</h6>
                                <div>
                                    <span class="badge bg-success me-2">Total: <?php echo formatCurrency($total_collection); ?></span>
                                    <span class="badge bg-info">Payments: <?php echo number_format($total_payments); ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Payment Count</th>
                                                <th>Total Collection</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($collection_data as $row): ?>
                                            <tr>
                                                <td><?php echo formatDate($row['payment_date'], 'M d, Y'); ?></td>
                                                <td><?php echo number_format($row['payment_count']); ?></td>
                                                <td><?php echo formatCurrency($row['total_collection']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($report_type == 'aging'): ?>
                        <!-- Aging Report -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Aging Report - Outstanding Bills</h6>
                            </div>
                            <div class="card-body">
                                <!-- Aging Summary -->
                                <div class="row mb-4">
                                    <?php foreach ($aging_summary as $category => $data): ?>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <h5><?php echo $data['count']; ?></h5>
                                            <small class="text-secondary"><?php echo $category; ?></small>
                                            <br>
                                            <small class="text-primary"><?php echo formatCurrency($data['amount']); ?></small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped data-table">
                                        <thead>
                                            <tr>
                                                <th>Account #</th>
                                                <th>Customer</th>
                                                <th>Bill #</th>
                                                <th>Amount</th>
                                                <th>Due Date</th>
                                                <th>Days Overdue</th>
                                                <th>Category</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($aging_data as $row): ?>
                                            <tr>
                                                <td><?php echo $row['account_number']; ?></td>
                                                <td><?php echo $row['last_name'] . ', ' . $row['first_name']; ?></td>
                                                <td><?php echo $row['bill_number']; ?></td>
                                                <td><?php echo formatCurrency($row['total_amount']); ?></td>
                                                <td><?php echo formatDate($row['due_date'], 'M d, Y'); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $row['days_overdue'] > 0 ? 'bg-danger' : 'bg-success'; ?>">
                                                        <?php echo $row['days_overdue']; ?> days
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning"><?php echo $row['aging_category']; ?></span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($report_type == 'revenue'): ?>
                        <!-- Revenue Report -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Monthly Revenue Report</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <canvas id="revenueChart" height="100"></canvas>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Month</th>
                                                        <th>Revenue</th>
                                                        <th>Payments</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($revenue_data as $row): ?>
                                                    <tr>
                                                        <td><?php echo date('M Y', strtotime($row['month'] . '-01')); ?></td>
                                                        <td><?php echo formatCurrency($row['total_revenue']); ?></td>
                                                        <td><?php echo number_format($row['payment_count']); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($report_type == 'usage'): ?>
                        <!-- Usage Report -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Usage Report by Location and Category</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped data-table">
                                        <thead>
                                            <tr>
                                                <th>Location</th>
                                                <th>Category</th>
                                                <th>Customers</th>
                                                <th>Avg Consumption</th>
                                                <th>Total Consumption</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($usage_data as $row): ?>
                                            <tr>
                                                <td><?php echo $row['barangay'] . ', ' . $row['municipality']; ?></td>
                                                <td><span class="badge bg-primary"><?php echo $row['category_name']; ?></span></td>
                                                <td><?php echo number_format($row['customer_count']); ?></td>
                                                <td><?php echo number_format($row['avg_consumption'], 2); ?> kWh</td>
                                                <td><?php echo number_format($row['total_consumption'], 2); ?> kWh</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($report_type == 'customers'): ?>
                        <!-- Customer Report -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Customer Distribution by Category</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <canvas id="customerChart" height="200"></canvas>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Category</th>
                                                        <th>Total Customers</th>
                                                        <th>Active Customers</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($customer_data as $row): ?>
                                                    <tr>
                                                        <td><?php echo $row['category_name']; ?></td>
                                                        <td><?php echo number_format($row['customer_count']); ?></td>
                                                        <td><?php echo number_format($row['active_customers']); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                    <?php elseif ($report_type == 'support'): ?>
                        <!-- Customer Support Report -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Customer Support KPIs</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <div class="p-3 border rounded h-100">
                                            <div class="small text-uppercase">Chat Sessions</div>
                                            <div class="h4 m-0"><?php echo number_format($support_kpis['chat_sessions'] ?? 0); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 border rounded h-100">
                                            <div class="small text-uppercase">Total Messages</div>
                                            <div class="h4 m-0"><?php echo number_format($support_kpis['chat_msgs_total'] ?? 0); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 border rounded h-100">
                                            <div class="small text-uppercase">Admin Messages</div>
                                            <div class="h4 m-0"><?php echo number_format($support_kpis['chat_msgs_admin'] ?? 0); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 border rounded h-100">
                                            <div class="small text-uppercase">Customer Messages</div>
                                            <div class="h4 m-0"><?php echo number_format($support_kpis['chat_msgs_customer'] ?? 0); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 border rounded h-100">
                                            <div class="small text-uppercase">Feedback Submitted</div>
                                            <div class="h4 m-0"><?php echo number_format($support_kpis['feedback_count'] ?? 0); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 border rounded h-100">
                                            <div class="small text-uppercase">Feedback Replies</div>
                                            <div class="h4 m-0"><?php echo number_format($support_kpis['feedback_replies'] ?? 0); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 border rounded h-100">
                                            <div class="small text-uppercase">Avg First Response</div>
                                            <div class="h4 m-0"><?php echo number_format((float)($support_kpis['avg_first_response_mins'] ?? 0), 1); ?> mins</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Chat Sessions</th>
                                                <th>Messages</th>
                                                <th>Feedback</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($support_daily)): foreach ($support_daily as $row): ?>
                                            <tr>
                                                <td><?php echo formatDate($row['day'], 'M d, Y'); ?></td>
                                                <td><?php echo number_format($row['sessions']); ?></td>
                                                <td><?php echo number_format($row['messages']); ?></td>
                                                <td><?php echo number_format($row['feedback']); ?></td>
                                            </tr>
                                            <?php endforeach; else: ?>
                                            <tr><td colspan="4" class="text-center">No data for selected range.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadReport(reportType) {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    
    let url = `reports.php?report=${reportType}`;
    if (dateFrom) url += `&date_from=${dateFrom}`;
    if (dateTo) url += `&date_to=${dateTo}`;
    
    window.location.href = url;
}

function applyDateFilter() {
    const reportType = '<?php echo $report_type; ?>';
    loadReport(reportType);
}

// Revenue Chart
<?php if ($report_type == 'revenue' && !empty($revenue_data)): ?>
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: [
            <?php foreach ($revenue_data as $row): ?>
                '<?php echo date('M Y', strtotime($row['month'] . '-01')); ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Monthly Revenue',
            data: [
                <?php foreach ($revenue_data as $row): ?>
                    <?php echo $row['total_revenue']; ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: 'rgba(102, 126, 234, 0.8)',
            borderColor: 'rgba(102, 126, 234, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
<?php endif; ?>

// Customer Chart
<?php if ($report_type == 'customers' && !empty($customer_data)): ?>
const customerCtx = document.getElementById('customerChart').getContext('2d');
const customerChart = new Chart(customerCtx, {
    type: 'doughnut',
    data: {
        labels: [
            <?php foreach ($customer_data as $row): ?>
                '<?php echo $row['category_name']; ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            data: [
                <?php foreach ($customer_data as $row): ?>
                    <?php echo $row['customer_count']; ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: [
                '#667eea',
                '#764ba2',
                '#f093fb',
                '#f5576c',
                '#4facfe'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>
