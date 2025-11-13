<?php
$page_title = 'Content Moderation';
require_once '../includes/header.php';

// Get moderation logs
$logs = fetchAll("
    SELECT 
        cm.*,
        c.first_name,
        c.last_name,
        c.account_number
    FROM chat_moderation_logs cm
    JOIN customers c ON cm.customer_id = c.customer_id
    ORDER BY cm.created_at DESC
    LIMIT 100
");

// Get statistics
$stats = [
    'total_flagged' => fetchOne("SELECT COUNT(*) as count FROM chat_moderation_logs")['count'],
    'high_severity' => fetchOne("SELECT COUNT(*) as count FROM chat_moderation_logs WHERE severity = 'high'")['count'],
    'medium_severity' => fetchOne("SELECT COUNT(*) as count FROM chat_moderation_logs WHERE severity = 'medium'")['count'],
    'blocked_messages' => fetchOne("SELECT COUNT(*) as count FROM chat_moderation_logs WHERE action_taken = 'block'")['count']
];

?>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Flagged</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo number_format($stats['total_flagged']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-flag stats-icon"></i>
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
                        <div class="text-xs font-weight-bold text-uppercase mb-1">High Severity</div>
                        <div class="h5 mb-0 font-weight-bold text-danger"><?php echo number_format($stats['high_severity']); ?></div>
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
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Medium Severity</div>
                        <div class="h5 mb-0 font-weight-bold text-warning"><?php echo number_format($stats['medium_severity']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-circle stats-icon"></i>
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
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Blocked Messages</div>
                        <div class="h5 mb-0 font-weight-bold text-info"><?php echo number_format($stats['blocked_messages']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-ban stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Content Moderation Logs
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped data-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Message</th>
                                <th>Severity</th>
                                <th>Flagged Words</th>
                                <th>Action</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($log['account_number']); ?></small>
                                </td>
                                <td>
                                    <div class="message-preview" style="max-width: 300px;">
                                        <?php echo htmlspecialchars(substr($log['message'], 0, 100)); ?>
                                        <?php if (strlen($log['message']) > 100): ?>
                                            <span class="text-muted">...</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $log['severity'] === 'high' ? 'danger' : ($log['severity'] === 'medium' ? 'warning' : 'info'); ?>">
                                        <?php echo ucfirst($log['severity']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo htmlspecialchars($log['flagged_words']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $log['action_taken'] === 'block' ? 'danger' : ($log['action_taken'] === 'warn' ? 'warning' : 'success'); ?>">
                                        <?php echo ucfirst($log['action_taken']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?php echo formatDate($log['created_at'], 'M j, Y g:i A'); ?></small>
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

<?php require_once '../includes/footer.php'; ?>
