<?php
/**
 * Student Registration Page
 * Student Notes Sharing Platform
 */

$pageTitle = "Student Registration";
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
require_guest();

$departments = get_all_departments();
$error = null;

// Form values sticky state
$name = '';
$email = '';
$department_id = '';
$semester = '';
$roll_number = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $name          = clean_input($_POST['name'] ?? '');
        $email         = clean_input($_POST['email'] ?? '');
        $password      = $_POST['password'] ?? '';
        $password_conf = $_POST['password_confirm'] ?? '';
        $department_id = (int)($_POST['department_id'] ?? 0);
        $semester      = (int)($_POST['semester'] ?? 0);
        $roll_number   = clean_input($_POST['roll_number'] ?? '');

        // Validation logic
        if (empty($name) || empty($email) || empty($password) || empty($password_conf) || empty($department_id) || empty($semester)) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please provide a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $password_conf) {
            $error = 'The password confirmation does not match.';
        } elseif ($semester < 1 || $semester > 8) {
            $error = 'Semester must be between 1 and 8.';
        } else {
            try {
                $db = get_db();

                // Check for duplicate email
                $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $checkStmt->execute([$email]);
                if ($checkStmt->fetch()) {
                    $error = 'An account with this email address already exists. Please log in.';
                } else {
                    // Hash password securely with native PHP password_hash
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // Insert student
                    $insertStmt = $db->prepare("
                        INSERT INTO users (name, email, password, role, department_id, semester, roll_number)
                        VALUES (?, ?, ?, 'student', ?, ?, ?)
                    ");
                    $insertStmt->execute([
                        $name,
                        $email,
                        $hashedPassword,
                        $department_id,
                        $semester,
                        $roll_number
                    ]);

                    $newUserId = (int)$db->lastInsertId();

                    // Fetch complete user record to establish session
                    $userStmt = $db->prepare("
                        SELECT u.*, d.name as dept_name, d.code as dept_code 
                        FROM users u
                        LEFT JOIN departments d ON u.department_id = d.id
                        WHERE u.id = ?
                    ");
                    $userStmt->execute([$newUserId]);
                    $newUser = $userStmt->fetch();

                    // Automatically log in
                    login_user($newUser);
                    set_flash('success', "Welcome to NoteShare Hub, {$name}! Your student account is now active.");
                    redirect('/dashboard.php');
                }
            } catch (PDOException $e) {
                $error = 'Registration failed due to a database error. Please verify your database configuration.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="form-card">
                <div class="text-center mb-4">
                    <div class="brand-icon-box mx-auto mb-3" style="width:50px; height:50px;">
                        <i class="fa-solid fa-user-plus fs-4"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Create Student Account</h3>
                    <p class="text-muted small">Join your college peer network to discover and share study materials</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4 small" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
                        <div><?= e($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/register.php">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= e($name) ?>" placeholder="e.g. Rahul Sharma" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">College Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" placeholder="e.g. student@notesharing.edu" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select" id="department_id" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= $department_id == $dept['id'] ? 'selected' : '' ?>>
                                        <?= e($dept['name']) ?> (<?= e($dept['code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label for="semester" class="form-label">Current Semester <span class="text-danger">*</span></label>
                            <select class="form-select" id="semester" name="semester" required>
                                <option value="">Select Semester</option>
                                <?php for ($s = 1; $s <= 8; $s++): ?>
                                    <option value="<?= $s ?>" <?= $semester == $s ? 'selected' : '' ?>>
                                        Semester <?= $s ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="roll_number" class="form-label">Roll Number / Student ID <span class="text-muted small">(Optional)</span></label>
                        <input type="text" class="form-control" id="roll_number" name="roll_number" value="<?= e($roll_number) ?>" placeholder="e.g. 21CS1042">
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Min. 6 chars" required minlength="6">
                        </div>
                        <div class="col-sm-6">
                            <label for="password_confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Repeat password" required minlength="6">
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Register & Access Dashboard
                        </button>
                    </div>

                    <div class="text-center small text-muted">
                        Already have an account? 
                        <a href="<?= BASE_URL ?>/login.php" class="fw-semibold text-primary">Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
