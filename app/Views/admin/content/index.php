<?php
// app/Views/admin/content/index.php
ob_start();
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            <?= $selectedContentType ? htmlspecialchars($selectedContentType['name']) . ' Entries' : 'All Content Entries' ?>
        </h1>
        <p class="text-gray-600 mt-1">
            <?= $totalEntries ?> total entries
            <?= $selectedContentType ? 'for ' . htmlspecialchars($selectedContentType['name']) : 'across all content types' ?>
        </p>
    </div>
    <div class="flex items-center space-x-4">
        <?php if ($selectedContentType): ?>
        <a href="/admin/content/create?type=<?= urlencode($selectedContentType['slug']) ?>" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            <i class="fas fa-plus mr-2"></i>Add <?= htmlspecialchars($selectedContentType['name']) ?>
        </a>
        <?php else: ?>
        <a href="/admin/content/create" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            <i class="fas fa-plus mr-2"></i>Create Content
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <label class="text-sm font-medium text-gray-700">Filter by type:</label>
            <select onchange="filterByContentType(this.value)" 
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Content Types</option>
                <?php foreach ($contentTypes as $type): ?>
                <option value="<?= htmlspecialchars($type['slug']) ?>" 
                        <?= ($selectedContentType && $selectedContentType['slug'] === $type['slug']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($type['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="flex items-center space-x-4">
            <?php if ($selectedContentType): ?>
            <a href="/admin/content" class="text-gray-600 hover:text-gray-800 text-sm">
                <i class="fas fa-times mr-1"></i>Clear Filter
            </a>
            <?php endif; ?>
            
            <div class="text-sm text-gray-500">
                Page <?= $pagination['current'] ?> of <?= $pagination['total'] ?>
            </div>
        </div>
    </div>
</div>

<?php if (empty($entries)): ?>
<!-- Empty State -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-file-alt text-gray-400 text-2xl"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">
        <?= $selectedContentType ? 'No ' . htmlspecialchars($selectedContentType['name']) . ' entries yet' : 'No content entries yet' ?>
    </h3>
    <p class="text-gray-600 mb-6 max-w-md mx-auto">
        <?= $selectedContentType 
            ? 'Start creating content for your ' . htmlspecialchars($selectedContentType['name']) . ' content type.'
            : 'Create your first content entry to populate your API.' ?>
    </p>
    
    <?php if (empty($contentTypes)): ?>
    <a href="/admin/content-types/create" 
       class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
        <i class="fas fa-shapes mr-2"></i>Create Content Type First
    </a>
    <?php elseif ($selectedContentType): ?>
    <a href="/admin/content/create?type=<?= urlencode($selectedContentType['slug']) ?>" 
       class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
        <i class="fas fa-plus mr-2"></i>Create Your First <?= htmlspecialchars($selectedContentType['name']) ?>
    </a>
    <?php else: ?>
    <a href="/admin/content/create" 
       class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
        <i class="fas fa-plus mr-2"></i>Create Your First Content Entry
    </a>
    <?php endif; ?>
</div>

<?php else: ?>
<!-- Content Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Content
                    </th>
                    <?php if (!$selectedContentType): ?>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type
                    </th>
                    <?php endif; ?>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Author
                    </th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Updated
                    </th>
                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($entries as $entry): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900 mb-1">
                                    <?= htmlspecialchars($entry['display_title']) ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    ID: <?= $entry['id'] ?>
                                    <?php if (!empty($entry['data'])): ?>
                                    • <?= count($entry['data']) ?> field<?= count($entry['data']) !== 1 ? 's' : '' ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    
                    <?php if (!$selectedContentType): ?>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?= htmlspecialchars($entry['type_name']) ?>
                        </span>
                    </td>
                    <?php endif; ?>
                    
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                   <?= $entry['status'] === 'published' ? 'bg-green-100 text-green-800' : 
                                       ($entry['status'] === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') ?>">
                            <?= ucfirst($entry['status']) ?>
                        </span>
                    </td>
                    
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <?= htmlspecialchars($entry['created_by_name'] ?? 'Unknown') ?>
                    </td>
                    
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <div><?= date('M j, Y', strtotime($entry['updated_at'])) ?></div>
                        <div class="text-xs"><?= date('g:i A', strtotime($entry['updated_at'])) ?></div>
                    </td>
                    
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="/admin/content/<?= $entry['id'] ?>" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                               title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/admin/content/<?= $entry['id'] ?>/edit" 
                               class="text-gray-600 hover:text-gray-800 text-sm font-medium"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteContent(<?= $entry['id'] ?>, '<?= htmlspecialchars($entry['display_title']) ?>')"
                                    class="text-gray-400 hover:text-red-600 text-sm"
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($pagination['total'] > 1): ?>
<div class="flex items-center justify-between mt-6">
    <div class="text-sm text-gray-700">
        Showing page <?= $pagination['current'] ?> of <?= $pagination['total'] ?>
    </div>
    
    <div class="flex items-center space-x-2">
        <?php if ($pagination['hasPrev']): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current'] - 1])) ?>" 
           class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-chevron-left mr-1"></i>Previous
        </a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $pagination['current'] - 2); $i <= min($pagination['total'], $pagination['current'] + 2); $i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
           class="px-3 py-2 border rounded-md text-sm font-medium 
                  <?= $i === $pagination['current'] 
                      ? 'bg-blue-600 text-white border-blue-600' 
                      : 'border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($pagination['hasNext']): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current'] + 1])) ?>" 
           class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
            Next<i class="fas fa-chevron-right ml-1"></i>
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<script>
function filterByContentType(slug) {
    if (slug) {
        window.location.href = '/admin/content?type=' + encodeURIComponent(slug);
    } else {
        window.location.href = '/admin/content';
    }
}

function deleteContent(id, title) {
    if (confirm(`Are you sure you want to delete "${title}"?\n\nThis action cannot be undone.`)) {
        fetch(`/admin/content/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-HTTP-Method-Override': 'DELETE'
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
            alert('Error deleting content: ' + error.message);
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>