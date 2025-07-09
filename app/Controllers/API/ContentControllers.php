<?php
// app/Controllers/API/ContentController.php

namespace App\Controllers\API;

use App\Core\Database;
use App\Helpers\Response;
use Exception;

class ContentController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Enable CORS for all API requests
        Response::cors();
    }

    public function index($contentTypeSlug)
    {
        try {
            // Get content type
            $contentType = $this->getContentType($contentTypeSlug);
            
            // Parse query parameters
            $params = $this->parseQueryParams();
            
            // Build where clause
            $whereClause = ['content_type_id' => $contentType['id']];
            
            // Add status filter (default to published only)
            $status = $params['status'] ?? 'published';
            if ($status !== 'all') {
                $whereClause['status'] = $status;
            }
            
            // Add custom filters
            if (!empty($params['filters'])) {
                foreach ($params['filters'] as $field => $value) {
                    // Use JSON extraction for filtering data fields
                    $whereClause["JSON_EXTRACT(data, '$.{$field}')"] = $value;
                }
            }
            
            // Get total count for pagination
            $totalCount = $this->db->count('content_entries', $whereClause);
            
            // Add pagination and sorting
            $orderClause = $this->buildOrderClause($params);
            $limitClause = $this->buildLimitClause($params);
            
            $finalClause = array_merge($whereClause, $orderClause, $limitClause);
            
            // Get entries with relations
            $entries = $this->db->select('content_entries', [
                '[>]users' => ['created_by' => 'id']
            ], [
                'content_entries.id',
                'content_entries.data',
                'content_entries.status',
                'content_entries.created_at',
                'content_entries.updated_at',
                'users.username(created_by_name)'
            ], $finalClause);
            
            // Process entries
            $processedEntries = [];
            foreach ($entries as $entry) {
                $data = json_decode($entry['data'], true) ?: [];
                
                // Select specific fields if requested
                if (!empty($params['fields'])) {
                    $filteredData = [];
                    foreach ($params['fields'] as $field) {
                        if (isset($data[$field])) {
                            $filteredData[$field] = $data[$field];
                        }
                    }
                    $data = $filteredData;
                }
                
                $processedEntry = [
                    'id' => $entry['id'],
                    'data' => $data,
                    'meta' => [
                        'status' => $entry['status'],
                        'created_at' => $entry['created_at'],
                        'updated_at' => $entry['updated_at'],
                        'created_by' => $entry['created_by_name']
                    ]
                ];
                
                $processedEntries[] = $processedEntry;
            }
            
            // Build pagination metadata
            $pagination = $this->buildPaginationMeta($params, $totalCount);
            
            Response::json([
                'data' => $processedEntries,
                'meta' => [
                    'total' => $totalCount,
                    'count' => count($processedEntries),
                    'pagination' => $pagination,
                    'content_type' => [
                        'name' => $contentType['name'],
                        'slug' => $contentType['slug']
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function show($contentTypeSlug, $id)
    {
        try {
            // Get content type
            $contentType = $this->getContentType($contentTypeSlug);
            
            // Parse query parameters
            $params = $this->parseQueryParams();
            
            // Get single entry
            $entry = $this->db->get('content_entries', [
                '[>]users' => ['created_by' => 'id']
            ], [
                'content_entries.id',
                'content_entries.data',
                'content_entries.status',
                'content_entries.created_at',
                'content_entries.updated_at',
                'users.username(created_by_name)'
            ], [
                'content_entries.id' => $id,
                'content_entries.content_type_id' => $contentType['id']
            ]);
            
            if (!$entry) {
                Response::notFound('Content entry not found');
            }
            
            // Check if entry is published (unless status=all is specified)
            if ($entry['status'] !== 'published' && ($_GET['status'] ?? 'published') !== 'all') {
                Response::notFound('Content entry not found');
            }
            
            $data = json_decode($entry['data'], true) ?: [];
            
            // Select specific fields if requested
            if (!empty($params['fields'])) {
                $filteredData = [];
                foreach ($params['fields'] as $field) {
                    if (isset($data[$field])) {
                        $filteredData[$field] = $data[$field];
                    }
                }
                $data = $filteredData;
            }
            
            Response::json([
                'data' => [
                    'id' => $entry['id'],
                    'data' => $data,
                    'meta' => [
                        'status' => $entry['status'],
                        'created_at' => $entry['created_at'],
                        'updated_at' => $entry['updated_at'],
                        'created_by' => $entry['created_by_name']
                    ]
                ],
                'meta' => [
                    'content_type' => [
                        'name' => $contentType['name'],
                        'slug' => $contentType['slug']
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function store($contentTypeSlug)
    {
        try {
            // Get content type
            $contentType = $this->getContentType($contentTypeSlug);
            $fields = json_decode($contentType['fields'], true) ?: [];
            
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                Response::error('Invalid JSON data', 400);
            }
            
            // Validate and process data
            $data = $this->validateAndProcessData($input, $fields);
            $status = $input['status'] ?? 'published';
            
            if (!in_array($status, ['published', 'draft', 'archived'])) {
                $status = 'published';
            }
            
            // Create entry
            $entryId = $this->db->insert('content_entries', [
                'content_type_id' => $contentType['id'],
                'data' => json_encode($data),
                'status' => $status,
                'created_by' => null // API requests don't have user context for now
            ]);
            
            if (!$entryId) {
                Response::error('Failed to create content entry', 500);
            }
            
            Response::json([
                'data' => [
                    'id' => $entryId,
                    'data' => $data,
                    'meta' => [
                        'status' => $status,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                ],
                'message' => 'Content created successfully'
            ], 201);
            
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function update($contentTypeSlug, $id)
    {
        try {
            // Get content type
            $contentType = $this->getContentType($contentTypeSlug);
            $fields = json_decode($contentType['fields'], true) ?: [];
            
            // Check if entry exists
            $existingEntry = $this->db->get('content_entries', '*', [
                'id' => $id,
                'content_type_id' => $contentType['id']
            ]);
            
            if (!$existingEntry) {
                Response::notFound('Content entry not found');
            }
            
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                Response::error('Invalid JSON data', 400);
            }
            
            // Validate and process data
            $data = $this->validateAndProcessData($input, $fields);
            $status = $input['status'] ?? $existingEntry['status'];
            
            if (!in_array($status, ['published', 'draft', 'archived'])) {
                $status = $existingEntry['status'];
            }
            
            // Update entry
            $updated = $this->db->update('content_entries', [
                'data' => json_encode($data),
                'status' => $status
            ], ['id' => $id]);
            
            if (!$updated) {
                Response::error('Failed to update content entry', 500);
            }
            
            Response::json([
                'data' => [
                    'id' => $id,
                    'data' => $data,
                    'meta' => [
                        'status' => $status,
                        'created_at' => $existingEntry['created_at'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                ],
                'message' => 'Content updated successfully'
            ]);
            
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function destroy($contentTypeSlug, $id)
    {
        try {
            // Get content type
            $contentType = $this->getContentType($contentTypeSlug);
            
            // Check if entry exists
            $entry = $this->db->get('content_entries', '*', [
                'id' => $id,
                'content_type_id' => $contentType['id']
            ]);
            
            if (!$entry) {
                Response::notFound('Content entry not found');
            }
            
            // Delete entry
            $deleted = $this->db->delete('content_entries', ['id' => $id]);
            
            if (!$deleted) {
                Response::error('Failed to delete content entry', 500);
            }
            
            Response::json([
                'message' => 'Content deleted successfully'
            ]);
            
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    private function getContentType($slug)
    {
        $contentType = $this->db->get('content_types', '*', ['slug' => $slug]);
        
        if (!$contentType) {
            Response::notFound("Content type '{$slug}' not found");
        }
        
        return $contentType;
    }

    private function parseQueryParams()
    {
        $params = [
            'page' => max(1, intval($_GET['page'] ?? 1)),
            'limit' => min(100, max(1, intval($_GET['limit'] ?? 10))),
            'sort' => $_GET['sort'] ?? 'created_at',
            'order' => strtoupper($_GET['order'] ?? 'DESC'),
            'fields' => [],
            'filters' => []
        ];
        
        // Parse fields parameter
        if (!empty($_GET['fields'])) {
            $params['fields'] = array_map('trim', explode(',', $_GET['fields']));
        }
        
        // Parse filter parameters
        foreach ($_GET as $key => $value) {
            if (strpos($key, 'filter[') === 0 && substr($key, -1) === ']') {
                $fieldName = substr($key, 7, -1);
                $params['filters'][$fieldName] = $value;
            }
        }
        
        // Validate sort order
        if (!in_array($params['order'], ['ASC', 'DESC'])) {
            $params['order'] = 'DESC';
        }
        
        return $params;
    }

    private function buildOrderClause($params)
    {
        $sortField = $params['sort'];
        $order = $params['order'];
        
        // Handle sorting by data fields vs meta fields
        if (in_array($sortField, ['id', 'created_at', 'updated_at', 'status'])) {
            return ['ORDER' => ["content_entries.{$sortField}" => $order]];
        } else {
            // Sort by JSON field
            return ['ORDER' => ["JSON_EXTRACT(data, '$.{$sortField}')" => $order]];
        }
    }

    private function buildLimitClause($params)
    {
        $offset = ($params['page'] - 1) * $params['limit'];
        return ['LIMIT' => [$offset, $params['limit']]];
    }

    private function buildPaginationMeta($params, $totalCount)
    {
        $totalPages = ceil($totalCount / $params['limit']);
        
        return [
            'current_page' => $params['page'],
            'per_page' => $params['limit'],
            'total_pages' => $totalPages,
            'has_next' => $params['page'] < $totalPages,
            'has_prev' => $params['page'] > 1
        ];
    }

    private function validateAndProcessData($input, $fields)
    {
        $data = [];
        $errors = [];

        foreach ($fields as $field) {
            $fieldKey = $field['key'];
            $fieldName = $field['name'];
            $fieldType = $field['type'];
            $isRequired = $field['required'] ?? false;
            $value = $input[$fieldKey] ?? null;

            // Check required fields
            if ($isRequired && (empty($value) && $value !== '0' && $value !== 0)) {
                $errors[] = "{$fieldName} is required";
                continue;
            }

            // Skip empty non-required fields
            if ((empty($value) && $value !== '0' && $value !== 0) && !$isRequired) {
                continue;
            }

            // Validate and process based on field type
            switch ($fieldType) {
                case 'text':
                case 'textarea':
                case 'rich_text':
                case 'email':
                case 'url':
                    $data[$fieldKey] = is_string($value) ? trim($value) : (string)$value;
                    
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
                    $data[$fieldKey] = is_numeric($value) ? floatval($value) : 0;
                    break;

                case 'boolean':
                    $data[$fieldKey] = !empty($value) || $value === 'true' || $value === true;
                    break;

                case 'date':
                case 'datetime':
                    $data[$fieldKey] = $value; // Assume valid date format
                    break;

                case 'select':
                    $options = $field['settings']['options'] ?? [];
                    if (!in_array($value, $options)) {
                        $errors[] = "{$fieldName} must be one of the allowed options";
                    } else {
                        $data[$fieldKey] = $value;
                    }
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
}
?>