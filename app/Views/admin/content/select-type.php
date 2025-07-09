<?php
// app/Views/admin/content/select-type.php
ob_start();
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Select Content Type</h1>
        <p class="text-gray-600 mt-1">Choose the type of content you want to create</p>
    </div>
    <a href="/admin/content" 
       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Back to Content
    </a>
</div>

<?php if (empty($contentTypes)): ?>
<!-- No Content Types -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-shapes text-gray-400 text-2xl"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Content Types Available</h3>
    <p class="text-gray-600 mb-6 max-w-md mx-auto">
        You need to create at least one content type before you can add content entries.
    </p>
    <a href="/admin/content-types/create" 
       class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
        <i class="fas fa-shapes mr-2"></i>Create Your First Content Type
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
    
    <a href="/admin/content/create?type=<?= urlencode($contentType['slug']) ?>" 
       class="block bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all">
        
        <!-- Header -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900">
                    <?= htmlspecialchars($contentType['name']) ?>
                </h3>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-plus text-blue-600"></i>
                </div>
            </div>
            
            <?php if (!empty($contentType['description'])): ?>
            <p class="text-sm text-gray-600 mb-3">
                <?= htmlspecialchars($contentType['description']) ?>
            </p>
            <?php endif; ?>
            
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

        <!-- Fields Preview -->
        <div class="p-6">
            <?php if (empty($fields)): ?>
            <p class="text-gray-500 text-sm italic">No fields defined</p>
            <?php else: ?>
            <div class="space-y-2">
                <h4 class="text-sm font-medium text-gray-700 mb-3">You'll be creating:</h4>
                <div class="space-y-2">
                    <?php foreach (array_slice($fields, 0, 4) as $field): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-900">
                            <?= htmlspecialchars($field['name']) ?>
                            <?php if ($field['required']): ?>
                            <span class="text-red-500">*</span>
                            <?php endif; ?>
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                            <?= htmlspecialchars($field['type']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($fields) > 4): ?>
                    <div class="text-sm text-gray-500 text-center pt-2">
                        +<?= count($fields) - 4 ?> more field<?= count($fields) - 4 !== 1 ? 's' : '' ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Call to Action -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
            <div class="flex items-center justify-center">
                <span class="text-blue-600 font-medium text-sm">
                    <i class="fas fa-plus mr-2"></i>Create <?= htmlspecialchars($contentType['name']) ?>
                </span>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<!-- Quick Actions -->
<div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Need More Options?</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="/admin/content-types" 
           class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-shapes text-blue-600"></i>
            </div>
            <div>
                <h4 class="font-medium text-gray-900">Manage Content Types</h4>
                <p class="text-sm text-gray-600">View, edit, or create new content types</p>
            </div>
        </a>
        
        <a href="/admin/content-types/create" 
           class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-plus text-green-600"></i>
            </div>
            <div>
                <h4 class="font-medium text-gray-900">Create New Type</h4>
                <p class="text-sm text-gray-600">Define a new content structure</p>
            </div>
        </a>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>