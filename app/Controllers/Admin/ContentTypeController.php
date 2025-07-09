<?php
// app/Controllers/Admin/ContentTypeController.php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Helpers\Response;
use App\Helpers\Session;
use Exception;

class ContentTypeController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->checkAuth();
    }

    public function index()
    {
        // Get all content types
        $contentTypes = $this->db->select('content_types', '*', [
            'ORDER' => ['created_at' => 'DESC']
        ]);

        Response::view('admin/content-types/index', [
            'title' => 'Content Types',
            'pageTitle' => 'Content Types',
            'contentTypes' => $contentTypes ?: []
        ]);
    }

    public function create()
    {
        Response::view('admin/content-types/create', [
            'title' => 'Create Content Type',
            'pageTitle' => 'Create Content Type'
        ]);
    }

    public function store()
    {
        try {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $fields = json_decode($_POST['fields'] ?? '[]', true);

            // Validate input
            if (empty($name)) {
                Response::error('Content type name is required');
            }

            // Generate slug from name
            $slug = $this->generateSlug($name);

            // Check if slug already exists
            if ($this->db->has('content_types', ['slug' => $slug])) {
                Response::error('A content type with this name already exists');
            }

            // Validate fields
            if (empty($fields)) {
                Response::error('At least one field is required');
            }

            $validatedFields = $this->validateFields($fields);

            // Create content type
            $contentTypeId = $this->db->insert('content_types', [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'fields' => json_encode($validatedFields),
                'settings' => json_encode([
                    'api_enabled' => true,
                    'public' => true
                ])
            ]);

            if ($contentTypeId) {
                Response::success('Content type created successfully', [
                    'id' => $contentTypeId,
                    'slug' => $slug
                ]);
            } else {
                Response::error('Failed to create content type');
            }

        } catch (Exception $e) {
            Response::error('Error creating content type: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $contentType = $this->db->get('content_types', '*', ['id' => $id]);
        
        if (!$contentType) {
            Response::notFound('Content type not found');
        }

        // Get content entries count
        $entriesCount = $this->db->count('content_entries', ['content_type_id' => $id]);

        // Parse fields
        $contentType['fields'] = json_decode($contentType['fields'], true) ?: [];
        $contentType['settings'] = json_decode($contentType['settings'], true) ?: [];

        Response::view('admin/content-types/show', [
            'title' => $contentType['name'],
            'pageTitle' => $contentType['name'],
            'contentType' => $contentType,
            'entriesCount' => $entriesCount
        ]);
    }

    public function edit($id)
    {
        $contentType = $this->db->get('content_types', '*', ['id' => $id]);
        
        if (!$contentType) {
            Response::notFound('Content type not found');
        }

        // Parse fields for editing
        $contentType['fields'] = json_decode($contentType['fields'], true) ?: [];

        Response::view('admin/content-types/edit', [
            'title' => 'Edit ' . $contentType['name'],
            'pageTitle' => 'Edit Content Type',
            'contentType' => $contentType
        ]);
    }

    public function update($id)
    {
        try {
            $contentType = $this->db->get('content_types', '*', ['id' => $id]);
            
            if (!$contentType) {
                Response::notFound('Content type not found');
            }

            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $fields = json_decode($_POST['fields'] ?? '[]', true);

            // Validate input
            if (empty($name)) {
                Response::error('Content type name is required');
            }

            // Generate slug from name (but keep original if name hasn't changed)
            $slug = ($name === $contentType['name']) ? $contentType['slug'] : $this->generateSlug($name);

            // Check if new slug already exists (excluding current record)
            if ($slug !== $contentType['slug'] && $this->db->has('content_types', ['slug' => $slug])) {
                Response::error('A content type with this name already exists');
            }

            // Validate fields
            if (empty($fields)) {
                Response::error('At least one field is required');
            }

            $validatedFields = $this->validateFields($fields);

            // Update content type
            $updated = $this->db->update('content_types', [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'fields' => json_encode($validatedFields)
            ], ['id' => $id]);

            if ($updated) {
                Response::success('Content type updated successfully');
            } else {
                Response::error('Failed to update content type');
            }

        } catch (Exception $e) {
            Response::error('Error updating content type: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $contentType = $this->db->get('content_types', '*', ['id' => $id]);
            
            if (!$contentType) {
                Response::notFound('Content type not found');
            }

            // Check if there are any content entries
            $entriesCount = $this->db->count('content_entries', ['content_type_id' => $id]);
            
            if ($entriesCount > 0) {
                Response::error("Cannot delete content type. It has {$entriesCount} content entries. Delete all entries first.");
            }

            // Delete the content type
            $deleted = $this->db->delete('content_types', ['id' => $id]);

            if ($deleted) {
                Response::success('Content type deleted successfully');
            } else {
                Response::error('Failed to delete content type');
            }

        } catch (Exception $e) {
            Response::error('Error deleting content type: ' . $e->getMessage());
        }
    }

    private function generateSlug($name)
    {
        // Convert to lowercase and replace spaces with hyphens
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', $slug));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return $slug ?: 'content-type-' . time();
    }

    private function validateFields($fields)
    {
        $validatedFields = [];
        $allowedTypes = ['text', 'textarea', 'number', 'email', 'url', 'date', 'datetime', 'boolean', 'select', 'media', 'rich_text'];

        foreach ($fields as $index => $field) {
            if (!isset($field['name']) || !isset($field['type'])) {
                continue; // Skip invalid fields
            }

            $fieldName = trim($field['name']);
            $fieldType = trim($field['type']);

            if (empty($fieldName) || !in_array($fieldType, $allowedTypes)) {
                continue; // Skip invalid fields
            }

            // Generate field key from name
            $fieldKey = $this->generateFieldKey($fieldName);

            $validatedField = [
                'key' => $fieldKey,
                'name' => $fieldName,
                'type' => $fieldType,
                'required' => isset($field['required']) && $field['required'],
                'settings' => []
            ];

            // Add type-specific settings
            switch ($fieldType) {
                case 'text':
                case 'email':
                case 'url':
                    $validatedField['settings']['max_length'] = intval($field['settings']['max_length'] ?? 255);
                    break;
                case 'textarea':
                case 'rich_text':
                    $validatedField['settings']['max_length'] = intval($field['settings']['max_length'] ?? 5000);
                    break;
                case 'number':
                    $validatedField['settings']['min'] = floatval($field['settings']['min'] ?? 0);
                    $validatedField['settings']['max'] = floatval($field['settings']['max'] ?? 999999);
                    break;
                case 'select':
                    $options = array_filter(array_map('trim', explode(',', $field['settings']['options'] ?? '')));
                    $validatedField['settings']['options'] = $options;
                    break;
            }

            // Add help text if provided
            if (!empty($field['help_text'])) {
                $validatedField['help_text'] = trim($field['help_text']);
            }

            $validatedFields[] = $validatedField;
        }

        return $validatedFields;
    }

    private function generateFieldKey($name)
    {
        // Convert to snake_case
        $key = strtolower(trim($name));
        $key = preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', $key));
        $key = preg_replace('/_+/', '_', $key);
        $key = trim($key, '_');
        
        return $key ?: 'field_' . time();
    }

    private function checkAuth()
    {
        if (!Session::isAuthenticated()) {
            Session::setIntendedUrl($_SERVER['REQUEST_URI']);
            Response::redirect('/admin/login');
        }
    }
}
?>