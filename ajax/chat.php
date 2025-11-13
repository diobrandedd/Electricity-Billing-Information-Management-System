<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Helpers
function requireAdmin() {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
}

function requireCustomer() {
    if (!isset($_SESSION['customer_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'ensure_session':
            // Customer ensures there is an open chat session; create if none
            requireCustomer();
            $customer_id = (int) $_SESSION['customer_id'];
            $session = fetchOne("SELECT * FROM chat_sessions WHERE customer_id = ? AND status = 'open' ORDER BY created_at DESC LIMIT 1", [$customer_id]);
            if (!$session) {
                $session_id = executeQueryWithId("INSERT INTO chat_sessions (customer_id) VALUES (?)", [$customer_id]);
                $session = fetchOne("SELECT * FROM chat_sessions WHERE session_id = ?", [$session_id]);

                // Auto-greet from system/admin so customers know chat is active
                // Get customer name for personalized greeting
                $customer = fetchOne("SELECT first_name, last_name FROM customers WHERE customer_id = ?", [$customer_id]);
                $customer_name = $customer ? trim($customer['first_name'] . ' ' . $customer['last_name']) : 'Valued Customer';
                
                // Create personalized greeting with business hours
                $greeting = "Hi " . $customer_name . "! 👋 Welcome to SOCOTECO customer support. We're here to help you with your electricity billing needs. Our support hours are Monday-Friday 8:00 AM - 5:00 PM. How can we assist you today?";
                
                // Insert as admin message with NULL user (system greeting)
                executeQuery("INSERT INTO chat_messages (session_id, sender_type, sender_user_id, message, is_read, is_read_by_admin) VALUES (?, 'admin', NULL, ?, 0, 1)", [$session_id, $greeting]);
                executeQuery("UPDATE chat_sessions SET last_activity = NOW() WHERE session_id = ?", [$session_id]);
            }
            echo json_encode(['session' => $session]);
            break;

        case 'send_message':
            // Sender can be customer or admin
            $session_id = (int) ($_POST['session_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            $explicit_sender = $_POST['sender'] ?? '';
            if ($session_id <= 0 || $message === '') {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid parameters']);
                exit;
            }

            // Content moderation for customer messages
            if ($explicit_sender === 'customer' || (isset($_SESSION['customer_id']) && !isset($_SESSION['user_id']))) {
                require_once __DIR__ . '/../includes/ContentModerator.php';
                $moderator = new ContentModerator();
                $moderation_result = $moderator->checkContent($message);
                
                // Log inappropriate content
                if (!$moderation_result['is_appropriate']) {
                    $moderator->logInappropriateContent($_SESSION['customer_id'], $message, $moderation_result, $session_id);
                }
                
                // Block high severity content
                if ($moderation_result['action'] === 'block') {
                    echo json_encode([
                        'success' => false, 
                        'error' => 'Your message contains inappropriate content and cannot be sent. Please keep the conversation respectful.',
                        'moderation' => $moderation_result
                    ]);
                    exit;
                }
                
                // Warn for medium severity content
                if ($moderation_result['action'] === 'warn') {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Please keep your message respectful. Inappropriate language is not allowed.',
                        'moderation' => $moderation_result,
                        'warning' => true
                    ]);
                    exit;
                }
            }

            $session = fetchOne("SELECT * FROM chat_sessions WHERE session_id = ?", [$session_id]);
            if (!$session || $session['status'] !== 'open') {
                http_response_code(404);
                echo json_encode(['error' => 'Session not found']);
                exit;
            }

            // Determine sender with explicit hint first
            if ($explicit_sender === 'customer' && isset($_SESSION['customer_id'])) {
                if ((int)$session['customer_id'] !== (int)$_SESSION['customer_id']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Forbidden']);
                    exit;
                }
                $message_id = executeQueryWithId(
                    "INSERT INTO chat_messages (session_id, sender_type, sender_customer_id, message, is_read_by_admin) VALUES (?, 'customer', ?, ?, 0)",
                    [$session_id, $_SESSION['customer_id'], $message]
                );
                // Touch session last_activity
                executeQuery("UPDATE chat_sessions SET last_activity = NOW() WHERE session_id = ?", [$session_id]);
            } elseif ($explicit_sender === 'admin' && isset($_SESSION['user_id']) && ($_SESSION['role'] ?? null) === 'admin') {
                $message_id = executeQueryWithId(
                    "INSERT INTO chat_messages (session_id, sender_type, sender_user_id, message) VALUES (?, 'admin', ?, ?)",
                    [$session_id, $_SESSION['user_id'], $message]
                );
                executeQuery("UPDATE chat_sessions SET last_activity = NOW() WHERE session_id = ?", [$session_id]);
            } elseif (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? null) === 'admin') {
                $message_id = executeQueryWithId(
                    "INSERT INTO chat_messages (session_id, sender_type, sender_user_id, message) VALUES (?, 'admin', ?, ?)",
                    [$session_id, $_SESSION['user_id'], $message]
                );
                executeQuery("UPDATE chat_sessions SET last_activity = NOW() WHERE session_id = ?", [$session_id]);
            } elseif (isset($_SESSION['customer_id'])) {
                if ((int)$session['customer_id'] !== (int)$_SESSION['customer_id']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Forbidden']);
                    exit;
                }
                $message_id = executeQueryWithId(
                    "INSERT INTO chat_messages (session_id, sender_type, sender_customer_id, message, is_read_by_admin) VALUES (?, 'customer', ?, ?, 0)",
                    [$session_id, $_SESSION['customer_id'], $message]
                );
                executeQuery("UPDATE chat_sessions SET last_activity = NOW() WHERE session_id = ?", [$session_id]);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }

            echo json_encode(['success' => true, 'message_id' => (int)($message_id ?? 0)]);
            break;

        case 'fetch_messages':
            $session_id = (int) ($_GET['session_id'] ?? 0);
            $since_id = (int) ($_GET['since_id'] ?? 0);
            if ($session_id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid parameters']);
                exit;
            }
            $session = fetchOne("SELECT * FROM chat_sessions WHERE session_id = ?", [$session_id]);
            if (!$session) {
                http_response_code(404);
                echo json_encode(['error' => 'Session not found']);
                exit;
            }
            // Authorization: customer must own the session; admin can access any
            if (isset($_SESSION['customer_id'])) {
                if ((int)$session['customer_id'] !== (int)$_SESSION['customer_id']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Forbidden']);
                    exit;
                }
            } elseif (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'admin') {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }

            if ($since_id > 0) {
                $messages = fetchAll("SELECT * FROM chat_messages WHERE session_id = ? AND message_id > ? ORDER BY message_id ASC", [$session_id, $since_id]);
            } else {
                $messages = fetchAll("SELECT * FROM chat_messages WHERE session_id = ? ORDER BY message_id ASC LIMIT 200", [$session_id]);
            }
            echo json_encode(['messages' => $messages]);
            break;

        case 'mark_read':
            $session_id = (int) ($_POST['session_id'] ?? 0);
            if ($session_id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid parameters']);
                exit;
            }
            // Only admin marks customer messages read; customer marks admin messages read
            if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? null) === 'admin') {
                executeQuery("UPDATE chat_messages SET is_read = 1 WHERE session_id = ? AND sender_type = 'customer'", [$session_id]);
            } elseif (isset($_SESSION['customer_id'])) {
                // Ensure ownership
                $session = fetchOne("SELECT * FROM chat_sessions WHERE session_id = ?", [$session_id]);
                if (!$session || (int)$session['customer_id'] !== (int)$_SESSION['customer_id']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Forbidden']);
                    exit;
                }
                executeQuery("UPDATE chat_messages SET is_read = 1 WHERE session_id = ? AND sender_type = 'admin'", [$session_id]);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            echo json_encode(['success' => true]);
            break;

        case 'admin_list_sessions':
            requireAdmin();
            $sessions = fetchAll("SELECT cs.*, c.first_name, c.last_name, c.account_number,
                (SELECT COUNT(*) FROM chat_messages cm WHERE cm.session_id = cs.session_id AND cm.sender_type = 'customer' AND cm.is_read = 0) as unread_count,
                (SELECT MAX(created_at) FROM chat_messages cm2 WHERE cm2.session_id = cs.session_id) as last_message_at
                FROM chat_sessions cs
                JOIN customers c ON cs.customer_id = c.customer_id
                WHERE cs.status = 'open'
                ORDER BY COALESCE(last_message_at, cs.last_activity) DESC
                LIMIT 100");
            echo json_encode(['sessions' => $sessions]);
            break;

        case 'close_session':
            requireAdmin();
            $session_id = (int) ($_POST['session_id'] ?? 0);
            if ($session_id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid parameters']);
                exit;
            }
            executeQuery("UPDATE chat_sessions SET status = 'closed' WHERE session_id = ?", [$session_id]);
            echo json_encode(['success' => true]);
            break;

        case 'unread_count':
            requireAdmin();
            $count = fetchOne("SELECT COUNT(*) as count FROM chat_messages WHERE sender_type = 'customer' AND is_read_by_admin = 0");
            echo json_encode(['count' => (int)$count['count']]);
            break;

        case 'mark_read':
            requireAdmin();
            $session_id = (int) ($_POST['session_id'] ?? 0);
            if ($session_id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid parameters']);
                exit;
            }
            executeQuery("UPDATE chat_messages SET is_read_by_admin = 1, read_by_admin_at = NOW() WHERE session_id = ? AND sender_type = 'customer' AND is_read_by_admin = 0", [$session_id]);
            echo json_encode(['success' => true]);
            break;

        case 'mark_all_read':
            requireAdmin();
            executeQuery("UPDATE chat_messages SET is_read_by_admin = 1, read_by_admin_at = NOW() WHERE sender_type = 'customer' AND is_read_by_admin = 0");
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
?>


