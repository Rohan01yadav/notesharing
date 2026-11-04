<?php
/**
 * Administrator Departments Management Page
 * Student Notes Sharing Platform
 */

$pageTitle = "Manage Engineering Departments";
require_once __DIR__ . '/../includes/admin-header.php';

$db = get_db();

// Handle Add / Delete Department
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_department') {
        $code = strtoupper(clean_input($_POST['code'] ?? ''));
        $name = clean_input($_POST['name'] ?? '');
        $description = clean_input($_POST['description'] ?? '');

        if (empty($code) || empty($name)) {
            set_flash('error', 'Department code and name are required.');
        } else {
            try {
                $insStmt = $db->prepare("INSERT INTO departments (code, name, description) VALUES (?, ?, ?)");
                $insStmt->execute([$code, $name, $description]);
                set_flash('success', "Department '{$code} - {$name}' created successfully.");
            } catch (PDOException $e) {
                set_flash('error', 'Failed to create department: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'delete_department') {
        $deptId = (int)($_POST['department_id'] ?? 0);
        if ($deptId > 0) {
            try {
                $delStmt = $db->prepare("DELETE FROM departments WHERE id = ?");
                $delStmt->execute([$deptId]);
                set_flash('success', 'Department deleted successfully.');
            } catch (PDOException $e) {
                set_flash('error', 'Failed to delete department: ' . $e->getMessage());
            }
        }
    }
    redirect('/admin/departments.php');
}

// Fetch departments with subject and note counts
$stmt = $db->query("
    SELECT d.*, 
           COUNT(DISTINCT s.id) as total_subjects,
           COUNT(DISTINCT n.id) as total_notes
    FROM departments d
    LEFT JOIN subjects s ON d.id = s.department_id
    LEFT JOIN notes n ON d.id = n.department_id
    GROUP BY d.id
    ORDER BY d.code ASC
");
$departments = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Academic Departments</h4>
            <p class="text-muted small mb-0">Manage engineering disciplines, faculty departments, and branches</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDeptModal">
            <i class="fa-solid fa-plus me-1"></i> Add Department
        </button>
    </div>

    <!-- Departments Grid -->
    <div class="row g-4 mb-4">
        <?php foreach ($departments as $dept): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card-modern bg-white p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary-subtle text-primary fs-6 fw-bold px-2 py-1">
                                <?= e($dept['code']) ?>
                            </span>
                            <form method="POST" action="<?= BASE_URL ?>/admin/departments.php" class="d-inline" onsubmit="return confirm('Deleting department <?= e($dept['code']) ?> will also remove all its subjects and notes. Continue?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_department">
                                <input type="hidden" name="department_id" value="<?= $dept['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete Department">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                        <h5 class="fw-bold text-dark mb-2"><?= e($dept['name']) ?></h5>
                        <p class="text-muted small mb-3">
                            <?= e($dept['description'] ?: 'No description provided for this department.') ?>
                        </p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top small">
                        <span class="text-muted">
                            <i class="fa-solid fa-book-bookmark text-primary me-1"></i> <?= $dept['total_subjects'] ?> Subjects
                        </span>
                        <span class="badge bg-light text-dark border">
                            <i class="fa-solid fa-file-pdf text-danger me-1"></i> <?= $dept['total_notes'] ?> Notes
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-labelledby="addDeptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="addDeptModalLabel">
                    <i class="fa-solid fa-building-columns text-primary me-2"></i> Add Academic Department
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/departments.php">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add_department">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Department Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. AI-DS" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Department Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Artificial Intelligence & Data Science" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the department's focus area..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
