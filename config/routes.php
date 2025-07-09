<?php
// config/routes.php

// Get the router instance from the application
$router = $this->router;

// Installation Routes
$router->get('/install', 'Install\InstallController@index');
$router->post('/install', 'Install\InstallController@process');
$router->post('/install/test-connection', 'Install\InstallController@testConnection');


// Admin Authentication Routes
$router->get('/admin/login', 'Admin\AuthController@loginForm');
$router->post('/admin/login', 'Admin\AuthController@login');
$router->get('/admin/logout', 'Admin\AuthController@logout');
$router->get('/admin/forgot-password', 'Admin\AuthController@forgotPasswordForm');
$router->post('/admin/forgot-password', 'Admin\AuthController@sendResetLink');
$router->get('/admin/reset-password', 'Admin\AuthController@resetPasswordForm');
$router->post('/admin/reset-password', 'Admin\AuthController@resetPassword');

// Admin Routes (protected by middleware)
$router->get('/admin', 'Admin\DashboardController@index');
$router->get('/admin/content', 'Admin\ContentController@index');
$router->get('/admin/content/create', 'Admin\ContentController@create');
$router->post('/admin/content', 'Admin\ContentController@store');
$router->get('/admin/content/{id}', 'Admin\ContentController@show');
$router->get('/admin/content/{id}/edit', 'Admin\ContentController@edit');
$router->put('/admin/content/{id}', 'Admin\ContentController@update');
$router->delete('/admin/content/{id}', 'Admin\ContentController@destroy');

// Content Types Routes
$router->get('/admin/content-types', 'Admin\ContentTypeController@index');
$router->get('/admin/content-types/create', 'Admin\ContentTypeController@create');
$router->post('/admin/content-types', 'Admin\ContentTypeController@store');
$router->get('/admin/content-types/{id}/edit', 'Admin\ContentTypeController@edit');
$router->put('/admin/content-types/{id}', 'Admin\ContentTypeController@update');
$router->delete('/admin/content-types/{id}', 'Admin\ContentTypeController@destroy');

// Media Management Routes
$router->get('/admin/media', 'Admin\MediaController@index');
$router->post('/admin/media/upload', 'Admin\MediaController@upload');
$router->delete('/admin/media/bulk-delete', 'Admin\MediaController@bulkDelete'); // NEW: Bulk delete
$router->get('/admin/media/{id}', 'Admin\MediaController@show');
$router->put('/admin/media/{id}', 'Admin\MediaController@update');
$router->delete('/admin/media/{id}', 'Admin\MediaController@delete');

// Static file serving (if you need it)
$router->get('/uploads/{path}', function($path) {
    $filePath = __DIR__ . '/../public/uploads/' . $path;
    if (file_exists($filePath)) {
        $mimeType = mime_content_type($filePath);
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
    http_response_code(404);
    echo 'File not found';
});

// Settings Routes
$router->get('/admin/settings', 'Admin\SettingsController@index');
$router->post('/admin/settings', 'Admin\SettingsController@update');

// API Documentation Routes
$router->get('/admin/api/documentation', 'Admin\APIController@documentation');
$router->get('/admin/api/endpoints', 'Admin\APIController@endpoints');

// API Routes (with CORS support)
$router->get('/api', 'API\ContentTypeController@index');
$router->get('/api/content-types', 'API\ContentTypeController@index');
$router->get('/api/content-types/{slug}', 'API\ContentTypeController@show');

// Dynamic content API routes
$router->get('/api/{contentType}', 'API\ContentController@index');
$router->get('/api/{contentType}/{id}', 'API\ContentController@show');
$router->post('/api/{contentType}', 'API\ContentController@store');
$router->put('/api/{contentType}/{id}', 'API\ContentController@update');
$router->delete('/api/{contentType}/{id}', 'API\ContentController@destroy');

// Media API Routes
$router->get('/api/media', 'API\MediaController@index');
$router->get('/api/media/{id}', 'API\MediaController@show');
$router->post('/api/media/upload', 'API\MediaController@upload');

// Settings Routes
$router->get('/admin/settings', 'Admin\\SettingsController@index');
$router->post('/admin/settings', 'Admin\\SettingsController@update');
$router->get('/admin/settings/export', 'Admin\\SettingsController@exportSettings');
$router->post('/admin/settings/import', 'Admin\\SettingsController@importSettings');
$router->post('/admin/settings/clear-cache', 'Admin\\SettingsController@clearCache');

// Handle OPTIONS requests for CORS preflight
$router->get('/api/options', function() {
    \App\Helpers\Response::cors();
    http_response_code(204);
    exit;
});

// Root route - redirect to admin
$router->get('/', function() {
    header('Location: /admin');
    exit;
});
?>