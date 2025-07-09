<?php
// app/Controllers/Admin/MediaController.php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Helpers\Response;
use Exception;

class MediaController
{
    private $db;
    private $uploadPath;
    private $uploadUrl;
    private $maxFileSize;
    private $allowedTypes;

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // CRITICAL: Ensure session is started for user ID
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialize upload settings
        $this->uploadPath = __DIR__ . '/../../../public/uploads/';
        $this->uploadUrl = '/uploads/';
        $this->maxFileSize = 10485760; // 10MB
        
        // Strict file type validation
        $this->allowedTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg', 
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        // Ensure upload directory exists
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    public function index()
    {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 24;
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['search'] ?? '');
        $type = trim($_GET['type'] ?? '');

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

        // Get files - NO JOINS to avoid Medoo issues
        $files = $this->db->select('media', [
            'id',
            'filename', 
            'original_name',
            'mime_type',
            'size',
            'path',
            'alt_text',
            'uploaded_by',
            'created_at'
        ], array_merge($whereClause, [
            'ORDER' => ['created_at' => 'DESC'],
            'LIMIT' => [$offset, $limit]
        ]));

        // Process files for display
        foreach ($files as &$file) {
            $file['url'] = $this->uploadUrl . $file['filename'];
            $file['size_formatted'] = $this->formatFileSize($file['size']);
            $file['is_image'] = $this->isImage($file['mime_type']);
            $file['icon'] = $this->getFileIcon($file['mime_type']);
        }

        // Get file type statistics
        $typeStats = $this->getFileTypeStats();

        $totalPages = ceil($totalFiles / $limit);

