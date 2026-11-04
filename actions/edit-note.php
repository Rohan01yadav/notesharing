<?php
/**
 * Edit Note Action Handler
 * Student Notes Sharing Platform
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/my-uploads.php');
}

check_csrf();

$userId = current_user_id();
$noteId = (int)($_POST['note_id'] ?? 0);

if (!$noteId) {
    set_flash('error', 'Invalid note specified.');
    redirect('/my-uploads.php');
}

$db = get_db();
$checkStmt = $db->prepare("SELECT user_id FROM notes WHERE id = ?");
$checkStmt->execute([$noteId]);
$note = $checkStmt->fetch();

if (!$note) {
    set_flash('error', 'Note not found.');
    redirect('/my-uploads.php');
}

if ($note['user_id'] != $userId && !is_admin()) {
    set_flash('error', 'Unauthorized operation.');
    redirect('/my-uploads.php');
}

$title         = clean_input($_POST['title'] ?? '');
$description   = clean_input($_POST['description'] ?? '');
$departmentId  = (int)($_POST['department_id'] ?? 0);
$semester      = (int)($_POST['semester'] ?? 0);
$subjectId     = (int)($_POST['subject_id'] ?? 0);
$academicYear  = clean_input($_POST['academic_year'] ?? '2024-2025');

if (empty($title) || empty($description) || empty($departmentId) || empty($semester) || empty($subjectId)) {
    set_flash('error', 'Please fill in all required fields.');
    redirect('/edit-note.php?id=' . $noteId);
}

try {
    $updateStmt = $db->prepare("
        UPDATE notes 
        SET title = ?, description = ?, department_id = ?, semester = ?, subject_id = ?, academic_year = ?
        WHERE id = ?
    ");
    $updateStmt->execute([
        $title,
        $description,
        $departmentId,
        $semester,
        $subjectId,
        $academicYear,
        $noteId
    ]);

    set_flash('success', 'Note details updated successfully.');
    redirect('/note-details.php?id=' . $noteId);
} catch (PDOException $e) {
    set_flash('error', 'Failed to update note details: ' . $e->getMessage());
    redirect('/edit-note.php?id=' . $noteId);
}
