<?php
// app/Views/admin/media/index.php
ob_start();
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Media Library</h1>
        <p class="text-gray-600 mt-1"><?= $totalFiles ?> files • Upload and manage your media</p>
    </div>
    <div class="flex space-x-3">
        <button id="bulkDeleteBtn" onclick="bulkDeleteSelected()" 
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors hidden">
            <i class="fas fa-trash mr-2"></i>Delete Selected (<span id="selectedCount">0</span>)
        </button>
        <button onclick="openUploadModal()" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            <i class="fas fa-upload mr-2"></i>Upload Files
        </button>
    </div>
</div>

<!-- Stats and Filters -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4">
        <?php foreach ($typeStats as $stat): ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600"><?= htmlspecialchars($stat['type']) ?></p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stat['count'] ?></p>
                    <p class="text-xs text-gray-500"><?= $stat['total_size_formatted'] ?></p>
                </div>
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file text-blue-600"></i>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h3 class="font-semibold text-gray-900 mb-4">Filters</h3>
        <form method="GET" class="space-y-3">
            <!-- Search -->
            <div>
                <input type="text" name="search" value="<?= htmlspecialchars($currentSearch) ?>" 
                       placeholder="Search files..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            
            <!-- File Type -->
            <div>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">All Types</option>
                    <option value="image/" <?= strpos($currentType, 'image/') === 0 ? 'selected' : '' ?>>Images</option>
                    <option value="application/pdf" <?= $currentType === 'application/pdf' ? 'selected' : '' ?>>PDFs</option>
                    <option value="application/" <?= strpos($currentType, 'application/') === 0 && $currentType !== 'application/pdf' ? 'selected' : '' ?>>Documents</option>
                </select>
            </div>
            
            <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors">
                Apply Filters
            </button>
            
            <?php if ($currentSearch || $currentType): ?>
            <a href="/admin/media" class="block w-full text-center text-gray-600 hover:text-gray-800 text-sm">
                Clear Filters
            </a>
            <?php endif; ?>
            
            <!-- Bulk Actions -->
            <div class="pt-3 border-t border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Bulk Actions</span>
                    <button type="button" onclick="toggleSelectAll()" id="selectAllBtn"
                            class="text-xs text-blue-600 hover:text-blue-800">
                        Select All
                    </button>
                </div>
                <button type="button" onclick="clearAllSelections()" id="clearAllBtn"
                        class="w-full bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors mb-2 hidden">
                    Clear Selection
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($files)): ?>
<!-- Empty State -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-images text-gray-400 text-2xl"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">
        <?= $currentSearch || $currentType ? 'No files match your filters' : 'No files uploaded yet' ?>
    </h3>
    <p class="text-gray-600 mb-6 max-w-md mx-auto">
        <?= $currentSearch || $currentType 
            ? 'Try adjusting your search or filters to find what you\'re looking for.'
            : 'Upload your first file to start building your media library.' ?>
    </p>
    
    <?php if (!$currentSearch && !$currentType): ?>
    <button onclick="openUploadModal()" 
            class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
        <i class="fas fa-upload mr-2"></i>Upload Your First File
    </button>
    <?php endif; ?>
</div>

