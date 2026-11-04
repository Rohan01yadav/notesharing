<?php
/**
 * Admin Panel Header & Sidebar Component
 * Student Notes Sharing Platform
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

// Enforce strict Administrator authorization
require_admin();

$adminUser = current_user();
$adminPage = basename($_SERVER['PHP_SELF'] ?? '');

// Get pending reports count for sidebar badge
try {
    $db = get_db();
    $pendingReportsCount = (int)$db->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
} catch (PDOException $e) {
    $pendingReportsCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | Admin Portal' : 'Admin Dashboard - NoteShare Hub' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <!-- Admin & Main CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    
    <script>
        window.APP_BASE_URL = "<?= BASE_URL ?>";
    </script>
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">
            <span class="brand-icon-box" style="width:36px; height:36px; font-size:1rem;">
                <i class="fa-solid fa-shield-halved"></i>
            </span>
            <span class="text-white">NoteShareHub Admin</span>
        </div>

        <ul class="admin-nav">
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="admin-nav-link <?= $adminPage === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>/admin/notes.php" class="admin-nav-link <?= $adminPage === 'notes.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Manage Notes</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>/admin/users.php" class="admin-nav-link <?= $adminPage === 'users.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-users"></i>
                    <span>Manage Students</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>/admin/subjects.php" class="admin-nav-link <?= $adminPage === 'subjects.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-book-bookmark"></i>
                    <span>Subjects</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>/admin/departments.php" class="admin-nav-link <?= $adminPage === 'departments.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-building-columns"></i>
                    <span>Departments</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>/admin/reports.php" class="admin-nav-link <?= $adminPage === 'reports.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-flag"></i>
                    <span class="flex-grow-1">Reported Notes</span>
                    <?php if ($pendingReportsCount > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= $pendingReportsCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <div class="admin-sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px;">
                    A
                </div>
                <div class="text-truncate" style="max-width: 140px;">
                    <div class="text-white small fw-bold text-truncate"><?= e($adminUser['name']) ?></div>
                    <div class="text-muted" style="font-size:0.75rem;">Administrator</div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-light btn-sm text-start">
                    <i class="fa-solid fa-arrow-left me-2"></i> View Main Site
                </a>
                <a href="<?= BASE_URL ?>/logout.php" class="btn btn-danger btn-sm text-start">
                    <i class="fa-solid fa-right-from-bracket me-2"></i> Sign Out
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="admin-main">
        <!-- Admin Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none border" id="adminSidebarToggle" type="button">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="fw-bold text-dark d-none d-sm-block">
                    <?= e($pageTitle ?? 'Administrator Control Panel') ?>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="<?= BASE_URL ?>/setup.php" class="btn btn-outline-primary btn-sm d-none d-md-inline-block">
                    <i class="fa-solid fa-database me-1"></i> Database Status
                </a>
                <a href="<?= BASE_URL ?>/explore.php" class="btn btn-light border btn-sm">
                    <i class="fa-solid fa-compass me-1"></i> Explore Notes
                </a>
            </div>
        </header>

        <!-- Flash messages inside admin area -->
        <div class="container-fluid px-4 pt-3">
            <?= render_flash_messages() ?>
        </div>

        <main class="admin-content">
