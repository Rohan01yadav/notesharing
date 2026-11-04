<?php
/**
 * Global Header Component
 * Student Notes Sharing Platform
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' . APP_SHORT_NAME : APP_NAME . ' - ' . APP_TAGLINE ?></title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom Application CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    
    <!-- Pass Base URL to Client Scripts -->
    <script>
        window.APP_BASE_URL = "<?= BASE_URL ?>";
    </script>
</head>
<body class="<?= $currentPage === 'index.php' ? 'home-page' : '' ?>">

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            <span class="brand-icon-box">
                <i class="fa-solid fa-graduation-cap"></i>
            </span>
            <span>
                <span class="fw-bold text-primary">NoteShare</span><span class="text-dark">Hub</span>
            </span>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars fs-4 text-dark"></i>
        </button>

        <!-- Navbar Menu -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">
                        <i class="fa-solid fa-house-chimney me-1 small"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'explore.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/explore.php">
                        <i class="fa-solid fa-compass me-1 small"></i> Explore Notes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'about.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/about.php">
                        <i class="fa-solid fa-circle-info me-1 small"></i> About
                    </a>
                </li>
            </ul>

            <!-- Right-Hand Side (Auth Buttons / User Dropdown) -->
            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <?php if (is_logged_in()): ?>
                    <a href="<?= BASE_URL ?>/upload-note.php" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Upload Note</span>
                    </a>

                    <div class="dropdown">
                        <button class="btn btn-light border dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="badge bg-primary rounded-circle p-2 text-white" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center;">
                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                            </span>
                            <span class="fw-semibold small d-none d-sm-inline"><?= e($user['name']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2 p-2" aria-labelledby="userMenuDropdown" style="min-width: 220px;">
                            <li class="px-3 py-2 border-bottom mb-1">
                                <div class="fw-bold text-dark text-truncate"><?= e($user['name']) ?></div>
                                <div class="text-muted small text-truncate"><?= e($user['email']) ?></div>
                                <span class="badge <?= is_admin() ? 'bg-danger' : 'bg-primary' ?> text-uppercase mt-1" style="font-size: 0.65rem;">
                                    <?= is_admin() ? 'Administrator' : 'Student (' . e($user['department'] ?: 'General') . ')' ?>
                                </span>
                            </li>
                            
                            <?php if (is_admin()): ?>
                                <li>
                                    <a class="dropdown-item py-2 rounded-2 text-danger fw-semibold" href="<?= BASE_URL ?>/admin/dashboard.php">
                                        <i class="fa-solid fa-shield-halved me-2"></i> Admin Panel
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                            <?php endif; ?>

                            <li>
                                <a class="dropdown-item py-2 rounded-2" href="<?= BASE_URL ?>/dashboard.php">
                                    <i class="fa-solid fa-gauge-high me-2 text-muted"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 rounded-2" href="<?= BASE_URL ?>/my-uploads.php">
                                    <i class="fa-solid fa-folder-open me-2 text-muted"></i> My Uploads
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 rounded-2" href="<?= BASE_URL ?>/bookmarks.php">
                                    <i class="fa-solid fa-bookmark me-2 text-muted"></i> Saved Bookmarks
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 rounded-2 text-danger" href="<?= BASE_URL ?>/logout.php">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline-primary">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Login
                    </a>
                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary">
                        <i class="fa-solid fa-user-plus me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Global Flash Messages Notification Container -->
<div class="container mt-3">
    <?= render_flash_messages() ?>
</div>

<main>
