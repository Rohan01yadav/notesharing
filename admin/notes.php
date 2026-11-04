<?php
/**
 * Administrator Notes Management Page
 * Student Notes Sharing Platform
 */

$pageTitle = "Manage Academic Notes";
require_once __DIR__ . '/../includes/admin-header.php';

$db = get_db();

// Filter inputs
$search = clean_input($_GET['q'] ?? '');
$deptFilter = (int)($_GET['dept'] ?? 0);
$semFilter = (int)($_GET['sem'] ?? 0);

$sql = "
    SELECT n.*, 
           u.name as uploader_name, u.email as uploader_email,
           s.name as subject_name, s.code as subject_code,
           d.name as dept_name, d.code as dept_code
    FROM notes n
    JOIN users u ON n.user_id = u.id
    JOIN subjects s ON n.subject_id = s.id
    JOIN departments d ON n.department_id = d.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (n.title LIKE ? OR u.name LIKE ? OR s.code LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($deptFilter > 0) {
    $sql .= " AND n.department_id = ?";
    $params[] = $deptFilter;
}

if ($semFilter > 0) {
    $sql .= " AND n.semester = ?";
    $params[] = $semFilter;
}

$sql .= " ORDER BY n.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$notes = $stmt->fetchAll();

$departments = get_all_departments();
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Manage All Academic Notes</h4>
            <p class="text-muted small mb-0">Review, moderate, or remove study materials submitted by students</p>
        </div>
        <a href="<?= BASE_URL ?>/upload-note.php" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Note
        </a>
    </div>

    <!-- Search & Filters -->
    <div class="card-modern bg-white p-3 mb-4">
        <form method="GET" action="<?= BASE_URL ?>/admin/notes.php" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Search by title, uploader name, or course code..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="dept" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="0">All Departments</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= $deptFilter == $d['id'] ? 'selected' : '' ?>>
                            <?= e($d['code']) ?> - <?= e($d['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sem" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="0">All Semesters</option>
                    <?php for ($s = 1; $s <= 8; $s++): ?>
                        <option value="<?= $s ?>" <?= $semFilter == $s ? 'selected' : '' ?>>Sem <?= $s ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">Filter</button>
                <?php if (!empty($search) || $deptFilter || $semFilter): ?>
                    <a href="<?= BASE_URL ?>/admin/notes.php" class="btn btn-outline-secondary btn-sm" title="Clear">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Notes Table -->
    <div class="admin-table-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Total Notes (<?= count($notes) ?>)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 admin-data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Note Title</th>
                        <th>Contributor</th>
                        <th>Subject</th>
                        <th>Dept & Sem</th>
                        <th>File Size</th>
                        <th>Downloads</th>
                        <th>Upload Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notes)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No study notes found matching your criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notes as $item): ?>
                            <tr>
                                <td class="text-muted small">#<?= $item['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                                        <div>
                                            <a href="<?= BASE_URL ?>/note-details.php?id=<?= $item['id'] ?>" class="fw-bold text-dark text-decoration-none">
                                                <?= e($item['title']) ?>
                                            </a>
                                            <div class="text-muted" style="font-size:0.75rem;"><?= e($item['academic_year']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold small text-dark"><?= e($item['uploader_name']) ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= e($item['uploader_email']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= e($item['subject_code']) ?></span>
                                    <div class="small text-muted text-truncate" style="max-width:140px;"><?= e($item['subject_name']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary"><?= e($item['dept_code']) ?></span>
                                    <span class="badge bg-secondary-subtle text-secondary">Sem <?= $item['semester'] ?></span>
                                </td>
                                <td class="small text-muted"><?= format_bytes($item['file_size']) ?></td>
                                <td>
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="fa-solid fa-download me-1"></i> <?= $item['download_count'] ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?= date('M j, Y', strtotime($item['created_at'])) ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/note-details.php?id=<?= $item['id'] ?>" class="btn btn-light border" title="View Note">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/edit-note.php?id=<?= $item['id'] ?>" class="btn btn-light border" title="Edit Note">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/actions/download.php?id=<?= $item['id'] ?>" class="btn btn-light border" title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/actions/delete-note.php?id=<?= $item['id'] ?>&csrf_token=<?= csrf_token() ?>" 
                                           class="btn btn-light border text-danger btn-confirm-delete" 
                                           data-title="<?= e($item['title']) ?>" 
                                           title="Delete Note">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
