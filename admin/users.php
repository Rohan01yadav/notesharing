<?php
/**
 * Administrator Users Management Page
 * Student Notes Sharing Platform
 */

$pageTitle = "Manage Users & Students";
require_once __DIR__ . '/../includes/admin-header.php';

$db = get_db();
$currentAdminId = current_user_id();

// Handle Role Change or User Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    $targetUserId = (int)($_POST['target_user_id'] ?? 0);

    if ($targetUserId > 0) {
        if ($action === 'change_role') {
            $newRole = ($_POST['new_role'] ?? 'student') === 'admin' ? 'admin' : 'student';

            // Prevent admin from demoting themselves
            if ($targetUserId === $currentAdminId && $newRole !== 'admin') {
                set_flash('error', 'You cannot demote your own administrator account.');
            } else {
                $roleStmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
                $roleStmt->execute([$newRole, $targetUserId]);
                set_flash('success', "User role successfully changed to " . ucfirst($newRole) . ".");
            }
        } elseif ($action === 'delete_user') {
            // Prevent self-deletion
            if ($targetUserId === $currentAdminId) {
                set_flash('error', 'You cannot delete your own account while logged in.');
            } else {
                // Cascading delete deletes their notes, bookmarks, etc.
                $delStmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $delStmt->execute([$targetUserId]);
                set_flash('success', 'User account and associated notes removed.');
            }
        }
    }
    redirect('/admin/users.php');
}

// Filtering
$search = clean_input($_GET['q'] ?? '');
$roleFilter = clean_input($_GET['role'] ?? '');
$deptFilter = (int)($_GET['dept'] ?? 0);

$sql = "
    SELECT u.*, 
           d.name as dept_name, d.code as dept_code,
           COUNT(n.id) as total_notes_uploaded
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    LEFT JOIN notes n ON u.id = n.user_id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.roll_number LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if (!empty($roleFilter)) {
    $sql .= " AND u.role = ?";
    $params[] = $roleFilter;
}

if ($deptFilter > 0) {
    $sql .= " AND u.department_id = ?";
    $params[] = $deptFilter;
}

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$usersList = $stmt->fetchAll();

$departments = get_all_departments();
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Manage Registered Users</h4>
            <p class="text-muted small mb-0">Control student accounts, assign administrative roles, and view activity</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card-modern bg-white p-3 mb-4">
        <form method="GET" action="<?= BASE_URL ?>/admin/users.php" class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Search by name, email, or student roll number..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="student" <?= $roleFilter === 'student' ? 'selected' : '' ?>>Students Only</option>
                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admins Only</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">Search</button>
                <?php if (!empty($search) || !empty($roleFilter) || $deptFilter): ?>
                    <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline-secondary btn-sm" title="Clear">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="admin-table-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Registered Users (<?= count($usersList) ?>)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Roll Number</th>
                        <th>Role</th>
                        <th>Notes Uploaded</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usersList as $u): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary rounded-circle p-2 text-white" style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-dark small"><?= e($u['name']) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem;"><?= e($u['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border small"><?= e($u['dept_code'] ?: 'N/A') ?></span>
                            </td>
                            <td><?= $u['semester'] ? 'Sem ' . $u['semester'] : '-' ?></td>
                            <td class="small text-muted"><?= e($u['roll_number'] ?: '-') ?></td>
                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-primary-subtle text-primary' ?> small">
                                    <?= ucfirst($u['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary small">
                                    <?= $u['total_notes_uploaded'] ?> Notes
                                </span>
                            </td>
                            <td class="text-muted small"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                            <td class="text-end">
                                <?php if ($u['id'] != $currentAdminId): ?>
                                    <div class="d-inline-flex gap-1">
                                        <!-- Role Toggle Form -->
                                        <form method="POST" action="<?= BASE_URL ?>/admin/users.php" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="change_role">
                                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="new_role" value="<?= $u['role'] === 'admin' ? 'student' : 'admin' ?>">
                                            <button type="submit" class="btn btn-sm btn-light border small" title="<?= $u['role'] === 'admin' ? 'Demote to Student' : 'Promote to Admin' ?>">
                                                <i class="fa-solid <?= $u['role'] === 'admin' ? 'fa-user-minus text-warning' : 'fa-user-shield text-success' ?>"></i>
                                            </button>
                                        </form>

                                        <!-- Delete User Form -->
                                        <form method="POST" action="<?= BASE_URL ?>/admin/users.php" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this user account and their notes?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-light border text-danger small" title="Delete User">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border small">Current Session</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
