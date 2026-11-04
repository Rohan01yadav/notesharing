<?php
/**
 * Saved Bookmarks Page
 * Student Notes Sharing Platform
 */

$pageTitle = "My Saved Bookmarks";
require_once __DIR__ . '/includes/header.php';

require_login();
$userId = current_user_id();
$db = get_db();

$stmt = $db->prepare("
    SELECT n.*, 
           u.name as uploader_name,
           s.name as subject_name, s.code as subject_code,
           d.name as dept_name, d.code as dept_code,
           b.created_at as bookmarked_at
    FROM bookmarks b
    JOIN notes n ON b.note_id = n.id
    JOIN users u ON n.user_id = u.id
    JOIN subjects s ON n.subject_id = s.id
    JOIN departments d ON n.department_id = d.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$userId]);
$bookmarkedNotes = $stmt->fetchAll();
?>

<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Bookmarks</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Saved Bookmarks</h3>
            <p class="text-muted small mb-0">Your curated collection of important study materials and exam notes</p>
        </div>
        <a href="<?= BASE_URL ?>/explore.php" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-compass me-1"></i> Discover More Notes
        </a>
    </div>

    <!-- Notes Grid -->
    <?php if (empty($bookmarkedNotes)): ?>
        <div class="card-modern bg-white p-5 text-center">
            <i class="fa-regular fa-bookmark fa-3x text-muted mb-3 opacity-50"></i>
            <h5 class="fw-bold text-dark">No bookmarked notes yet.</h5>
            <p class="text-muted small mb-3">You can save any note for quick offline or revision access by clicking the bookmark ribbon icon.</p>
            <a href="<?= BASE_URL ?>/explore.php" class="btn btn-primary btn-sm px-4">
                <i class="fa-solid fa-magnifying-glass me-1"></i> Explore Academic Notes
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($bookmarkedNotes as $note): ?>
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
                                    <button type="button" class="bookmark-btn active btn-bookmark-toggle" data-note-id="<?= $note['id'] ?>" title="Remove bookmark">
                                        <i class="fa-solid fa-bookmark"></i>
                                    </button>
                                </div>
                            </div>

                            <h5 class="fw-bold text-dark mb-2 text-truncate" title="<?= e($note['title']) ?>">
                                <a href="<?= BASE_URL ?>/note-details.php?id=<?= $note['id'] ?>" class="text-dark">
                                    <?= e($note['title']) ?>
                                </a>
                            </h5>

                            <div class="text-primary small fw-semibold mb-2">
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
                                <div class="d-flex align-items-center gap-2">
                                    <span><i class="fa-solid fa-download me-1 text-primary"></i> <?= $note['download_count'] ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="note-card-footer d-flex gap-2">
                            <a href="<?= BASE_URL ?>/note-details.php?id=<?= $note['id'] ?>" class="btn btn-light border btn-sm flex-fill">
                                <i class="fa-solid fa-eye me-1"></i> Details
                            </a>
                            <a href="<?= BASE_URL ?>/actions/download.php?id=<?= $note['id'] ?>" class="btn btn-primary btn-sm flex-fill">
                                <i class="fa-solid fa-arrow-down me-1"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
