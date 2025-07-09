<?php
// app/Views/admin/api/documentation.php
ob_start();
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">API Documentation</h1>
        <p class="text-gray-600 mt-1">Complete guide to using your headless CMS API</p>
    </div>
    <div class="flex items-center space-x-4">
        <a href="/api/content-types" target="_blank"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <i class="fas fa-external-link-alt mr-2"></i>View Live API
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <!-- Main Content -->
    <div class="lg:col-span-3 space-y-8">
        
        <!-- Getting Started -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">🚀 Getting Started</h2>
            
            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Base URL</h3>
                    <div class="bg-gray-100 rounded-md p-3 font-mono text-sm">
                        <?= htmlspecialchars($baseUrl) ?>/api
                    </div>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Content Types Available</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php foreach ($contentTypes as $type): ?>
                        <div class="border border-gray-200 rounded-md p-3">
                            <div class="font-medium text-gray-900"><?= htmlspecialchars($type['name']) ?></div>
                            <div class="text-sm text-blue-600 font-mono">/api/<?= htmlspecialchars($type['slug']) ?></div>
                            <?php if ($type['description']): ?>
                            <div class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($type['description']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authentication -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">🔐 Authentication</h2>
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <p class="text-blue-800 text-sm">
                    <strong>Public API:</strong> Currently, all published content is publicly accessible. 
                    Authentication will be added in future updates for protected content and write operations.
                </p>
            </div>
        </div>

        <!-- Endpoints -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">📡 Available Endpoints</h2>
            
            <!-- Content Types Endpoint -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Content Types</h3>
                <div class="space-y-4">
                    <div class="border border-gray-200 rounded-md p-4">
                        <div class="flex items-center mb-2">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium mr-3">GET</span>
                            <code class="text-sm font-mono">/api/content-types</code>
                        </div>
                        <p class="text-gray-600 text-sm mb-3">Get all available content types and their schemas</p>
                        <button onclick="showExample('content-types-example')" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Show Example →
                        </button>
                        <div id="content-types-example" class="hidden mt-4 bg-gray-900 text-green-400 p-4 rounded-md text-sm overflow-x-auto">
<pre>curl -X GET "<?= $baseUrl ?>/api/content-types"

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Posts",
      "slug": "posts",
      "description": "Blog posts content type",
      "fields": [
        {
          "key": "title",
          "name": "Title",
          "type": "text",
          "required": true
        }
      ],
      "api_endpoint": "/api/posts",
      "entry_count": 5
    }
  ]
}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Content Endpoints -->
            <?php if (!empty($contentTypes)): ?>
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Content Entries</h3>
                <p class="text-gray-600 text-sm mb-4">Replace <code>{content-type}</code> with any of your content type slugs</p>
                
                <div class="space-y-4">
                    <!-- GET List -->
                    <div class="border border-gray-200 rounded-md p-4">
                        <div class="flex items-center mb-2">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium mr-3">GET</span>
                            <code class="text-sm font-mono">/api/{content-type}</code>
                        </div>
                        <p class="text-gray-600 text-sm mb-3">Get all entries for a content type</p>
                        <div class="text-xs text-gray-500 mb-3">
                            <strong>Query Parameters:</strong> page, limit, sort, order, status, fields, filter[field]
                        </div>
                        <button onclick="showExample('get-list-example')" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Show Example →
                        </button>
                        <div id="get-list-example" class="hidden mt-4 bg-gray-900 text-green-400 p-4 rounded-md text-sm overflow-x-auto">
<pre>curl -X GET "<?= $baseUrl ?>/api/<?= $contentTypes[0]['slug'] ?? 'posts' ?>?page=1&limit=10&sort=created_at&order=DESC"

