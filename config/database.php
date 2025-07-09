<?php
// config/database.php

return [
    'database_type' => 'mysql',
    'database_name' => $_ENV['DB_NAME'] ?? 'cms_db',
    'server' => $_ENV['DB_HOST'] ?? 'localhost',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'option' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
];