<?php
// app/Core/Application.php

namespace App\Core;

use App\Core\Database;
use App\Core\Config;
use Exception;

class Application
{
    private static $instance = null;
    private $database;
    private $config;
    private $router;

    public function __construct()
    {
        self::$instance = $this;
        $this->loadConfig();
        $this->initializeDatabase();
        $this->setupRouter();
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    private function loadConfig()
    {
        $this->config = new Config();
    }

    private function initializeDatabase()
    {
        try {
            $this->database = new Database();
        } catch (Exception $e) {
            // If database connection fails, redirect to installer
            if (!$this->isInstallerRoute()) {
                header('Location: /install');
                exit;
            }
        }
    }

    private function setupRouter()
    {
        // Check if we're using qaxim/php-router
        if (class_exists('\Qaxim\Router')) {
            $this->router = new \Qaxim\Router();
        } elseif (class_exists('\Qaxim\Router\Router')) {
            $this->router = new \Qaxim\Router\Router();
        } else {
            // Fallback to a simple router implementation
            $this->router = new SimpleRouter();
        }
        
        $this->loadRoutes();
    }

    private function loadRoutes()
    {
        // Check remember me token before route handling
        $this->checkRememberToken();
        
        // Check if CMS is installed
        if (!$this->isInstalled() && !$this->isInstallerRoute()) {
            header('Location: /install');
            exit;
        }

        // Load route definitions if file exists
        $routesFile = __DIR__ . '/../../config/routes.php';
        if (file_exists($routesFile)) {
            require_once $routesFile;
        } else {
            // Define minimal routes if routes file doesn't exist
            $this->defineMinimalRoutes();
        }
    }

    private function checkRememberToken()
    {
        // Skip for installer, login, and API routes
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (strpos($uri, '/install') === 0 || 
            strpos($uri, '/admin/login') === 0 || 
            strpos($uri, '/admin/forgot-password') === 0 || 
            strpos($uri, '/admin/reset-password') === 0 ||
            strpos($uri, '/api') === 0) {
            return;
        }

        // Use Session helper to safely start session
        if (class_exists('App\\Helpers\\Session')) {
            $sessionClass = 'App\\Helpers\\Session';
            
            // Check if user is already logged in
            if ($sessionClass::isAuthenticated()) {
                return;
            }

            // Check remember me token
            if (isset($_COOKIE['remember_token']) && class_exists('App\\Controllers\\Admin\\AuthController')) {
                $authController = new \App\Controllers\Admin\AuthController();
                $authController->checkRememberToken();
            }
        }
    }

    private function defineMinimalRoutes()
    {
        // Define basic routes for installation
        $this->router->get('/install', 'Install\InstallController@index');
        $this->router->post('/install', 'Install\InstallController@process');
        
        // Redirect root to install
        $this->router->get('/', function() {
            header('Location: /install');
            exit;
        });
    }

    private function isInstalled()
    {
        // Check if .env file exists and has database config
        if (!file_exists(__DIR__ . '/../../.env')) {
            return false;
        }

        // Check if we can connect to database
        try {
            if ($this->database) {
                $result = $this->database->query("SELECT COUNT(*) as count FROM users LIMIT 1");
                return $result !== false;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    private function isInstallerRoute()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return strpos($uri, '/install') === 0;
    }

    public function run()
    {
        try {
            // Handle the request
            if ($this->router) {
                $this->router->dispatch();
            } else {
                throw new Exception("Router not initialized");
            }
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    private function handleError(Exception $e)
    {
        http_response_code(500);
        
        if ($this->config->get('app.debug', false)) {
            echo '<div style="font-family: monospace; padding: 20px; background: #f8f8f8; border: 1px solid #ddd;">';
            echo '<h3 style="color: #d32f2f;">Application Error</h3>';
            echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' (Line: ' . $e->getLine() . ')</p>';
            echo '<details><summary>Stack Trace</summary><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre></details>';
            echo '</div>';
        } else {
            echo '<h1>Internal Server Error</h1><p>Something went wrong. Please try again later.</p>';
        }
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function getConfig()
    {
        return $this->config;
    }
}

// Simple Router fallback if qaxim/php-router is not available
class SimpleRouter
{
    private $routes = [];

    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function delete($path, $callback)
    {
        $this->routes['DELETE'][$path] = $callback;
    }

    public function put($path, $callback)
    {
        $this->routes['PUT'][$path] = $callback;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Handle PUT and DELETE methods via _method parameter or X-HTTP-Method-Override header
        if ($method === 'POST') {
            if (isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
            } elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            }
        }

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $callback) {
                if ($this->matchRoute($route, $uri, $matches)) {
                    return $this->executeCallback($callback, $matches);
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
    }

    private function matchRoute($route, $uri, &$matches)
    {
        // Convert route to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';

        return preg_match($pattern, $uri, $matches);
    }

    private function executeCallback($callback, $matches)
    {
        array_shift($matches); // Remove full match

        if (is_string($callback)) {
            // Handle "Controller@method" format
            if (strpos($callback, '@') !== false) {
                list($controller, $method) = explode('@', $callback);
                $controller = "App\\Controllers\\{$controller}";
                
                if (class_exists($controller)) {
                    $instance = new $controller();
                    if (method_exists($instance, $method)) {
                        return call_user_func_array([$instance, $method], $matches);
                    } else {
                        throw new Exception("Method {$method} not found in {$controller}");
                    }
                } else {
                    // Controller doesn't exist - show a friendly message
                    http_response_code(501);
                    echo $this->getControllerNotFoundPage($controller, $method);
                    return;
                }
            }
        } elseif (is_callable($callback)) {
            return call_user_func_array($callback, $matches);
        }

        throw new Exception("Invalid route callback: " . print_r($callback, true));
    }

    private function getControllerNotFoundPage($controller, $method)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Controller Not Found</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error { color: #d32f2f; }
                .info { background: #e3f2fd; padding: 15px; border-radius: 4px; margin: 20px 0; }
                .code { background: #f5f5f5; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1 class="error">Controller Not Found</h1>
                <p>The controller <strong>' . htmlspecialchars($controller) . '</strong> does not exist.</p>
                
                <div class="info">
                    <strong>📝 Next Steps:</strong><br>
                    This is normal during development. You need to create the controller file:
                    <div class="code">' . str_replace('App\\Controllers\\', 'app/Controllers/', $controller) . '.php</div>
                </div>
                
                <p>Don\'t worry! We\'ll create all the controllers in the next steps. For now, you can:</p>
                <ul>
                    <li><a href="/install">Continue to Installation</a></li>
                    <li>Create the missing controller manually</li>
                </ul>
                
                <hr>
                <small>Method: <code>' . htmlspecialchars($method) . '</code></small>
            </div>
        </body>
        </html>';
    }
}
?>