Response:
{
  "data": [
    {
      "id": 1,
      "data": {
        "title": "My First Post",
        "content": "This is the content..."
      },
      "meta": {
        "status": "published",
        "created_at": "2024-01-01 12:00:00",
        "updated_at": "2024-01-01 12:00:00"
      }
    }
  ],
  "meta": {
    "total": 1,
    "count": 1,
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total_pages": 1
    }
  }
}</pre>
                        </div>
                    </div>

                    <!-- GET Single -->
                    <div class="border border-gray-200 rounded-md p-4">
                        <div class="flex items-center mb-2">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium mr-3">GET</span>
                            <code class="text-sm font-mono">/api/{content-type}/{id}</code>
                        </div>
                        <p class="text-gray-600 text-sm mb-3">Get a single entry by ID</p>
                        <button onclick="showExample('get-single-example')" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Show Example →
                        </button>
                        <div id="get-single-example" class="hidden mt-4 bg-gray-900 text-green-400 p-4 rounded-md text-sm overflow-x-auto">
<pre>curl -X GET "<?= $baseUrl ?>/api/<?= $contentTypes[0]['slug'] ?? 'posts' ?>/1"

Response:
{
  "data": {
    "id": 1,
    "data": {
      "title": "My First Post",
      "content": "This is the content..."
    },
    "meta": {
      "status": "published",
      "created_at": "2024-01-01 12:00:00",
      "updated_at": "2024-01-01 12:00:00"
    }
  }
}</pre>
                        </div>
                    </div>

                    <!-- POST Create -->
                    <div class="border border-gray-200 rounded-md p-4">
                        <div class="flex items-center mb-2">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium mr-3">POST</span>
                            <code class="text-sm font-mono">/api/{content-type}</code>
                        </div>
                        <p class="text-gray-600 text-sm mb-3">Create a new entry</p>
                        <button onclick="showExample('post-create-example')" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Show Example →
                        </button>
                        <div id="post-create-example" class="hidden mt-4 bg-gray-900 text-green-400 p-4 rounded-md text-sm overflow-x-auto">
<pre>curl -X POST "<?= $baseUrl ?>/api/<?= $contentTypes[0]['slug'] ?? 'posts' ?>" \
  -H "Content-Type: application/json" \
  -d '{
    <?php if (!empty($contentTypes[0]['fields'])): ?>
    <?php foreach ($contentTypes[0]['fields'] as $index => $field): ?>
    "<?= $field['key'] ?>": "<?= $field['type'] === 'boolean' ? 'true' : 'Sample ' . $field['name'] ?>"<?= $index < count($contentTypes[0]['fields']) - 1 ? ',' : '' ?>
    <?php endforeach; ?>
    <?php else: ?>
    "title": "New Post Title",
    "content": "Post content here"
    <?php endif; ?>,
    "status": "published"
  }'</pre>
                        </div>
                    </div>

                    <!-- PUT Update -->
                    <div class="border border-gray-200 rounded-md p-4">
                        <div class="flex items-center mb-2">
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium mr-3">PUT</span>
                            <code class="text-sm font-mono">/api/{content-type}/{id}</code>
                        </div>
                        <p class="text-gray-600 text-sm mb-3">Update an existing entry</p>
                        <button onclick="showExample('put-update-example')" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Show Example →
                        </button>
                        <div id="put-update-example" class="hidden mt-4 bg-gray-900 text-green-400 p-4 rounded-md text-sm overflow-x-auto">
<pre>curl -X PUT "<?= $baseUrl ?>/api/<?= $contentTypes[0]['slug'] ?? 'posts' ?>/1" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Post Title",
    "status": "published"
  }'</pre>
                        </div>
                    </div>

                    <!-- DELETE -->
                    <div class="border border-gray-200 rounded-md p-4">
                        <div class="flex items-center mb-2">
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium mr-3">DELETE</span>
                            <code class="text-sm font-mono">/api/{content-type}/{id}</code>
                        </div>
                        <p class="text-gray-600 text-sm mb-3">Delete an entry</p>
                        <button onclick="showExample('delete-example')" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Show Example →
                        </button>
                        <div id="delete-example" class="hidden mt-4 bg-gray-900 text-green-400 p-4 rounded-md text-sm overflow-x-auto">