<?php else: ?>
<!-- Files Grid -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4" id="mediaGrid">
        <?php foreach ($files as $file): ?>
        <div class="group border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow file-item" 
             data-file-id="<?= $file['id'] ?>">
            <!-- File Preview -->
            <div class="aspect-square bg-gray-100 flex items-center justify-center relative">
                <!-- Selection Checkbox -->
                <div class="absolute top-2 left-2 z-10">
                    <input type="checkbox" class="file-checkbox w-4 h-4 text-blue-600 bg-white border-2 border-gray-300 rounded focus:ring-blue-500" 
                           data-file-id="<?= $file['id'] ?>" onchange="updateBulkActions()">
                </div>
                
                <?php if ($file['is_image']): ?>
                    <img src="<?= htmlspecialchars($file['url']) ?>" 
                         alt="<?= htmlspecialchars($file['alt_text'] ?: $file['original_name']) ?>"
                         class="w-full h-full object-cover"
                         loading="lazy">
                <?php else: ?>
                    <i class="<?= $file['icon'] ?> text-gray-400 text-3xl"></i>
                <?php endif; ?>
                
                <!-- Actions Overlay -->
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-2">
                    <button onclick="viewFile(<?= $file['id'] ?>)" 
                            class="bg-white text-gray-900 p-2 rounded-full hover:bg-gray-100 transition-colors"
                            title="View">
                        <i class="fas fa-eye text-sm"></i>
                    </button>
                    <button onclick="copyUrl('<?= htmlspecialchars($file['url']) ?>')" 
                            class="bg-white text-gray-900 p-2 rounded-full hover:bg-gray-100 transition-colors"
                            title="Copy URL">
                        <i class="fas fa-link text-sm"></i>
                    </button>
                    <button onclick="deleteFile(<?= $file['id'] ?>, '<?= htmlspecialchars($file['original_name']) ?>')" 
                            class="bg-red-600 text-white p-2 rounded-full hover:bg-red-700 transition-colors"
                            title="Delete">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </div>
            </div>
            
            <!-- File Info -->
            <div class="p-3">
                <div class="text-sm font-medium text-gray-900 truncate" title="<?= htmlspecialchars($file['original_name']) ?>">
                    <?= htmlspecialchars($file['original_name']) ?>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    <?= $file['size_formatted'] ?> • <?= date('M j, Y', strtotime($file['created_at'])) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
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

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Upload Files</h2>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Drop Zone -->
            <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors">
                <i class="fas fa-cloud-upload-alt text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-600 mb-4">Drag and drop files here, or click to select</p>
                <input type="file" id="fileInput" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx" class="hidden">
                <button onclick="document.getElementById('fileInput').click()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Select Files
                </button>
                <p class="text-xs text-gray-500 mt-2">
                    Supported: JPG, PNG, GIF, WebP, PDF, DOC, DOCX, XLS, XLSX (Max 10MB)
                </p>
            </div>
            
            <!-- Upload Progress -->
            <div id="uploadProgress" class="hidden mt-6 space-y-3">
                <h3 class="font-semibold text-gray-900">Uploading Files</h3>
                <div id="uploadList" class="space-y-2"></div>
            </div>
            
            <!-- Error Messages -->
            <div id="uploadErrors" class="hidden mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-400 mr-2 mt-0.5"></i>
                    <div class="text-sm text-red-700" id="errorList"></div>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex justify-end space-x-3">
            <button onclick="closeUploadModal()" 
                    class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium">
                Close
            </button>
            <button id="uploadAllBtn" onclick="uploadAllFiles()" 
                    class="hidden bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">
                Upload All
            </button>
        </div>
    </div>
</div>

<script>
// Global state
let selectedFiles = [];
let uploadsInProgress = false;
let selectedFileIds = new Set();

// Modal functions
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
    resetUploadModal();
}

function closeUploadModal() {
    if (uploadsInProgress) {
        if (!confirm('Upload is in progress. Are you sure you want to close?')) {
            return;
        }
    }
    document.getElementById('uploadModal').classList.add('hidden');
    resetUploadModal();
}

function resetUploadModal() {
    selectedFiles = [];
    uploadsInProgress = false;
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadProgress').classList.add('hidden');
    document.getElementById('uploadErrors').classList.add('hidden');
    document.getElementById('uploadAllBtn').classList.add('hidden');
    document.getElementById('uploadList').innerHTML = '';
    document.getElementById('errorList').innerHTML = '';
}

// File selection and drag/drop
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const dropZone = document.getElementById('dropZone');
    
    fileInput.addEventListener('change', function(e) {
        handleFiles(Array.from(e.target.files));
    });

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('border-blue-400', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('border-blue-400', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('border-blue-400', 'bg-blue-50');
        
        const files = Array.from(e.dataTransfer.files);
        handleFiles(files);
    });
});

