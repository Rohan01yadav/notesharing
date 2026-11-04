<?php
/**
 * Public Landing Page
 * Student Notes Sharing Platform
 */

$pageTitle = "Home";
require_once __DIR__ . '/includes/header.php';

// Fetch dynamic data for the landing page
$stats = get_platform_stats();
$popularSubjects = get_popular_subjects(6);
$recentNotes = get_recent_notes(6);
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 text-center text-lg-start">
                <div class="hero-pill">
                    <i class="fa-solid fa-sparkles text-primary"></i>
                    <span>Welcome to NoteShareHub</span>
                </div>
                <h1 class="hero-title mb-3">
                    Share and Access Study Notes
                </h1>
                <p class="hero-subtitle mb-4 text-secondary">
                    Find useful notes shared by fellow students, or upload your own study materials to help your classmates.
                </p>

                <!-- Hero Live Search Box -->
                <form action="<?= BASE_URL ?>/explore.php" method="GET" class="mb-4">
                    <div class="hero-search-wrapper">
                        <i class="fa-solid fa-magnifying-glass text-muted ms-2 fs-5"></i>
                        <input type="text" name="q" placeholder="Search notes by title, subject, or topic..." required>
                        <button type="submit" class="btn btn-primary">Search Notes</button>
                    </div>
                </form>

                <!-- Action CTAs -->
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                    <a href="<?= BASE_URL ?>/explore.php" class="btn btn-primary btn-lg shadow-sm">
                        <i class="fa-solid fa-compass me-2"></i> Explore Notes
                    </a>
                    <a href="<?= is_logged_in() ? BASE_URL . '/upload-note.php' : BASE_URL . '/register.php' ?>" class="btn btn-outline-primary btn-lg">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload Notes
                    </a>
                </div>

                <div class="d-flex align-items-center justify-content-center justify-content-lg-start gap-4 mt-4 pt-2 text-muted small">
                    <div class="d-flex align-items-center gap-1">
                        <i class="fa-solid fa-check text-success"></i> Free & Open Access
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <i class="fa-solid fa-check text-success"></i> Standard PDF Format
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <i class="fa-solid fa-check text-success"></i> Peer Reviewed
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <!-- Hero Visual Showcase Card -->
                <div class="card-modern p-4 shadow-lg border-0 bg-white" style="border-radius: 20px;">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger rounded-circle p-2"></span>
                            <span class="badge bg-warning rounded-circle p-2"></span>
                            <span class="badge bg-success rounded-circle p-2"></span>
                            <span class="fw-semibold small text-muted ms-2">Featured Note Preview</span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary fw-bold">Sem 4 • CSE</span>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="pdf-icon-badge">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Database Management Systems</h6>
                            <p class="text-muted small mb-0">Relational Algebra, SQL, Normal Forms & ACID properties.</p>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3 small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subject Code:</span>
                            <span class="fw-semibold">CS8492</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Uploaded By:</span>
                            <span class="fw-semibold text-primary">Rahul Sharma (CSE)</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">File Size:</span>
                            <span class="fw-semibold">3.1 MB (PDF)</span>
                        </div>
                    </div>

                    <div class="d-grid">
                        <a href="<?= BASE_URL ?>/explore.php" class="btn btn-outline-primary btn-sm py-2">
                            <i class="fa-solid fa-eye me-1"></i> Browse 100+ Free Study Guides
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Platform Statistics Banner -->
<section class="py-5">
    <div class="container">
        <div class="stats-banner">
            <div class="row g-4 text-center">
                <div class="col-6 col-md-3">
                    <div class="stat-counter-number mb-2"><?= number_format($stats['notes']) ?></div>
                    <div class="text-light opacity-75 fw-semibold small text-uppercase">Academic Notes</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-counter-number mb-2"><?= number_format($stats['students']) ?></div>
                    <div class="text-light opacity-75 fw-semibold small text-uppercase">Registered Students</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-counter-number mb-2"><?= number_format($stats['downloads']) ?></div>
                    <div class="text-light opacity-75 fw-semibold small text-uppercase">Total Downloads</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-counter-number mb-2"><?= number_format($stats['subjects']) ?></div>
                    <div class="text-light opacity-75 fw-semibold small text-uppercase">Active Subjects</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="text-center max-w-xl mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 mb-2">Simple & Efficient</span>
            <h2 class="fw-bold">How NoteShare Hub Works</h2>
            <p class="text-muted">Collaborative academic resource sharing in four straightforward steps.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <div class="card-modern p-4 text-center">
                    <div class="icon-badge mx-auto mb-3" style="width:60px; height:60px; border-radius:14px; background:var(--primary-subtle); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:1.5rem;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h5 class="fw-bold mb-2">1. Register</h5>
                    <p class="text-muted small mb-0">Sign up with your college email, department, and current semester.</p>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card-modern p-4 text-center">
                    <div class="icon-badge mx-auto mb-3" style="width:60px; height:60px; border-radius:14px; background:#e0f2fe; color:#0284c7; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">
                        <i class="fa-solid fa-filter"></i>
                    </div>
                    <h5 class="fw-bold mb-2">2. Search & Filter</h5>
                    <p class="text-muted small mb-0">Discover lecture notes by subject code, semester, or academic department.</p>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card-modern p-4 text-center">
                    <div class="icon-badge mx-auto mb-3" style="width:60px; height:60px; border-radius:14px; background:#dcfce7; color:#16a34a; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">
                        <i class="fa-solid fa-file-arrow-down"></i>
                    </div>
                    <h5 class="fw-bold mb-2">3. Download & Learn</h5>
                    <p class="text-muted small mb-0">Preview documents in your browser or download PDFs for offline revision.</p>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card-modern p-4 text-center">
                    <div class="icon-badge mx-auto mb-3" style="width:60px; height:60px; border-radius:14px; background:#fef3c7; color:#d97706; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <h5 class="fw-bold mb-2">4. Share & Earn</h5>
                    <p class="text-muted small mb-0">Upload your own handwritten or typed notes to assist juniors and peers.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recently Uploaded Notes Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 mb-2">Latest Uploads</span>
                <h2 class="fw-bold mb-1">Recently Added Study Notes</h2>
                <p class="text-muted mb-0 small">Fresh academic materials uploaded by students across departments.</p>
            </div>
            <a href="<?= BASE_URL ?>/explore.php" class="btn btn-outline-primary btn-sm px-3 py-2">
                View All Notes <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php if (empty($recentNotes)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                    <h5>No notes uploaded yet.</h5>
                    <p class="text-muted">Be the first student to upload revision materials!</p>
                    <a href="<?= BASE_URL ?>/upload-note.php" class="btn btn-primary mt-2">Upload Note</a>
                </div>
            <?php else: ?>
                <?php foreach ($recentNotes as $note): 
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
                                        <button type="button" class="bookmark-btn btn-bookmark-toggle <?= $isBookmarked ? 'active' : '' ?>" data-note-id="<?= $note['id'] ?>" title="Save Bookmark">
                                            <i class="fa-<?= $isBookmarked ? 'solid' : 'regular' ?> fa-bookmark"></i>
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
                                    <div class="d-flex align-items-center gap-3">
                                        <span><i class="fa-solid fa-download me-1 text-primary"></i> <?= $note['download_count'] ?></span>
                                        <span><?= time_ago($note['created_at']) ?></span>
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
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Popular Subjects Section -->
<section class="py-5 bg-light border-top border-bottom">
    <div class="container">
        <div class="text-center max-w-xl mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 mb-2">Trending Courses</span>
            <h2 class="fw-bold">Popular Academic Subjects</h2>
            <p class="text-muted">Quickly access verified notes for the most frequented subjects.</p>
        </div>

        <div class="row g-3">
            <?php foreach ($popularSubjects as $subj): ?>
                <div class="col-md-4 col-sm-6">
                    <a href="<?= BASE_URL ?>/explore.php?subject=<?= $subj['id'] ?>" class="text-decoration-none">
                        <div class="card-modern p-3 d-flex align-items-center justify-content-between bg-white">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 p-2 bg-primary-subtle text-primary" style="width:42px; height:42px; display:flex; align-items:center; justify-content:center;">
                                    <i class="fa-solid fa-book-bookmark fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small text-truncate" style="max-width:180px;"><?= e($subj['name']) ?></div>
                                    <div class="text-muted small"><?= e($subj['code']) ?> • <?= e($subj['dept_code']) ?> Sem <?= $subj['semester'] ?></div>
                                </div>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill"><?= $subj['note_count'] ?> notes</span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center max-w-xl mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 mb-2">Student Feedback</span>
            <h2 class="fw-bold">Loved by Engineering Students</h2>
            <p class="text-muted">See how NoteShare Hub helps peers excel in their university examinations.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card-modern p-4">
                    <div class="d-flex align-items-center gap-1 text-warning mb-3">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p class="text-secondary small mb-3">
                        "The comprehensive DBMS revision notes saved me during mid-term preparations. Having properly structured handwritten solutions with diagrams made all the difference."
                    </p>
                    <div class="d-flex align-items-center gap-3 pt-2 border-top">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:38px; height:38px;">
                            R
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">Rahul Sharma</div>
                            <div class="text-muted" style="font-size:0.75rem;">CSE 6th Semester • Anna University</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-modern p-4">
                    <div class="d-flex align-items-center gap-1 text-warning mb-3">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p class="text-secondary small mb-3">
                        "Uploading my Web Tech lab manuals and DAA formula sheets was super easy. It feels great to see hundreds of classmates downloading and benefiting from my materials."
                    </p>
                    <div class="d-flex align-items-center gap-3 pt-2 border-top">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width:38px; height:38px;">
                            P
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">Priya Nair</div>
                            <div class="text-muted" style="font-size:0.75rem;">IT 6th Semester • Class Representative</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-modern p-4">
                    <div class="d-flex align-items-center gap-1 text-warning mb-3">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p class="text-secondary small mb-3">
                        "The ability to preview PDFs right in the browser before downloading and filtering specifically by semester and branch makes this platform ten times better than messy Google Drive links."
                    </p>
                    <div class="d-flex align-items-center gap-3 pt-2 border-top">
                        <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center fw-bold" style="width:38px; height:38px;">
                            A
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">Arjun Verma</div>
                            <div class="text-muted" style="font-size:0.75rem;">ECE 5th Semester • Peer Tutor</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 bg-white border-top">
    <div class="container text-center py-4">
        <h2 class="fw-bold mb-3">Ready to Accelerate Your Academic Journey?</h2>
        <p class="text-muted max-w-lg mx-auto mb-4">
            Join hundreds of engineering students sharing lecture notes, lab guides, and exam preparation sheets today.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-lg shadow-sm">
                <i class="fa-solid fa-user-plus me-2"></i> Join NoteShare Hub
            </a>
            <a href="<?= BASE_URL ?>/explore.php" class="btn btn-outline-primary btn-lg">
                <i class="fa-solid fa-magnifying-glass me-2"></i> Explore All Notes
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
