<?php
/**
 * System Configuration for SOCOTECO II Billing Management System
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define constants
define('SITE_URL', 'http://localhost/socobillSys');
define('SITE_NAME', 'SOCOTECO II Billing Management System');
define('SITE_VERSION', '1.0.0');

// Database Configuration Constants (for security)
define('DB_HOST', 'localhost');
define('DB_NAME', 'socoteco_billing');
define('DB_USER', 'root');
define('DB_PASS', 'bisayawa!'); // CHANGE THIS TO A STRONG PASSWORD
define('DB_CHARSET', 'utf8mb4');

// Environment setting (set to 'production' in production)
define('ENVIRONMENT', 'development'); // Options: 'development' or 'production'

// Timezone setting for Philippines
date_default_timezone_set('Asia/Manila');

// Error reporting (disable in production)
if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Include database configuration
require_once __DIR__ . '/database.php';

// Helper functions
function redirect($url) {
    // Handle relative URLs properly
    if (strpos($url, 'http') === 0) {
        // Absolute URL
        header("Location: " . $url);
    } else {
        // Relative URL - make it relative to the site root
        $base_url = rtrim(SITE_URL, '/');
        header("Location: " . $base_url . '/' . ltrim($url, '/'));
    }
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('auth/login.php');
    }
}

function requireRole($allowed_roles) {
    requireLogin();
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        redirect('unauthorized.php');
    }
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

function generateBillNumber() {
    $year = date('Y');
    $month = date('m');
    $prefix = "SOC{$year}{$month}";
    
    $sql = "SELECT COUNT(*) as count FROM bills WHERE bill_number LIKE ?";
    $result = fetchOne($sql, ["{$prefix}%"]);
    $count = $result['count'] + 1;
    
    return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function generateORNumber() {
    $year = date('Y');
    $prefix = "OR{$year}";
    
    $sql = "SELECT COUNT(*) as count FROM payments WHERE or_number LIKE ?";
    $result = fetchOne($sql, ["{$prefix}%"]);
    $count = $result['count'] + 1;
    
    return $prefix . str_pad($count, 6, '0', STR_PAD_LEFT);
}

function logActivity($action, $table_name = null, $record_id = null, $old_values = null, $new_values = null) {
    if (!isLoggedIn()) return;
    
    // Check if user_id exists in users table before logging
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $user_check = fetchOne("SELECT user_id FROM users WHERE user_id = ?", [$_SESSION['user_id']]);
        if (!$user_check) {
            return; // Don't log if user doesn't exist
        }
    } else {
        return; // Don't log if no user_id in session
    }
    
    $sql = "INSERT INTO audit_trail (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    executeQuery($sql, [
        $_SESSION['user_id'],
        $action,
        $table_name,
        $record_id,
        $old_values ? json_encode($old_values) : null,
        $new_values ? json_encode($new_values) : null,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
}

function getSystemSetting($key, $default = null) {
    $sql = "SELECT setting_value FROM system_settings WHERE setting_key = ?";
    $result = fetchOne($sql, [$key]);
    return $result ? $result['setting_value'] : $default;
}

function setSystemSetting($key, $value) {
    $sql = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    executeQuery($sql, [$key, $value]);
}

// URL helper function
function url($path = '') {
    $base_url = rtrim(SITE_URL, '/');
    return $base_url . '/' . ltrim($path, '/');
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input sanitization
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// File upload helper
function uploadFile($file, $upload_dir = 'uploads/', $allowed_types = ['jpg', 'jpeg', 'png', 'pdf']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    if ($file['size'] > 5000000) { // 5MB limit
        throw new RuntimeException('Exceeded filesize limit.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

    if (!in_array($extension, $allowed_types)) {
        throw new RuntimeException('Invalid file format.');
    }

    $filename = sprintf('%s.%s', uniqid(), $extension);
    $filepath = $upload_dir . $filename;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return $filepath;
}
?>
