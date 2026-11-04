<?php
/**
 * Report Note Action Handler
 * Student Notes Sharing Platform
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/explore.php');
}

check_csrf();

$userId  = current_user_id();
$noteId  = (int)($_POST['note_id'] ?? 0);
$reason  = clean_input($_POST['reason'] ?? '');
$details = clean_input($_POST['details'] ?? '');

if (!$noteId || empty($reason)) {
    set_flash('error', 'Please provide a valid reason for reporting this note.');
    redirect('/note-details.php?id=' . $noteId);
}

try {
    $db = get_db();
    $stmt = $db->prepare("
        INSERT INTO reports (note_id, user_id, reason, details, status)
        VALUES (?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$noteId, $userId, $reason, $details]);

    set_flash('success', 'Thank you. Your report has been submitted for administrative review.');
    redirect('/note-details.php?id=' . $noteId);
} catch (PDOException $e) {
    set_flash('error', 'Failed to submit report: ' . $e->getMessage());
    redirect('/note-details.php?id=' . $noteId);
}