<pre>curl -X DELETE "<?= $baseUrl ?>/api/<?= $contentTypes[0]['slug'] ?? 'posts' ?>/1"

Response:
{
  "message": "Content deleted successfully"
}</pre>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Query Parameters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">🔍 Query Parameters</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3 font-medium text-gray-700">Parameter</th>
                            <th class="text-left p-3 font-medium text-gray-700">Description</th>
                            <th class="text-left p-3 font-medium text-gray-700">Example</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="p-3 font-mono text-blue-600">page</td>
                            <td class="p-3">Page number for pagination (default: 1)</td>
                            <td class="p-3 font-mono text-gray-600">?page=2</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-mono text-blue-600">limit</td>
                            <td class="p-3">Items per page (default: 10, max: 100)</td>
                            <td class="p-3 font-mono text-gray-600">?limit=20</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-mono text-blue-600">sort</td>
                            <td class="p-3">Field to sort by</td>
                            <td class="p-3 font-mono text-gray-600">?sort=title</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-mono text-blue-600">order</td>
                            <td class="p-3">Sort order: ASC or DESC (default: DESC)</td>
                            <td class="p-3 font-mono text-gray-600">?order=ASC</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-mono text-blue-600">status</td>
                            <td class="p-3">Filter by status (default: published)</td>
                            <td class="p-3 font-mono text-gray-600">?status=all</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-mono text-blue-600">fields</td>
                            <td class="p-3">Comma-separated list of fields to return</td>
                            <td class="p-3 font-mono text-gray-600">?fields=title,content</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-mono text-blue-600">filter[field]</td>
                            <td class="p-3">Filter by specific field value</td>
                            <td class="p-3 font-mono text-gray-600">?filter[category]=news</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Response Format -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">📄 Response Format</h2>
            
            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Success Response</h3>
                    <div class="bg-gray-900 text-green-400 p-4 rounded-md text-sm">
<pre>{
  "data": [...],
  "meta": {
    "total": 10,
    "count": 10,
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total_pages": 1,
      "has_next": false,
      "has_prev": false
    }
  }
}</pre>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Error Response</h3>
                    <div class="bg-gray-900 text-red-400 p-4 rounded-md text-sm">
<pre>{
  "success": false,
  "message": "Content type 'invalid' not found"
}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1">
        <div class="sticky top-6 space-y-6">
            <!-- Quick Links -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Quick Links</h3>
                <div class="space-y-3 text-sm">
                    <a href="#getting-started" class="block text-blue-600 hover:text-blue-800">Getting Started</a>
                    <a href="#authentication" class="block text-blue-600 hover:text-blue-800">Authentication</a>
                    <a href="#endpoints" class="block text-blue-600 hover:text-blue-800">Endpoints</a>
                    <a href="#query-parameters" class="block text-blue-600 hover:text-blue-800">Query Parameters</a>
                    <a href="#response-format" class="block text-blue-600 hover:text-blue-800">Response Format</a>
                </div>
            </div>

            <!-- Live API Links -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Live API</h3>
                <div class="space-y-2 text-sm">
                    <a href="/api/content-types" target="_blank" 
                       class="block text-green-600 hover:text-green-800 font-mono">
                        GET /api/content-types
                    </a>
                    <?php foreach (array_slice($contentTypes, 0, 3) as $type): ?>
                    <a href="/api/<?= urlencode($type['slug']) ?>" target="_blank" 
                       class="block text-green-600 hover:text-green-800 font-mono">
                        GET /api/<?= htmlspecialchars($type['slug']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Support -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-800 mb-2">Need Help?</h3>
                <p class="text-blue-700 text-sm">
                    This API is auto-generated from your content types. 
                    Create new content types to automatically get new API endpoints!
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function showExample(elementId) {
    const element = document.getElementById(elementId);
    if (element.classList.contains('hidden')) {
        element.classList.remove('hidden');
    } else {
        element.classList.add('hidden');
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>