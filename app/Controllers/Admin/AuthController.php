<?php
// app/Controllers/Admin/AuthController.php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Helpers\Response;
use App\Helpers\Session;
use Exception;

class AuthController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function loginForm()
    {
        // Check if already logged in
        if (Session::isAuthenticated()) {
            Response::redirect('/admin');
        }

        $error = Session::flash('login_error');
        $success = Session::flash('login_success');

        Response::view('admin/auth/login', [
            'title' => 'Admin Login',
            'error' => $error,
            'success' => $success
        ]);
    }

    public function login()
    {
        try {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            // Validate input
            if (empty($username) || empty($password)) {
                throw new Exception('Username and password are required');
            }

            // Rate limiting check
            $this->checkRateLimit();

            // Find user
            $user = $this->db->get('users', '*', [
                'OR' => [
                    'username' => $username,
                    'email' => $username
                ],
                'status' => 'active'
            ]);

            if (!$user) {
                $this->recordFailedAttempt();
                throw new Exception('Invalid username or password');
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->recordFailedAttempt();
                throw new Exception('Invalid username or password');
            }

            // Check if user has admin role
            if (!in_array($user['role'], ['admin', 'editor'])) {
                throw new Exception('Access denied. Admin privileges required.');
            }

            // Clear failed attempts
            $this->clearFailedAttempts();

            // Set session using Session helper
            Session::setUser([
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]);

            // Regenerate session ID for security
            Session::regenerate();

            // Set remember me cookie if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (30 * 24 * 60 * 60); // 30 days
                
                // Store token in database
                $this->db->insert('user_tokens', [
                    'user_id' => $user['id'],
                    'token' => hash('sha256', $token),
                    'type' => 'remember',
                    'expires_at' => date('Y-m-d H:i:s', $expires)
                ]);

                // Set cookie
                setcookie('remember_token', $token, $expires, '/', '', false, true);
            }

            // Update last login
            $this->db->update('users', [
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $user['id']]);

            // Redirect to intended page or dashboard
            $redirectTo = Session::getIntendedUrl('/admin');
            Response::redirect($redirectTo);

        } catch (Exception $e) {
            Session::flash('login_error', $e->getMessage());
            Response::redirect('/admin/login');
        }
    }

    public function logout()
    {
        $userId = Session::user('id');
        
        // Clear remember me token if exists
        if (isset($_COOKIE['remember_token']) && $userId) {
            $token = $_COOKIE['remember_token'];
            $this->db->delete('user_tokens', [
                'user_id' => $userId,
                'token' => hash('sha256', $token),
                'type' => 'remember'
            ]);
            
            // Clear cookie
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }

        // Clear session
        Session::destroy();
        
        Session::flash('login_success', 'You have been logged out successfully');
        Response::redirect('/admin/login');
    }

    public function forgotPasswordForm()
    {
        $error = Session::flash('forgot_error');
        $success = Session::flash('forgot_success');

        Response::view('admin/auth/forgot-password', [
            'title' => 'Forgot Password',
            'error' => $error,
            'success' => $success
        ]);
    }

    public function sendResetLink()
    {
        try {
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                throw new Exception('Email address is required');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid email address');
            }

            // Find user by email
            $user = $this->db->get('users', '*', [
                'email' => $email,
                'status' => 'active'
            ]);

            // Always show success message for security (don't reveal if email exists)
            Session::flash('forgot_success', 'If an account with that email exists, we have sent you a password reset link.');

            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

                // Delete old reset tokens for this user
                $this->db->delete('user_tokens', [
                    'user_id' => $user['id'],
                    'type' => 'password_reset'
                ]);

                // Store new reset token
                $this->db->insert('user_tokens', [
                    'user_id' => $user['id'],
                    'token' => hash('sha256', $token),
                    'type' => 'password_reset',
                    'expires_at' => $expires
                ]);

                // Send email (for now, just log the reset link)
                $resetUrl = ($_ENV['APP_URL'] ?? 'http://localhost') . '/admin/reset-password?token=' . $token;
                
                // Log reset link for development (in production, send email)
                error_log("Password reset link for {$user['email']}: {$resetUrl}");
                
                // TODO: Send actual email in production
                // $this->sendPasswordResetEmail($user, $resetUrl);
            }

            Response::redirect('/admin/forgot-password');

        } catch (Exception $e) {
            Session::flash('forgot_error', $e->getMessage());
            Response::redirect('/admin/forgot-password');
        }
    }

    public function resetPasswordForm()
    {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $_SESSION['login_error'] = 'Invalid or missing reset token';
            Response::redirect('/admin/login');
        }

        // Verify token
        $tokenRecord = $this->db->get('user_tokens', [
            '[>]users' => ['user_id' => 'id']
        ], [
            'user_tokens.user_id',
            'user_tokens.expires_at',
            'users.email'
        ], [
            'user_tokens.token' => hash('sha256', $token),
            'user_tokens.type' => 'password_reset',
            'user_tokens.expires_at[>]' => date('Y-m-d H:i:s')
        ]);

        if (!$tokenRecord) {
            $_SESSION['login_error'] = 'Invalid or expired reset token';
            Response::redirect('/admin/login');
        }

        $error = $_SESSION['reset_error'] ?? null;
        unset($_SESSION['reset_error']);

        Response::view('admin/auth/reset-password', [
            'title' => 'Reset Password',
            'token' => $token,
            'email' => $tokenRecord['email'],
            'error' => $error
        ]);
    }

    public function resetPassword()
    {
        Session::start();
        
        try {
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['password_confirmation'] ?? '';

            // Validate input
            if (empty($token) || empty($password) || empty($confirmPassword)) {
                throw new Exception('All fields are required');
            }

            if ($password !== $confirmPassword) {
                throw new Exception('Passwords do not match');
            }

            if (strlen($password) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }

            // Verify token
            $tokenRecord = $this->db->get('user_tokens', '*', [
                'token' => hash('sha256', $token),
                'type' => 'password_reset',
                'expires_at[>]' => date('Y-m-d H:i:s')
            ]);

            if (!$tokenRecord) {
                throw new Exception('Invalid or expired reset token');
            }

            // Update password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $this->db->update('users', [
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $tokenRecord['user_id']]);

            // Delete used token
            $this->db->delete('user_tokens', [
                'id' => $tokenRecord['id']
            ]);

            // Delete all remember me tokens for security
            $this->db->delete('user_tokens', [
                'user_id' => $tokenRecord['user_id'],
                'type' => 'remember'
            ]);

            $_SESSION['login_success'] = 'Password reset successfully. Please log in with your new password.';
            Response::redirect('/admin/login');

        } catch (Exception $e) {
            $_SESSION['reset_error'] = $e->getMessage();
            Response::redirect('/admin/reset-password?token=' . urlencode($_POST['token'] ?? ''));
        }
    }

    public function checkRememberToken()
    {
        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }

        $token = $_COOKIE['remember_token'];
        $hashedToken = hash('sha256', $token);

        $tokenRecord = $this->db->get('user_tokens', [
            '[>]users' => ['user_id' => 'id']
        ], [
            'users.*'
        ], [
            'user_tokens.token' => $hashedToken,
            'user_tokens.type' => 'remember',
            'user_tokens.expires_at[>]' => date('Y-m-d H:i:s'),
            'users.status' => 'active'
        ]);

        if ($tokenRecord && in_array($tokenRecord['role'], ['admin', 'editor'])) {
            // Auto login using Session helper
            Session::setUser([
                'id' => $tokenRecord['id'],
                'username' => $tokenRecord['username'],
                'email' => $tokenRecord['email'],
                'role' => $tokenRecord['role']
            ]);
            return true;
        }

        // Invalid token, clear cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        return false;
    }

    private function checkRateLimit()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $cacheFile = __DIR__ . '/../../../storage/cache/login_attempts_' . md5($ip);
        
        if (file_exists($cacheFile)) {
            $attempts = json_decode(file_get_contents($cacheFile), true) ?: [];
            $recentAttempts = array_filter($attempts, function($time) {
                return $time > (time() - 900); // 15 minutes
            });
            
            if (count($recentAttempts) >= 5) {
                throw new Exception('Too many failed login attempts. Please try again in 15 minutes.');
            }
        }
    }

    private function recordFailedAttempt()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $cacheFile = __DIR__ . '/../../../storage/cache/login_attempts_' . md5($ip);
        
        $attempts = [];
        if (file_exists($cacheFile)) {
            $attempts = json_decode(file_get_contents($cacheFile), true) ?: [];
        }
        
        $attempts[] = time();
        file_put_contents($cacheFile, json_encode($attempts));
    }

    private function clearFailedAttempts()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $cacheFile = __DIR__ . '/../../../storage/cache/login_attempts_' . md5($ip);
        
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
}
?>