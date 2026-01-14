<?php
// db.php - Enhanced with Security Functions

// Secure session start with session regeneration to prevent session fixation attacks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    session_regenerate_id(true); // Regenerates session ID to prevent session hijacking
}

// Database connection with error handling
$conn = mysqli_connect('localhost', 'root', '', 'gm_db');

if (!$conn) {
    // Log the actual error but show generic message to users (prevents information disclosure)
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection error. Please try again later.");
}

// Set UTF-8 character set to prevent encoding issues and SQL injection through multibyte characters
mysqli_set_charset($conn, "utf8mb4");

// ========== SECURITY FUNCTIONS ==========

/**
 * Cleans user input to prevent XSS and SQL injection attacks
 * This is the FIRST LINE OF DEFENSE against malicious input
 */
function cleanInput($data) {
    if (empty($data)) return $data;
    
    $data = trim($data); // Removes whitespace from beginning and end
    $data = stripslashes($data); // Removes backslashes (if magic quotes were on)
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // Converts special characters to HTML entities
    return mysqli_real_escape_string($GLOBALS['conn'], $data); // Escapes special SQL characters
}

/**
 * Securely hashes passwords using bcrypt algorithm
 * Bcrypt is intentionally slow to prevent brute-force attacks
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]); // Higher cost = more secure but slower
}

/**
 * Verifies password against stored hash
 * Uses timing-safe comparison to prevent timing attacks
 */
function checkPassword($password, $hash) {
    return password_verify($password, $hash); // Built-in PHP function that's timing-safe
}

/**
 * Executes safe database queries using prepared statements
 * This is the MOST IMPORTANT defense against SQL injection
 */
function safeQuery($conn, $sql, $params = [], $types = '') {
    try {
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            logError("Failed to prepare statement: " . mysqli_error($conn));
            return false;
        }
        
        if ($params) {
            if (empty($types)) {
                $types = str_repeat('s', count($params)); // Default to string parameters
            }
            mysqli_stmt_bind_param($stmt, $types, ...$params); // Binds parameters safely
        }
        
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    } catch (Exception $e) {
        logError("Query error: " . $e->getMessage());
        return false;
    }
}

// ========== LOGGING FUNCTIONS ==========

/**
 * Logs user actions for audit trail
 * Helps detect suspicious activities and provides accountability
 */
function logAction($user_id, $action, $details = '') {
    $log_file = 'logs/user_actions.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $log_entry = "[$timestamp] [IP: $ip_address] [User: $user_id] [Action: $action]";
    if ($details) {
        $log_entry .= " [Details: $details]";
    }
    $log_entry .= " [Agent: $user_agent]\n";
    
    // Create secure logs directory
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }
    
    // Append to log file with file locking
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Logs system errors securely
 * Errors are logged to file instead of being displayed to users
 */
function logError($error_message) {
    $log_file = 'logs/errors.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_entry = "[$timestamp] ERROR: $error_message\n";
    
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Logs failed login attempts for brute-force detection
 * This helps identify and block malicious login attempts
 */
function logFailedLogin($email, $reason) {
    $log_file = 'logs/failed_logins.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $log_entry = "[$timestamp] [IP: $ip_address] Failed login attempt for email: $email - Reason: $reason\n";
    
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// ========== VALIDATION FUNCTIONS ==========

/**
 * Validates email format using PHP's built-in filter
 * Prevents invalid email formats from being processed
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validates phone number using regex pattern
 * Ensures phone numbers are in expected format
 */
function validatePhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

/**
 * Sanitizes output before displaying to prevent XSS
 * Always use when outputting user-generated content
 */
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Verifies CSRF token to prevent Cross-Site Request Forgery attacks
 * Each form submission requires a valid token
 */
function verifyCSRFToken() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        logError("CSRF token validation failed");
        return false;
    }
    return true;
}

/**
 * Generates cryptographically secure CSRF token
 * Creates unique token for each session
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 256-bit random token
    }
    return $_SESSION['csrf_token'];
}

// Configure PHP error handling
error_reporting(E_ALL); // Report all errors
ini_set('display_errors', 0); // Don't show errors to users (prevent info disclosure)
ini_set('log_errors', 1); // Log errors to file
ini_set('error_log', 'logs/php_errors.log'); // Custom error log location

// Set security headers (for defense explanation)
header("X-Content-Type-Options: nosniff"); // Prevent MIME type sniffing
header("X-Frame-Options: DENY"); // Prevent clickjacking
header("X-XSS-Protection: 1; mode=block"); // Enable XSS protection

?>