<?php
// app/Controllers/Install/InstallController.php

namespace App\Controllers\Install;

use App\Core\Database;
use App\Helpers\Response;
use Exception;

class InstallController
{
    public function index()
    {
        // Check if already installed
        if ($this->isAlreadyInstalled()) {
            Response::redirect('/admin');
        }

        $step = $_GET['step'] ?? 1;
        
        switch ($step) {
            case 1:
                $this->showRequirementsCheck();
                break;
            case 2:
                $this->showDatabaseSetup();
                break;
            case 3:
                $this->showAdminSetup();
                break;
            case 4:
                $this->showCompletion();
                break;
            default:
                $this->showRequirementsCheck();
        }
    }

    public function process()
    {
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        $step = $_POST['step'] ?? 1;
        
        // Debug: Log the received data
        file_put_contents(__DIR__ . '/../../../storage/logs/install_debug.log', 
            date('Y-m-d H:i:s') . " - Step: $step, POST: " . print_r($_POST, true) . "\n", 
            FILE_APPEND
        );
        
        try {
            switch ($step) {
                case 1:
                    $this->processRequirements();
                    break;
                case 2:
                    $this->processDatabase();
                    break;
                case 3:
                    $this->processAdmin();
                    break;
                default:
                    Response::error('Invalid installation step');
            }
        } catch (Exception $e) {
            // Debug: Log errors
            file_put_contents(__DIR__ . '/../../../storage/logs/install_debug.log', 
                date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", 
                FILE_APPEND
            );
            Response::error($e->getMessage());
        }
    }

    public function testConnection()
    {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            Response::error('Invalid JSON data');
        }
        
        $host = $input['host'] ?? '';
        $port = $input['port'] ?? 3306;
        $database = $input['database'] ?? '';
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        // Validate required fields
        if (empty($host) || empty($username)) {
            Response::error('Host and username are required');
        }
        
