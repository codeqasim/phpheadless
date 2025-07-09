<?php
// app/Controllers/Admin/DashboardController.php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Helpers\Response;
use App\Helpers\Session;

class DashboardController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->checkAuth();
    }

    public function index()
    {
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Render dashboard view
        Response::view('admin/dashboard', [
            'title' => 'Dashboard',
            'pageTitle' => 'Dashboard',
            'stats' => $stats
        ]);
    }

    private function getDashboardStats()
    {
        try {
            // Get content types count
            $contentTypesCount = $this->db->count('content_types');
            
            // Get content entries count
            $contentEntriesCount = $this->db->count('content_entries');
            
            // Get media count
            $mediaCount = $this->db->count('media');
            
            // Get users count
            $usersCount = $this->db->count('users');
            
            // Get recent content entries
            $recentEntries = $this->db->select('content_entries', [
                '[>]content_types' => ['content_type_id' => 'id'],
                '[>]users' => ['created_by' => 'id']
            ], [
                'content_entries.id',
                'content_entries.status',
                'content_entries.created_at',
                'content_types.name(type_name)',
                'users.username(created_by_name)'
            ], [
                'ORDER' => ['content_entries.created_at' => 'DESC'],
                'LIMIT' => 5
            ]);
            
            return [
                'content_types' => $contentTypesCount,
                'content_entries' => $contentEntriesCount,
                'media_files' => $mediaCount,
                'users' => $usersCount,
                'recent_entries' => $recentEntries ?: []
            ];
            
        } catch (\Exception $e) {
            // If tables don't exist yet, return default stats
            return [
                'content_types' => 0,
                'content_entries' => 0,
                'media_files' => 0,
                'users' => 1, // At least the admin user exists
                'recent_entries' => []
            ];
        }
    }

    private function checkAuth()
    {
        Session::start();
        
        if (!isset($_SESSION['admin_logged_in'])) {
            // Store intended URL for redirect after login
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: /admin/login');
            exit;
        }
    }
}
?>