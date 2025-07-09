
<?php

// Error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set timezone
date_default_timezone_set('UTC');

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize application
try {
    $app = new App\Core\Application();
    $app->run();
} catch (Exception $e) {
    // Basic error handling
    http_response_code(500);
    if ($_ENV['APP_DEBUG'] ?? false) {
        echo '<pre>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
    } else {
        echo 'Internal Server Error';
    }
}
?>