<?php
/**
 * My Uploads Management Page
 * Student Notes Sharing Platform
 */

$pageTitle = "My Uploaded Notes";
require_once __DIR__ . '/includes/header.php';

require_login();
$userId = current_user_id();
$db = get_db();

// Search or filter parameters
$search = clean_input($_GET['q'] ?? '');

$sql = "
    SELECT n.*, 
           s.name as subject_name, s.code as subject_code,
           d.name as dept_name, d.code as dept_code
    FROM notes n
    JOIN subjects s ON n.subject_id = s.id
    JOIN departments d ON n.department_id = d.id
    WHERE n.user_id = ?
";
$params = [$userId];

if (!empty($search)) {
    $sql .= " AND (n.title LIKE ? OR s.name LIKE ? OR s.code LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY n.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$myNotes = $stmt->fetchAll();

$totalUploads = count($myNotes);
$totalDownloads = array_sum(array_column($myNotes, 'download_count'));
?>

<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Uploads</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">My Uploaded Notes</h3>
            <p class="text-muted small mb-0">Manage, edit, or remove the study materials you contributed</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/upload-note.php" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload New Note
            </a>
        </div>
    </div>

    <!-- Summary Stats Strip -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card-modern p-3 bg-white">
                <div class="text-muted small fw-semibold text-uppercase">Total Uploaded</div>
                <div class="fs-3 fw-bold text-dark"><?= $totalUploads ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card-modern p-3 bg-white">
                <div class="text-muted small fw-semibold text-uppercase">Total Downloads Received</div>
                <div class="fs-3 fw-bold text-success"><?= $totalDownloads ?></div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card-modern p-3 bg-white mb-4">
        <form method="GET" action="<?= BASE_URL ?>/my-uploads.php" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Filter notes by title or subject code..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                <?php if (!empty($search)): ?>
                    <a href="<?= BASE_URL ?>/my-uploads.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Notes List / Table -->
    <div class="card-modern bg-white p-4">
        <?php if (empty($myNotes)): ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-folder-open fa-3x text-muted mb-3 opacity-50"></i>
                <h5 class="fw-bold">No uploaded notes found.</h5>
                <p class="text-muted small mb-3">
                    <?= !empty($search) ? 'No notes matched your search query.' : 'You have not uploaded any study materials yet.' ?>
                </p>
                <a href="<?= BASE_URL ?>/upload-note.php" class="btn btn-primary btn-sm px-3">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload a Note
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Document Title</th>
                            <th>Subject & Code</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>Downloads</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myNotes as $note): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-file-pdf text-danger fs-4"></i>
                                        <div>
                                            <a href="<?= BASE_URL ?>/note-details.php?id=<?= $note['id'] ?>" class="fw-bold text-dark text-decoration-none">
                                                <?= e($note['title']) ?>
                                            </a>
                                            <div class="text-muted small"><?= e($note['academic_year']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark small"><?= e($note['subject_name']) ?></div>
                                    <span class="badge bg-light text-muted border"><?= e($note['subject_code']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        <?= e($note['dept_code']) ?>
                                    </span>
                                </td>
                                <td>Sem <?= e($note['semester']) ?></td>
                                <td>
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="fa-solid fa-download me-1"></i> <?= $note['download_count'] ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= format_bytes($note['file_size']) ?></td>
                                <td class="text-muted small"><?= date('M j, Y', strtotime($note['created_at'])) ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/note-details.php?id=<?= $note['id'] ?>" class="btn btn-light border" title="View Note">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/edit-note.php?id=<?= $note['id'] ?>" class="btn btn-light border" title="Edit Note">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/actions/download.php?id=<?= $note['id'] ?>" class="btn btn-light border" title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/actions/delete-note.php?id=<?= $note['id'] ?>&csrf_token=<?= csrf_token() ?>" 
                                           class="btn btn-light border text-danger btn-confirm-delete" 
                                           data-title="<?= e($note['title']) ?>" 
                                           title="Delete Note">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
