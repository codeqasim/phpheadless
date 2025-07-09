<?php
// app/Controllers/Admin/APIController.php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Helpers\Response;
use App\Helpers\Session;

class APIController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->checkAuth();
    }

    public function documentation()
    {
        // Get all content types for examples
        $contentTypes = $this->db->select('content_types', '*', [
            'ORDER' => ['name' => 'ASC']
        ]);

        $processedTypes = [];
        foreach ($contentTypes as $type) {
            $fields = json_decode($type['fields'], true) ?: [];
            $processedTypes[] = [
                'name' => $type['name'],
                'slug' => $type['slug'],
                'description' => $type['description'],
                'fields' => $fields
            ];
        }

        Response::view('admin/api/documentation', [
            'title' => 'API Documentation',
            'pageTitle' => 'API Documentation',
            'contentTypes' => $processedTypes,
            'baseUrl' => ($_ENV['APP_URL'] ?? 'http://localhost')
        ]);
    }

    public function endpoints()
    {
        // Get all content types
        $contentTypes = $this->db->select('content_types', ['name', 'slug', 'description'], [
            'ORDER' => ['name' => 'ASC']
        ]);

        Response::view('admin/api/endpoints', [
            'title' => 'API Endpoints',
            'pageTitle' => 'API Endpoints',
            'contentTypes' => $contentTypes,
            'baseUrl' => ($_ENV['APP_URL'] ?? 'http://localhost')
        ]);
    }

    private function checkAuth()
    {
        if (!Session::isAuthenticated()) {
            Session::setIntendedUrl($_SERVER['REQUEST_URI']);
            Response::redirect('/admin/login');
        }
    }
}
?>