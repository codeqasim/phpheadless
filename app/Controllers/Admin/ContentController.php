<?php
// app/Controllers/Admin/ContentController.php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Helpers\Response;
use App\Helpers\Session;
use Exception;

class ContentController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->checkAuth();
    }

    public function index()
    {
        $contentTypeSlug = $_GET['type'] ?? null;
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get all content types for filter
        $contentTypes = $this->db->select('content_types', ['id', 'name', 'slug'], [
            'ORDER' => ['name' => 'ASC']
        ]);

        $whereClause = [];
        $selectedContentType = null;

        // Filter by content type if specified
        if ($contentTypeSlug) {
            $selectedContentType = $this->db->get('content_types', '*', ['slug' => $contentTypeSlug]);
            if ($selectedContentType) {
                $whereClause['content_type_id'] = $selectedContentType['id'];
            }
        }

        // Get content entries with pagination
        $entries = $this->db->select('content_entries', [
            '[>]content_types' => ['content_type_id' => 'id'],
            '[>]users' => ['created_by' => 'id']
        ], [
            'content_entries.id',
            'content_entries.data',
            'content_entries.status',
            'content_entries.created_at',
            'content_entries.updated_at',
            'content_types.name(type_name)',
            'content_types.slug(type_slug)',
            'content_types.fields',
            'users.username(created_by_name)'
        ], array_merge($whereClause, [
            'ORDER' => ['content_entries.updated_at' => 'DESC'],
            'LIMIT' => [$offset, $limit]
        ]));

        // Get total count for pagination
        $totalEntries = $this->db->count('content_entries', $whereClause);
        $totalPages = ceil($totalEntries / $limit);

        // Process entries data
        foreach ($entries as &$entry) {
            $entry['data'] = json_decode($entry['data'], true) ?: [];
            $entry['fields'] = json_decode($entry['fields'], true) ?: [];
            
            // Get display title from first text field or use ID
            $entry['display_title'] = $this->getDisplayTitle($entry['data'], $entry['fields']) ?: "Entry #{$entry['id']}";
        }

        Response::view('admin/content/index', [
            'title' => 'Content Entries',
            'pageTitle' => $selectedContentType ? $selectedContentType['name'] . ' Entries' : 'All Content Entries',
            'entries' => $entries ?: [],
            'contentTypes' => $contentTypes ?: [],
            'selectedContentType' => $selectedContentType,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'hasNext' => $page < $totalPages,
                'hasPrev' => $page > 1
            ],
            'totalEntries' => $totalEntries
        ]);
    }

    public function create()
    {
        $contentTypeSlug = $_GET['type'] ?? null;
        
        if (!$contentTypeSlug) {
            // Show content type selection
            $contentTypes = $this->db->select('content_types', '*', [
                'ORDER' => ['name' => 'ASC']
            ]);

            Response::view('admin/content/select-type', [
                'title' => 'Create Content',
                'pageTitle' => 'Select Content Type',
                'contentTypes' => $contentTypes ?: []
            ]);
            return;
        }

        $contentType = $this->db->get('content_types', '*', ['slug' => $contentTypeSlug]);
        
        if (!$contentType) {
            Response::notFound('Content type not found');
        }

        $contentType['fields'] = json_decode($contentType['fields'], true) ?: [];

        Response::view('admin/content/create', [
            'title' => 'Create ' . $contentType['name'],
            'pageTitle' => 'Create ' . $contentType['name'],
            'contentType' => $contentType
        ]);
    }

    public function store()
    {
        try {
            $contentTypeId = $_POST['content_type_id'] ?? null;
            $status = $_POST['status'] ?? 'draft';
            
            if (!$contentTypeId) {
                Response::error('Content type is required');
            }

            // Get content type
            $contentType = $this->db->get('content_types', '*', ['id' => $contentTypeId]);
            if (!$contentType) {
                Response::error('Content type not found');
            }

            $fields = json_decode($contentType['fields'], true) ?: [];
            
            // Validate and process form data
            $data = $this->validateAndProcessFormData($_POST, $fields);

            // Create content entry
            $entryId = $this->db->insert('content_entries', [
                'content_type_id' => $contentTypeId,
                'data' => json_encode($data),
                'status' => $status,
                'created_by' => $_SESSION['user']['id'] ?? 1
            ]);

            if ($entryId) {
                Response::success('Content created successfully', [
                    'id' => $entryId,
                    'content_type_slug' => $contentType['slug']
                ]);
            } else {
                Response::error('Failed to create content');
            }

        } catch (Exception $e) {
            Response::error('Error creating content: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $entry = $this->db->get('content_entries', [
            '[>]content_types' => ['content_type_id' => 'id'],
            '[>]users' => ['created_by' => 'id']
        ], [
            'content_entries.*',
            'content_types.name(type_name)',
            'content_types.slug(type_slug)',
            'content_types.fields',
            'users.username(created_by_name)'
        ], ['content_entries.id' => $id]);

        if (!$entry) {
            Response::notFound('Content entry not found');
        }

        $entry['data'] = json_decode($entry['data'], true) ?: [];
        $entry['fields'] = json_decode($entry['fields'], true) ?: [];

        Response::view('admin/content/show', [
            'title' => $this->getDisplayTitle($entry['data'], $entry['fields']) ?: "Entry #{$id}",
            'pageTitle' => 'View Content',
            'entry' => $entry
        ]);
    }

    public function edit($id)
    {
        $entry = $this->db->get('content_entries', [
            '[>]content_types' => ['content_type_id' => 'id']
        ], [
            'content_entries.*',
            'content_types.name(type_name)',
            'content_types.slug(type_slug)',
            'content_types.fields'
        ], ['content_entries.id' => $id]);

        if (!$entry) {
            Response::notFound('Content entry not found');
        }

        $entry['data'] = json_decode($entry['data'], true) ?: [];
        $entry['fields'] = json_decode($entry['fields'], true) ?: [];

        Response::view('admin/content/edit', [
            'title' => 'Edit ' . ($this->getDisplayTitle($entry['data'], $entry['fields']) ?: "Entry #{$id}"),
            'pageTitle' => 'Edit Content',
            'entry' => $entry
        ]);
    }

    public function update($id)
    {
        try {
            $entry = $this->db->get('content_entries', [
                '[>]content_types' => ['content_type_id' => 'id']
            ], [
                'content_entries.*',
                'content_types.fields'
            ], ['content_entries.id' => $id]);

            if (!$entry) {
                Response::notFound('Content entry not found');
            }

            $fields = json_decode($entry['fields'], true) ?: [];
            $status = $_POST['status'] ?? $entry['status'];
            
            // Validate and process form data
            $data = $this->validateAndProcessFormData($_POST, $fields);

            // Update content entry
            $updated = $this->db->update('content_entries', [
                'data' => json_encode($data),
                'status' => $status
            ], ['id' => $id]);

            if ($updated) {
                Response::success('Content updated successfully');
            } else {
                Response::error('Failed to update content');
            }

        } catch (Exception $e) {
            Response::error('Error updating content: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $entry = $this->db->get('content_entries', '*', ['id' => $id]);
            
            if (!$entry) {
                Response::notFound('Content entry not found');
            }

            $deleted = $this->db->delete('content_entries', ['id' => $id]);

            if ($deleted) {
                Response::success('Content deleted successfully');
            } else {
                Response::error('Failed to delete content');
            }

        } catch (Exception $e) {
            Response::error('Error deleting content: ' . $e->getMessage());
        }
    }

    private function validateAndProcessFormData($postData, $fields)
    {
        $data = [];
        $errors = [];

        foreach ($fields as $field) {
            $fieldKey = $field['key'];
            $fieldName = $field['name'];
            $fieldType = $field['type'];
            $isRequired = $field['required'] ?? false;
            $value = $postData[$fieldKey] ?? null;

            // Check required fields
            if ($isRequired && (empty($value) && $value !== '0')) {
                $errors[] = "{$fieldName} is required";
                continue;
            }

            // Skip empty non-required fields
            if (empty($value) && $value !== '0' && !$isRequired) {
                continue;
            }

            // Validate and process based on field type
            switch ($fieldType) {
                case 'text':
                case 'textarea':
                case 'rich_text':
                case 'email':
                case 'url':
                    $data[$fieldKey] = trim($value);
                    
                    // Validate max length
                    $maxLength = $field['settings']['max_length'] ?? 255;
                    if (strlen($data[$fieldKey]) > $maxLength) {
                        $errors[] = "{$fieldName} must be {$maxLength} characters or less";
                    }

                    // Validate email format
                    if ($fieldType === 'email' && !filter_var($data[$fieldKey], FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "{$fieldName} must be a valid email address";
                    }

                    // Validate URL format
                    if ($fieldType === 'url' && !filter_var($data[$fieldKey], FILTER_VALIDATE_URL)) {
                        $errors[] = "{$fieldName} must be a valid URL";
                    }
                    break;

                case 'number':
                    $data[$fieldKey] = floatval($value);
                    
                    // Validate min/max
                    $min = $field['settings']['min'] ?? null;
                    $max = $field['settings']['max'] ?? null;
                    
                    if ($min !== null && $data[$fieldKey] < $min) {
                        $errors[] = "{$fieldName} must be at least {$min}";
                    }
                    if ($max !== null && $data[$fieldKey] > $max) {
                        $errors[] = "{$fieldName} must be no more than {$max}";
                    }
                    break;

                case 'boolean':
                    $data[$fieldKey] = !empty($value);
                    break;

                case 'date':
                case 'datetime':
                    $data[$fieldKey] = $value; // Assume valid date format from HTML input
                    break;

                case 'select':
                    $options = $field['settings']['options'] ?? [];
                    if (!in_array($value, $options)) {
                        $errors[] = "{$fieldName} must be one of the allowed options";
                    } else {
                        $data[$fieldKey] = $value;
                    }
                    break;

                case 'media':
                    // For now, just store the value (file upload handling would be more complex)
                    $data[$fieldKey] = $value;
                    break;

                default:
                    $data[$fieldKey] = $value;
            }
        }

        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        return $data;
    }

    private function getDisplayTitle($data, $fields)
    {
        // Try to find the first text field to use as display title
        foreach ($fields as $field) {
            if (in_array($field['type'], ['text', 'textarea']) && isset($data[$field['key']])) {
                return substr($data[$field['key']], 0, 50) . (strlen($data[$field['key']]) > 50 ? '...' : '');
            }
        }

        // Fallback to any string value
        foreach ($data as $value) {
            if (is_string($value) && !empty(trim($value))) {
                return substr(trim($value), 0, 50) . (strlen(trim($value)) > 50 ? '...' : '');
            }
        }

        return null;
    }

    private function checkAuth()
    {
        Session::start();
        if (!isset($_SESSION['admin_logged_in'])) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            Response::redirect('/admin/login');
        }
    }
}
?>