        Response::view('admin/media/index', [
            'title' => 'Media Library',
            'pageTitle' => 'Media Library',
            'files' => $files ?: [],
            'typeStats' => $typeStats,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'hasNext' => $page < $totalPages,
                'hasPrev' => $page > 1
            ],
            'totalFiles' => $totalFiles,
            'currentSearch' => $search,
            'currentType' => $type
        ]);
    }

    public function upload()
    {
        // Force JSON response headers
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }

            if (!isset($_FILES['file'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No file uploaded']);
                exit;
            }

            $file = $_FILES['file'];
            
            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception($this->getUploadErrorMessage($file['error']));
            }

            // Validate file
            $this->validateFile($file);

            // Generate unique filename with date structure
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = $this->generateUniqueFilename($extension);
            $filepath = $this->uploadPath . $filename;

            // Create directory if needed
            $dir = dirname($filepath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to save uploaded file');
            }

            // Get additional info
            $altText = $_POST['alt_text'] ?? '';
            
            // Save to database
            $mediaId = $this->db->insert('media', [
                'filename' => $filename,
                'original_name' => $file['name'],
                'mime_type' => $file['type'],
                'size' => $file['size'],
                'path' => $filename, // Store relative path
                'alt_text' => $altText,
                'uploaded_by' => $_SESSION['user_id'] ?? 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if (!$mediaId) {
                // Clean up file if database insert failed
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                throw new Exception('Failed to save file information to database');
            }

            // Generate thumbnail for images
            if ($this->isImage($file['type'])) {
                $this->generateThumbnail($filepath, $filename);
            }

            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file' => [
                    'id' => $mediaId,
                    'filename' => $filename,
                    'url' => $this->uploadUrl . $filename,
                    'original_name' => $file['name'],
                    'size' => $file['size'],
                    'size_formatted' => $this->formatFileSize($file['size']),
                    'mime_type' => $file['type'],
                    'is_image' => $this->isImage($file['type'])
                ]
            ]);
            exit;

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function show($id)
    {
        // Simple select without joins
        $file = $this->db->get('media', [
            'id',
            'filename',
            'original_name', 
            'mime_type',
            'size',
            'path',
            'alt_text',
            'uploaded_by',
            'created_at'
        ], ['id' => $id]);

        if (!$file) {
            Response::notFound();
            return;
        }

        $file['url'] = $this->uploadUrl . $file['filename'];
        $file['full_url'] = 'http://' . $_SERVER['HTTP_HOST'] . $this->uploadUrl . $file['filename'];
        $file['size_formatted'] = $this->formatFileSize($file['size']);
        $file['is_image'] = $this->isImage($file['mime_type']);

        Response::view('admin/media/show', [
            'title' => $file['original_name'],
            'pageTitle' => 'File Details', 
            'file' => $file
        ]);
    }

    public function update($id)
    {
        try {
            $file = $this->db->get('media', ['id', 'original_name'], ['id' => $id]);
            
            if (!$file) {
                Response::json(['success' => false, 'message' => 'File not found'], 404);
                return;
            }

            $altText = $_POST['alt_text'] ?? '';

            $updated = $this->db->update('media', [
                'alt_text' => $altText
            ], ['id' => $id]);

            if ($updated !== false) {
                Response::json(['success' => true, 'message' => 'File updated successfully']);
            } else {
                Response::json(['success' => false, 'message' => 'Failed to update file']);
            }

        } catch (Exception $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        // Force JSON response headers
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            $file = $this->db->get('media', ['filename', 'path'], ['id' => $id]);
            
            if (!$file) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'File not found']);
                exit;
            }

            // Delete physical file
            $filepath = $this->uploadPath . $file['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            // Delete thumbnail if exists
            $thumbnailPath = $this->uploadPath . 'thumbs/' . $file['filename'];
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }

            // Delete from database
            $deleted = $this->db->delete('media', ['id' => $id]);

            if ($deleted) {
                echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete file from database']);
            }
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    private function validateFile($file)
    {
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum allowed size of ' . $this->formatFileSize($this->maxFileSize));
        }

        // Check if file is empty
        if ($file['size'] === 0) {
            throw new Exception('File is empty');
        }

        // Enhanced file validation
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Check if extension is allowed
        if (!array_key_exists($extension, $this->allowedTypes)) {
            throw new Exception('File type ".' . $extension . '" not allowed. Allowed types: ' . implode(', ', array_keys($this->allowedTypes)));
        }

        // Verify MIME type matches extension
        $expectedMimeType = $this->allowedTypes[$extension];
        if ($file['type'] !== $expectedMimeType) {
            // Double-check with finfo for extra security
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $actualMimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if ($actualMimeType !== $expectedMimeType && $file['type'] !== $expectedMimeType) {
                    throw new Exception('File content does not match extension. Expected: ' . $expectedMimeType . ', got: ' . $file['type']);
                }
            }
        }

        // Additional security: Check for executable files
        $dangerousExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'cmd', 'scr', 'js', 'html', 'htm'];
        if (in_array($extension, $dangerousExtensions)) {
            throw new Exception('File type not allowed for security reasons');
        }
    }

    private function generateUniqueFilename($extension)
    {
        // Create date-based directory structure
        $dateDir = date('Y/m/d/');
        $fullDir = $this->uploadPath . $dateDir;
        
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }
        
        do {
            $filename = $dateDir . uniqid() . '.' . $extension;
            $filepath = $this->uploadPath . $filename;
        } while (file_exists($filepath));

        return $filename;
    }

    private function generateThumbnail($originalPath, $filename)
    {
        try {
            // Check if GD extension is available
            if (!extension_loaded('gd')) {
                return;
            }

            $thumbnailDir = $this->uploadPath . 'thumbs/';
            
            // Create thumbnail directory structure matching original
            $thumbPath = $thumbnailDir . $filename;
            $thumbDir = dirname($thumbPath);
            
            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0755, true);
            }

            // Get image info
            $info = getimagesize($originalPath);
            if (!$info) return;

            $width = $info[0];
            $height = $info[1];
            $type = $info[2];

            // Calculate thumbnail dimensions (max 300x300)
            $maxSize = 300;
            if ($width > $height) {
                $newWidth = $maxSize;
                $newHeight = ($height * $maxSize) / $width;
            } else {
                $newHeight = $maxSize;
                $newWidth = ($width * $maxSize) / $height;
            }

            // Create image resource
            $source = null;
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($originalPath);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($originalPath);
                    break;
                case IMAGETYPE_GIF:
                    $source = imagecreatefromgif($originalPath);
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagecreatefromwebp')) {
                        $source = imagecreatefromwebp($originalPath);
                    }
                    break;
                default:
                    return;
            }

            if (!$source) return;

            // Create thumbnail
            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG and GIF
            if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
                imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save thumbnail
            switch ($type) {
                case IMAGETYPE_JPEG:
                    imagejpeg($thumbnail, $thumbPath, 85);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($thumbnail, $thumbPath);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($thumbnail, $thumbPath);
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagewebp')) {
                        imagewebp($thumbnail, $thumbPath, 85);
                    }
                    break;
            }

            imagedestroy($source);
            imagedestroy($thumbnail);

        } catch (Exception $e) {
            // Thumbnail generation failed, but don't stop the upload
            error_log('Thumbnail generation failed: ' . $e->getMessage());
        }
    }

    private function getFileTypeStats()
    {
        // Get all files and process stats in PHP to avoid SQL complexity
        $allFiles = $this->db->select('media', ['mime_type', 'size']);
        
        $stats = [
            'Images' => ['count' => 0, 'total_size' => 0],
            'PDFs' => ['count' => 0, 'total_size' => 0], 
            'Documents' => ['count' => 0, 'total_size' => 0]
        ];
        
        foreach ($allFiles as $file) {
            if (strpos($file['mime_type'], 'image/') === 0) {
                $stats['Images']['count']++;
                $stats['Images']['total_size'] += $file['size'];
            } elseif ($file['mime_type'] === 'application/pdf') {
                $stats['PDFs']['count']++;
                $stats['PDFs']['total_size'] += $file['size'];
            } elseif (strpos($file['mime_type'], 'application/') === 0) {
                $stats['Documents']['count']++;
                $stats['Documents']['total_size'] += $file['size'];
            }
        }
        
        $result = [];
        foreach ($stats as $type => $data) {
            if ($data['count'] > 0) {
                $result[] = [
                    'type' => $type,
                    'count' => $data['count'],
                    'total_size' => $data['total_size'],
                    'total_size_formatted' => $this->formatFileSize($data['total_size'])
                ];
            }
        }
        
        return $result;
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

    private function getFileIcon($mimeType)
    {
        if (strpos($mimeType, 'image/') === 0) return 'fas fa-image';
        if (strpos($mimeType, 'video/') === 0) return 'fas fa-video';
        if (strpos($mimeType, 'audio/') === 0) return 'fas fa-music';
        if ($mimeType === 'application/pdf') return 'fas fa-file-pdf';
        if (strpos($mimeType, 'application/msword') === 0) return 'fas fa-file-word';
        if (strpos($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml') === 0) return 'fas fa-file-word';
        if (strpos($mimeType, 'application/vnd.ms-excel') === 0) return 'fas fa-file-excel';
        if (strpos($mimeType, 'application/vnd.openxmlformats-officedocument.spreadsheetml') === 0) return 'fas fa-file-excel';
        return 'fas fa-file';
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

public function bulkDelete()
{
    // Force JSON response headers
    header('Content-Type: application/json');
    
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        
        // Get JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['ids']) || !is_array($input['ids'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request: ids array required']);
            exit;
        }
        
        $ids = array_map('intval', $input['ids']);
        $deletedCount = 0;
        $errors = [];
        
        foreach ($ids as $id) {
            try {
                // Get file info before deletion
                $file = $this->db->get('media', ['filename', 'path', 'original_name'], ['id' => $id]);
                
                if (!$file) {
                    $errors[] = "File with ID $id not found";
                    continue;
                }
                
                // Delete physical file
                $filepath = $this->uploadPath . $file['filename'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                
                // Delete thumbnail if exists
                $thumbnailPath = $this->uploadPath . 'thumbs/' . $file['filename'];
                if (file_exists($thumbnailPath)) {
                    unlink($thumbnailPath);
                }
                
                // Delete from database
                $deleted = $this->db->delete('media', ['id' => $id]);
                
                if ($deleted) {
                    $deletedCount++;
                } else {
                    $errors[] = "Failed to delete '{$file['original_name']}' from database";
                }
                
            } catch (Exception $e) {
                $errors[] = "Error deleting file with ID $id: " . $e->getMessage();
            }
        }
        
        $response = [
            'success' => $deletedCount > 0,
            'deleted_count' => $deletedCount,
            'total_requested' => count($ids),
            'errors' => $errors
        ];
        
        if ($deletedCount > 0) {
            $response['message'] = "Successfully deleted $deletedCount file" . ($deletedCount > 1 ? 's' : '');
            if (!empty($errors)) {
                $response['message'] .= " with " . count($errors) . " error" . (count($errors) > 1 ? 's' : '');
            }
        } else {
            $response['message'] = 'No files were deleted';
        }
        
        echo json_encode($response);
        exit;
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

}

?>