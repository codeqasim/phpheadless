<?php
// app/Core/Config.php

namespace App\Core;

class Config
{
    private $config = [];

    public function __construct()
    {
        $this->loadConfig();
    }

    private function loadConfig()
    {
        // Load app config
        $appConfig = $this->loadFile('app');
        if ($appConfig) {
            $this->config['app'] = $appConfig;
        }

        // Load database config
        $dbConfig = $this->loadFile('database');
        if ($dbConfig) {
            $this->config['database'] = $dbConfig;
        }
    }

    private function loadFile($name)
    {
        $path = __DIR__ . "/../../config/{$name}.php";
        if (file_exists($path)) {
            return require $path;
        }
        return null;
    }

    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

    public function set($key, $value)
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    public function has($key)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return false;
            }
        }

        return true;
    }

    public function all()
    {
        return $this->config;
    }
}
?>