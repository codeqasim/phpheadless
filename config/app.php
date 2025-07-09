<?php
// config/app.php
return [
    'name' => $_ENV['APP_NAME'] ?? 'PHP Headless CMS',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'debug' => $_ENV['APP_DEBUG'] ?? true,
    'timezone' => 'UTC'
];