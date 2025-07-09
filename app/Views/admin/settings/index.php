<?php
// app/Views/admin/settings/index.php
ob_start();
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Project Settings</h1>
        <p class="text-gray-600 mt-1">Configure your CMS settings and preferences</p>
    </div>
    <div class="flex space-x-3">
        <button onclick="exportSettings()" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
            <i class="fas fa-download mr-2"></i>Export Settings
        </button>
        <button onclick="document.getElementById('importFile').click()" 
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
            <i class="fas fa-upload mr-2"></i>Import Settings
        </button>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
<div class="mb-6 p-4 rounded-lg <?= $_SESSION['flash_type'] === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>">
    <div class="flex">
        <i class="fas <?= $_SESSION['flash_type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2 mt-0.5"></i>
        <span><?= htmlspecialchars($_SESSION['flash_message']) ?></span>
    </div>
</div>
<?php 
unset($_SESSION['flash_message'], $_SESSION['flash_type']); 
endif; 
?>

<!-- Settings Tabs -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200">
        <nav class="flex space-x-8 px-6">
            <button onclick="switchTab('general')" id="tab-general" 
                    class="py-4 px-1 border-b-2 border-blue-500 text-blue-600 font-medium text-sm tab-button active">
                <i class="fas fa-cog mr-2"></i>General
            </button>
            <button onclick="switchTab('api')" id="tab-api" 
                    class="py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm tab-button">
                <i class="fas fa-plug mr-2"></i>API
            </button>
            <button onclick="switchTab('upload')" id="tab-upload" 
                    class="py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm tab-button">
                <i class="fas fa-upload mr-2"></i>Upload
            </button>
            <button onclick="switchTab('cache')" id="tab-cache" 
                    class="py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm tab-button">
                <i class="fas fa-database mr-2"></i>Cache
            </button>
            <button onclick="switchTab('security')" id="tab-security" 
                    class="py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm tab-button">
                <i class="fas fa-shield-alt mr-2"></i>Security
            </button>
            <button onclick="switchTab('system')" id="tab-system" 
                    class="py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm tab-button">
                <i class="fas fa-server mr-2"></i>System
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="p-6">
        <!-- General Settings -->
        <div id="content-general" class="tab-content">
            <form method="POST" action="/admin/settings" class="space-y-6">
                <input type="hidden" name="category" value="general">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
                        <input type="text" id="site_name" name="site_name" 
                               value="<?= htmlspecialchars($settings['site_name']['value'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="cors_origins" class="block text-sm font-medium text-gray-700 mb-2">CORS Origins</label>
                    <input type="text" id="cors_origins" name="cors_origins" 
                           value="<?= htmlspecialchars($settings['cors_origins']['value'] ?? '*') ?>"
                           placeholder="* or https://example.com,https://app.example.com"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Use * for all origins or comma-separated list of domains</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                        Save API Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Upload Settings -->
        <div id="content-upload" class="tab-content hidden">
            <form method="POST" action="/admin/settings" class="space-y-6">
                <input type="hidden" name="category" value="upload">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="max_file_size" class="block text-sm font-medium text-gray-700 mb-2">Max File Size</label>
                        <input type="text" id="max_file_size" name="max_file_size" 
                               value="<?= formatFileSize($settings['upload_max_size']['value'] ?? 10485760) ?>"
                               placeholder="10MB"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Examples: 5MB, 100KB, 1GB</p>
                    </div>
                    
                    <div>
                        <label for="upload_thumbnail_size" class="block text-sm font-medium text-gray-700 mb-2">Thumbnail Size (px)</label>
                        <input type="number" id="upload_thumbnail_size" name="upload_thumbnail_size" min="100" max="800"
                               value="<?= $settings['upload_thumbnail_size']['value'] ?? 300 ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="upload_allowed_types" class="block text-sm font-medium text-gray-700 mb-2">Allowed File Types</label>
                    <input type="text" id="upload_allowed_types" name="upload_allowed_types" 
                           value="<?= htmlspecialchars($settings['upload_allowed_types']['value'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Comma-separated list of file extensions</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="upload_auto_thumbnail" name="upload_auto_thumbnail" 
                               <?= ($settings['upload_auto_thumbnail']['value'] ?? true) ? 'checked' : '' ?>
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="upload_auto_thumbnail" class="ml-2 text-sm font-medium text-gray-700">Auto-generate thumbnails</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="upload_organize_by_date" name="upload_organize_by_date" 
                               <?= ($settings['upload_organize_by_date']['value'] ?? true) ? 'checked' : '' ?>
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="upload_organize_by_date" class="ml-2 text-sm font-medium text-gray-700">Organize files by date</label>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                        Save Upload Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Cache Settings -->
        <div id="content-cache" class="tab-content hidden">
            <form method="POST" action="/admin/settings" class="space-y-6">
                <input type="hidden" name="category" value="cache">
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="cache_enabled" name="cache_enabled" 
                               <?= ($settings['cache_enabled']['value'] ?? false) ? 'checked' : '' ?>
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="cache_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable Caching</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="cache_api_responses" name="cache_api_responses" 
                               <?= ($settings['cache_api_responses']['value'] ?? false) ? 'checked' : '' ?>
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="cache_api_responses" class="ml-2 text-sm font-medium text-gray-700">Cache API Responses</label>
                    </div>
                </div>

                <div>
                    <label for="cache_duration" class="block text-sm font-medium text-gray-700 mb-2">Cache Duration (seconds)</label>
                    <input type="number" id="cache_duration" name="cache_duration" min="60" max="86400"
                           value="<?= $settings['cache_duration']['value'] ?? 3600 ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">How long to cache content (3600 = 1 hour)</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-2">Cache Management</h4>
                    <p class="text-sm text-gray-600 mb-4">Clear all cached files to free up space or force refresh.</p>
                    <button type="button" onclick="clearCache()" 
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                        <i class="fas fa-trash mr-2"></i>Clear Cache
                    </button>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                        Save Cache Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Security Settings -->
        <div id="content-security" class="tab-content hidden">
            <form method="POST" action="/admin/settings" class="space-y-6">
                <input type="hidden" name="category" value="security">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="login_attempts_limit" class="block text-sm font-medium text-gray-700 mb-2">Login Attempts Limit</label>
                        <input type="number" id="login_attempts_limit" name="login_attempts_limit" min="3" max="20"
                               value="<?= $settings['login_attempts_limit']['value'] ?? 5 ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="login_lockout_duration" class="block text-sm font-medium text-gray-700 mb-2">Lockout Duration (seconds)</label>
                        <input type="number" id="login_lockout_duration" name="login_lockout_duration" min="300" max="3600"
                               value="<?= $settings['login_lockout_duration']['value'] ?? 900 ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="session_timeout" class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (seconds)</label>
                    <input type="number" id="session_timeout" name="session_timeout" min="1800" max="28800"
                           value="<?= $settings['session_timeout']['value'] ?? 7200 ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">How long users stay logged in (7200 = 2 hours)</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="require_https" name="require_https" 
                               <?= ($settings['require_https']['value'] ?? false) ? 'checked' : '' ?>
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="require_https" class="ml-2 text-sm font-medium text-gray-700">Require HTTPS</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="enable_2fa" name="enable_2fa" 
                               <?= ($settings['enable_2fa']['value'] ?? false) ? 'checked' : '' ?>
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="enable_2fa" class="ml-2 text-sm font-medium text-gray-700">Enable Two-Factor Authentication</label>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                        Save Security Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- System Info -->
        <div id="content-system" class="tab-content hidden">
            <div class="space-y-6">
                <!-- PHP Information -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">PHP Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div><strong>Version:</strong> <?= $phpInfo['version'] ?></div>
                        <div><strong>Memory Limit:</strong> <?= $phpInfo['memory_limit'] ?></div>
                        <div><strong>Max Execution Time:</strong> <?= $phpInfo['max_execution_time'] ?>s</div>
                        <div><strong>Upload Max Size:</strong> <?= $phpInfo['upload_max_filesize'] ?></div>
                        <div><strong>Post Max Size:</strong> <?= $phpInfo['post_max_size'] ?></div>
                    </div>
                </div>

                <!-- PHP Extensions -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">PHP Extensions</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                        <?php foreach ($phpInfo['extensions'] as $ext => $loaded): ?>
                        <div class="flex items-center">
                            <i class="fas <?= $loaded ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' ?> mr-2"></i>
                            <span><?= ucfirst($ext) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- System Information -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">System Information</h3>
                    <div class="space-y-2 text-sm">
                        <div><strong>Server:</strong> <?= htmlspecialchars($systemInfo['server_software']) ?></div>
                        <div><strong>Disk Space:</strong> 
                            <?= formatFileSize($systemInfo['disk_space']['free']) ?> free of 
                            <?= formatFileSize($systemInfo['disk_space']['total']) ?> total
                        </div>
                        <div class="flex items-center">
                            <strong class="mr-2">Database:</strong>
                            <i class="fas <?= $systemInfo['database']['connection'] ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' ?> mr-1"></i>
                            <span><?= $systemInfo['database']['connection'] ? 'Connected' : 'Not Connected' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Directory Permissions -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Directory Permissions</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center">
                            <i class="fas <?= $systemInfo['directories']['uploads_writable'] ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' ?> mr-2"></i>
                            <span>Uploads Directory: <?= $systemInfo['directories']['uploads_writable'] ? 'Writable' : 'Not Writable' ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas <?= $systemInfo['directories']['cache_writable'] ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' ?> mr-2"></i>
                            <span>Cache Directory: <?= $systemInfo['directories']['cache_writable'] ? 'Writable' : 'Not Writable' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Import Form -->