function handleFiles(files) {
    // Validate and add files
    const validFiles = [];
    const errors = [];
    
    // Allowed file types (must match server validation)
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 
                         'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                         'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    files.forEach(file => {
        if (!allowedTypes.includes(file.type)) {
            errors.push(`${file.name}: File type not allowed`);
            return;
        }
        
        if (file.size > maxSize) {
            errors.push(`${file.name}: File too large (max 10MB)`);
            return;
        }
        
        // Check if file already selected
        if (selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
            errors.push(`${file.name}: Already selected`);
            return;
        }
        
        validFiles.push(file);
    });
    
    if (errors.length > 0) {
        showErrors(errors);
    }
    
    if (validFiles.length > 0) {
        selectedFiles.push(...validFiles);
        showSelectedFiles();
        // Start upload immediately
        uploadAllFiles();
    }
}

function showSelectedFiles() {
    const uploadList = document.getElementById('uploadList');
    const uploadProgress = document.getElementById('uploadProgress');
    
    uploadList.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const fileDiv = document.createElement('div');
        fileDiv.className = 'border border-gray-200 rounded-md p-3';
        fileDiv.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-900 truncate flex-1 mr-2">${escapeHtml(file.name)}</span>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-500">${formatFileSize(file.size)}</span>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%" id="progress-${index}"></div>
            </div>
            <div class="text-xs text-gray-500 mt-1" id="status-${index}">Ready to upload</div>
        `;
        uploadList.appendChild(fileDiv);
    });
    
    if (selectedFiles.length > 0) {
        uploadProgress.classList.remove('hidden');
    }
}

function showErrors(errors) {
    const errorDiv = document.getElementById('uploadErrors');
    const errorList = document.getElementById('errorList');
    
    errorList.innerHTML = errors.map(error => `<div>• ${escapeHtml(error)}</div>`).join('');
    errorDiv.classList.remove('hidden');
    
    // Hide errors after 5 seconds
    setTimeout(() => {
        errorDiv.classList.add('hidden');
    }, 5000);
}

async function uploadAllFiles() {
    if (selectedFiles.length === 0) return;
    
    uploadsInProgress = true;
    
    let successCount = 0;
    let failCount = 0;
    
    for (let i = 0; i < selectedFiles.length; i++) {
        try {
            await uploadSingleFile(selectedFiles[i], i);
            successCount++;
        } catch (error) {
            failCount++;
            updateFileStatus(i, 'error', error.message);
        }
    }
    
    uploadsInProgress = false;
    
    // Show completion message
    if (successCount > 0) {
        // Refresh page after successful uploads
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
}

function uploadSingleFile(file, index) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file);
        
        const xhr = new XMLHttpRequest();
        
        // Update progress
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                updateFileProgress(index, percentComplete);
            }
        });
        
        xhr.addEventListener('load', function() {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    updateFileStatus(index, 'success', 'Upload complete');
                    resolve(response);
                } else {
                    updateFileStatus(index, 'error', response.message || 'Upload failed');
                    reject(new Error(response.message || 'Upload failed'));
                }
            } catch (error) {
                updateFileStatus(index, 'error', 'Invalid server response');
                reject(error);
            }
        });
        
        xhr.addEventListener('error', function() {
            updateFileStatus(index, 'error', 'Network error');
            reject(new Error('Network error'));
        });
        
        updateFileStatus(index, 'uploading', 'Uploading...');
        xhr.open('POST', '/admin/media/upload', true);
        xhr.send(formData);
    });
}

function updateFileProgress(index, percent) {
    const progressBar = document.getElementById(`progress-${index}`);
    if (progressBar) {
        progressBar.style.width = percent + '%';
    }
}

function updateFileStatus(index, status, message) {
    const statusElement = document.getElementById(`status-${index}`);
    const progressBar = document.getElementById(`progress-${index}`);
    
    if (statusElement) {
        statusElement.textContent = message;
    }
    
    if (progressBar) {
        switch (status) {
            case 'uploading':
                progressBar.className = 'bg-blue-600 h-2 rounded-full transition-all duration-300';
                break;
            case 'success':
                progressBar.className = 'bg-green-600 h-2 rounded-full transition-all duration-300';
                progressBar.style.width = '100%';
                break;
            case 'error':
                progressBar.className = 'bg-red-600 h-2 rounded-full transition-all duration-300';
                break;
        }
    }
}

// Bulk selection functions
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.file-checkbox:checked');
    const count = checkboxes.length;
    
    selectedFileIds.clear();
    checkboxes.forEach(cb => {
        selectedFileIds.add(parseInt(cb.dataset.fileId));
    });
    
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    const clearAllBtn = document.getElementById('clearAllBtn');
    
    if (count > 0) {
        bulkDeleteBtn.classList.remove('hidden');
        clearAllBtn.classList.remove('hidden');
        selectedCountSpan.textContent = count;
    } else {
        bulkDeleteBtn.classList.add('hidden');
        clearAllBtn.classList.add('hidden');
    }
}

function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.file-checkbox');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const isAllSelected = document.querySelectorAll('.file-checkbox:checked').length === checkboxes.length;
    
    checkboxes.forEach(cb => {
        cb.checked = !isAllSelected;
    });
    
    selectAllBtn.textContent = isAllSelected ? 'Select All' : 'Select None';
    updateBulkActions();
}

function clearAllSelections() {
    const checkboxes = document.querySelectorAll('.file-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = false;
    });
    updateBulkActions();
}

function bulkDeleteSelected() {
    if (selectedFileIds.size === 0) return;
    
    const fileCount = selectedFileIds.size;
    const confirmMessage = `Are you sure you want to delete ${fileCount} selected file${fileCount > 1 ? 's' : ''}?\n\nThis action cannot be undone.`;
    
    if (!confirm(confirmMessage)) return;
    
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    bulkDeleteBtn.disabled = true;
    bulkDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
    
    // Use bulk delete endpoint
    fetch('/admin/media/bulk-delete', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            ids: Array.from(selectedFileIds)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
            bulkDeleteBtn.disabled = false;
            bulkDeleteBtn.innerHTML = '<i class="fas fa-trash mr-2"></i>Delete Selected';
        }
    })
    .catch(error => {
        alert('Error during bulk delete: ' + error.message);
        bulkDeleteBtn.disabled = false;
        bulkDeleteBtn.innerHTML = '<i class="fas fa-trash mr-2"></i>Delete Selected';
    });
}

function deleteFileById(id) {
    return fetch(`/admin/media/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => ({ success: data.success, id: id }))
    .catch(error => ({ success: false, id: id, error: error.message }));
}

// Utility functions
function viewFile(id) {
    window.open(`/admin/media/${id}`, '_blank');
}

function copyUrl(url) {
    const fullUrl = window.location.origin + url;
    navigator.clipboard.writeText(fullUrl).then(() => {
        // Show temporary success message
        const button = event.target.closest('button');
        const originalIcon = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check text-sm"></i>';
        button.className = button.className.replace('bg-white text-gray-900', 'bg-green-600 text-white');
        
        setTimeout(() => {
            button.innerHTML = originalIcon;
            button.className = button.className.replace('bg-green-600 text-white', 'bg-white text-gray-900');
        }, 1000);
    }).catch(() => {
        alert('Failed to copy URL to clipboard');
    });
}

function deleteFile(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone.`)) {
        fetch(`/admin/media/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
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
            alert('Error deleting file: ' + error.message);
        });
    }
}

function formatFileSize(bytes) {
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unitIndex = 0;
    
    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }
    
    return Math.round(size * 100) / 100 + ' ' + units[unitIndex];
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('uploadModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeUploadModal();
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>