        try {
            // Test connection without database first
            $result = Database::testConnection($host, $username, $password, null, $port);
            
            if ($result['success']) {
                // If database name is provided, test with database
                if (!empty($database)) {
                    $dbResult = Database::testConnection($host, $username, $password, $database, $port);
                    if ($dbResult['success']) {
                        Response::success('Database connection successful');
                    } else {
                        // Database doesn't exist, but connection works
                        Response::success('Connection successful. Database will be created during installation.');
                    }
                } else {
                    Response::success('Connection successful');
                }
            } else {
                Response::error('Connection failed: ' . $result['message']);
            }
        } catch (Exception $e) {
            Response::error('Connection failed: ' . $e->getMessage());
        }
    }

    private function isAlreadyInstalled()
    {
        return file_exists(__DIR__ . '/../../../.env') && 
               file_exists(__DIR__ . '/../../../storage/installed.lock');
    }

    private function showRequirementsCheck()
    {
        $requirements = $this->checkRequirements();
        $canProceed = !in_array(false, array_column($requirements, 'status'));
        
        echo $this->renderTemplate('requirements', [
            'requirements' => $requirements,
            'canProceed' => $canProceed
        ]);
    }

    private function showDatabaseSetup()
    {
        echo $this->renderTemplate('database', []);
    }

    private function showAdminSetup()
    {
        // Get database config from session using Session helper
        if (class_exists('App\\Helpers\\Session')) {
            $sessionClass = 'App\\Helpers\\Session';
            if (!$sessionClass::has('db_config')) {
                Response::redirect('/install?step=2');
            }
        } else {
            // Fallback to direct session access
            session_start();
            if (!isset($_SESSION['db_config'])) {
                Response::redirect('/install?step=2');
            }
        }
        
        echo $this->renderTemplate('admin', []);
    }

    private function showCompletion()
    {
        echo $this->renderTemplate('completion', []);
    }

    private function processRequirements()
    {
        $requirements = $this->checkRequirements();
        $canProceed = !in_array(false, array_column($requirements, 'status'));
        
        if ($canProceed) {
            Response::redirect('/install?step=2');
        } else {
            Response::error('System requirements not met');
        }
    }

    private function processDatabase()
    {
        $host = $_POST['db_host'] ?? '';
        $port = $_POST['db_port'] ?? 3306;
        $database = $_POST['db_name'] ?? '';
        $username = $_POST['db_user'] ?? '';
        $password = $_POST['db_pass'] ?? '';

        // Validate input
        if (empty($host) || empty($database) || empty($username)) {
            Response::error('Please fill in all required database fields');
        }

        // Test connection
        $testResult = Database::testConnection($host, $username, $password, null, $port);
        if (!$testResult['success']) {
            Response::error('Database connection failed: ' . $testResult['message']);
        }

        // Create database if it doesn't exist
        $createResult = Database::createDatabase($host, $username, $password, $database, $port);
        if (!$createResult['success']) {
            Response::error('Database creation failed: ' . $createResult['message']);
        }

        // Test connection to the new database
        $testDbResult = Database::testConnection($host, $username, $password, $database, $port);
        if (!$testDbResult['success']) {
            Response::error('Cannot connect to created database: ' . $testDbResult['message']);
        }

        // Store database config in session using Session helper if available
        if (class_exists('App\\Helpers\\Session')) {
            $sessionClass = 'App\\Helpers\\Session';
            $sessionClass::set('db_config', [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'password' => $password
            ]);
        } else {
            // Fallback to direct session access
            session_start();
            $_SESSION['db_config'] = [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'password' => $password
            ];
        }

        Response::redirect('/install?step=3');
    }

    private function processAdmin()
    {
        // Get database config using Session helper if available
        $dbConfig = null;
        if (class_exists('App\\Helpers\\Session')) {
            $sessionClass = 'App\\Helpers\\Session';
            $dbConfig = $sessionClass::get('db_config');
        } else {
            // Fallback to direct session access
            session_start();
            $dbConfig = $_SESSION['db_config'] ?? null;
        }
        
        if (!$dbConfig) {
            Response::error('Database configuration not found. Please restart installation.');
        }

        $adminUser = $_POST['admin_user'] ?? '';
        $adminPass = $_POST['admin_pass'] ?? '';
        $adminEmail = $_POST['admin_email'] ?? '';
        $siteName = $_POST['site_name'] ?? 'My CMS';

        // Validate input
        if (empty($adminUser) || empty($adminPass) || empty($adminEmail)) {
            Response::error('Please fill in all admin fields');
        }

        if (strlen($adminPass) < 6) {
            Response::error('Admin password must be at least 6 characters');
        }

        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            Response::error('Please enter a valid email address');
        }

        try {
            // Create .env file
            $this->createEnvFile($dbConfig, $siteName);
            
            // Create database tables
            $this->createTables($dbConfig);
            
            // Create admin user
            $this->createAdminUser($dbConfig, $adminUser, $adminPass, $adminEmail);
            
            // Create installation lock file
            file_put_contents(__DIR__ . '/../../../storage/installed.lock', date('Y-m-d H:i:s'));
            
            // Clear session data
            if (class_exists('App\\Helpers\\Session')) {
                $sessionClass = 'App\\Helpers\\Session';
                $sessionClass::remove('db_config');
            } else {
                unset($_SESSION['db_config']);
            }
            
            Response::redirect('/install?step=4');
            
        } catch (Exception $e) {
            Response::error('Installation failed: ' . $e->getMessage());
        }
    }

    private function checkRequirements()
    {
        return [
            [
                'name' => 'PHP Version (>= 8.2)',
                'status' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'current' => PHP_VERSION
            ],
            [
                'name' => 'PDO Extension',
                'status' => extension_loaded('pdo'),
                'current' => extension_loaded('pdo') ? 'Enabled' : 'Disabled'
            ],
            [
                'name' => 'PDO MySQL Extension',
                'status' => extension_loaded('pdo_mysql'),
                'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled'
            ],
            [
                'name' => 'JSON Extension',
                'status' => extension_loaded('json'),
                'current' => extension_loaded('json') ? 'Enabled' : 'Disabled'
            ],
            [
                'name' => 'Writable Storage Directory',
                'status' => is_writable(__DIR__ . '/../../../storage'),
                'current' => is_writable(__DIR__ . '/../../../storage') ? 'Writable' : 'Not Writable'
            ],
            [
                'name' => 'Writable Root Directory',
                'status' => is_writable(__DIR__ . '/../../..'),
                'current' => is_writable(__DIR__ . '/../../..') ? 'Writable' : 'Not Writable'
            ]
        ];
    }

    private function createEnvFile($dbConfig, $siteName)
    {
        $envContent = "# Database Configuration
DB_HOST={$dbConfig['host']}
DB_NAME={$dbConfig['database']}
DB_USER={$dbConfig['username']}
DB_PASS={$dbConfig['password']}
DB_PORT={$dbConfig['port']}

# Application Configuration
APP_NAME=\"{$siteName}\"
APP_URL=http://localhost
APP_KEY=" . bin2hex(random_bytes(16)) . "
APP_DEBUG=false

# JWT Configuration
JWT_SECRET=" . bin2hex(random_bytes(32)) . "
JWT_EXPIRE=3600

# File Upload Configuration
UPLOAD_MAX_SIZE=10485760
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf,doc,docx

# Cache Configuration
CACHE_DRIVER=file
CACHE_TTL=3600
";

        if (!file_put_contents(__DIR__ . '/../../../.env', $envContent)) {
            throw new Exception('Could not create .env file');
        }
    }

    private function createTables($dbConfig)
    {
        // Create temporary database connection
        $pdo = new \PDO(
            "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4",
            $dbConfig['username'],
            $dbConfig['password'],
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]
        );

        // Users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(50) NOT NULL UNIQUE,
                `email` varchar(100) NOT NULL UNIQUE,
                `password` varchar(255) NOT NULL,
                `role` enum('admin','editor','viewer') DEFAULT 'editor',
                `status` enum('active','inactive') DEFAULT 'active',
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_username` (`username`),
                KEY `idx_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Content types table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `content_types` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `slug` varchar(100) NOT NULL UNIQUE,
                `description` text,
                `fields` json,
                `settings` json,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Content entries table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `content_entries` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `content_type_id` int(11) NOT NULL,
                `data` json NOT NULL,
                `status` enum('published','draft','archived') DEFAULT 'draft',
                `created_by` int(11),
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_content_type` (`content_type_id`),
                KEY `idx_status` (`status`),
                KEY `idx_created_by` (`created_by`),
                FOREIGN KEY (`content_type_id`) REFERENCES `content_types`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Media table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `media` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `filename` varchar(255) NOT NULL,
                `original_name` varchar(255) NOT NULL,
                `mime_type` varchar(100) NOT NULL,
                `size` bigint(20) NOT NULL,
                `path` varchar(500) NOT NULL,
                `alt_text` text,
                `uploaded_by` int(11),
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_filename` (`filename`),
                KEY `idx_mime_type` (`mime_type`),
                KEY `idx_uploaded_by` (`uploaded_by`),
                FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Settings table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `key` varchar(100) NOT NULL UNIQUE,
                `value` text,
                `type` enum('string','integer','boolean','json') DEFAULT 'string',
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_key` (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // User tokens table (for remember me and password reset)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `user_tokens` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `token` varchar(255) NOT NULL,
                `type` enum('remember','password_reset') NOT NULL,
                `expires_at` timestamp NOT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_token` (`token`),
                KEY `idx_type` (`type`),
                KEY `idx_expires` (`expires_at`),
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function createAdminUser($dbConfig, $username, $password, $email)
    {
        $pdo = new \PDO(
            "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4",
            $dbConfig['username'],
            $dbConfig['password'],
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]
        );

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role) 
            VALUES (?, ?, ?, 'admin')
        ");
        
        $stmt->execute([$username, $email, $hashedPassword]);
    }

    private function renderTemplate($template, $data = [])
    {
        extract($data);
        ob_start();
        include __DIR__ . "/../../Views/install/{$template}.php";
        return ob_get_clean();
    }
}
?>