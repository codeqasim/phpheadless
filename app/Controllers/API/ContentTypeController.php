<?php
// app/Controllers/API/ContentTypeController.php

namespace App\Controllers\API;

use App\Core\Database;
use App\Helpers\Response;
use Exception;

class ContentTypeController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Enable CORS for all API requests
        Response::cors();
    }

    public function index()
    {
        try {
            // Get all content types
            $contentTypes = $this->db->select('content_types', '*', [
                'ORDER' => ['name' => 'ASC']
            ]);

            $processedTypes = [];
            foreach ($contentTypes as $type) {
                $fields = json_decode($type['fields'], true) ?: [];
                $settings = json_decode($type['settings'], true) ?: [];
                
                // Get entry count for this content type
                $entryCount = $this->db->count('content_entries', [
                    'content_type_id' => $type['id'],
                    'status' => 'published'
                ]);

                $processedTypes[] = [
                    'id' => $type['id'],
                    'name' => $type['name'],
                    'slug' => $type['slug'],
                    'description' => $type['description'],
                    'fields' => $this->processFieldsForAPI($fields),
                    'api_endpoint' => "/api/{$type['slug']}",
                    'entry_count' => $entryCount,
                    'created_at' => $type['created_at'],
                    'updated_at' => $type['updated_at']
                ];
            }

            Response::json([
                'data' => $processedTypes,
                'meta' => [
                    'total' => count($processedTypes),
                    'endpoints' => $this->generateEndpointsList($processedTypes)
                ]
            ]);

        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function show($slug)
    {
        try {
            // Get content type by slug
            $contentType = $this->db->get('content_types', '*', ['slug' => $slug]);

            if (!$contentType) {
                Response::notFound("Content type '{$slug}' not found");
            }

            $fields = json_decode($contentType['fields'], true) ?: [];
            $settings = json_decode($contentType['settings'], true) ?: [];
            
            // Get entry count and recent entries
            $entryCount = $this->db->count('content_entries', [
                'content_type_id' => $contentType['id'],
                'status' => 'published'
            ]);

            $recentEntries = $this->db->select('content_entries', [
                'id',
                'created_at',
                'updated_at'
            ], [
                'content_type_id' => $contentType['id'],
                'status' => 'published',
                'ORDER' => ['created_at' => 'DESC'],
                'LIMIT' => 5
            ]);

            Response::json([
                'data' => [
                    'id' => $contentType['id'],
                    'name' => $contentType['name'],
                    'slug' => $contentType['slug'],
                    'description' => $contentType['description'],
                    'fields' => $this->processFieldsForAPI($fields),
                    'api_endpoints' => [
                        'list' => [
                            'method' => 'GET',
                            'url' => "/api/{$contentType['slug']}",
                            'description' => 'Get all entries for this content type'
                        ],
                        'show' => [
                            'method' => 'GET',
                            'url' => "/api/{$contentType['slug']}/{id}",
                            'description' => 'Get a single entry by ID'
                        ],
                        'create' => [
                            'method' => 'POST',
                            'url' => "/api/{$contentType['slug']}",
                            'description' => 'Create a new entry'
                        ],
                        'update' => [
                            'method' => 'PUT',
                            'url' => "/api/{$contentType['slug']}/{id}",
                            'description' => 'Update an existing entry'
                        ],
                        'delete' => [
                            'method' => 'DELETE',
                            'url' => "/api/{$contentType['slug']}/{id}",
                            'description' => 'Delete an entry'
                        ]
                    ],
                    'query_parameters' => [
                        'list' => [
                            'page' => 'Page number (default: 1)',
                            'limit' => 'Items per page (default: 10, max: 100)',
                            'sort' => 'Field to sort by (default: created_at)',
                            'order' => 'Sort order: ASC or DESC (default: DESC)',
                            'status' => 'Filter by status: published, draft, archived, all (default: published)',
                            'fields' => 'Comma-separated list of fields to return',
                            'filter[field]' => 'Filter by field value'
                        ]
                    ],
                    'entry_count' => $entryCount,
                    'recent_entries' => $recentEntries,
                    'created_at' => $contentType['created_at'],
                    'updated_at' => $contentType['updated_at']
                ]
            ]);

        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    private function processFieldsForAPI($fields)
    {
        $processedFields = [];
        
        foreach ($fields as $field) {
            $processedField = [
                'key' => $field['key'],
                'name' => $field['name'],
                'type' => $field['type'],
                'required' => $field['required'] ?? false
            ];

            // Add type-specific information
            switch ($field['type']) {
                case 'text':
                case 'email':
                case 'url':
                    $processedField['max_length'] = $field['settings']['max_length'] ?? 255;
                    break;
                case 'textarea':
                case 'rich_text':
                    $processedField['max_length'] = $field['settings']['max_length'] ?? 5000;
                    break;
                case 'number':
                    $processedField['min'] = $field['settings']['min'] ?? null;
                    $processedField['max'] = $field['settings']['max'] ?? null;
                    break;
                case 'select':
                    $processedField['options'] = $field['settings']['options'] ?? [];
                    break;
            }

            if (!empty($field['help_text'])) {
                $processedField['help_text'] = $field['help_text'];
            }

            $processedFields[] = $processedField;
        }

        return $processedFields;
    }

    private function generateEndpointsList($contentTypes)
    {
        $endpoints = [];
        
        foreach ($contentTypes as $type) {
            $slug = $type['slug'];
            $endpoints[] = [
                'content_type' => $type['name'],
                'endpoints' => [
                    "GET /api/{$slug}",
                    "GET /api/{$slug}/{id}",
                    "POST /api/{$slug}",
                    "PUT /api/{$slug}/{id}",
                    "DELETE /api/{$slug}/{id}"
                ]
            ];
        }

        return $endpoints;
    }
}
?>