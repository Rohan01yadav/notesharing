<?php
/**
 * Note Upload Action Handler
 * Student Notes Sharing Platform
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/upload-note.php');
}

// 1. Verify CSRF Token
check_csrf();

$userId        = current_user_id();
$title         = clean_input($_POST['title'] ?? '');
$description   = clean_input($_POST['description'] ?? '');
$departmentId  = (int)($_POST['department_id'] ?? 0);
$semester      = (int)($_POST['semester'] ?? 0);
$subjectId     = (int)($_POST['subject_id'] ?? 0);
$academicYear  = clean_input($_POST['academic_year'] ?? '2024-2025');

// 2. Validate Text Inputs
if (empty($title) || empty($description) || empty($departmentId) || empty($semester) || empty($subjectId)) {
    set_flash('error', 'Please fill in all required fields to upload notes.');
    redirect('/upload-note.php');
}

// 3. Validate Uploaded File
if (!isset($_FILES['note_file']) || $_FILES['note_file']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was selected for upload.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder for uploads.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
    ];
    $errorCode = $_FILES['note_file']['error'] ?? UPLOAD_ERR_NO_FILE;
    $errorMessage = $uploadErrors[$errorCode] ?? 'An error occurred during file upload.';
    set_flash('error', $errorMessage);
    redirect('/upload-note.php');
}

$file = $_FILES['note_file'];
$originalFilename = basename($file['name']);
$fileSize = (int)$file['size'];
$fileTmpPath = $file['tmp_name'];

// Check file size constraint (25 MB)
if ($fileSize > MAX_FILE_SIZE) {
    set_flash('error', 'File size exceeds maximum allowed limit of 25MB.');
    redirect('/upload-note.php');
}

// Check extension
$fileExtension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
if ($fileExtension !== 'pdf') {
    set_flash('error', 'Invalid file type. Only PDF documents (.pdf) are allowed.');
    redirect('/upload-note.php');
}

// Check MIME type using finfo or binary header
$isValidPdf = false;
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmpPath);
    finfo_close($finfo);
    if ($mimeType === 'application/pdf') {
        $isValidPdf = true;
    }
} elseif (function_exists('mime_content_type')) {
    if (mime_content_type($fileTmpPath) === 'application/pdf') {
        $isValidPdf = true;
    }
} else {
    // Binary header check for PDF: %PDF-
    $handle = fopen($fileTmpPath, 'rb');
    $header = fread($handle, 4);
    fclose($handle);
    if ($header === '%PDF') {
        $isValidPdf = true;
    }
}

if (!$isValidPdf) {
    set_flash('error', 'Uploaded file is not a valid PDF document.');
    redirect('/upload-note.php');
}

// 4. Generate Safe Storage Filename
$safeFilename = 'note_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.pdf';
$destination = NOTES_UPLOAD_PATH . DIRECTORY_SEPARATOR . $safeFilename;

// Ensure upload directory exists and is writable
if (!is_dir(NOTES_UPLOAD_PATH)) {
    mkdir(NOTES_UPLOAD_PATH, 0755, true);
}

if (!move_uploaded_file($fileTmpPath, $destination)) {
    set_flash('error', 'Failed to save the uploaded file to server storage.');
    redirect('/upload-note.php');
}

// 5. Insert Record into Database
try {
    $db = get_db();
    $stmt = $db->prepare("
        INSERT INTO notes (
            user_id, subject_id, department_id, semester, academic_year,
            title, description, file_path, original_filename, file_size, file_type
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'application/pdf')
    ");

    $stmt->execute([
        $userId,
        $subjectId,
        $departmentId,
        $semester,
        $academicYear,
        $title,
        $description,
        $safeFilename,
        $originalFilename,
        $fileSize
    ]);

    $newNoteId = (int)$db->lastInsertId();

    set_flash('success', 'Your academic note has been uploaded and published successfully!');
    redirect('/note-details.php?id=' . $newNoteId);
} catch (PDOException $e) {
    // If DB insert fails, clean up the orphaned file
    if (file_exists($destination)) {
        unlink($destination);
    }
    set_flash('error', 'Failed to record note in the database: ' . $e->getMessage());
    redirect('/upload-note.php');
}
