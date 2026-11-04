<?php
/**
 * Secure File Download Action Handler
 * Student Notes Sharing Platform
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$noteId = (int)($_GET['id'] ?? 0);

if (!$noteId) {
    set_flash('error', 'Invalid document request.');
    redirect('/explore.php');
}

$db = get_db();
$stmt = $db->prepare("SELECT * FROM notes WHERE id = ?");
$stmt->execute([$noteId]);
$note = $stmt->fetch();

if (!$note) {
    set_flash('error', 'The requested study note does not exist.');
    redirect('/explore.php');
}

// Security: Prevent path traversal by sanitizing filename
$filename = basename($note['file_path']);
$filePath = NOTES_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename;

// Verify file existence
if (!file_exists($filePath) || !is_readable($filePath)) {
    set_flash('error', 'The requested PDF file is not available on server storage.');
    redirect('/note-details.php?id=' . $noteId);
}

// 1. Increment note download count
try {
    $db->prepare("UPDATE notes SET download_count = download_count + 1 WHERE id = ?")->execute([$noteId]);

    // 2. Log download activity in downloads table
    $userId = current_user_id();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    $logStmt = $db->prepare("INSERT INTO downloads (user_id, note_id, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $logStmt->execute([$userId, $noteId, $ip, $agent]);
} catch (PDOException $e) {
    // Continue file download even if log fails
}

// 3. Clear output buffer before sending headers
if (ob_get_level()) {
    ob_end_clean();
}

$downloadName = $note['original_filename'];
if (!str_ends_with(strtolower($downloadName), '.pdf')) {
    $downloadName .= '.pdf';
}

// 4. Send HTTP headers for safe PDF file download
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . str_replace('"', '', $downloadName) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// 5. Output file
readfile($filePath);
exit;
