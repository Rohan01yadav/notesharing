<?php
/**
 * Authentication and Session Management
 * Includes role-based access control and CSRF protection.
 */

require_once __DIR__ . '/config.php';

/**
 * Check if a user is currently logged in
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the currently logged-in user ID
 */
function current_user_id(): ?int {
    return is_logged_in() ? (int)$_SESSION['user_id'] : null;
}

/**
 * Get current user session data
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'            => $_SESSION['user_id'],
        'name'          => $_SESSION['user_name'] ?? '',
        'email'         => $_SESSION['user_email'] ?? '',
        'role'          => $_SESSION['user_role'] ?? 'student',
        'department_id' => $_SESSION['user_dept_id'] ?? null,
        'department'    => $_SESSION['user_dept_name'] ?? '',
        'semester'      => $_SESSION['user_semester'] ?? null,
        'roll_number'   => $_SESSION['user_roll_number'] ?? '',
    ];
}

/**
 * Check if the logged-in user is an admin
 */
function is_admin(): bool {
    return is_logged_in() && (($_SESSION['user_role'] ?? '') === 'admin');
}

/**
 * Check if the logged-in user is a student
 */
function is_student(): bool {
    return is_logged_in() && (($_SESSION['user_role'] ?? '') === 'student');
}

/**
 * Restrict access to authenticated users only
 */
function require_login(string $redirect = '/login.php'): void {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Please log in to access this page.';
        header("Location: " . BASE_URL . $redirect);
        exit;
    }
}

/**
 * Restrict access to administrators only
 */
function require_admin(string $redirect = '/dashboard.php'): void {
    require_login();
    if (!is_admin()) {
        $_SESSION['flash_error'] = 'Access denied. Administrator privileges are required.';
        header("Location: " . BASE_URL . $redirect);
        exit;
    }
}

/**
 * Restrict access to guests only (redirect logged in users)
 */
function require_guest(string $redirect = '/dashboard.php'): void {
    if (is_logged_in()) {
        if (is_admin()) {
            header("Location: " . BASE_URL . '/admin/dashboard.php');
        } else {
            header("Location: " . BASE_URL . $redirect);
        }
        exit;
    }
}

/**
 * Log in a user securely and regenerate the session ID
 */
function login_user(array $user): void {
    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);

    $_SESSION['user_id']          = (int)$user['id'];
    $_SESSION['user_name']        = $user['name'];
    $_SESSION['user_email']       = $user['email'];
    $_SESSION['user_role']        = $user['role'] ?? 'student';
    $_SESSION['user_dept_id']     = $user['department_id'] ?? null;
    $_SESSION['user_dept_name']   = $user['dept_name'] ?? ($user['department'] ?? '');
    $_SESSION['user_semester']    = $user['semester'] ?? null;
    $_SESSION['user_roll_number'] = $user['roll_number'] ?? '';
    $_SESSION['logged_in_time']   = time();
}

/**
 * Log out the current user and destroy the session
 */
function logout_user(): void {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

/**
 * Generate or retrieve CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate CSRF hidden input field
 */
function csrf_field(): string {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify submitted CSRF token
 */
function verify_csrf(?string $token = null): bool {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Enforce CSRF verification or abort with 403 Forbidden
 */
function check_csrf(): void {
    if (!verify_csrf()) {
        http_response_code(403);
        die('Invalid or missing security token (CSRF). Please refresh the page and try again.');
    }
}