<form id="importForm" method="POST" action="/admin/settings/import" enctype="multipart/form-data" class="hidden">
    <input type="file" id="importFile" name="settings_file" accept=".json" onchange="importSettings()">
</form>

<script>
function switchTab(tab) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tab).classList.remove('hidden');
    
    // Add active class to selected tab button
    const activeButton = document.getElementById('tab-' + tab);
    activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
    activeButton.classList.remove('border-transparent', 'text-gray-500');
}

function exportSettings() {
    window.location.href = '/admin/settings/export';
}

function importSettings() {
    const form = document.getElementById('importForm');
    const file = document.getElementById('importFile').files[0];
    
    if (!file) return;
    
    if (!file.name.endsWith('.json')) {
        alert('Please select a JSON file');
        return;
    }
    
    if (confirm('Are you sure you want to import these settings? This will overwrite existing settings.')) {
        form.submit();
    }
}

function clearCache() {
    if (!confirm('Are you sure you want to clear all cached files?')) {
        return;
    }
    
    fetch('/admin/settings/clear-cache', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error clearing cache: ' + error.message);
    });
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

// Initialize first tab as active
document.addEventListener('DOMContentLoaded', function() {
    switchTab('general');
});
</script>

<?php
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $unit = 0;
    
    while ($bytes >= 1024 && $unit < count($units) - 1) {
        $bytes /= 1024;
        $unit++;
    }
    
    return round($bytes, 2) . ' ' . $units[$unit];
}

