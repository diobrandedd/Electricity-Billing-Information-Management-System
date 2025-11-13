<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

function json_ok($data = []) { echo json_encode(['ok' => true] + $data); exit; }
function json_err($msg, $code = 400) { http_response_code($code); echo json_encode(['ok' => false, 'error' => $msg]); exit; }

if ($method === 'GET' && $action === 'list') {
    $sinceId = (int)($_GET['since_id'] ?? 0);
    $sinceReplyId = (int)($_GET['since_reply_id'] ?? 0);
    $limit = min(max((int)($_GET['limit'] ?? 20), 1), 100);
    $q = trim($_GET['q'] ?? '');
    $category = trim($_GET['category'] ?? '');
    
    // Handle both customer and admin sessions
    $customerId = (int)($_SESSION['customer_id'] ?? 0);
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';

    $params = [$customerId, $sinceId];
    $sql = "SELECT f.feedback_id, f.customer_id, COALESCE(f.customer_name, c.first_name) AS customer_name, f.message, f.category, f.status, f.is_read_by_admin, f.created_at,
                   COALESCE(lc.cnt,0) AS like_count,
                   CASE WHEN my.customer_id IS NULL THEN 0 ELSE 1 END AS liked_by_me
            FROM feedback f
            LEFT JOIN customers c ON f.customer_id = c.customer_id
            LEFT JOIN (
                SELECT feedback_id, COUNT(*) AS cnt FROM feedback_likes GROUP BY feedback_id
            ) lc ON lc.feedback_id = f.feedback_id
            LEFT JOIN feedback_likes my ON my.feedback_id = f.feedback_id AND my.customer_id = ?
            WHERE f.feedback_id > ?";
    if ($category !== '') {
        $sql .= " AND f.category = ?";
        $params[] = $category;
    }
    if ($q !== '') {
        $like = '%' . $q . '%';
        $sql .= " AND (f.message LIKE ? OR COALESCE(f.customer_name, c.first_name) LIKE ? OR EXISTS (
                    SELECT 1 FROM feedback_replies r LEFT JOIN users u ON u.user_id = r.admin_user_id
                    WHERE r.feedback_id = f.feedback_id AND (r.message LIKE ? OR u.full_name LIKE ?)
                ))";
        array_push($params, $like, $like, $like, $like);
    }
    $sql .= " ORDER BY f.feedback_id DESC LIMIT ?";
    $params[] = $limit;

    $rows = fetchAll($sql, $params);

    // Attach replies for returned feedback
    $ids = array_column($rows, 'feedback_id');
    $repliesByFeedback = [];
    if (!empty($ids)) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $rws = fetchAll(
            "SELECT r.reply_id, r.feedback_id, r.admin_user_id, u.full_name AS admin_name, r.message, r.created_at
             FROM feedback_replies r
             LEFT JOIN users u ON u.user_id = r.admin_user_id
             WHERE r.feedback_id IN ($in)
             ORDER BY r.created_at ASC",
            $ids
        );
        foreach ($rws as $r) { $repliesByFeedback[$r['feedback_id']][] = $r; }
    }

    // Incremental new replies across all feedback (for realtime updates)
    $repliesNew = [];
    if ($sinceReplyId > 0) {
        $repliesNew = fetchAll(
            "SELECT r.reply_id, r.feedback_id, r.admin_user_id, u.full_name AS admin_name, r.message, r.created_at
             FROM feedback_replies r
             LEFT JOIN users u ON u.user_id = r.admin_user_id
             WHERE r.reply_id > ?
             ORDER BY r.reply_id ASC",
            [$sinceReplyId]
        );
    }

    json_ok(['feedback' => $rows, 'replies' => $repliesByFeedback, 'replies_new' => $repliesNew]);
}

