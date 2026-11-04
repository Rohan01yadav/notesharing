<?php
/**
 * Administrator Subjects Management Page
 * Student Notes Sharing Platform
 */

$pageTitle = "Manage Academic Subjects";
require_once __DIR__ . '/../includes/admin-header.php';

$db = get_db();

// Handle Add / Delete Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_subject') {
        $name         = clean_input($_POST['name'] ?? '');
        $code         = strtoupper(clean_input($_POST['code'] ?? ''));
        $departmentId = (int)($_POST['department_id'] ?? 0);
        $semester     = (int)($_POST['semester'] ?? 0);
        $description  = clean_input($_POST['description'] ?? '');

        if (empty($name) || empty($code) || empty($departmentId) || empty($semester)) {
            set_flash('error', 'Please fill in all required subject fields.');
        } else {
            try {
                $insStmt = $db->prepare("
                    INSERT INTO subjects (department_id, semester, code, name, description)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insStmt->execute([$departmentId, $semester, $code, $name, $description]);
                set_flash('success', "Subject '{$code} - {$name}' created successfully.");
            } catch (PDOException $e) {
                set_flash('error', 'Failed to create subject: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'delete_subject') {
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        if ($subjectId > 0) {
            try {
                // Check if notes exist for this subject
                $chk = $db->prepare("SELECT COUNT(*) FROM notes WHERE subject_id = ?");
                $chk->execute([$subjectId]);
                $noteCount = (int)$chk->fetchColumn();

                if ($noteCount > 0) {
                    set_flash('error', "Cannot delete subject: {$noteCount} notes are currently linked to this course. Delete or reassign those notes first.");
                } else {
                    $delStmt = $db->prepare("DELETE FROM subjects WHERE id = ?");
                    $delStmt->execute([$subjectId]);
                    set_flash('success', 'Subject deleted successfully.');
                }
            } catch (PDOException $e) {
                set_flash('error', 'Failed to delete subject: ' . $e->getMessage());
            }
        }
    }
    redirect('/admin/subjects.php');
}

// Fetch all subjects with note counts and department details
$stmt = $db->query("
    SELECT s.*, 
           d.name as dept_name, d.code as dept_code,
           COUNT(n.id) as note_count
    FROM subjects s
    JOIN departments d ON s.department_id = d.id
    LEFT JOIN notes n ON s.id = n.subject_id
    GROUP BY s.id
    ORDER BY d.code ASC, s.semester ASC, s.name ASC
");
$subjects = $stmt->fetchAll();

$departments = get_all_departments();
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Academic Subjects Curriculum</h4>
            <p class="text-muted small mb-0">Define courses, syllabus codes, semester mappings, and manage curricula</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
            <i class="fa-solid fa-plus me-1"></i> Add New Subject
        </button>
    </div>

    <!-- Subjects Table -->
    <div class="admin-table-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Total Subjects (<?= count($subjects) ?>)</h6>
            <div class="col-md-3">
                <input type="text" id="adminTableSearch" class="form-control form-control-sm" placeholder="Quick filter subjects...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 admin-data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Subject Name</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Description / Topics</th>
                        <th>Notes Count</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subj): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border fw-bold"><?= e($subj['code']) ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark small"><?= e($subj['name']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary"><?= e($subj['dept_code']) ?></span>
                            </td>
                            <td>Sem <?= e($subj['semester']) ?></td>
                            <td class="text-muted small text-truncate" style="max-width:250px;">
                                <?= e($subj['description'] ?: 'No syllabus description provided.') ?>
                            </td>
                            <td>
                                <span class="badge <?= $subj['note_count'] > 0 ? 'bg-success-subtle text-success' : 'bg-light text-muted border' ?>">
                                    <?= $subj['note_count'] ?> Notes
                                </span>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="<?= BASE_URL ?>/admin/subjects.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete subject <?= e($subj['code']) ?>?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_subject">
                                    <input type="hidden" name="subject_id" value="<?= $subj['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-light border text-danger small" title="Delete Subject">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="addSubjectModalLabel">
                    <i class="fa-solid fa-book-bookmark text-primary me-2"></i> Add Academic Subject
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/subjects.php">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add_subject">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Subject Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. CS8651" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Subject Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Internet Programming" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= e($dept['code']) ?> - <?= e($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Semester <span class="text-danger">*</span></label>
                            <select name="semester" class="form-select" required>
                                <option value="">Select Semester</option>
                                <?php for ($s = 1; $s <= 8; $s++): ?>
                                    <option value="<?= $s ?>">Semester <?= $s ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description / Key Topics</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief outline of syllabus units and topics..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
