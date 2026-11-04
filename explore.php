<?php
/**
 * Explore Notes Discovery Page
 * Student Notes Sharing Platform
 */

$pageTitle = "Explore Academic Notes";
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// 1. Capture Filters & Search Query
$searchKeyword = clean_input($_GET['q'] ?? '');
$deptFilter    = (int)($_GET['dept'] ?? 0);
$semFilter     = (int)($_GET['sem'] ?? 0);
$subjFilter    = (int)($_GET['subject'] ?? 0);
$yearFilter    = clean_input($_GET['year'] ?? '');
$sortBy        = clean_input($_GET['sort'] ?? 'newest');
$page          = max((int)($_GET['page'] ?? 1), 1);
$perPage       = 9;
$offset        = ($page - 1) * $perPage;

// 2. Build SQL query with parameters
$whereConditions = ["1=1"];
$params = [];

if (!empty($searchKeyword)) {
    $whereConditions[] = "(n.title LIKE ? OR n.description LIKE ? OR s.name LIKE ? OR s.code LIKE ?)";
    $likeTerm = "%{$searchKeyword}%";
    $params[] = $likeTerm;
    $params[] = $likeTerm;
    $params[] = $likeTerm;
    $params[] = $likeTerm;
}

if ($deptFilter > 0) {
    $whereConditions[] = "n.department_id = ?";
    $params[] = $deptFilter;
}

if ($semFilter > 0) {
    $whereConditions[] = "n.semester = ?";
    $params[] = $semFilter;
}

if ($subjFilter > 0) {
    $whereConditions[] = "n.subject_id = ?";
    $params[] = $subjFilter;
}

if (!empty($yearFilter)) {
    $whereConditions[] = "n.academic_year = ?";
    $params[] = $yearFilter;
}

$whereClause = implode(" AND ", $whereConditions);

// Determine Sort Order
$orderClause = match ($sortBy) {
    'downloads' => "n.download_count DESC, n.created_at DESC",
    'title_asc' => "n.title ASC",
    'oldest'    => "n.created_at ASC",
    default     => "n.created_at DESC",
};

// 3. Count Total Matching Notes for Pagination
$countSql = "
    SELECT COUNT(*) 
    FROM notes n
    JOIN subjects s ON n.subject_id = s.id
    WHERE {$whereClause}
";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalNotes = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalNotes / $perPage);

// 4. Fetch Paginated Notes
$notesSql = "
    SELECT n.*, 
           u.name as uploader_name,
           s.name as subject_name, s.code as subject_code,
           d.name as dept_name, d.code as dept_code
    FROM notes n
    JOIN users u ON n.user_id = u.id
    JOIN subjects s ON n.subject_id = s.id
    JOIN departments d ON n.department_id = d.id
    WHERE {$whereClause}
    ORDER BY {$orderClause}
    LIMIT {$perPage} OFFSET {$offset}
";
$notesStmt = $db->prepare($notesSql);
$notesStmt->execute($params);
$notesList = $notesStmt->fetchAll();

// Pre-load reference lists for filter selects
$allDepartments = get_all_departments();
$allSubjects = get_all_subjects($deptFilter ?: null, $semFilter ?: null);

// Helper to preserve active query params when paginating
function pagination_url(int $targetPage): string {
    $params = $_GET;
    $params['page'] = $targetPage;
    return BASE_URL . '/explore.php?' . http_build_query($params);
}
?>

