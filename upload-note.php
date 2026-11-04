<?php
/**
 * Note Upload Page
 * Student Notes Sharing Platform
 */

$pageTitle = "Upload Note";
require_once __DIR__ . '/includes/header.php';

// User must be logged in to upload notes
require_login();

$departments = get_all_departments();
$allSubjects = get_all_subjects();
$user = current_user();

// Default values based on logged-in student
$defaultDeptId = $user['department_id'] ?? 1;
$defaultSem = $user['semester'] ?? 4;
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-2 mb-3">
                <a href="<?= BASE_URL ?>/dashboard.php" class="text-muted small">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <div class="form-card">
                <div class="d-flex align-items-center gap-3 pb-3 mb-4 border-bottom">
                    <div class="pdf-icon-badge" style="width:48px; height:48px;">
                        <i class="fa-solid fa-cloud-arrow-up fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Upload Academic Note</h4>
                        <p class="text-muted small mb-0">Share lecture notes, lab guides, or revision summaries in PDF format</p>
                    </div>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/actions/upload.php" enctype="multipart/form-data" id="uploadNoteForm">
                    <?= csrf_field() ?>

                    <!-- Note Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Note Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Complete Database Management Systems Unit 1-5 Revision Notes" required maxlength="255">
                        <div class="form-text small text-muted">Provide a descriptive title including unit numbers or key topics.</div>
                    </div>

                    <!-- Department & Semester Row -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="department_select" class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select" id="department_select" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= $dept['id'] == $defaultDeptId ? 'selected' : '' ?>>
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
                                    <option value="<?= $s ?>" <?= $s == $defaultSem ? 'selected' : '' ?>>
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
                                            <?= ($subj['department_id'] == $defaultDeptId) ? '' : 'style="display:none;"' ?>>
                                        <?= e($subj['name']) ?> (<?= e($subj['code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label for="academic_year" class="form-label">Academic Year <span class="text-danger">*</span></label>
                            <select class="form-select" id="academic_year" name="academic_year" required>
                                <option value="2025-2026">2025-2026</option>
                                <option value="2024-2025" selected>2024-2025</option>
                                <option value="2023-2024">2023-2024</option>
                                <option value="2022-2023">2022-2023</option>
                            </select>
                        </div>
                    </div>

                    <!-- Note Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label">Description & Syllabus Topics <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Mention the key topics covered, syllabus units, solved questions, or reference textbooks..." required></textarea>
                        <div class="form-text small text-muted">A clear description helps classmates find your notes in search.</div>
                    </div>

                    <!-- PDF Drag and Drop Area -->
                    <div class="mb-4">
                        <label class="form-label">PDF File Document <span class="text-danger">*</span></label>
                        <input type="file" name="note_file" id="note_file_input" class="d-none" accept=".pdf,application/pdf" required>
                        
                        <div class="file-dropzone" id="note_dropzone">
                            <i class="fa-solid fa-file-pdf fa-3x text-danger mb-3"></i>
                            <h6 class="fw-bold text-dark mb-1">Click to browse or drag & drop your PDF file here</h6>
                            <p class="text-muted small mb-0">Supported format: <strong>.PDF</strong> (Max size: <strong>25 MB</strong>)</p>
                        </div>

                        <!-- Selected File Details Indicator -->
                        <div id="file_details_box" class="p-3 bg-light rounded-3 mt-2 border d-none">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-success fs-5"></i>
                                    <div>
                                        <div class="fw-bold text-dark small file-name">filename.pdf</div>
                                        <div class="text-muted file-size" style="font-size:0.75rem;">0 KB</div>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success">Ready to upload</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnSubmitUpload">
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload & Publish Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
