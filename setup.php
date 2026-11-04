<?php
/**
 * Student Notes Sharing Platform
 * Automated Setup & Database Installation Wizard
 */

require_once __DIR__ . '/includes/config.php';

$message = null;
$error = null;
$installed = false;
$initialCredentials = [];

// Check prerequisites
$phpVersionOk = version_compare(PHP_VERSION, '8.0.0', '>=');
$pdoMysqlOk = extension_loaded('pdo_mysql');
$uploadDirOk = is_dir(NOTES_UPLOAD_PATH) && is_writable(NOTES_UPLOAD_PATH);

// Process installation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    $dbHost = trim($_POST['db_host'] ?? DB_HOST);
    $dbUser = trim($_POST['db_user'] ?? DB_USER);
    $dbPass = $_POST['db_pass'] ?? '';
    $dbName = trim($_POST['db_name'] ?? DB_NAME);

    try {
        // Step 1: Connect to MySQL server without database
        $pdo = new PDO("mysql:host={$dbHost};charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Step 2: Read SQL file
        $sqlPath = __DIR__ . '/database.sql';
        if (!file_exists($sqlPath)) {
            throw new Exception("database.sql file was not found in project root.");
        }

        $sqlContent = file_get_contents($sqlPath);

        // Step 3: Execute SQL queries
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `{$dbName}`;");

        // Split by semicolon (preserving seed data)
        // Execute the entire dump
        $pdo->exec($sqlContent);

        $demoAccounts = [
            ['email' => 'admin@notesharing.edu', 'role' => 'admin'],
            ['email' => 'rahul.cse@notesharing.edu', 'role' => 'student'],
            ['email' => 'priya.it@notesharing.edu', 'role' => 'student'],
            ['email' => 'arjun.ece@notesharing.edu', 'role' => 'student'],
        ];
        $updatePassword = $pdo->prepare('UPDATE users SET password = ? WHERE email = ? AND role = ?');
        $pdo->beginTransaction();
        try {
            foreach ($demoAccounts as $account) {
                $password = bin2hex(random_bytes(16));
                $updatePassword->execute([
                    password_hash($password, PASSWORD_DEFAULT),
                    $account['email'],
                    $account['role'],
                ]);
                if ($updatePassword->rowCount() !== 1) {
                    throw new RuntimeException('Unable to initialize a demo account.');
                }
                $initialCredentials[] = ['email' => $account['email'], 'password' => $password];
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        $installed = true;
        $message = "Database '{$dbName}' installed and populated with sample data successfully. Save the generated demo account passwords below; they are shown only on this setup response.";
    } catch (Exception $e) {
        $initialCredentials = [];
        $error = "Installation failed: " . $e->getMessage();
    }
} else {
    // Check if database already exists
    try {
        $testPdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $tableCount = $testPdo->query("SHOW TABLES")->rowCount();
        if ($tableCount >= 5) {
            $installed = true;
            $message = "The database is already configured and contains {$tableCount} tables.";
        }
    } catch (PDOException $e) {
        // Database not created yet, normal for first run
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup Wizard - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #3978b8;
            --primary-hover: #285d91;
            --bg-body: #ffffff;
        }
        body {
            background-color: var(--bg-body);
            font-family: Arial, Helvetica, system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .setup-card {
            background: #ffffff;
            border-radius: 7px;
            box-shadow: none;
            border: 1px solid #dce5ed;
            width: 100%;
            max-width: 680px;
            overflow: hidden;
        }
        .setup-header {
            background: #eaf3fb;
            color: #263746;
            padding: 1.75rem 2rem;
            text-align: center;
        }
        .check-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .check-pass { background: #e8f5ed; color: #277044; }
        .check-fail { background: #fbeaea; color: #a33131; }
        .demo-box {
            background: #f4f8fc;
            border-radius: 6px;
            padding: 1rem 1.25rem;
            border: 1px solid #dce5ed;
        }
        .shadow-sm {
            box-shadow: none !important;
        }
    </style>
</head>
<body>

<div class="setup-card">
    <div class="setup-header">
        <div class="mb-2">
            <i class="fa-solid fa-graduation-cap fa-2x"></i>
        </div>
        <h3 class="fw-bold mb-1"><?= APP_NAME ?></h3>
        <p class="mb-0 opacity-75 small">One-Click Database Setup & Configuration Wizard</p>
    </div>

    <div class="p-4 p-md-5">
        <?php if ($message): ?>
            <div class="alert alert-success d-flex align-items-center mb-4">
                <i class="fa-solid fa-circle-check fs-4 me-3"></i>
                <div><?= e($message) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center mb-4">
                <i class="fa-solid fa-triangle-exclamation fs-4 me-3"></i>
                <div><?= e($error) ?></div>
            </div>
        <?php endif; ?>

        <!-- Environment Checks -->
        <h6 class="text-uppercase text-muted fw-bold small mb-3">System Environment Check</h6>
        <div class="list-group mb-4 shadow-sm">
            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div>
                    <span class="fw-semibold">PHP Version:</span>
                    <span class="text-muted small ms-2">(PHP 8.0+ recommended)</span>
                </div>
                <span class="check-badge <?= $phpVersionOk ? 'check-pass' : 'check-fail' ?>">
                    <i class="fa-solid <?= $phpVersionOk ? 'fa-check' : 'fa-xmark' ?>"></i>
                    <?= PHP_VERSION ?>
                </span>
            </div>

            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div>
                    <span class="fw-semibold">PDO MySQL Driver:</span>
                    <span class="text-muted small ms-2">(Database connector)</span>
                </div>
                <span class="check-badge <?= $pdoMysqlOk ? 'check-pass' : 'check-fail' ?>">
                    <i class="fa-solid <?= $pdoMysqlOk ? 'fa-check' : 'fa-xmark' ?>"></i>
                    <?= $pdoMysqlOk ? 'Enabled' : 'Missing' ?>
                </span>
            </div>

            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div>
                    <span class="fw-semibold">Uploads Directory:</span>
                    <span class="text-muted small ms-2">uploads/notes/</span>
                </div>
                <span class="check-badge <?= $uploadDirOk ? 'check-pass' : 'check-fail' ?>">
                    <i class="fa-solid <?= $uploadDirOk ? 'fa-check' : 'fa-xmark' ?>"></i>
                    <?= $uploadDirOk ? 'Writable' : 'Not Writable' ?>
                </span>
            </div>
        </div>

        <?php if ($installed): ?>
            <div class="demo-box mb-4">
                <?php if ($initialCredentials): ?>
                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-key text-primary me-2"></i>Generated Demo Account Passwords</h6>
                    <p class="small text-muted">Save these unique passwords now. They are displayed only in this setup response.</p>
                    <div class="row g-2 small">
                        <?php foreach ($initialCredentials as $credential): ?>
                            <div class="col-sm-6">
                                <div class="p-2 bg-white rounded border">
                                    Email: <code><?= htmlspecialchars($credential['email'], ENT_QUOTES, 'UTF-8') ?></code><br>
                                    Password: <code><?= htmlspecialchars($credential['password'], ENT_QUOTES, 'UTF-8') ?></code>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-key text-primary me-2"></i>Demo Accounts</h6>
                    <p class="small mb-0">Passwords are generated during initial setup and shown only once. Existing databases retain their current passwords; change them before deployment.</p>
                <?php endif; ?>
            </div>

            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary btn-lg shadow-sm" style="background:var(--primary); border-color:var(--primary);">
                    <i class="fa-solid fa-arrow-right me-2"></i> Launch Application
                </a>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Go directly to Login
                </a>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <input type="hidden" name="action" value="install">
                
                <h6 class="text-uppercase text-muted fw-bold small mb-3">Database Connection Parameters</h6>
                
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Database Host</label>
                        <input type="text" name="db_host" class="form-control" value="<?= e(DB_HOST) ?>" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Database Name</label>
                        <input type="text" name="db_name" class="form-control" value="<?= e(DB_NAME) ?>" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">User (default: root)</label>
                        <input type="text" name="db_user" class="form-control" value="<?= e(DB_USER) ?>" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Password (default: blank)</label>
                        <input type="password" name="db_pass" class="form-control" placeholder="Leave empty for XAMPP default">
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 small text-muted mb-4 border">
                    <i class="fa-solid fa-circle-info text-primary me-2"></i>
                    Clicking <strong>Install & Seed Database</strong> will execute <code>database.sql</code>, creating the tables and inserting 6 departments, 22 subjects, 4 demo users, and 8 sample verified notes.
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm" style="background:var(--primary); border-color:var(--primary);">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Install & Seed Database
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
