<?php
/**
 * About the Project Page
 * Student Notes Sharing Platform
 * BE CSE Web Technology Laboratory
 */

$pageTitle = "About Platform & Architecture";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <!-- Header Hero Banner -->
    <div class="card-modern p-4 p-md-5 mb-4 bg-white border-0 shadow-sm" style="border-radius: 20px;">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 mb-2">Web Technology Laboratory Project</span>
                <h2 class="fw-bold text-dark mb-2"><?= APP_NAME ?></h2>
                <p class="lead text-primary fw-semibold mb-3">"<?= APP_TAGLINE ?>"</p>
                <p class="text-secondary mb-0">
                    A comprehensive, full-stack academic resource repository engineered to simplify knowledge dissemination across university engineering departments. Built with a focus on usability, robust security principles, and normalized database architecture.
                </p>
            </div>
            <div class="col-lg-4 text-center">
                <div class="p-4 bg-light rounded-4 border">
                    <div class="brand-icon-box mx-auto mb-3" style="width:64px; height:64px; font-size:1.75rem;">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <h5 class="fw-bold mb-1">BE CSE Laboratory</h5>
                    <div class="text-muted small">Department of Computer Science</div>
                    <span class="badge bg-primary mt-2">Evaluation Ready</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Technology Stack Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card-modern p-4 bg-white h-100">
                <div class="rounded-3 p-3 bg-primary-subtle text-primary mb-3 d-inline-block" style="font-size:1.5rem;">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <h5 class="fw-bold mb-2">Frontend Stack</h5>
                <p class="text-muted small mb-3">Minimalist, modern SaaS interface designed without heavy JavaScript framework bloat.</p>
                <ul class="list-unstyled small mb-0 text-secondary">
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Semantic HTML5 Markup</li>
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Custom CSS3 with CSS Variables</li>
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Vanilla JavaScript (ES6+ AJAX)</li>
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Bootstrap 5 Responsive Grid</li>
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Chart.js Visualizations</li>
                </ul>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-modern p-4 bg-white h-100">
                <div class="rounded-3 p-3 bg-success-subtle text-success mb-3 d-inline-block" style="font-size:1.5rem;">
                    <i class="fa-brands fa-php"></i>
                </div>
                <h5 class="fw-bold mb-2">Backend Architecture</h5>
                <p class="text-muted small mb-3">Pure PHP 8+ object-oriented database abstraction and secure session authentication.</p>
                <ul class="list-unstyled small mb-0 text-secondary">
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>PHP Data Objects (PDO)</li>
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Strict Prepared Statements</li>
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Bcrypt Password Hashing</li>
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Session Fixation Regeneration</li>
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Role-Based Access Control (RBAC)</li>
                </ul>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-modern p-4 bg-white h-100">
                <div class="rounded-3 p-3 bg-info-subtle text-info mb-3 d-inline-block" style="font-size:1.5rem;">
                    <i class="fa-solid fa-database"></i>
                </div>
                <h5 class="fw-bold mb-2">Database & Security</h5>
                <p class="text-muted small mb-3">Normalized MySQL relational schema with cascading foreign keys and indexes.</p>
                <ul class="list-unstyled small mb-0 text-secondary">
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>7 Normalized Relational Tables</li>
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>MIME Type & Extension Checks</li>
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>CSRF Protection Tokens</li>
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Path Traversal Prevention</li>
                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Contextual XSS Output Escaping</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Laboratory Review Q&A Section -->
    <div class="card-modern bg-white p-4 p-md-5 mb-4">
        <h4 class="fw-bold text-dark mb-4">
            <i class="fa-solid fa-circle-question text-primary me-2"></i> Web Technology Laboratory Review Notes
        </h4>

        <div class="accordion" id="labAccordion">
            <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                        1. How does the application prevent SQL Injection?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#labAccordion">
                    <div class="accordion-body text-secondary small">
                        All database interactions throughout the platform use PHP Data Objects (PDO) with parameterized prepared statements. User inputs are bound as values (`?` placeholders) and never concatenated directly into SQL queries, ensuring the MySQL query parser treats them strictly as data rather than executable statements.
                    </div>
                </div>
            </div>

            <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                        2. How is file upload security handled for student PDF documents?
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#labAccordion">
                    <div class="accordion-body text-secondary small">
                        File uploads undergo multi-layered server-side verification:
                        <ul>
                            <li><strong>Extension checking:</strong> Only <code>.pdf</code> is permitted.</li>
                            <li><strong>MIME type detection:</strong> Validated using <code>finfo_file()</code> to ensure genuine <code>application/pdf</code> MIME type rather than spoofed extensions.</li>
                            <li><strong>File size enforcement:</strong> Capped at 25 MB.</li>
                            <li><strong>Unique file renaming:</strong> Uploaded files are renamed using <code>uniqid()</code> and random cryptographic bytes to prevent overwrites or executable path exploitation.</li>
                            <li><strong>Directory security:</strong> The uploads folder includes an <code>.htaccess</code> rule prohibiting script execution (PHP, Python, CGI, etc.).</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                        3. What mechanism prevents Cross-Site Request Forgery (CSRF)?
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#labAccordion">
                    <div class="accordion-body text-secondary small">
                        A cryptographically secure pseudorandom token is generated with <code>bin2hex(random_bytes(32))</code> and stored in the user's session. Every state-changing form (login, registration, upload, edit, delete, report) includes this token in a hidden field, which is validated server-side using <code>hash_equals()</code> prior to processing.
                    </div>
                </div>
            </div>

            <div class="accordion-item border rounded-3 overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                        4. What are the database relationships implemented?
                    </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#labAccordion">
                    <div class="accordion-body text-secondary small">
                        The schema uses 7 normalized InnoDB tables:
                        <ul>
                            <li><strong>departments:</strong> One-to-many relationship with <code>subjects</code> and <code>notes</code>.</li>
                            <li><strong>subjects:</strong> Belongs to a department and semester; one-to-many with <code>notes</code>.</li>
                            <li><strong>users:</strong> Can be students or admins; one-to-many with <code>notes</code>, <code>bookmarks</code>, and <code>downloads</code>.</li>
                            <li><strong>notes:</strong> Central entity referencing <code>user_id</code>, <code>subject_id</code>, and <code>department_id</code> with foreign keys.</li>
                            <li><strong>bookmarks:</strong> Composite uniqueness on <code>(user_id, note_id)</code> to prevent duplicate saves.</li>
                            <li><strong>downloads & reports:</strong> Audit logs for download analytics and content moderation.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
