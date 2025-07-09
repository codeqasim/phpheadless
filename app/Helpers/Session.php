<?php
// app/Helpers/Session.php

namespace App\Helpers;

class Session
{
    /**
     * Safely start a session if not already started
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated()
    {
        self::start();
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    /**
     * Get current user data
     */
    public static function user($key = null)
    {
        self::start();
        
        if (!isset($_SESSION['user'])) {
            return null;
        }

        if ($key === null) {
            return $_SESSION['user'];
        }

        return $_SESSION['user'][$key] ?? null;
    }

    /**
     * Set user session data
     */
    public static function setUser($userData)
    {
        self::start();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user'] = $userData;
    }

    /**
     * Get session value
     */
    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     */
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Check if session key exists
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public static function remove($key)
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Flash message - set a message that will be available for one request
     */
    public static function flash($key, $value = null)
    {
        self::start();
        
        if ($value === null) {
            // Get flash message
            $message = $_SESSION["flash_{$key}"] ?? null;
            unset($_SESSION["flash_{$key}"]);
            return $message;
        } else {
            // Set flash message
            $_SESSION["flash_{$key}"] = $value;
        }
    }

    /**
     * Destroy the session
     */
    public static function destroy()
    {
        self::start();
        session_destroy();
        $_SESSION = [];
    }

    /**
     * Regenerate session ID for security
     */
    public static function regenerate($deleteOld = true)
    {
        self::start();
        session_regenerate_id($deleteOld);
    }

    /**
     * Set intended URL for redirect after login
     */
    public static function setIntendedUrl($url)
    {
        self::set('intended_url', $url);
    }

    /**
     * Get and remove intended URL
     */
    public static function getIntendedUrl($default = '/admin')
    {
        $url = self::get('intended_url', $default);
        self::remove('intended_url');
        return $url;
    }
}
?>