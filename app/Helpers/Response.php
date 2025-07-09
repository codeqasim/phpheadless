<?php
// app/Helpers/Response.php

namespace App\Helpers;

class Response
{
    public static function json($data, $status = 200, $headers = [])
    {
        http_response_code($status);
        
        // Set default headers
        $defaultHeaders = [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache, must-revalidate'
        ];
        
        $headers = array_merge($defaultHeaders, $headers);
        
        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }
        
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($message = 'Success', $data = null)
    {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return self::json($response);
    }

    public static function error($message = 'Error', $status = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        return self::json($response, $status);
    }

    public static function notFound($message = 'Resource not found')
    {
        return self::error($message, 404);
    }

    public static function unauthorized($message = 'Unauthorized')
    {
        return self::error($message, 401);
    }

    public static function forbidden($message = 'Forbidden')
    {
        return self::error($message, 403);
    }

    public static function validationError($errors, $message = 'Validation failed')
    {
        return self::error($message, 422, $errors);
    }

    public static function serverError($message = 'Internal server error')
    {
        return self::error($message, 500);
    }

    public static function redirect($url, $status = 302)
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }

    public static function view($viewPath, $data = [])
    {
        // Extract data variables
        extract($data);
        
        // Build the full path
        $fullPath = __DIR__ . '/../../app/Views/' . $viewPath . '.php';
        
        if (file_exists($fullPath)) {
            ob_start();
            include $fullPath;
            $content = ob_get_clean();
            echo $content;
        } else {
            throw new \Exception("View not found: {$viewPath}");
        }
        
        exit;
    }

    public static function cors($allowedOrigins = ['*'], $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'], $allowedHeaders = ['Content-Type', 'Authorization'])
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
        }
        
        header("Access-Control-Allow-Methods: " . implode(', ', $allowedMethods));
        header("Access-Control-Allow-Headers: " . implode(', ', $allowedHeaders));
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400");
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public static function download($filePath, $fileName = null, $contentType = 'application/octet-stream')
    {
        if (!file_exists($filePath)) {
            return self::notFound('File not found');
        }
        
        $fileName = $fileName ?: basename($filePath);
        $fileSize = filesize($filePath);
        
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        readfile($filePath);
        exit;
    }

    public static function cache($seconds = 3600)
    {
        $expires = gmdate('D, d M Y H:i:s', time() + $seconds) . ' GMT';
        header("Cache-Control: public, max-age={$seconds}");
        header("Expires: {$expires}");
    }

    public static function noCache()
    {
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}
?>