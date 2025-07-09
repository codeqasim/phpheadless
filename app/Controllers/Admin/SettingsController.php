<?php
// app/Controllers/Admin/SettingsController.php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Helpers\Response;
use Exception;

class SettingsController
{
    private $db;
    private $settingsCache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Load settings into cache
        $this->loadSettings();
    }

    public function index()
    {
        $settings = $this->getAllSettings();
        
        Response::view('admin/settings/index', [
            'title' => 'Settings',
            'pageTitle' => 'Project Settings',
            'settings' => $settings,
            'phpInfo' => $this->getPhpInfo(),
            'systemInfo' => $this->getSystemInfo()
        ]);
    }

    public function update()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                Response::redirect('/admin/settings');
                return;
            }

            $category = $_POST['category'] ?? '';
            $errors = [];
            $successCount = 0;

            switch ($category) {
                case 'general':
                    $result = $this->updateGeneralSettings($_POST);
                    break;
                case 'api':
                    $result = $this->updateApiSettings($_POST);
                    break;
                case 'upload':
                    $result = $this->updateUploadSettings($_POST);
                    break;
                case 'cache':
                    $result = $this->updateCacheSettings($_POST);
                    break;
                case 'security':
                    $result = $this->updateSecuritySettings($_POST);
                    break;
                default:
                    throw new Exception('Invalid settings category');
            }

            if ($result['success']) {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }

            Response::redirect('/admin/settings');

        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Error updating settings: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
            Response::redirect('/admin/settings');
        }
    }

    public function clearCache()
    {
        try {
            $cacheDir = __DIR__ . '/../../../storage/cache/';
            $cleared = $this->clearDirectory($cacheDir);
            
            Response::json([
                'success' => true,
                'message' => "Cache cleared successfully. Removed $cleared files."
            ]);

        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportSettings()
    {
        try {
            $settings = $this->getAllSettings();
            $export = [
                'cms_name' => 'PHP Headless CMS',
                'exported_at' => date('Y-m-d H:i:s'),
                'settings' => $settings
            ];

            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="cms-settings-' . date('Y-m-d') . '.json"');
            echo json_encode($export, JSON_PRETTY_PRINT);
            exit;

        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Error exporting settings: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
            Response::redirect('/admin/settings');
        }
    }

    public function importSettings()
    {
        try {
            if (!isset($_FILES['settings_file'])) {
                throw new Exception('No file uploaded');
            }

            $file = $_FILES['settings_file'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload error');
            }

            $content = file_get_contents($file['tmp_name']);
            $data = json_decode($content, true);

            if (!$data || !isset($data['settings'])) {
                throw new Exception('Invalid settings file format');
            }

            $imported = 0;
            foreach ($data['settings'] as $key => $setting) {
                if ($this->setSetting($key, $setting['value'], $setting['type'])) {
                    $imported++;
                }
            }

            $_SESSION['flash_message'] = "Successfully imported $imported settings";
            $_SESSION['flash_type'] = 'success';

        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Error importing settings: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        }

        Response::redirect('/admin/settings');
    }

    private function updateGeneralSettings($data)
    {
        try {
            $settings = [
                'site_name' => ['value' => $data['site_name'] ?? '', 'type' => 'string'],
                'site_description' => ['value' => $data['site_description'] ?? '', 'type' => 'string'],
                'site_url' => ['value' => rtrim($data['site_url'] ?? '', '/'), 'type' => 'string'],
                'admin_email' => ['value' => $data['admin_email'] ?? '', 'type' => 'string'],
                'timezone' => ['value' => $data['timezone'] ?? 'UTC', 'type' => 'string'],
                'date_format' => ['value' => $data['date_format'] ?? 'Y-m-d', 'type' => 'string'],
                'items_per_page' => ['value' => (int)($data['items_per_page'] ?? 20), 'type' => 'integer']
            ];

            // Validate email
            if (!empty($settings['admin_email']['value']) && !filter_var($settings['admin_email']['value'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }

            // Validate URL
            if (!empty($settings['site_url']['value']) && !filter_var($settings['site_url']['value'], FILTER_VALIDATE_URL)) {
                throw new Exception('Invalid site URL');
            }

            foreach ($settings as $key => $setting) {
                $this->setSetting($key, $setting['value'], $setting['type']);
            }

            return ['success' => true, 'message' => 'General settings updated successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function updateApiSettings($data)
    {
        try {
            $settings = [
                'api_enabled' => ['value' => isset($data['api_enabled']) ? 1 : 0, 'type' => 'boolean'],
                'api_rate_limit' => ['value' => (int)($data['api_rate_limit'] ?? 1000), 'type' => 'integer'],
                'api_rate_window' => ['value' => (int)($data['api_rate_window'] ?? 3600), 'type' => 'integer'],
                'cors_enabled' => ['value' => isset($data['cors_enabled']) ? 1 : 0, 'type' => 'boolean'],
                'cors_origins' => ['value' => $data['cors_origins'] ?? '*', 'type' => 'string'],
                'api_auth_required' => ['value' => isset($data['api_auth_required']) ? 1 : 0, 'type' => 'boolean']
            ];

            foreach ($settings as $key => $setting) {
                $this->setSetting($key, $setting['value'], $setting['type']);
            }

            return ['success' => true, 'message' => 'API settings updated successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function updateUploadSettings($data)
    {
        try {
            $maxSize = $this->parseFileSize($data['max_file_size'] ?? '10MB');
            
            $settings = [
                'upload_max_size' => ['value' => $maxSize, 'type' => 'integer'],
                'upload_allowed_types' => ['value' => $data['upload_allowed_types'] ?? 'jpg,jpeg,png,gif,pdf', 'type' => 'string'],
                'upload_auto_thumbnail' => ['value' => isset($data['upload_auto_thumbnail']) ? 1 : 0, 'type' => 'boolean'],
                'upload_thumbnail_size' => ['value' => (int)($data['upload_thumbnail_size'] ?? 300), 'type' => 'integer'],
                'upload_organize_by_date' => ['value' => isset($data['upload_organize_by_date']) ? 1 : 0, 'type' => 'boolean']
            ];

            foreach ($settings as $key => $setting) {
                $this->setSetting($key, $setting['value'], $setting['type']);
            }

            return ['success' => true, 'message' => 'Upload settings updated successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function updateCacheSettings($data)
    {
        try {
            $settings = [
                'cache_enabled' => ['value' => isset($data['cache_enabled']) ? 1 : 0, 'type' => 'boolean'],
                'cache_duration' => ['value' => (int)($data['cache_duration'] ?? 3600), 'type' => 'integer'],
                'cache_api_responses' => ['value' => isset($data['cache_api_responses']) ? 1 : 0, 'type' => 'boolean']
            ];

            foreach ($settings as $key => $setting) {
                $this->setSetting($key, $setting['value'], $setting['type']);
            }

            return ['success' => true, 'message' => 'Cache settings updated successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function updateSecuritySettings($data)
    {
        try {
            $settings = [
                'login_attempts_limit' => ['value' => (int)($data['login_attempts_limit'] ?? 5), 'type' => 'integer'],
                'login_lockout_duration' => ['value' => (int)($data['login_lockout_duration'] ?? 900), 'type' => 'integer'],
                'session_timeout' => ['value' => (int)($data['session_timeout'] ?? 7200), 'type' => 'integer'],
                'require_https' => ['value' => isset($data['require_https']) ? 1 : 0, 'type' => 'boolean'],
                'enable_2fa' => ['value' => isset($data['enable_2fa']) ? 1 : 0, 'type' => 'boolean']
            ];

            foreach ($settings as $key => $setting) {
                $this->setSetting($key, $setting['value'], $setting['type']);
            }

            return ['success' => true, 'message' => 'Security settings updated successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function loadSettings()
    {
        $settings = $this->db->select('settings', ['key', 'value', 'type']);
        
        foreach ($settings as $setting) {
            $this->settingsCache[$setting['key']] = $this->castValue($setting['value'], $setting['type']);
        }
    }

    private function getAllSettings()
    {
        $settings = $this->db->select('settings', ['key', 'value', 'type', 'updated_at'], [], ['ORDER' => ['key' => 'ASC']]);
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key']] = [
                'value' => $this->castValue($setting['value'], $setting['type']),
                'type' => $setting['type'],
                'updated_at' => $setting['updated_at']
            ];
        }

        return $result;
    }

    private function getSetting($key, $default = null)
    {
        return $this->settingsCache[$key] ?? $default;
    }

    private function setSetting($key, $value, $type = 'string')
    {
        try {
            // Cast value based on type
            $castedValue = $this->castValue($value, $type);
            
            $exists = $this->db->get('settings', 'id', ['key' => $key]);
            
            if ($exists) {
                $result = $this->db->update('settings', [
                    'value' => (string)$value,
                    'type' => $type,
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['key' => $key]);
            } else {
                $result = $this->db->insert('settings', [
                    'key' => $key,
                    'value' => (string)$value,
                    'type' => $type,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            if ($result !== false) {
                $this->settingsCache[$key] = $castedValue;
                return true;
            }

            return false;

        } catch (Exception $e) {
            error_log('Error setting ' . $key . ': ' . $e->getMessage());
            return false;
        }
    }

    private function castValue($value, $type)
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

    private function parseFileSize($size)
    {
        $size = trim($size);
        $unit = strtoupper(substr($size, -2));
        $number = (int)$size;

        switch ($unit) {
            case 'KB':
                return $number * 1024;
            case 'MB':
                return $number * 1024 * 1024;
            case 'GB':
                return $number * 1024 * 1024 * 1024;
            default:
                return $number;
        }
    }

    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;
        
        while ($bytes >= 1024 && $unit < count($units) - 1) {
            $bytes /= 1024;
            $unit++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unit];
    }

    private function clearDirectory($dir)
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $count = 0;
        $files = glob($dir . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }

        return $count;
    }

    private function getPhpInfo()
    {
        return [
            'version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'extensions' => [
                'gd' => extension_loaded('gd'),
                'curl' => extension_loaded('curl'),
                'mbstring' => extension_loaded('mbstring'),
                'pdo' => extension_loaded('pdo'),
                'json' => extension_loaded('json'),
                'openssl' => extension_loaded('openssl')
            ]
        ];
    }

    private function getSystemInfo()
    {
        $uploadsDir = __DIR__ . '/../../../public/uploads/';
        $cacheDir = __DIR__ . '/../../../storage/cache/';
        
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'disk_space' => [
                'free' => disk_free_space('.'),
                'total' => disk_total_space('.')
            ],
            'directories' => [
                'uploads_writable' => is_writable($uploadsDir),
                'cache_writable' => is_writable($cacheDir)
            ],
            'database' => [
                'type' => 'MySQL',
                'connection' => $this->testDatabaseConnection()
            ]
        ];
    }

    private function testDatabaseConnection()
    {
        try {
            $this->db->select('settings', 'key', [], ['LIMIT' => 1]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>