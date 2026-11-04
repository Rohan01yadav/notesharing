<?php
/**
 * User Login Page
 * Student Notes Sharing Platform
 */

$pageTitle = "Login";
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
require_guest();

$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification
    if (!verify_csrf()) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $email = clean_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please enter both your email and password.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                $db = get_db();
                $stmt = $db->prepare("
                    SELECT u.*, d.name as dept_name, d.code as dept_code 
                    FROM users u
                    LEFT JOIN departments d ON u.department_id = d.id
                    WHERE u.email = ?
                    LIMIT 1
                ");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Password is correct, start session
                    login_user($user);
                    set_flash('success', "Welcome back, " . $user['name'] . "!");

                    // Role-based redirection
                    if ($user['role'] === 'admin') {
                        redirect('/admin/dashboard.php');
                    } else {
                        $redirectTo = $_SESSION['redirect_after_login'] ?? '/dashboard.php';
                        unset($_SESSION['redirect_after_login']);
                        redirect($redirectTo);
                    }
                } else {
                    $error = 'Invalid email or password combination.';
                }
            } catch (PDOException $e) {
                $error = 'A database error occurred. Please verify database configuration in setup.php.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="form-card">
                <div class="text-center mb-4">
                    <div class="brand-icon-box mx-auto mb-3" style="width:50px; height:50px;">
                        <i class="fa-solid fa-graduation-cap fs-4"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Welcome Back</h3>
                    <p class="text-muted small">Sign in to your NoteShare Hub account</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4 small" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
                        <div><?= e($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/login.php" id="loginForm">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label">College Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" class="form-control border-start-0" id="email" name="email" value="<?= e($email) ?>" placeholder="student@notesharing.edu" required autocomplete="email">
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label mb-0">Password</label>
                            <span class="text-muted small" style="font-size:0.8rem;">Minimum 6 characters</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> Sign In
                        </button>
                    </div>

                    <div class="text-center small text-muted">
                        Don't have an account yet? 
                        <a href="<?= BASE_URL ?>/register.php" class="fw-semibold text-primary">Create an account</a>
                    </div>
                </form>

                <!-- Demo account email quick selector -->
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.725rem;">
                            <i class="fa-solid fa-wand-magic-sparkles text-primary me-1"></i> Demo Accounts
                        </span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light border btn-sm flex-fill small" onclick="fillEmail('rahul.cse@notesharing.edu')">
                            <i class="fa-solid fa-user-graduate me-1 text-primary"></i> Student
                        </button>
                        <button type="button" class="btn btn-light border btn-sm flex-fill small" onclick="fillEmail('admin@notesharing.edu')">
                            <i class="fa-solid fa-user-shield me-1 text-danger"></i> Admin
                        </button>
                    </div>
                    <p class="text-muted small mt-2 mb-0">Enter the unique password generated during setup.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillEmail(email) {
    document.getElementById('email').value = email;
    showToast('Demo account selected', 'Enter the password generated during setup.', 'info');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