<div class="container py-4">
    <!-- Header Banner -->
    <div class="card-modern p-4 mb-4 bg-white border-0 shadow-sm" style="border-radius: 18px;">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 mb-2">Resource Discovery</span>
                <h3 class="fw-bold text-dark mb-1">Explore Academic Study Materials</h3>
                <p class="text-muted small mb-0">Browse university syllabus-aligned notes, lab guides, and revision sheets across all engineering branches.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?= BASE_URL ?>/upload-note.php" class="btn btn-primary shadow-sm">
                    <i class="fa-solid fa-cloud-arrow-up me-2"></i> Share Your Notes
                </a>
            </div>
        </div>
    </div>

    <!-- Search & Multi-Filter Form -->
    <div class="card-modern bg-white p-4 mb-4">
        <form method="GET" action="<?= BASE_URL ?>/explore.php" id="exploreFilterForm">
            <!-- Search Input Row -->
            <div class="row g-2 mb-3">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Search by note title, subject code, or topic (e.g., CS8492, DBMS, Trees)..." value="<?= e($searchKeyword) ?>">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fa-solid fa-filter me-1"></i> Apply Filters
                    </button>
                    <?php if (!empty($searchKeyword) || $deptFilter || $semFilter || $subjFilter || !empty($yearFilter) || $sortBy !== 'newest'): ?>
                        <a href="<?= BASE_URL ?>/explore.php" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Detailed Filter Dropdowns -->
            <div class="row g-2 small">
                <!-- Department -->
                <div class="col-6 col-md-3">
                    <label class="form-label text-muted fw-semibold mb-1">Department</label>
                    <select name="dept" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="0">All Departments</option>
                        <?php foreach ($allDepartments as $dept): ?>
                            <option value="<?= $dept['id'] ?>" <?= $deptFilter == $dept['id'] ? 'selected' : '' ?>>
                                <?= e($dept['code']) ?> - <?= e($dept['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Semester -->
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted fw-semibold mb-1">Semester</label>
                    <select name="sem" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="0">All Semesters</option>
                        <?php for ($s = 1; $s <= 8; $s++): ?>
                            <option value="<?= $s ?>" <?= $semFilter == $s ? 'selected' : '' ?>>
                                Semester <?= $s ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Subject -->
                <div class="col-6 col-md-3">
                    <label class="form-label text-muted fw-semibold mb-1">Specific Course</label>
                    <select name="subject" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="0">All Subjects</option>
                        <?php foreach ($allSubjects as $subj): ?>
                            <option value="<?= $subj['id'] ?>" <?= $subjFilter == $subj['id'] ? 'selected' : '' ?>>
                                <?= e($subj['code']) ?> - <?= e($subj['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Academic Year -->
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted fw-semibold mb-1">Academic Year</label>
                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Years</option>
                        <option value="2025-2026" <?= $yearFilter === '2025-2026' ? 'selected' : '' ?>>2025-2026</option>
                        <option value="2024-2025" <?= $yearFilter === '2024-2025' ? 'selected' : '' ?>>2024-2025</option>
                        <option value="2023-2024" <?= $yearFilter === '2023-2024' ? 'selected' : '' ?>>2023-2024</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div class="col-12 col-md-2">
                    <label class="form-label text-muted fw-semibold mb-1">Sort By</label>
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest Uploads</option>
                        <option value="downloads" <?= $sortBy === 'downloads' ? 'selected' : '' ?>>Most Downloaded</option>
                        <option value="title_asc" <?= $sortBy === 'title_asc' ? 'selected' : '' ?>>Title (A to Z)</option>
                        <option value="oldest" <?= $sortBy === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Status Strip -->
    <div class="d-flex justify-content-between align-items-center mb-3 text-muted small">
        <div>
            Showing <strong><?= count($notesList) ?></strong> of <strong><?= $totalNotes ?></strong> study notes found
            <?php if (!empty($searchKeyword)): ?>
                matching "<strong><?= e($searchKeyword) ?></strong>"
            <?php endif; ?>
        </div>
        <div>
            Page <strong><?= $page ?></strong> of <strong><?= max($totalPages, 1) ?></strong>
        </div>
    </div>

    <!-- Notes Cards Grid -->
    <?php if (empty($notesList)): ?>
        <div class="card-modern bg-white p-5 text-center my-4">
            <i class="fa-solid fa-file-circle-question fa-3x text-muted mb-3 opacity-50"></i>
            <h5 class="fw-bold text-dark">No academic notes found</h5>
            <p class="text-muted small mb-3">No study materials match your specific filter criteria. Try adjusting your search query or department filters.</p>
            <div>
                <a href="<?= BASE_URL ?>/explore.php" class="btn btn-outline-primary btn-sm px-3">
                    <i class="fa-solid fa-filter-circle-xmark me-1"></i> Clear All Filters
                </a>
                <a href="<?= BASE_URL ?>/upload-note.php" class="btn btn-primary btn-sm px-3 ms-2">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload this Note
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4 mb-4">
            <?php foreach ($notesList as $note): 
                $isBookmarked = is_note_bookmarked((int)$note['id']);
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card-modern note-card">
                        <div class="note-card-top">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="pdf-icon-badge">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge-tag badge-dept"><?= e($note['dept_code']) ?></span>
                                    <span class="badge-tag badge-sem">Sem <?= e($note['semester']) ?></span>
                                    <button type="button" class="bookmark-btn btn-bookmark-toggle <?= $isBookmarked ? 'active' : '' ?>" data-note-id="<?= $note['id'] ?>" title="Bookmark Note">
                                        <i class="fa-<?= $isBookmarked ? 'solid' : 'regular' ?> fa-bookmark"></i>
                                    </button>
                                </div>
                            </div>

                            <h5 class="fw-bold text-dark mb-2 text-truncate" title="<?= e($note['title']) ?>">
                                <a href="<?= BASE_URL ?>/note-details.php?id=<?= $note['id'] ?>" class="text-dark">
                                    <?= e($note['title']) ?>
                                </a>
                            </h5>

                            <div class="text-primary small fw-semibold mb-2 text-truncate">
                                <i class="fa-solid fa-book-open me-1"></i> <?= e($note['subject_name']) ?> (<?= e($note['subject_code']) ?>)
                            </div>

                            <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 40px;">
                                <?= e($note['description']) ?>
                            </p>

                            <div class="d-flex align-items-center justify-content-between small text-muted pt-2 border-top">
                                <div class="d-flex align-items-center gap-1">
                                    <i class="fa-regular fa-user"></i>
                                    <span class="text-truncate" style="max-width: 130px;"><?= e($note['uploader_name']) ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span><i class="fa-solid fa-download me-1 text-primary"></i> <?= $note['download_count'] ?></span>
                                    <span><?= time_ago($note['created_at']) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="note-card-footer d-flex gap-2">
                            <a href="<?= BASE_URL ?>/note-details.php?id=<?= $note['id'] ?>" class="btn btn-light border btn-sm flex-fill">
                                <i class="fa-solid fa-eye me-1"></i> View Details
                            </a>
                            <a href="<?= BASE_URL ?>/actions/download.php?id=<?= $note['id'] ?>" class="btn btn-primary btn-sm flex-fill">
                                <i class="fa-solid fa-arrow-down me-1"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page -->
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= pagination_url($page - 1) ?>" aria-label="Previous">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= pagination_url($p) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= pagination_url($page + 1) ?>" aria-label="Next">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
