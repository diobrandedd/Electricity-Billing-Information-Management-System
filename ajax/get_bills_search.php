<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','cashier'])) {
    http_response_code(401);
    echo json_encode(['results' => []]);
    exit;
}

$q = trim($_GET['q'] ?? '');
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$params = [];
$where = [];

// Remaining balance condition
$where[] = "(b.total_amount - COALESCE((SELECT SUM(p.amount_paid) FROM payments p WHERE p.bill_id = b.bill_id), 0)) > 0";

if ($customer_id > 0) {
    $where[] = 'b.customer_id = ?';
    $params[] = $customer_id;
}

if ($q !== '') {
    $like = "%$q%";
    $where[] = "(b.bill_number LIKE ? OR c.account_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
    array_push($params, $like, $like, $like, $like);
}

$sql = "SELECT 
            b.bill_id,
            b.bill_number,
            b.total_amount,
            c.customer_id,
            c.account_number,
            c.first_name,
            c.last_name,
            (b.total_amount - COALESCE((SELECT SUM(p.amount_paid) FROM payments p WHERE p.bill_id = b.bill_id), 0)) AS remaining_balance
        FROM bills b
        JOIN customers c ON b.customer_id = c.customer_id";

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY b.due_date ASC LIMIT ' . (int)$per_page . ' OFFSET ' . (int)$offset;

$rows = fetchAll($sql, $params);

$results = [];
foreach ($rows as $r) {
    $text = $r['bill_number'] . ' - ' . $r['account_number'] . ' - ' . $r['last_name'] . ', ' . $r['first_name'] . ' (Remaining: ' . formatCurrency(max(0, $r['remaining_balance'])) . ')';
    $results[] = [
        'id' => $r['bill_id'],
        'text' => $text,
        'bill_number' => $r['bill_number'],
        'remaining' => max(0, $r['remaining_balance'])
    ];
}

echo json_encode([
    'results' => $results,
    'pagination' => [ 'more' => count($results) === $per_page ]
]);
