<?php
/**
 * Global Utility Functions
 * Student Notes Sharing Platform
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

/**
 * Escape HTML output to prevent XSS attacks
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format bytes into human-readable format (KB, MB, GB)
 */
function format_bytes(int $bytes, int $precision = 1): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Return human-friendly time elapsed string (e.g. "2 hours ago")
 */
function time_ago(string|int $datetime): string {
    $timestamp = is_numeric($datetime) ? (int)$datetime : strtotime($datetime);
    $difference = time() - $timestamp;

    if ($difference < 60) {
        return 'Just now';
    } elseif ($difference < 3600) {
        $minutes = round($difference / 60);
        return $minutes . ' min' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = round($difference / 3600);
        return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = round($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 2592000) {
        $weeks = round($difference / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}

/**
 * Set a session flash message
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Check if a flash message exists
 */
function has_flash(string $type): bool {
    return !empty($_SESSION['flash_' . $type]);
}

/**
 * Get and clear a session flash message
 */
function get_flash(string $type): ?string {
    if (isset($_SESSION['flash_' . $type])) {
        $msg = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $msg;
    }
    return null;
}

/**
 * Render any pending flash messages as modern floating alerts
 */
function render_flash_messages(): string {
    $html = '';
    $types = [
        'success' => ['icon' => 'fa-circle-check', 'class' => 'alert-success'],
        'error'   => ['icon' => 'fa-circle-exclamation', 'class' => 'alert-danger'],
        'warning' => ['icon' => 'fa-triangle-exclamation', 'class' => 'alert-warning'],
        'info'    => ['icon' => 'fa-circle-info', 'class' => 'alert-info']
    ];

    foreach ($types as $type => $info) {
        if ($message = get_flash($type)) {
            $html .= '<div class="alert ' . $info['class'] . ' alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">';
            $html .= '<i class="fa-solid ' . $info['icon'] . ' me-2 fs-5"></i>';
            $html .= '<div class="flex-grow-1">' . e($message) . '</div>';
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            $html .= '</div>';
        }
    }

    return $html;
}

/**
 * Safe redirect helper
 */
function redirect(string $path): void {
    if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
        $path = BASE_URL . '/' . ltrim($path, '/');
    }
    header("Location: " . $path);
    exit;
}

/**
 * Clean & sanitize user input string
 */
function clean_input(mixed $data): string {
    if (is_array($data)) {
        return '';
    }
    return trim((string)$data);
}

/**
 * Check if note is bookmarked by user
 */
function is_note_bookmarked(int $note_id, ?int $user_id = null): bool {
    if ($user_id === null) {
        $user_id = current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    try {
        $db = get_db();
        $stmt = $db->prepare("SELECT 1 FROM bookmarks WHERE user_id = ? AND note_id = ? LIMIT 1");
        $stmt->execute([$user_id, $note_id]);
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Fetch all departments ordered by name
 */
function get_all_departments(): array {
    try {
        $db = get_db();
        $stmt = $db->query("SELECT * FROM departments ORDER BY code ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch subjects optionally filtered by department and semester
 */
function get_all_subjects(?int $department_id = null, ?int $semester = null): array {
    try {
        $db = get_db();
        $sql = "SELECT s.*, d.code as dept_code, d.name as dept_name 
                FROM subjects s 
                JOIN departments d ON s.department_id = d.id 
                WHERE 1=1";
        $params = [];

        if ($department_id) {
            $sql .= " AND s.department_id = ?";
            $params[] = $department_id;
        }

        if ($semester) {
            $sql .= " AND s.semester = ?";
            $params[] = $semester;
        }

        $sql .= " ORDER BY s.name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch platform overview statistics
 */
function get_platform_stats(): array {
    try {
        $db = get_db();
        $totalNotes = $db->query("SELECT COUNT(*) FROM notes")->fetchColumn() ?: 0;
        $totalStudents = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn() ?: 0;
        $totalDownloads = $db->query("SELECT COALESCE(SUM(download_count), 0) FROM notes")->fetchColumn() ?: 0;
        $totalSubjects = $db->query("SELECT COUNT(*) FROM subjects")->fetchColumn() ?: 0;
        $totalDepartments = $db->query("SELECT COUNT(*) FROM departments")->fetchColumn() ?: 0;

        return [
            'notes'       => (int)$totalNotes,
            'students'    => (int)$totalStudents,
            'downloads'   => (int)$totalDownloads,
            'subjects'    => (int)$totalSubjects,
            'departments' => (int)$totalDepartments,
        ];
    } catch (PDOException $e) {
        return [
            'notes'       => 0,
            'students'    => 0,
            'downloads'   => 0,
            'subjects'    => 0,
            'departments' => 0,
        ];
    }
}

/**
 * Fetch popular subjects with note counts
 */
function get_popular_subjects(int $limit = 6): array {
    try {
        $db = get_db();
        $stmt = $db->prepare("
            SELECT s.*, d.code as dept_code, COUNT(n.id) as note_count
            FROM subjects s
            JOIN departments d ON s.department_id = d.id
            LEFT JOIN notes n ON s.id = n.subject_id
            GROUP BY s.id
            ORDER BY note_count DESC, s.name ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch recent notes with author and subject details
 */
function get_recent_notes(int $limit = 6): array {
    try {
        $db = get_db();
        $stmt = $db->prepare("
            SELECT n.*, 
                   u.name as uploader_name, u.email as uploader_email,
                   s.name as subject_name, s.code as subject_code,
                   d.code as dept_code, d.name as dept_name
            FROM notes n
            JOIN users u ON n.user_id = u.id
            JOIN subjects s ON n.subject_id = s.id
            JOIN departments d ON n.department_id = d.id
            ORDER BY n.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