// Toggle like (customer only)
if ($method === 'POST' && $action === 'toggle_like') {
    $customerId = (int)($_SESSION['customer_id'] ?? 0);
    if ($customerId <= 0) { json_err('Not authenticated', 401); }
    $feedbackId = (int)($_POST['feedback_id'] ?? 0);
    if ($feedbackId <= 0) { json_err('Invalid feedback'); }

    $exists = fetchOne('SELECT 1 FROM feedback_likes WHERE feedback_id = ? AND customer_id = ? LIMIT 1', [$feedbackId, $customerId]);
    if ($exists) {
        executeQuery('DELETE FROM feedback_likes WHERE feedback_id = ? AND customer_id = ?', [$feedbackId, $customerId]);
        $liked = false;
    } else {
        executeQuery('INSERT INTO feedback_likes (feedback_id, customer_id) VALUES (?, ?)', [$feedbackId, $customerId]);
        $liked = true;
    }
    $countRow = fetchOne('SELECT COUNT(*) AS cnt FROM feedback_likes WHERE feedback_id = ?', [$feedbackId]);
    $likeCount = (int)($countRow['cnt'] ?? 0);
    json_ok(['liked' => $liked, 'like_count' => $likeCount]);
}

if ($method === 'POST' && $action === 'create') {
    // Only customers can post
    $customerId = $_SESSION['customer_id'] ?? null;
    if (!$customerId) {
        json_err('Not authenticated', 401);
    }

    $category = trim($_POST['category'] ?? '');
    $raw = trim($_POST['message'] ?? '');
    if ($category === '') { json_err('Category is required'); }
    if ($raw === '') { json_err('Message is required'); }
    if (mb_strlen($raw) > 2000) { json_err('Message too long'); }

    $message = $raw; // DB is parameterized; output will be escaped in UI

    try {
        $sql = "INSERT INTO feedback (customer_id, category, message) VALUES (?, ?, ?)";
        $id = executeQueryWithId($sql, [$customerId, $category, $message]);
        if (!$id || $id <= 0) {
            // Fallback if driver didn't return last insert id correctly
            $row = fetchOne('SELECT feedback_id FROM feedback WHERE customer_id = ? ORDER BY feedback_id DESC LIMIT 1', [$customerId]);
            $id = (int)($row['feedback_id'] ?? 0);
        }
        if ($id <= 0) {
            json_err('Failed to create feedback', 500);
        }
        $row = fetchOne("SELECT f.feedback_id, f.customer_id, COALESCE(f.customer_name, c.first_name) AS customer_name, f.message, f.category, f.status, f.is_read_by_admin, f.created_at
                         FROM feedback f LEFT JOIN customers c ON f.customer_id = c.customer_id WHERE f.feedback_id = ?", [$id]);
        json_ok(['feedback' => $row]);
    } catch (Throwable $e) {
        json_err($e->getMessage(), 500);
    }
}

if ($method === 'POST' && $action === 'reply') {
    // Only admins can reply (case-insensitive role check)
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
        json_err('Forbidden', 403);
    }
    $feedbackId = (int)($_POST['feedback_id'] ?? 0);
    $raw = trim($_POST['message'] ?? '');
    if ($feedbackId <= 0) { json_err('Invalid feedback'); }
    if ($raw === '') { json_err('Message is required'); }
    if (mb_strlen($raw) > 4000) { json_err('Message too long'); }

    // Ensure feedback exists
    $exists = fetchOne('SELECT feedback_id FROM feedback WHERE feedback_id = ?', [$feedbackId]);
    if (!$exists) { json_err('Feedback not found', 404); }

    // Insert reply and get ID robustly
    executeQuery('INSERT INTO feedback_replies (feedback_id, admin_user_id, message) VALUES (?, ?, ?)', [$feedbackId, $_SESSION['user_id'], $raw]);
    $id = (int)(getLastInsertId() ?? 0);
    if ($id <= 0) {
        // Fallback: fetch latest reply by this admin on this feedback
        $row = fetchOne('SELECT reply_id FROM feedback_replies WHERE feedback_id = ? AND admin_user_id = ? ORDER BY reply_id DESC LIMIT 1', [$feedbackId, $_SESSION['user_id']]);
        $id = (int)($row['reply_id'] ?? 0);
    }

    if ($id <= 0) {
        json_err('Failed to create reply', 500);
    }

    $reply = fetchOne('SELECT r.reply_id, r.feedback_id, r.admin_user_id, u.full_name AS admin_name, r.message, r.created_at
                       FROM feedback_replies r LEFT JOIN users u ON u.user_id = r.admin_user_id WHERE r.reply_id = ?', [$id]);
    if (!$reply) {
        json_err('Failed to fetch created reply', 500);
    }
    json_ok(['reply' => $reply]);
}

// Flag feedback (admin)
if ($method === 'POST' && $action === 'flag') {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
        json_err('Forbidden', 403);
    }
    $feedbackId = (int)($_POST['feedback_id'] ?? 0);
    if ($feedbackId <= 0) { json_err('Invalid feedback'); }
    executeQuery("UPDATE feedback SET status = 'flagged' WHERE feedback_id = ?", [$feedbackId]);
    json_ok();
}

