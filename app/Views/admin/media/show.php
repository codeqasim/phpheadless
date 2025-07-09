<?php
// app/Views/admin/media/show.php
ob_start();
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">File Details</h1>
        <nav class="text-sm text-gray-600 mt-1">
            <a href="/admin/media" class="hover:text-gray-900">Media Library</a>
            <span class="mx-2">/</span>
            <span><?= htmlspecialchars($file['original_name']) ?></span>
        </nav>
    </div>
    <div class="flex space-x-3">
        <button onclick="copyUrl('<?= htmlspecialchars($file['url']) ?>')" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
            <i class="fas fa-link mr-2"></i>Copy URL
        </button>
        <a href="<?= htmlspecialchars($file['url']) ?>" target="_blank"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
            <i class="fas fa-external-link-alt mr-2"></i>Open
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- File Preview -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Preview</h2>
        
        <div class="flex items-center justify-center bg-gray-50 rounded-lg p-8 min-h-[300px]">
            <?php if ($file['is_image']): ?>
                <img src="<?= htmlspecialchars($file['url']) ?>" 
                     alt="<?= htmlspecialchars($file['alt_text'] ?: $file['original_name']) ?>"
                     class="max-w-full max-h-[400px] object-contain rounded-lg shadow-sm">
            <?php else: ?>
                <div class="text-center">
                    <i class="<?= $this->getFileIcon($file['mime_type']) ?? 'fas fa-file' ?> text-gray-400 text-6xl mb-4"></i>
                    <p class="text-gray-600">No preview available</p>
                    <p class="text-sm text-gray-500 mt-2">Click "Open" to view file</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- File Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">File Information</h2>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File Name</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?= htmlspecialchars($file['original_name']) ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File Type</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?= htmlspecialchars($file['mime_type']) ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File Size</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?= htmlspecialchars($file['size_formatted']) ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Date</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?= date('F j, Y \a\t g:i A', strtotime($file['created_at'])) ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                <div class="flex">
                    <input type="text" value="<?= htmlspecialchars($file['full_url']) ?>" 
                           readonly class="flex-1 text-sm text-gray-900 bg-gray-50 p-2 rounded-l border-r-0 focus:outline-none">
                    <button onclick="copyUrl('<?= htmlspecialchars($file['url']) ?>')"
                            class="bg-gray-200 hover:bg-gray-300 px-3 py-2 rounded-r text-sm">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            
            <!-- Alt Text Form -->
            <form method="POST" action="/admin/media/<?= $file['id'] ?>" class="space-y-3">
                <input type="hidden" name="_method" value="PUT">
                <div>
                    <label for="alt_text" class="block text-sm font-medium text-gray-700 mb-1">Alt Text</label>
                    <input type="text" id="alt_text" name="alt_text" 
                           value="<?= htmlspecialchars($file['alt_text']) ?>"
                           placeholder="Describe this image for accessibility"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    Update Alt Text
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
    
    <div class="flex space-x-4">
        <button onclick="copyUrl('<?= htmlspecialchars($file['url']) ?>')" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
            <i class="fas fa-link mr-2"></i>Copy URL
        </button>
        
        <a href="<?= htmlspecialchars($file['url']) ?>" download
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
            <i class="fas fa-download mr-2"></i>Download
        </a>
        
        <button onclick="deleteFile(<?= $file['id'] ?>, '<?= htmlspecialchars($file['original_name']) ?>')" 
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
            <i class="fas fa-trash mr-2"></i>Delete
        </button>
    </div>
</div>

<script>
function copyUrl(url) {
    const fullUrl = window.location.origin + url;
    navigator.clipboard.writeText(fullUrl).then(() => {
        // Show temporary success message
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-2"></i>Copied!';
        button.className = button.className.replace('bg-blue-600 hover:bg-blue-700', 'bg-green-600 hover:bg-green-700');
        
        setTimeout(() => {
            button.innerHTML = originalContent;
            button.className = button.className.replace('bg-green-600 hover:bg-green-700', 'bg-blue-600 hover:bg-blue-700');
        }, 2000);
    }).catch(() => {
        alert('Failed to copy URL to clipboard');
    });
}

function deleteFile(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone and you will be redirected to the media library.`)) {
        fetch(`/admin/media/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/admin/media';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error deleting file: ' + error.message);
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>