<?php
// app/Controllers/API/MediaController.php

namespace App\Controllers\API;

use App\Core\Database;
use App\Helpers\Response;
use Exception;

class MediaController
{
    private $db;
    private $uploadUrl;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->uploadUrl = '/uploads/';
        
        // Enable CORS for all API requests
        Response::cors();
    }

    public function index()
    {
        try {
            // Parse query parameters
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(1, intval($_GET['limit'] ?? 10)));
            $search = $_GET['search'] ?? '';
            $type = $_GET['type'] ?? '';

            // Build where clause
            $whereClause = [];
            
            if ($search) {
                $whereClause['OR'] = [
                    'original_name[~]' => $search,
                    'alt_text[~]' => $search
                ];
            }

            if ($type) {
                $whereClause['mime_type[~]'] = $type;
            }

            // Get total count
            $totalFiles = $this->db->count('media', $whereClause);

            // Get files with pagination
            $offset = ($page - 1) * $limit;
            $files = $this->db->select('media', [
                '[>]users' => ['uploaded_by' => 'id']
            ], [
                'media.id',
                'media.filename',
                'media.original_name',
                'media.mime_type',
                'media.size',
                'media.alt_text',
                'media.created_at',
                'users.username(uploaded_by_name)'
            ], array_merge($whereClause, [
                'ORDER' => ['media.created_at' => 'DESC'],
                'LIMIT' => [$offset, $limit]
            ]));

            // Process files for API response
            $processedFiles = [];
            foreach ($files as $file) {
                $processedFiles[] = [
                    'id' => $file['id'],
                    'filename' => $file['filename'],
                    'original_name' => $file['original_name'],
                    'url' => $this->uploadUrl . $file['filename'],
                    'thumbnail_url' => $this->getThumbnailUrl($file['filename'], $file['mime_type']),
                    'mime_type' => $file['mime_type'],
                    'size' => $file['size'],
                    'size_formatted' => $this->formatFileSize($file['size']),
                    'alt_text' => $file['alt_text'],
                    'is_image' => $this->isImage($file['mime_type']),
                    'meta' => [
                        'uploaded_by' => $file['uploaded_by_name'],
                        'created_at' => $file['created_at']
                    ]
                ];
            }

            // Build pagination metadata
            $totalPages = ceil($totalFiles / $limit);
            $pagination = [
                'current_page' => $page,
                'per_page' => $limit,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ];

            Response::json([
                'data' => $processedFiles,
                'meta' => [
                    'total' => $totalFiles,
                    'count' => count($processedFiles),
                    'pagination' => $pagination
                ]
            ]);

        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $file = $this->db->get('media', [
                '[>]users' => ['uploaded_by' => 'id']
            ], [
                'media.*',
                'users.username(uploaded_by_name)'
            ], ['media.id' => $id]);

            if (!$file) {
                Response::notFound('File not found');
            }

            Response::json([
                'data' => [
                    'id' => $file['id'],
                    'filename' => $file['filename'],
                    'original_name' => $file['original_name'],
                    'url' => $this->uploadUrl . $file['filename'],
                    'thumbnail_url' => $this->getThumbnailUrl($file['filename'], $file['mime_type']),
                    'mime_type' => $file['mime_type'],
                    'size' => $file['size'],
                    'size_formatted' => $this->formatFileSize($file['size']),
                    'alt_text' => $file['alt_text'],
                    'is_image' => $this->isImage($file['mime_type']),
                    'meta' => [
                        'uploaded_by' => $file['uploaded_by_name'],
                        'created_at' => $file['created_at']
                    ]
                ]
            ]);

        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function upload()
    {
        try {
            if (!isset($_FILES['file'])) {
                Response::error('No file uploaded', 400);
            }

            $file = $_FILES['file'];
            
            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception($this->getUploadErrorMessage($file['error']));
            }

            // Validate file
            $this->validateFile($file);

            // Upload settings
            $uploadPath = __DIR__ . '/../../../public/uploads/';
            $maxFileSize = $_ENV['UPLOAD_MAX_SIZE'] ?? 10485760; // 10MB
            $allowedTypes = explode(',', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'jpg,jpeg,png,gif,pdf');

            // Generate unique filename
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = $this->generateUniqueFilename($extension, $uploadPath);
            $filepath = $uploadPath . $filename;

            // Create directory structure if needed
            $dir = dirname($filepath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to save uploaded file');
            }

            // Get additional info from request
            $altText = $_POST['alt_text'] ?? '';
            
            // Save to database
            $mediaId = $this->db->insert('media', [
                'filename' => $filename,
                'original_name' => $file['name'],
                'mime_type' => $file['type'],
                'size' => $file['size'],
                'path' => $this->uploadUrl . $filename,
                'alt_text' => $altText,
                'uploaded_by' => null // API uploads don't have user context
            ]);

            if (!$mediaId) {
                // Clean up file if database insert failed
                unlink($filepath);
                throw new Exception('Failed to save file information');
            }

            Response::json([
                'data' => [
                    'id' => $mediaId,
                    'filename' => $filename,
                    'original_name' => $file['name'],
                    'url' => $this->uploadUrl . $filename,
                    'thumbnail_url' => $this->getThumbnailUrl($filename, $file['type']),
                    'mime_type' => $file['type'],
                    'size' => $file['size'],
                    'size_formatted' => $this->formatFileSize($file['size']),
                    'alt_text' => $altText,
                    'is_image' => $this->isImage($file['type'])
                ],
                'message' => 'File uploaded successfully'
            ], 201);

        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    private function validateFile($file)
    {
        $maxFileSize = $_ENV['UPLOAD_MAX_SIZE'] ?? 10485760;
        $allowedTypes = explode(',', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'jpg,jpeg,png,gif,pdf');

        // Check file size
        if ($file['size'] > $maxFileSize) {
            throw new Exception('File size exceeds maximum allowed size of ' . $this->formatFileSize($maxFileSize));
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $allowedTypes));
        }
    }

    private function generateUniqueFilename($extension, $uploadPath)
    {
        do {
            $filename = date('Y/m/d/') . uniqid() . '.' . $extension;
            $filepath = $uploadPath . $filename;
        } while (file_exists($filepath));

        return $filename;
    }

    private function getThumbnailUrl($filename, $mimeType)
    {
        if (!$this->isImage($mimeType)) {
            return null;
        }

        $thumbnailPath = __DIR__ . '/../../../public/uploads/thumbs/' . $filename;
        if (file_exists($thumbnailPath)) {
            return '/uploads/thumbs/' . $filename;
        }

        return null;
    }

    private function formatFileSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }

    private function isImage($mimeType)
    {
        return strpos($mimeType, 'image/') === 0;
    }

    private function getUploadErrorMessage($error)
    {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File is too large';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary directory';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
}
?>