// Mark reviewed (admin)
if ($method === 'POST' && $action === 'reviewed') {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
        json_err('Forbidden', 403);
    }
    $feedbackId = (int)($_POST['feedback_id'] ?? 0);
    if ($feedbackId <= 0) { json_err('Invalid feedback'); }
    executeQuery("UPDATE feedback SET status = 'reviewed' WHERE feedback_id = ?", [$feedbackId]);
    json_ok();
}

// Delete feedback and its replies (admin)
if ($method === 'POST' && $action === 'delete') {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
        json_err('Forbidden', 403);
    }
    $feedbackId = (int)($_POST['feedback_id'] ?? 0);
    if ($feedbackId <= 0) { json_err('Invalid feedback'); }
    executeQuery('DELETE FROM feedback_replies WHERE feedback_id = ?', [$feedbackId]);
    executeQuery('DELETE FROM feedback WHERE feedback_id = ?', [$feedbackId]);
    json_ok();
}

// Get category counts
if ($method === 'GET' && $action === 'category_counts') {
    $counts = fetchAll("SELECT category, COUNT(*) as count FROM feedback GROUP BY category");
    $result = [
        'customer_support' => 0,
        'service' => 0,
        'website' => 0
    ];
    foreach ($counts as $row) {
        $result[$row['category']] = (int)$row['count'];
    }
    json_ok(['counts' => $result]);
}

// Get unread feedback count for admin notifications
if ($method === 'GET' && $action === 'unread_count') {
    // Only admins can access this
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
        json_err('Forbidden', 403);
    }
    
    $count = fetchOne('SELECT COUNT(*) as count FROM feedback WHERE is_read_by_admin = 0');
    $unreadCount = (int)($count['count'] ?? 0);
    json_ok(['unread_count' => $unreadCount]);
}

// Mark feedback as read by admin
if ($method === 'POST' && $action === 'mark_read') {
    // Only admins can access this
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
        json_err('Forbidden', 403);
    }
    
    $feedbackId = (int)($_POST['feedback_id'] ?? 0);
    if ($feedbackId <= 0) { json_err('Invalid feedback ID'); }
    
    executeQuery('UPDATE feedback SET is_read_by_admin = 1, read_by_admin_at = NOW() WHERE feedback_id = ?', [$feedbackId]);
    json_ok();
}

// Mark all feedback as read by admin
if ($method === 'POST' && $action === 'mark_all_read') {
    // Only admins can access this
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
        json_err('Forbidden', 403);
    }
    
    executeQuery('UPDATE feedback SET is_read_by_admin = 1, read_by_admin_at = NOW() WHERE is_read_by_admin = 0');
    json_ok();
}

json_err('Unsupported action', 404);
?>


