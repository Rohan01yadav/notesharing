<?php
/**
 * Administrator Dashboard
 * Student Notes Sharing Platform
 */

$pageTitle = "Admin Dashboard & Platform Analytics";
require_once __DIR__ . '/../includes/admin-header.php';

$db = get_db();

// 1. Fetch KPI Metrics
$totalStudents   = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalAdmins     = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$totalNotes      = (int)$db->query("SELECT COUNT(*) FROM notes")->fetchColumn();
$totalDownloads  = (int)$db->query("SELECT COALESCE(SUM(download_count), 0) FROM notes")->fetchColumn();
$totalSubjects   = (int)$db->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$totalDepts      = (int)$db->query("SELECT COUNT(*) FROM departments")->fetchColumn();
$pendingReports  = (int)$db->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();

// 2. Fetch Department Notes Distribution for Chart.js
$deptStatsStmt = $db->query("
    SELECT d.code, COUNT(n.id) as note_count
    FROM departments d
    LEFT JOIN notes n ON d.id = n.department_id
    GROUP BY d.id
    ORDER BY d.code ASC
");
$deptStats = $deptStatsStmt->fetchAll();
$deptLabels = array_column($deptStats, 'code');
$deptCounts = array_map('intval', array_column($deptStats, 'note_count'));

// 3. Fetch Top 5 Most Downloaded Notes for Chart.js
$topNotesStmt = $db->query("
    SELECT title, download_count 
    FROM notes 
    ORDER BY download_count DESC 
    LIMIT 5
");
$topNotes = $topNotesStmt->fetchAll();
$topNoteTitles = array_map(function($item) {
    return mb_strimwidth($item['title'], 0, 20, "...");
}, $topNotes);
$topNoteDownloads = array_map('intval', array_column($topNotes, 'download_count'));

// 4. Fetch 5 Recent Uploads
$recentNotesStmt = $db->query("
    SELECT n.*, u.name as uploader_name, s.code as subject_code, d.code as dept_code
    FROM notes n
    JOIN users u ON n.user_id = u.id
    JOIN subjects s ON n.subject_id = s.id
    JOIN departments d ON n.department_id = d.id
    ORDER BY n.created_at DESC
    LIMIT 5
");
$recentNotes = $recentNotesStmt->fetchAll();

// 5. Fetch 5 Recent Registered Students
$recentUsersStmt = $db->query("
    SELECT u.*, d.code as dept_code
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    ORDER BY u.created_at DESC
    LIMIT 5
");
$recentUsers = $recentUsersStmt->fetchAll();
?>

<!-- Pass Data to Admin Charts JavaScript -->
<script>
    window.ADMIN_CHART_DATA = {
        deptLabels: <?= json_encode($deptLabels) ?>,
        deptCounts: <?= json_encode($deptCounts) ?>,
        topNoteTitles: <?= json_encode($topNoteTitles) ?>,
        topNoteDownloads: <?= json_encode($topNoteDownloads) ?>
    };
</script>

<div class="container-fluid">
    <!-- Page Title & Quick Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Platform Analytics Overview</h4>
            <p class="text-muted small mb-0">Real-time statistics for Student Notes Sharing Platform</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/upload-note.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Note
            </a>
            <a href="<?= BASE_URL ?>/admin/subjects.php" class="btn btn-light border btn-sm">
                <i class="fa-solid fa-plus me-1"></i> Add Subject
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- Total Students -->
        <div class="col-sm-6 col-xl-3">
            <div class="admin-stat-card">
                <div class="admin-stat-icon icon-purple">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalStudents ?></div>
                    <div class="text-muted small">Registered Students</div>
                </div>
            </div>
        </div>

        <!-- Total Uploaded Notes -->
        <div class="col-sm-6 col-xl-3">
            <div class="admin-stat-card">
                <div class="admin-stat-icon icon-emerald">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalNotes ?></div>
                    <div class="text-muted small">Total Academic Notes</div>
                </div>
            </div>
        </div>

        <!-- Total Downloads -->
        <div class="col-sm-6 col-xl-3">
            <div class="admin-stat-card">
                <div class="admin-stat-icon icon-amber">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalDownloads ?></div>
                    <div class="text-muted small">Total Note Downloads</div>
                </div>
            </div>
        </div>

        <!-- Flagged Reports -->
        <div class="col-sm-6 col-xl-3">
            <div class="admin-stat-card">
                <div class="admin-stat-icon icon-rose">
                    <i class="fa-solid fa-flag"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $pendingReports ?></div>
                    <div class="text-muted small">Pending Content Flags</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Visualizations Row -->
    <div class="row g-4 mb-4">
        <!-- Notes by Department Doughnut Chart -->
        <div class="col-lg-5">
            <div class="card-modern bg-white p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0">Notes by Department</h6>
                    <span class="badge bg-light text-muted border small"><?= $totalDepts ?> Departments</span>
                </div>
                <div style="position: relative; height: 260px;">
                    <canvas id="deptDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Most Downloaded Notes Bar Chart -->
        <div class="col-lg-7">
            <div class="card-modern bg-white p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0">Top Downloaded Study Guides</h6>
                    <span class="badge bg-light text-muted border small">Top 5 Resources</span>
                </div>
                <div style="position: relative; height: 260px;">
                    <canvas id="topNotesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row: Recent Uploads & Recent Students -->
    <div class="row g-4">
        <!-- Recent Notes Table -->
        <div class="col-lg-7">
            <div class="admin-table-card">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">Recently Uploaded Notes</h6>
                    <a href="<?= BASE_URL ?>/admin/notes.php" class="btn btn-outline-primary btn-sm">View All Notes</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Note Title</th>
                                <th>Uploader</th>
                                <th>Subject</th>
                                <th>Downloads</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentNotes as $rNote): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark text-truncate" style="max-width:200px;" title="<?= e($rNote['title']) ?>">
                                            <a href="<?= BASE_URL ?>/note-details.php?id=<?= $rNote['id'] ?>" class="text-dark">
                                                <?= e($rNote['title']) ?>
                                            </a>
                                        </div>
                                        <span class="text-muted small"><?= time_ago($rNote['created_at']) ?></span>
                                    </td>
                                    <td class="small"><?= e($rNote['uploader_name']) ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= e($rNote['dept_code']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success"><?= $rNote['download_count'] ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/actions/download.php?id=<?= $rNote['id'] ?>" class="btn btn-sm btn-light border" title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/actions/delete-note.php?id=<?= $rNote['id'] ?>&csrf_token=<?= csrf_token() ?>" 
                                           class="btn btn-sm btn-light border text-danger btn-confirm-delete" 
                                           data-title="<?= e($rNote['title']) ?>" 
                                           title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Registered Students Table -->
        <div class="col-lg-5">
            <div class="admin-table-card">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">Recent Registrations</h6>
                    <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline-primary btn-sm">All Users</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Dept</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $rUser): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark small"><?= e($rUser['name']) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem;"><?= e($rUser['email']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-muted border small"><?= e($rUser['dept_code'] ?: 'General') ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $rUser['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?> small">
                                            <?= ucfirst($rUser['role']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= time_ago($rUser['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
