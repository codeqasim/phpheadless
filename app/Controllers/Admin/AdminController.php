<?php
// app/Controllers/Admin/AdminController.php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Helpers\Response;
use App\Helpers\Session;
use CodeQasim\Mailer\Mailer;

class AdminController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->checkAuth();
    }

    public function index()
    {        
        // Render dashboard view
        

        $mailer = new Mailer();

        $mailer->configure([
            'server' => 'smtp.gmail.com',
            'port' => 587,
            'security' => 'tls', // 'tls' or 'ssl'
            'username' => 'phptravels.server3@gmail.com',
            'password' => 'ikni tqgv akxu btxj',
        ]);

        $mailer->sendEmail([
            'from' => 'phptravels.server3@gmail.com',
            'to' => 'compoxition@gmail.com',
            'subject' => 'Test Email',
            'template' => '/template.html', // Path to your template
            'variables' => [ // Variables for placeholders in the template
                'name' => 'John Doe',
                'date' => date('Y-m-d'),
            ],
        ]);

        
        Response::view('admin/test', [
            'title' => 'Test',
            'pageTitle' => 'Dashboard',
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