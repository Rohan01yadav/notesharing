<?php
/**
 * Student Notes Sharing Platform
 * Configuration File
 */

// Error reporting for development (can be disabled in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure session starts securely if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session cookie parameters
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7 days
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Database Configuration (Standard XAMPP settings)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_notes_platform');
define('DB_CHARSET', 'utf8mb4');

// Application Details
define('APP_NAME', 'Student Notes Sharing Platform');
define('APP_SHORT_NAME', 'NoteShare Hub');
define('APP_TAGLINE', 'Learn Together. Share Knowledge. Grow Together.');
define('APP_VERSION', '1.0.0');

// Path Constants
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'includes');
define('UPLOADS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('NOTES_UPLOAD_PATH', UPLOADS_PATH . DIRECTORY_SEPARATOR . 'notes');

// Dynamic Base URL detection for portability in XAMPP
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$hostName = $_SERVER['HTTP_HOST'] ?? 'localhost';
$currentScriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

// Normalize base URL
// If running from an 'admin' or 'actions' subfolder, trim that part
$cleanPath = preg_replace('#/(admin|actions)$#', '', $currentScriptDir);
$cleanPath = rtrim($cleanPath, '/');
define('BASE_URL', $protocol . $hostName . $cleanPath);

// File Upload Constraints
define('MAX_FILE_SIZE', 25 * 1024 * 1024); // 25 MB in bytes
define('ALLOWED_EXTENSIONS', ['pdf']);
define('ALLOWED_MIME_TYPES', ['application/pdf']);
