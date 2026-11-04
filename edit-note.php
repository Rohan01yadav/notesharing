<?php
/**
 * Edit Note Page
 * Student Notes Sharing Platform
 */

$pageTitle = "Edit Note Details";
require_once __DIR__ . '/includes/header.php';

require_login();
$userId = current_user_id();
$noteId = (int)($_GET['id'] ?? 0);

if (!$noteId) {
    set_flash('error', 'Invalid note specified.');
    redirect('/my-uploads.php');
}

$db = get_db();
$stmt = $db->prepare("SELECT * FROM notes WHERE id = ?");
$stmt->execute([$noteId]);
$note = $stmt->fetch();

if (!$note) {
    set_flash('error', 'The requested note does not exist.');
    redirect('/my-uploads.php');
}

// Check authorization (must be note owner or admin)
if ($note['user_id'] != $userId && !is_admin()) {
    set_flash('error', 'You are not authorized to edit this note.');
    redirect('/my-uploads.php');
}

$departments = get_all_departments();
$allSubjects = get_all_subjects();
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-2 mb-3">
                <a href="<?= BASE_URL ?>/my-uploads.php" class="text-muted small">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to My Uploads
                </a>
            </div>

            <div class="form-card">
                <div class="d-flex align-items-center gap-3 pb-3 mb-4 border-bottom">
                    <div class="pdf-icon-badge" style="width:48px; height:48px;">
                        <i class="fa-solid fa-pen-to-square fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Edit Note Details</h4>
                        <p class="text-muted small mb-0">Update academic metadata for <strong><?= e($note['original_filename']) ?></strong></p>
                    </div>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/actions/edit-note.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="note_id" value="<?= $note['id'] ?>">

                    <!-- Note Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Note Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= e($note['title']) ?>" required maxlength="255">
                    </div>

                    <!-- Department & Semester Row -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="department_select" class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select" id="department_select" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= $dept['id'] == $note['department_id'] ? 'selected' : '' ?>>
                                        <?= e($dept['name']) ?> (<?= e($dept['code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                            <select class="form-select" id="semester" name="semester" required>
                                <option value="">Select Semester</option>
                                <?php for ($s = 1; $s <= 8; $s++): ?>
                                    <option value="<?= $s ?>" <?= $s == $note['semester'] ? 'selected' : '' ?>>
                                        Semester <?= $s ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Subject & Academic Year Row -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-8">
                            <label for="subject_select" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="subject_select" name="subject_id" required>
                                <option value="">Select Subject</option>
                                <?php foreach ($allSubjects as $subj): ?>
                                    <option value="<?= $subj['id'] ?>" 
                                            data-department="<?= $subj['department_id'] ?>" 
                                            data-semester="<?= $subj['semester'] ?>"
                                            <?= $subj['id'] == $note['subject_id'] ? 'selected' : '' ?>>
                                        <?= e($subj['name']) ?> (<?= e($subj['code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label for="academic_year" class="form-label">Academic Year <span class="text-danger">*</span></label>
                            <select class="form-select" id="academic_year" name="academic_year" required>
                                <option value="2025-2026" <?= $note['academic_year'] === '2025-2026' ? 'selected' : '' ?>>2025-2026</option>
                                <option value="2024-2025" <?= $note['academic_year'] === '2024-2025' ? 'selected' : '' ?>>2024-2025</option>
                                <option value="2023-2024" <?= $note['academic_year'] === '2023-2024' ? 'selected' : '' ?>>2023-2024</option>
                                <option value="2022-2023" <?= $note['academic_year'] === '2022-2023' ? 'selected' : '' ?>>2022-2023</option>
                            </select>
                        </div>
                    </div>

                    <!-- Note Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label">Description & Syllabus Topics <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?= e($note['description']) ?></textarea>
                    </div>

                    <!-- Existing File Notice -->
                    <div class="p-3 bg-light rounded-3 mb-4 border d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-danger fs-4"></i>
                            <div>
                                <div class="fw-bold text-dark small"><?= e($note['original_filename']) ?></div>
                                <div class="text-muted small"><?= format_bytes($note['file_size']) ?> • <?= $note['download_count'] ?> downloads</div>
                            </div>
                        </div>
                        <a href="<?= BASE_URL ?>/actions/download.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-download me-1"></i> Download PDF
                        </a>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= BASE_URL ?>/my-uploads.php" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
