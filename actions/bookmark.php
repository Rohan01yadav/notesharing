<?php
/**
 * Bookmark Toggle Action Handler
 * Student Notes Sharing Platform
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
          (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));

// 1. Auth check
if (!is_logged_in()) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'unauthorized',
            'message' => 'Please log in to save bookmarks.'
        ]);
        exit;
    } else {
        set_flash('error', 'Please log in to save bookmarks.');
        redirect('/login.php');
    }
}

$userId = current_user_id();
$noteId = (int)($_POST['note_id'] ?? $_GET['note_id'] ?? 0);

if (!$noteId) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid note ID.']);
        exit;
    } else {
        set_flash('error', 'Invalid note specified.');
        redirect('/explore.php');
    }
}

try {
    $db = get_db();

    // Check if bookmark currently exists
    $checkStmt = $db->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND note_id = ?");
    $checkStmt->execute([$userId, $noteId]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        // Remove bookmark
        $delStmt = $db->prepare("DELETE FROM bookmarks WHERE id = ?");
        $delStmt->execute([$existing['id']]);
        $bookmarked = false;
        $message = "Removed from bookmarks.";
    } else {
        // Add bookmark
        $insStmt = $db->prepare("INSERT INTO bookmarks (user_id, note_id) VALUES (?, ?)");
        $insStmt->execute([$userId, $noteId]);
        $bookmarked = true;
        $message = "Note added to your bookmarks.";
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'bookmarked' => $bookmarked,
            'message' => $message
        ]);
        exit;
    } else {
        set_flash('success', $message);
        $referer = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/explore.php');
        header("Location: " . $referer);
        exit;
    }
} catch (PDOException $e) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    } else {
        set_flash('error', 'Database error: ' . $e->getMessage());
        redirect('/explore.php');
    }
}
