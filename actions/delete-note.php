<?php
/**
 * Delete Note Action Handler
 * Student Notes Sharing Platform
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

// Verify CSRF via GET or POST
$token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
if (!verify_csrf($token)) {
    set_flash('error', 'Security token mismatch. Action aborted.');
    redirect('/my-uploads.php');
}

$userId = current_user_id();
$noteId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if (!$noteId) {
    set_flash('error', 'Invalid note specified.');
    redirect('/my-uploads.php');
}

$db = get_db();
$stmt = $db->prepare("SELECT * FROM notes WHERE id = ?");
$stmt->execute([$noteId]);
$note = $stmt->fetch();

if (!$note) {
    set_flash('error', 'Note not found.');
    redirect('/my-uploads.php');
}

// Ownership check
if ($note['user_id'] != $userId && !is_admin()) {
    set_flash('error', 'You are not authorized to delete this note.');
    redirect('/my-uploads.php');
}

try {
    // 1. Delete physical file from disk
    $filePath = NOTES_UPLOAD_PATH . DIRECTORY_SEPARATOR . $note['file_path'];
    if (file_exists($filePath) && is_file($filePath)) {
        unlink($filePath);
    }

    // 2. Delete database record (foreign keys cascade to bookmarks and downloads)
    $delStmt = $db->prepare("DELETE FROM notes WHERE id = ?");
    $delStmt->execute([$noteId]);

    set_flash('success', 'The note "' . $note['title'] . '" was permanently deleted.');

    // Redirect to admin notes if admin deleted from admin panel
    if (str_contains($_SERVER['HTTP_REFERER'] ?? '', 'admin/notes.php')) {
        redirect('/admin/notes.php');
    } else {
        redirect('/my-uploads.php');
    }
} catch (PDOException $e) {
    set_flash('error', 'Failed to delete note: ' . $e->getMessage());
    redirect('/my-uploads.php');
}
