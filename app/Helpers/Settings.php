<?php
// app/Helpers/Settings.php

namespace App\Helpers;

use App\Core\Database;

class Settings
{
    private static $cache = [];
    private static $loaded = false;

    /**
     * Get a setting value
     */
    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::loadSettings();
        }

        return self::$cache[$key] ?? $default;
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $type = 'string')
    {
        try {
            $db = Database::getInstance();
            
            // Cast value based on type
            $castedValue = self::castValue($value, $type);
            
            $exists = $db->get('settings', 'id', ['key' => $key]);
            
            if ($exists) {
                $result = $db->update('settings', [
                    'value' => (string)$value,
                    'type' => $type,
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['key' => $key]);
            } else {
                $result = $db->insert('settings', [
                    'key' => $key,
                    'value' => (string)$value,
                    'type' => $type,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            if ($result !== false) {
                self::$cache[$key] = $castedValue;
                return true;
            }

            return false;

        } catch (Exception $e) {
            error_log('Error setting ' . $key . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all settings
     */
    public static function all()
    {
        if (!self::$loaded) {
            self::loadSettings();
        }

        return self::$cache;
    }

    /**
     * Refresh settings cache
     */
    public static function refresh()
    {
        self::$loaded = false;
        self::$cache = [];
        self::loadSettings();
    }

    /**
     * Load settings from database
     */
    private static function loadSettings()
    {
        try {
            $db = Database::getInstance();
            $settings = $db->select('settings', ['key', 'value', 'type']);
            
            foreach ($settings as $setting) {
                self::$cache[$setting['key']] = self::castValue($setting['value'], $setting['type']);
            }

            self::$loaded = true;

        } catch (Exception $e) {
            error_log('Error loading settings: ' . $e->getMessage());
            self::$loaded = true; // Prevent infinite loops
        }
    }

    /**
     * Cast value to appropriate type
     */
    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool)$value;
            case 'integer':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            default:
                return (string)$value;
        }
    }

    /**
     * Helper methods for common settings
     */
    public static function siteName($default = 'PHP Headless CMS')
    {
        return self::get('site_name', $default);
    }

    public static function siteUrl($default = '')
    {
        return rtrim(self::get('site_url', $default), '/');
    }

    public static function adminEmail($default = '')
    {
        return self::get('admin_email', $default);
    }

    public static function isApiEnabled($default = true)
    {
        return self::get('api_enabled', $default);
    }

    public static function isCacheEnabled($default = false)
    {
        return self::get('cache_enabled', $default);
    }

    public static function uploadMaxSize($default = 10485760)
    {
        return self::get('upload_max_size', $default);
    }

    public static function allowedFileTypes($default = 'jpg,jpeg,png,gif,pdf')
    {
        return explode(',', self::get('upload_allowed_types', $default));
    }

    public static function itemsPerPage($default = 20)
    {
        return self::get('items_per_page', $default);
    }

    public static function timezone($default = 'UTC')
    {
        return self::get('timezone', $default);
    }

    public static function dateFormat($default = 'Y-m-d')
    {
        return self::get('date_format', $default);
    }
}
?>