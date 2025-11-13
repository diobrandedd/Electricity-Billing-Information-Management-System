<?php
// Assumes config has been loaded by the including file

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? null;

$adminMenu = [
	[
		'label' => 'Dashboard',
		'icon' => 'fas fa-tachometer-alt',
		'path' => 'dashboard.php',
		'roles' => ['admin','cashier','meter_reader']
	],
	[
		'label' => 'Customers',
		'icon' => 'fas fa-users',
		'path' => 'customers.php',
		'roles' => ['admin','cashier']
	],
	[
		'label' => 'Meter Readings',
		'icon' => 'fas fa-tachometer',
		'path' => 'meter_readings.php',
		'roles' => ['admin','meter_reader']
	],
	[
		'label' => 'Billing',
		'icon' => 'fas fa-file-invoice',
		'children' => [
			['label' => 'Bills', 'path' => 'bills.php', 'roles' => ['admin','cashier']],
			['label' => 'Payments', 'path' => 'payments.php', 'roles' => ['admin','cashier']],
		],
		'roles' => ['admin','cashier']
	],
	[
		'label' => 'Priority System',
		'icon' => 'fas fa-ticket-alt',
		'children' => [
			['label' => 'Priority Management', 'path' => 'priority_queue_management.php', 'roles' => ['admin','cashier']],
			['label' => 'Display', 'path' => 'priority_display.php', 'roles' => ['admin','cashier']],
			['label' => 'Priority Settings', 'path' => 'priority_settings.php', 'roles' => ['admin']],
		],
		'roles' => ['admin','cashier']
	],
	[
		'label' => 'Reports',
		'icon' => 'fas fa-chart-bar',
		'path' => 'reports.php',
		'roles' => ['admin']
	],
	[
		'label' => 'User Management',
		'icon' => 'fas fa-user-cog',
		'path' => 'users.php',
		'roles' => ['admin']
	],
	[
		'label' => 'Settings',
		'icon' => 'fas fa-cog',
		'path' => 'settings.php',
		'roles' => ['admin']
	],
	[
		'label' => 'Support',
		'icon' => 'fas fa-headset',
		'children' => [
			['label' => 'Chat', 'path' => 'admin/chat.php', 'roles' => ['admin']],
			['label' => 'Content Moderation', 'path' => 'admin/content_moderation.php', 'roles' => ['admin']],
			['label' => 'Customer Feedback', 'path' => 'feedback_management.php', 'roles' => ['admin']],
		],
		'roles' => ['admin']
	],
];

function menu_item_visible($item_roles, $user_role) {
	if (!$item_roles) return true;
	return in_array($user_role, $item_roles, true);
}

?>
<div class="position-sticky pt-3">
	<div class="text-center mb-4">
		<div class="sidebar-logo mb-3" style="background-image: url('<?php echo url('img/logo1.png'); ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;">
		</div>
		<h4 class="text-white mb-1">
			<i class="fas fa-bolt me-2"></i>SOCOTECO II
		</h4>
		<small class="text-white-50">Billing Management System</small>
		<hr class="text-white-50 my-3">
	</div>

	<ul class="nav flex-column" id="sidebarAccordion">
		<?php foreach ($adminMenu as $item): ?>
			<?php if (!menu_item_visible($item['roles'] ?? [], $user_role)) continue; ?>
			<?php if (isset($item['children'])): ?>
				<?php
					$group_id = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $item['label']));
					$child_paths = array_map(function($c) { return $c['path'] ?? ''; }, $item['children']);
					$is_active_group = in_array($current_page, $child_paths, true);
				?>
				<li class="nav-item">
					<a class="nav-link d-flex justify-content-between align-items-center sidebar-group-toggle <?php echo $is_active_group ? 'active' : ''; ?>" data-group-id="<?php echo $group_id; ?>" data-bs-toggle="collapse" href="#collapse-<?php echo $group_id; ?>" role="button" aria-expanded="<?php echo $is_active_group ? 'true' : 'false'; ?>" aria-controls="collapse-<?php echo $group_id; ?>">
						<span><i class="<?php echo $item['icon']; ?>"></i><?php echo $item['label']; ?></span>
						<div class="d-flex align-items-center">
							<?php if ($item['label'] === 'Support'): ?>
								<span class="badge bg-danger me-2" id="support-notification-badge" style="display: none;">!</span>
							<?php endif; ?>
						<i class="fa fa-chevron-right caret-icon" style="transition: transform .2s; <?php echo $is_active_group ? 'transform: rotate(90deg);' : ''; ?>"></i>
						</div>
					</a>
					<div class="collapse <?php echo $is_active_group ? 'show' : ''; ?>" id="collapse-<?php echo $group_id; ?>" data-bs-parent="#sidebarAccordion">
						<ul class="nav flex-column ms-3 mt-1">
							<?php foreach ($item['children'] as $child): ?>
								<?php if (!menu_item_visible($child['roles'] ?? [], $user_role)) continue; ?>
								<li class="nav-item">
									<a class="nav-link <?php echo $current_page === ($child['path'] ?? '') ? 'active' : ''; ?>" href="<?php echo url($child['path']); ?>">
										<i class="fa fa-angle-right"></i> <?php echo $child['label']; ?>
										<?php if ($child['path'] === 'feedback_management.php'): ?>
											<span class="badge bg-danger ms-2" id="feedback-notification-badge" style="display: none;">0</span>
										<?php elseif ($child['path'] === 'admin/chat.php'): ?>
											<span class="badge bg-danger ms-2" id="chat-notification-badge" style="display: none;">0</span>
										<?php endif; ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</li>
			<?php else: ?>
				<li class="nav-item">
					<a class="nav-link <?php echo $current_page === ($item['path'] ?? '') ? 'active' : ''; ?>" href="<?php echo url($item['path']); ?>">
						<i class="<?php echo $item['icon']; ?>"></i><?php echo $item['label']; ?>
					</a>
				</li>
			<?php endif; ?>
		<?php endforeach;
    
        ?>
	</ul>