$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">Admin Email</label>
                        <input type="email" id="admin_email" name="admin_email" 
                               value="<?= htmlspecialchars($settings['admin_email']['value'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="site_description" class="block text-sm font-medium text-gray-700 mb-2">Site Description</label>
                    <textarea id="site_description" name="site_description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($settings['site_description']['value'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="site_url" class="block text-sm font-medium text-gray-700 mb-2">Site URL</label>
                        <input type="url" id="site_url" name="site_url" 
                               value="<?= htmlspecialchars($settings['site_url']['value'] ?? '') ?>"
                               placeholder="https://example.com"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                        <select id="timezone" name="timezone" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php
                            $timezones = ['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Asia/Tokyo'];
                            $currentTz = $settings['timezone']['value'] ?? 'UTC';
                            foreach ($timezones as $tz): ?>
                                <option value="<?= $tz ?>" <?= $tz === $currentTz ? 'selected' : '' ?>><?= $tz ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="date_format" class="block text-sm font-medium text-gray-700 mb-2">Date Format</label>
                        <select id="date_format" name="date_format" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php
                            $formats = ['Y-m-d' => 'YYYY-MM-DD', 'm/d/Y' => 'MM/DD/YYYY', 'd/m/Y' => 'DD/MM/YYYY', 'F j, Y' => 'Month DD, YYYY'];
                            $currentFormat = $settings['date_format']['value'] ?? 'Y-m-d';
                            foreach ($formats as $format => $label): ?>
                                <option value="<?= $format ?>" <?= $format === $currentFormat ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="items_per_page" class="block text-sm font-medium text-gray-700 mb-2">Items Per Page</label>
                        <input type="number" id="items_per_page" name="items_per_page" min="10" max="100"
                               value="<?= $settings['items_per_page']['value'] ?? 20 ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                        Save General Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- API Settings -->
        <div id="content-api" class="tab-content hidden">
            <form method="POST" action="/admin/settings" class="space-y-6">
                <input type="hidden" name="category" value="api">
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="api_enabled" name="api_enabled" 
                               <?= ($settings['api_enabled']['value'] ?? false) ? 'checked' : '' ?>
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="api_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable API</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="cors_enabled" name="cors_enabled" 
                               <?= ($settings['cors_enabled']['value'] ?? false) ? 'checked' : '' ?>
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="cors_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable CORS</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="api_auth_required" name="api_auth_required" 
                               <?= ($settings['api_auth_required']['value'] ?? false) ? 'checked' : '' ?>
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="api_auth_required" class="ml-2 text-sm font-medium text-gray-700">Require Authentication</label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="api_rate_limit" class="block text-sm font-medium text-gray-700 mb-2">Rate Limit (requests)</label>
                        <input type="number" id="api_rate_limit" name="api_rate_limit" min="100" max="10000"
                               value="<?= $settings['api_rate_limit']['value'] ?? 1000 ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="api_rate_window" class="block text-sm font-medium text-gray-700 mb-2">Rate Window (seconds)</label>
                        <input type="number" id="api_rate_window" name="api_rate_window" min="60" max="86400"
                               value="<?= $settings['api_rate_window']['value'] ?? 3600 ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline