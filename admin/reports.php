<?php
/**
 * Administrator Content Moderation & Reports Page
 * Student Notes Sharing Platform
 */

$pageTitle = "Moderate Content & User Reports";
require_once __DIR__ . '/../includes/admin-header.php';

$db = get_db();

// Handle Actions (Dismiss or Delete Reported Note)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    $reportId = (int)($_POST['report_id'] ?? 0);

    if ($reportId > 0) {
        if ($action === 'dismiss') {
            $stmt = $db->prepare("UPDATE reports SET status = 'dismissed' WHERE id = ?");
            $stmt->execute([$reportId]);
            set_flash('success', 'Report marked as dismissed.');
        } elseif ($action === 'delete_note') {
            $noteId = (int)($_POST['note_id'] ?? 0);
            if ($noteId > 0) {
                // Fetch note to unlink file
                $nStmt = $db->prepare("SELECT file_path, title FROM notes WHERE id = ?");
                $nStmt->execute([$noteId]);
                $note = $nStmt->fetch();

                if ($note) {
                    $filePath = NOTES_UPLOAD_PATH . DIRECTORY_SEPARATOR . $note['file_path'];
                    if (file_exists($filePath) && is_file($filePath)) {
                        unlink($filePath);
                    }
                    // Delete note (cascades)
                    $db->prepare("DELETE FROM notes WHERE id = ?")->execute([$noteId]);
                    // Update report status
                    $db->prepare("UPDATE reports SET status = 'reviewed' WHERE id = ?")->execute([$reportId]);
                    set_flash('success', "Note '{$note['title']}' was taken down and report resolved.");
                }
            }
        }
    }
    redirect('/admin/reports.php');
}

// Fetch all reports
$reportsStmt = $db->query("
    SELECT r.*, 
           u.name as reporter_name, u.email as reporter_email,
           n.title as note_title, n.file_path, n.user_id as author_id,
           author.name as author_name
    FROM reports r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN notes n ON r.note_id = n.id
    LEFT JOIN users author ON n.user_id = author.id
    ORDER BY 
        CASE WHEN r.status = 'pending' THEN 1 ELSE 2 END,
        r.created_at DESC
");
$reports = $reportsStmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Content Moderation Queue</h4>
            <p class="text-muted small mb-0">Review student flags regarding copyright, inappropriate materials, or syllabus mismatch</p>
        </div>
    </div>

    <!-- Reports Table Card -->
    <div class="admin-table-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Flagged Submissions (<?= count($reports) ?>)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Target Note</th>
                        <th>Reason</th>
                        <th>Details / Explanation</th>
                        <th>Reported By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-end">Moderation Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-circle-check text-success me-2"></i> All clear! No reported notes in the moderation queue.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $rep): ?>
                            <tr>
                                <td class="text-muted small">#<?= $rep['id'] ?></td>
                                <td>
                                    <?php if ($rep['note_title']): ?>
                                        <div class="fw-bold text-dark small text-truncate" style="max-width:200px;">
                                            <a href="<?= BASE_URL ?>/note-details.php?id=<?= $rep['note_id'] ?>" class="text-dark">
                                                <?= e($rep['note_title']) ?>
                                            </a>
                                        </div>
                                        <div class="text-muted" style="font-size:0.75rem;">Author: <?= e($rep['author_name']) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">[Note Already Removed]</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger fw-semibold">
                                        <?= e($rep['reason']) ?>
                                    </span>
                                </td>
                                <td class="small text-secondary" style="max-width:260px;">
                                    <?= e($rep['details'] ?: 'No additional details submitted.') ?>
                                </td>
                                <td>
                                    <div class="fw-semibold small text-dark"><?= e($rep['reporter_name']) ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= e($rep['reporter_email']) ?></div>
                                </td>
                                <td class="small text-muted"><?= date('M j, Y', strtotime($rep['created_at'])) ?></td>
                                <td>
                                    <?php if ($rep['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark"><i class="fa-regular fa-clock me-1"></i> Pending</span>
                                    <?php elseif ($rep['status'] === 'reviewed'): ?>
                                        <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Resolved</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fa-solid fa-ban me-1"></i> Dismissed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($rep['status'] === 'pending'): ?>
                                        <div class="d-inline-flex gap-1">
                                            <!-- Dismiss Report Form -->
                                            <form method="POST" action="<?= BASE_URL ?>/admin/reports.php" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="dismiss">
                                                <input type="hidden" name="report_id" value="<?= $rep['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-light border small" title="Dismiss Report">
                                                    <i class="fa-solid fa-xmark text-muted"></i> Dismiss
                                                </button>
                                            </form>

                                            <?php if ($rep['note_id']): ?>
                                                <!-- Delete Note Form -->
                                                <form method="POST" action="<?= BASE_URL ?>/admin/reports.php" class="d-inline" onsubmit="return confirm('Take down note and delete PDF file?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="delete_note">
                                                    <input type="hidden" name="report_id" value="<?= $rep['id'] ?>">
                                                    <input type="hidden" name="note_id" value="<?= $rep['note_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger small" title="Take Down Note">
                                                        <i class="fa-solid fa-trash-can"></i> Take Down
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">No action needed</span>
                                    <?php endif; ?>
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