</div>

<script>
// Feedback notification system
(function() {
    const notificationBadge = document.getElementById('feedback-notification-badge');
    if (!notificationBadge) return;
    
    function updateNotificationBadge() {
        fetch('<?php echo url('ajax/feedback.php'); ?>?action=unread_count')
            .then(response => response.json())
            .then(data => {
                if (data && data.ok) {
                    const count = data.unread_count || 0;
                    if (count > 0) {
                        notificationBadge.textContent = count;
                        notificationBadge.style.display = 'inline-block';
                    } else {
                        notificationBadge.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error updating notification badge:', error);
            });
    }
    
    // Update badge on page load
    updateNotificationBadge();
    
    // Update badge every 30 seconds
    setInterval(updateNotificationBadge, 30000);
    
    // Update badge when feedback management page is visited
    if (window.location.pathname.includes('feedback_management.php')) {
        // Mark all feedback as read when admin visits the page
        fetch('<?php echo url('ajax/feedback.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=mark_all_read'
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.ok) {
                updateNotificationBadge();
            }
        })
        .catch(error => {
            console.error('Error marking feedback as read:', error);
        });
    }
})();

// Chat notification system
(function() {
    const notificationBadge = document.getElementById('chat-notification-badge');
    if (!notificationBadge) return;
    
    function updateChatNotificationBadge() {
        fetch('<?php echo url('ajax/chat.php'); ?>?action=unread_count')
            .then(response => response.json())
            .then(data => {
                if (data && data.count !== undefined) {
                    const count = data.count || 0;
                    if (count > 0) {
                        notificationBadge.textContent = count;
                        notificationBadge.style.display = 'inline-block';
                    } else {
                        notificationBadge.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error updating chat notification badge:', error);
            });
    }
    
    // Update badge on page load
    updateChatNotificationBadge();
    
    // Update badge every 30 seconds
    setInterval(updateChatNotificationBadge, 30000);
    
    // Update badge when chat page is visited
    if (window.location.pathname.includes('admin/chat.php')) {
        // Mark all chat messages as read when admin visits the page
        fetch('<?php echo url('ajax/chat.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=mark_all_read'
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success) {
                updateChatNotificationBadge();
            }
        })
        .catch(error => {
            console.error('Error marking chat messages as read:', error);
        });
     }
 })();

 // Support notification system - shows "!" if there are any chat or feedback notifications
 (function() {
     const supportBadge = document.getElementById('support-notification-badge');
     if (!supportBadge) return;
     
     function updateSupportNotificationBadge() {
         // Check both chat and feedback notifications
         Promise.all([
             fetch('<?php echo url('ajax/chat.php'); ?>?action=unread_count'),
             fetch('<?php echo url('ajax/feedback.php'); ?>?action=unread_count')
         ])
         .then(responses => Promise.all(responses.map(r => r.json())))
         .then(([chatData, feedbackData]) => {
             let hasNotifications = false;
             
             // Check chat notifications
             if (chatData && chatData.count !== undefined && chatData.count > 0) {
                 hasNotifications = true;
             }
             
             // Check feedback notifications
             if (feedbackData && feedbackData.ok && feedbackData.unread_count > 0) {
                 hasNotifications = true;
             }
             
             // Show/hide support badge
             if (hasNotifications) {
                 supportBadge.style.display = 'inline-block';
             } else {
                 supportBadge.style.display = 'none';
             }
         })
         .catch(error => {
             console.error('Error updating support notification badge:', error);
         });
     }
     
     // Update badge on page load
     updateSupportNotificationBadge();
     
     // Update badge every 30 seconds
     setInterval(updateSupportNotificationBadge, 30000);
     
     // Update badge when any support page is visited
     if (window.location.pathname.includes('admin/chat.php') || 
         window.location.pathname.includes('feedback_management.php') ||
         window.location.pathname.includes('admin/content_moderation.php')) {
         // Mark all notifications as read when admin visits any support page
         Promise.all([
             fetch('<?php echo url('ajax/chat.php'); ?>', {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                 body: 'action=mark_all_read'
             }),
             fetch('<?php echo url('ajax/feedback.php'); ?>', {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                 body: 'action=mark_all_read'
             })
         ])
         .then(() => {
             updateSupportNotificationBadge();
         })
         .catch(error => {
             console.error('Error marking support notifications as read:', error);
         });
     }
 })();
 </script>

