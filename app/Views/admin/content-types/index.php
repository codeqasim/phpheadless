<?php
// app/Views/admin/content-types/index.php
ob_start();
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Content Types</h1>
        <p class="text-gray-600 mt-1">Define the structure of your content with custom fields</p>
    </div>
    <a href="/admin/content-types/create" 
       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
        <i class="fas fa-plus mr-2"></i>Create Content Type
    </a>
</div>

<?php if (empty($contentTypes)): ?>
<!-- Empty State -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-shapes text-gray-400 text-2xl"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Content Types Yet</h3>
    <p class="text-gray-600 mb-6 max-w-md mx-auto">
        Content types define the structure of your data. Create your first content type to start building your API.
    </p>
    <a href="/admin/content-types/create" 
       class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
        <i class="fas fa-plus mr-2"></i>Create Your First Content Type
    </a>
</div>

<?php else: ?>
<!-- Content Types Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($contentTypes as $contentType): ?>
    <?php 
        $fields = json_decode($contentType['fields'], true) ?: [];
        $settings = json_decode($contentType['settings'], true) ?: [];
        $fieldsCount = count($fields);
    ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
        <!-- Header -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <?= htmlspecialchars($contentType['name']) ?>
                    </h3>
                    <p class="text-sm text-gray-600 mb-3">
                        <?= htmlspecialchars($contentType['description'] ?: 'No description provided') ?>
                    </p>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span>
                            <i class="fas fa-list mr-1"></i>
                            <?= $fieldsCount ?> field<?= $fieldsCount !== 1 ? 's' : '' ?>
                        </span>
                        <span>
                            <i class="fas fa-link mr-1"></i>
                            /api/<?= htmlspecialchars($contentType['slug']) ?>
                        </span>
                    </div>
                </div>
                <div class="flex items-center space-x-2 ml-4">
                    <!-- API Status -->
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                               <?= ($settings['api_enabled'] ?? true) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                        <?= ($settings['api_enabled'] ?? true) ? 'API Active' : 'API Disabled' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Fields Preview -->
        <div class="p-6">
            <?php if (empty($fields)): ?>
            <p class="text-gray-500 text-sm italic">No fields defined</p>
            <?php else: ?>
            <div class="space-y-2">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Fields:</h4>
                <div class="space-y-2">
                    <?php foreach (array_slice($fields, 0, 3) as $field): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-900"><?= htmlspecialchars($field['name']) ?></span>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700">
                            <?= htmlspecialchars($field['type']) ?>
                            <?= $field['required'] ? ' *' : '' ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($fields) > 3): ?>
                    <div class="text-sm text-gray-500 text-center pt-2">
                        +<?= count($fields) - 3 ?> more field<?= count($fields) - 3 !== 1 ? 's' : '' ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="/admin/content-types/<?= $contentType['id'] ?>" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                    <a href="/admin/content-types/<?= $contentType['id'] ?>/edit" 
                       class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                    <a href="/admin/content?type=<?= $contentType['slug'] ?>" 
                       class="text-green-600 hover:text-green-800 text-sm font-medium">
                        <i class="fas fa-file-alt mr-1"></i>Content
                    </a>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="/api/<?= htmlspecialchars($contentType['slug']) ?>" 
                       target="_blank"
                       class="text-gray-400 hover:text-gray-600 text-sm" 
                       title="View API">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    <button onclick="deleteContentType(<?= $contentType['id'] ?>, '<?= htmlspecialchars($contentType['name']) ?>')"
                            class="text-gray-400 hover:text-red-600 text-sm" 
                            title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Quick Stats -->
<div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="text-center">
            <div class="text-2xl font-bold text-blue-600"><?= count($contentTypes) ?></div>
            <div class="text-sm text-gray-600">Content Types</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-green-600">
                <?= array_sum(array_map(function($ct) { 
                    return count(json_decode($ct['fields'], true) ?: []); 
                }, $contentTypes)) ?>
            </div>
            <div class="text-sm text-gray-600">Total Fields</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-purple-600">
                <?= count(array_filter($contentTypes, function($ct) { 
                    $settings = json_decode($ct['settings'], true) ?: [];
                    return $settings['api_enabled'] ?? true; 
                })) ?>
            </div>
            <div class="text-sm text-gray-600">API Endpoints</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-orange-600">
                <?= count(array_filter($contentTypes, function($ct) { 
                    return !empty($ct['description']); 
                })) ?>
            </div>
            <div class="text-sm text-gray-600">Documented</div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function deleteContentType(id, name) {
    if (confirm(`Are you sure you want to delete the content type "${name}"?\n\nThis action cannot be undone and will also delete all related content entries.`)) {
        fetch(`/admin/content-types/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error deleting content type: ' + error.message);
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>