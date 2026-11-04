<?php
/**
 * Student Dashboard
 * Student Notes Sharing Platform
 */

$pageTitle = "Student Dashboard";
require_once __DIR__ . '/includes/header.php';

// Ensure user is logged in
require_login();

$userId = current_user_id();
$db = get_db();

// 1. Fetch Student-specific stats
// Total notes uploaded by this user
$stmtMyNotes = $db->prepare("SELECT COUNT(*) FROM notes WHERE user_id = ?");
$stmtMyNotes->execute([$userId]);
$myNotesCount = (int)$stmtMyNotes->fetchColumn();

// Total downloads received on notes uploaded by this user
$stmtMyDownloads = $db->prepare("SELECT COALESCE(SUM(download_count), 0) FROM notes WHERE user_id = ?");
$stmtMyDownloads->execute([$userId]);
$myDownloadsCount = (int)$stmtMyDownloads->fetchColumn();

// Total bookmarks saved by this user
$stmtMyBookmarks = $db->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id = ?");
$stmtMyBookmarks->execute([$userId]);
$myBookmarksCount = (int)$stmtMyBookmarks->fetchColumn();

// Total community notes available
$totalCommunityNotes = (int)$db->query("SELECT COUNT(*) FROM notes")->fetchColumn();

// 2. Fetch User's Recent Uploads (up to 5)
$stmtRecentMyNotes = $db->prepare("
    SELECT n.*, s.name as subject_name, s.code as subject_code, d.code as dept_code
    FROM notes n
    JOIN subjects s ON n.subject_id = s.id
    JOIN departments d ON n.department_id = d.id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
    LIMIT 5
");
$stmtRecentMyNotes->execute([$userId]);
$myRecentUploads = $stmtRecentMyNotes->fetchAll();

// 3. Fetch User's Recent Bookmarks (up to 4)
$stmtBookmarks = $db->prepare("
    SELECT n.*, 
           u.name as uploader_name,
           s.name as subject_name, s.code as subject_code,
           d.code as dept_code,
           b.created_at as bookmarked_at
    FROM bookmarks b
    JOIN notes n ON b.note_id = n.id
    JOIN users u ON n.user_id = u.id
    JOIN subjects s ON n.subject_id = s.id
    JOIN departments d ON n.department_id = d.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
    LIMIT 4
");
$stmtBookmarks->execute([$userId]);
$mySavedNotes = $stmtBookmarks->fetchAll();

// 4. Fetch recommended subjects for student's department or semester
$stmtDeptSubjects = $db->prepare("
    SELECT s.*, d.code as dept_code, COUNT(n.id) as note_count
    FROM subjects s
    JOIN departments d ON s.department_id = d.id
    LEFT JOIN notes n ON s.id = n.subject_id
    WHERE s.department_id = ? OR s.semester = ?
    GROUP BY s.id
    ORDER BY note_count DESC, s.name ASC
    LIMIT 6
");
$stmtDeptSubjects->execute([$user['department_id'] ?: 1, $user['semester'] ?: 4]);
$recommendedSubjects = $stmtDeptSubjects->fetchAll();
?>

<div class="container py-4">
    <!-- Welcome Header Banner -->
    <div class="card-modern p-4 mb-4 bg-white border-0 shadow-sm" style="border-radius: 18px;">
        <div class="row align-items-center justify-content-between g-3">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="dashboard-user-badge">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h3 class="fw-bold text-dark mb-1">Welcome back, <?= e($user['name']) ?>!</h3>
                        <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                            <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1">
                                <i class="fa-solid fa-graduation-cap me-1"></i> <?= e($user['department'] ?: 'Engineering') ?>
                            </span>
                            <?php if ($user['semester']): ?>
                                <span class="badge bg-secondary-subtle text-secondary fw-semibold px-2 py-1">
                                    Semester <?= e($user['semester']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($user['roll_number'])): ?>
                                <span class="badge bg-light text-muted border px-2 py-1">
                                    ID: <?= e($user['roll_number']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= BASE_URL ?>/upload-note.php" class="btn btn-primary px-3 py-2 shadow-sm">
                    <i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload New Note
                </a>
                <a href="<?= BASE_URL ?>/explore.php" class="btn btn-outline-secondary px-3 py-2 ms-1">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Browse
                </a>
            </div>
        </div>
    </div>

    <!-- Student Stats Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card-modern p-3 d-flex align-items-center gap-3 bg-white">
                <div class="rounded-3 p-3 bg-primary-subtle text-primary" style="font-size:1.4rem;">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $myNotesCount ?></div>
                    <div class="text-muted small">My Uploaded Notes</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card-modern p-3 d-flex align-items-center gap-3 bg-white">
                <div class="rounded-3 p-3 bg-success-subtle text-success" style="font-size:1.4rem;">
                    <i class="fa-solid fa-download"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $myDownloadsCount ?></div>
                    <div class="text-muted small">Downloads Received</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card-modern p-3 d-flex align-items-center gap-3 bg-white">
                <div class="rounded-3 p-3 bg-warning-subtle text-warning" style="font-size:1.4rem;">
                    <i class="fa-solid fa-bookmark"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $myBookmarksCount ?></div>
                    <div class="text-muted small">Saved Bookmarks</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card-modern p-3 d-flex align-items-center gap-3 bg-white">
                <div class="rounded-3 p-3 bg-info-subtle text-info" style="font-size:1.4rem;">
                    <i class="fa-solid fa-book-open-reader"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalCommunityNotes ?></div>
                    <div class="text-muted small">Community Notes</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Student's Recent Uploads -->
        <div class="col-lg-8">
            <div class="card-modern bg-white p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">My Uploads</h5>
                        <p class="text-muted small mb-0">Study materials contributed by you</p>
                    </div>
                    <?php if ($myNotesCount > 0): ?>
                        <a href="<?= BASE_URL ?>/my-uploads.php" class="btn btn-sm btn-outline-primary">
                            View All (<?= $myNotesCount ?>)
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($myRecentUploads)): ?>
                    <div class="text-center py-5 border rounded-3 bg-light">
                        <i class="fa-solid fa-cloud-arrow-up fa-3x text-muted mb-3 opacity-50"></i>
                        <h6 class="fw-bold text-dark">You haven't uploaded any notes yet.</h6>
                        <p class="text-muted small mb-3">Share your handwritten notes, assignments, or summaries to assist fellow students.</p>
                        <a href="<?= BASE_URL ?>/upload-note.php" class="btn btn-primary btn-sm px-3">
                            <i class="fa-solid fa-plus me-1"></i> Upload Your First Note
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Note Title</th>
                                    <th>Subject</th>
                                    <th>Downloads</th>
                                    <th>Uploaded</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myRecentUploads as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                                                <div>
                                                    <a href="<?= BASE_URL ?>/note-details.php?id=<?= $item['id'] ?>" class="fw-bold text-dark text-decoration-none">
                                                        <?= e($item['title']) ?>
                                                    </a>
                                                    <div class="text-muted small"><?= format_bytes($item['file_size']) ?> • <?= e($item['academic_year']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= e($item['subject_code']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="fa-solid fa-download me-1"></i> <?= $item['download_count'] ?>
                                            </span>
                                        </td>
                                        <td class="text-muted small"><?= time_ago($item['created_at']) ?></td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= BASE_URL ?>/note-details.php?id=<?= $item['id'] ?>" class="btn btn-light border" title="View Note">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/edit-note.php?id=<?= $item['id'] ?>" class="btn btn-light border" title="Edit Note Details">
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
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Saved / Bookmarked Notes -->
            <div class="card-modern bg-white p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Bookmarked Notes</h5>
                        <p class="text-muted small mb-0">Study resources you saved for quick revision</p>
                    </div>
                    <?php if ($myBookmarksCount > 0): ?>
                        <a href="<?= BASE_URL ?>/bookmarks.php" class="btn btn-sm btn-outline-primary">
                            View All (<?= $myBookmarksCount ?>)
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($mySavedNotes)): ?>
                    <div class="text-center py-4 border rounded-3 bg-light">
                        <i class="fa-regular fa-bookmark fa-2x text-muted mb-2 opacity-50"></i>
                        <p class="text-muted small mb-0">No bookmarks saved yet. Click the bookmark icon on any note card to save it here.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($mySavedNotes as $sNote): ?>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 h-100 d-flex flex-column justify-content-between bg-light">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-primary-subtle text-primary" style="font-size:0.7rem;">
                                                <?= e($sNote['dept_code']) ?> Sem <?= $sNote['semester'] ?>
                                            </span>
                                            <button type="button" class="bookmark-btn active btn-bookmark-toggle" data-note-id="<?= $sNote['id'] ?>" title="Remove bookmark" style="width:28px; height:28px;">
                                                <i class="fa-solid fa-bookmark text-danger" style="font-size:0.8rem;"></i>
                                            </button>
                                        </div>
                                        <h6 class="fw-bold text-dark text-truncate mb-1">
                                            <a href="<?= BASE_URL ?>/note-details.php?id=<?= $sNote['id'] ?>" class="text-dark">
                                                <?= e($sNote['title']) ?>
                                            </a>
                                        </h6>
                                        <div class="text-muted small mb-2"><?= e($sNote['subject_name']) ?></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top small text-muted">
                                        <span>By <?= e($sNote['uploader_name']) ?></span>
                                        <a href="<?= BASE_URL ?>/actions/download.php?id=<?= $sNote['id'] ?>" class="text-primary fw-semibold">
                                            <i class="fa-solid fa-arrow-down me-1"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Quick Recommended Subjects & Shortcuts -->
        <div class="col-lg-4">
            <!-- Quick Actions Card -->
            <div class="card-modern bg-white p-4 mb-4">
                <h6 class="fw-bold text-dark mb-3">Quick Navigation</h6>
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/upload-note.php" class="btn btn-outline-primary text-start py-2">
                        <i class="fa-solid fa-cloud-arrow-up me-2 text-primary"></i> Upload Academic Notes
                    </a>
                    <a href="<?= BASE_URL ?>/explore.php" class="btn btn-light border text-start py-2">
                        <i class="fa-solid fa-compass me-2 text-primary"></i> Explore All Subjects
                    </a>
                    <a href="<?= BASE_URL ?>/my-uploads.php" class="btn btn-light border text-start py-2">
                        <i class="fa-solid fa-folder-open me-2 text-primary"></i> Manage My Notes
                    </a>
                    <a href="<?= BASE_URL ?>/bookmarks.php" class="btn btn-light border text-start py-2">
                        <i class="fa-solid fa-bookmark me-2 text-danger"></i> Saved Bookmarks
                    </a>
                </div>
            </div>

            <!-- Recommended Subjects for this Student -->
            <div class="card-modern bg-white p-4">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-lightbulb text-warning me-1"></i> Recommended For You
                </h6>
                <p class="text-muted small mb-3">Based on your department and academic semester:</p>

                <div class="d-flex flex-column gap-2">
                    <?php foreach ($recommendedSubjects as $rSub): ?>
                        <a href="<?= BASE_URL ?>/explore.php?subject=<?= $rSub['id'] ?>" class="text-decoration-none">
                            <div class="p-2 border rounded-3 d-flex justify-content-between align-items-center bg-light">
                                <div class="text-truncate me-2">
                                    <div class="fw-semibold text-dark small text-truncate"><?= e($rSub['name']) ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= e($rSub['code']) ?> • Sem <?= $rSub['semester'] ?></div>
                                </div>
                                <span class="badge bg-white text-muted border small"><?= $rSub['note_count'] ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
