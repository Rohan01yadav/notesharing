<?php
/**
 * Database Connection Handler using PDO
 * Supports prepared statements, transactions, and robust error management.
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    /**
     * Get the PDO Database Instance (Singleton)
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // If database doesn't exist yet, check if we should direct to setup.php
                $errorCode = $e->getCode();
                if ($errorCode === 1049 || str_contains($e->getMessage(), 'Unknown database')) {
                    $setupUrl = BASE_URL . '/setup.php';
                    if (!str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'setup.php')) {
                        header("Location: $setupUrl");
                        exit;
                    }
                }

                // In case of any other DB failure, display a clean error page
                die(self::renderErrorUI($e->getMessage()));
            }
        }

        return self::$instance;
    }

    /**
     * Get a raw PDO connection without selecting a database (used by setup wizard)
     */
    public static function getRawConnection(): PDO {
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    /**
     * Render user-friendly DB Connection Error UI
     */
    private static function renderErrorUI(string $error): string {
        $setupUrl = BASE_URL . '/setup.php';
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Connection Notice - Student Notes Sharing</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
            <style>
                body { background-color: #f8fafc; font-family: 'Segoe UI', system-ui, sans-serif; display: flex; align-items: center; min-height: 100vh; }
                .error-card { max-width: 600px; margin: auto; background: white; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05); padding: 2.5rem; border: 1px solid #e2e8f0; }
                .icon-badge { width: 64px; height: 64px; border-radius: 50%; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 1.5rem; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="error-card text-center">
                    <div class="icon-badge mx-auto">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-2">Database Not Initialized</h3>
                    <p class="text-muted mb-4">
                        The application could not connect to the <strong>student_notes_platform</strong> database. 
                        Make sure Apache and MySQL are running in your XAMPP Control Panel.
                    </p>
                    <div class="alert alert-warning text-start small mb-4">
                        <strong>Error details:</strong> {$error}
                    </div>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                        <a href="{$setupUrl}" class="btn btn-primary px-4 py-2" style="background:#4f46e5; border-color:#4f46e5;">
                            <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Run Automated Setup Wizard
                        </a>
                        <a href="javascript:location.reload()" class="btn btn-outline-secondary px-4 py-2">
                            <i class="fa-solid fa-rotate-right me-2"></i> Retry Connection
                        </a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}

/**
 * Global helper function to get PDO instance
 */
function get_db(): PDO {
    return Database::getInstance();
}
