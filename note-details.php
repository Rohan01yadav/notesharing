<?php
/**
 * Note Details & PDF Viewer Page
 * Student Notes Sharing Platform
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$noteId = (int)($_GET['id'] ?? 0);

if (!$noteId) {
    set_flash('error', 'Invalid study note requested.');
    redirect('/explore.php');
}

$db = get_db();

// Fetch Note details with relations
$stmt = $db->prepare("
    SELECT n.*, 
           u.id as uploader_id, u.name as uploader_name, u.email as uploader_email, u.role as uploader_role, u.created_at as uploader_joined,
           s.name as subject_name, s.code as subject_code, s.description as subject_desc,
           d.name as dept_name, d.code as dept_code
    FROM notes n
    JOIN users u ON n.user_id = u.id
    JOIN subjects s ON n.subject_id = s.id
    JOIN departments d ON n.department_id = d.id
    WHERE n.id = ?
");
$stmt->execute([$noteId]);
$note = $stmt->fetch();

if (!$note) {
    set_flash('error', 'The requested study note does not exist.');
    redirect('/explore.php');
}

$pageTitle = $note['title'];
require_once __DIR__ . '/includes/header.php';

$userId = current_user_id();
$isOwner = is_logged_in() && ($note['user_id'] == $userId || is_admin());
$isBookmarked = is_note_bookmarked($noteId);

// Fetch total notes uploaded by this author
$authorNotesCount = (int)$db->prepare("SELECT COUNT(*) FROM notes WHERE user_id = ?")
                             ->execute([$note['uploader_id']]) ? 
                             $db->query("SELECT COUNT(*) FROM notes WHERE user_id = {$note['uploader_id']}")->fetchColumn() : 1;

// Fetch related notes (same subject or semester)
$relStmt = $db->prepare("
    SELECT n.*, s.name as subject_name, s.code as subject_code, d.code as dept_code
    FROM notes n
    JOIN subjects s ON n.subject_id = s.id
    JOIN departments d ON n.department_id = d.id
    WHERE (n.subject_id = ? OR (n.department_id = ? AND n.semester = ?))
      AND n.id != ?
    ORDER BY n.download_count DESC
    LIMIT 4
");
$relStmt->execute([$note['subject_id'], $note['department_id'], $note['semester'], $noteId]);
$relatedNotes = $relStmt->fetchAll();

$webFilePath = BASE_URL . '/uploads/notes/' . rawurlencode($note['file_path']);
?>

<div class="container py-4">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/explore.php">Explore</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/explore.php?dept=<?= $note['department_id'] ?>"><?= e($note['dept_code']) ?></a></li>
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width:300px;"><?= e($note['title']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Main Note Content Column -->
        <div class="col-lg-8">
            <div class="card-modern bg-white p-4 mb-4">
                <!-- Badges Strip -->
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="badge-tag badge-dept"><?= e($note['dept_code']) ?> - <?= e($note['dept_name']) ?></span>
                    <span class="badge-tag badge-sem">Semester <?= e($note['semester']) ?></span>
                    <span class="badge bg-light text-muted border px-2 py-1 small"><?= e($note['academic_year']) ?></span>
                    <span class="badge bg-success-subtle text-success px-2 py-1 small">
                        <i class="fa-solid fa-download me-1"></i> <?= $note['download_count'] ?> Downloads
                    </span>
                </div>

                <!-- Title -->
                <h2 class="fw-bold text-dark mb-2"><?= e($note['title']) ?></h2>

                <!-- Subject Subtitle -->
                <div class="d-flex align-items-center gap-2 text-primary fw-semibold mb-4">
                    <i class="fa-solid fa-book-open"></i>
                    <span><?= e($note['subject_name']) ?> (<?= e($note['subject_code']) ?>)</span>
                </div>

                <!-- Action Buttons Strip -->
                <div class="d-flex flex-wrap gap-2 pb-4 mb-4 border-bottom">
                    <a href="<?= BASE_URL ?>/actions/download.php?id=<?= $note['id'] ?>" class="btn btn-primary btn-lg shadow-sm">
                        <i class="fa-solid fa-cloud-arrow-down me-2"></i> Download PDF
                    </a>

                    <button type="button" class="btn btn-outline-secondary btn-lg btn-bookmark-toggle <?= $isBookmarked ? 'active' : '' ?>" data-note-id="<?= $note['id'] ?>">
                        <i class="fa-<?= $isBookmarked ? 'solid' : 'regular' ?> fa-bookmark me-1 text-danger"></i>
                        <span><?= $isBookmarked ? 'Saved in Bookmarks' : 'Bookmark Note' ?></span>
                    </button>

                    <?php if ($isOwner): ?>
                        <div class="dropdown ms-auto">
                            <button class="btn btn-light border btn-lg dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-gear me-1"></i> Manage
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li>
                                    <a class="dropdown-item py-2" href="<?= BASE_URL ?>/edit-note.php?id=<?= $note['id'] ?>">
                                        <i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Edit Metadata
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item py-2 text-danger btn-confirm-delete" 
                                       href="<?= BASE_URL ?>/actions/delete-note.php?id=<?= $note['id'] ?>&csrf_token=<?= csrf_token() ?>"
                                       data-title="<?= e($note['title']) ?>">
                                        <i class="fa-solid fa-trash-can me-2"></i> Delete Note
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <button type="button" class="btn btn-light border btn-lg ms-auto text-muted small" data-bs-toggle="modal" data-bs-target="#reportModal">
                            <i class="fa-regular fa-flag me-1"></i> Report
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Description / Syllabus Topics -->
                <h5 class="fw-bold mb-3">About this Study Resource</h5>
                <div class="text-secondary mb-4" style="line-height: 1.8; white-space: pre-line;">
                    <?= e($note['description']) ?>
                </div>

                <!-- PDF Embedded Preview -->
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">
                            <i class="fa-solid fa-file-pdf text-danger me-2"></i> Document Preview
                        </h5>
                        <a href="<?= BASE_URL ?>/actions/download.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-up-right-from-square me-1"></i> Open / Download Full PDF
                        </a>
                    </div>

                    <!-- PDF Viewer Container -->
                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden border shadow-sm" style="min-height: 480px;">
                        <iframe src="<?= $webFilePath ?>#toolbar=1" title="PDF Document Viewer" allowfullscreen></iframe>
                    </div>
                    <div class="text-muted small text-center mt-2">
                        <i class="fa-solid fa-circle-info me-1"></i> If the PDF preview does not render in your browser, click <strong>Download PDF</strong> above to view offline.
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Details & Contributor Column -->
        <div class="col-lg-4">
            <!-- Contributor Info Card -->
            <div class="card-modern bg-white p-4 mb-4">
                <h6 class="fw-bold text-dark mb-3">Uploaded By</h6>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="dashboard-user-badge" style="width:48px; height:48px; font-size:1.2rem;">
                        <?= strtoupper(substr($note['uploader_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold text-dark"><?= e($note['uploader_name']) ?></div>
                        <div class="text-muted small"><?= e($note['dept_code']) ?> Student</div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Contributions:</span>
                        <span class="fw-semibold"><?= $authorNotesCount ?> Notes</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Joined Platform:</span>
                        <span class="fw-semibold"><?= date('M Y', strtotime($note['uploader_joined'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Document Metadata Summary Card -->
            <div class="card-modern bg-white p-4 mb-4">
                <h6 class="fw-bold text-dark mb-3">Document Information</h6>
                <ul class="list-unstyled mb-0 small">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">File Format</span>
                        <span class="fw-semibold text-danger"><i class="fa-solid fa-file-pdf me-1"></i> PDF Document</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">File Size</span>
                        <span class="fw-semibold"><?= format_bytes($note['file_size']) ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Original Filename</span>
                        <span class="fw-semibold text-truncate ms-2" style="max-width:180px;" title="<?= e($note['original_filename']) ?>">
                            <?= e($note['original_filename']) ?>
                        </span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Upload Date</span>
                        <span class="fw-semibold"><?= date('M j, Y', strtotime($note['created_at'])) ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Total Downloads</span>
                        <span class="fw-bold text-success"><?= $note['download_count'] ?></span>
                    </li>
                </ul>
            </div>

            <!-- Related Notes -->
            <?php if (!empty($relatedNotes)): ?>
                <div class="card-modern bg-white p-4">
                    <h6 class="fw-bold text-dark mb-3">Related Study Notes</h6>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($relatedNotes as $rNote): ?>
                            <a href="<?= BASE_URL ?>/note-details.php?id=<?= $rNote['id'] ?>" class="text-decoration-none">
                                <div class="p-2 border rounded-3 bg-light hover-shadow">
                                    <div class="fw-semibold text-dark small text-truncate"><?= e($rNote['title']) ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;">
                                        <?= e($rNote['subject_code']) ?> • <?= $rNote['download_count'] ?> downloads
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Report Inappropriate Note Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="reportModalLabel">
                    <i class="fa-solid fa-flag text-danger me-2"></i> Report Study Material
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/actions/report-note.php">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="note_id" value="<?= $note['id'] ?>">

                    <div class="mb-3">
                        <label for="report_reason" class="form-label small fw-semibold">Reason for Report <span class="text-danger">*</span></label>
                        <select class="form-select" id="report_reason" name="reason" required>
                            <option value="">Select a reason</option>
                            <option value="Inappropriate Content">Inappropriate or offensive content</option>
                            <option value="Copyright Violation">Copyright or intellectual property infringement</option>
                            <option value="Wrong Subject or Semester">Wrong subject, syllabus, or semester tag</option>
                            <option value="Corrupted or Blank PDF">Corrupted, unreadable, or blank PDF</option>
                            <option value="Spam or Advertising">Spam or unauthorized commercial advertising</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="report_details" class="form-label small fw-semibold">Additional Details <span class="text-muted">(Optional)</span></label>
                        <textarea class="form-control" id="report_details" name="details" rows="3" placeholder="Explain the issue to help administrators review this document..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
