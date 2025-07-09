<?php
// app/Views/admin/dashboard.php
ob_start();
?>

<!-- Welcome Message -->
<div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-6 text-white mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-2">Welcome back, <?= $_SESSION['user']['username'] ?? 'Admin' ?>! 👋</h1>
            <p class="text-blue-100">Your PHP Headless CMS is ready to use. Start by creating your first content type.</p>
        </div>
        <div class="hidden lg:block">
            <i class="fas fa-rocket text-6xl text-blue-200"></i>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Content Types -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Content Types</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['content_types'] ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-shapes text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <a href="/admin/content-types" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Manage Types →
            </a>
        </div>
    </div>

    <!-- Content Entries -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Content Entries</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['content_entries'] ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-file-alt text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <a href="/admin/content" class="text-green-600 hover:text-green-800 text-sm font-medium">
                View Content →
            </a>
        </div>
    </div>

    <!-- Media Files -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Media Files</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['media_files'] ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-images text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <a href="/admin/media" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                Media Library →
            </a>
        </div>
    </div>

    <!-- Users -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Users</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['users'] ?></p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-orange-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <a href="/admin/users" class="text-orange-600 hover:text-orange-800 text-sm font-medium">
                Manage Users →
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Quick Actions -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Create Content Type -->
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-plus text-blue-600"></i>
                        </div>
                        <h4 class="font-medium text-gray-900">Create Content Type</h4>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Define the structure for your content with custom fields</p>
                    <a href="/admin/content-types/create" 
                       class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Get Started <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>

                <!-- Upload Media -->
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-upload text-purple-600"></i>
                        </div>
                        <h4 class="font-medium text-gray-900">Upload Media</h4>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Add images, documents, and other files to your media library</p>
                    <a href="/admin/media" 
                       class="inline-flex items-center text-purple-600 hover:text-purple-800 text-sm font-medium">
                        Upload Files <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>

                <!-- API Documentation -->
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-code text-green-600"></i>
                        </div>
                        <h4 class="font-medium text-gray-900">API Documentation</h4>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Learn how to consume your headless CMS via REST API</p>
                    <a href="/admin/api/documentation" 
                       class="inline-flex items-center text-green-600 hover:text-green-800 text-sm font-medium">
                        View Docs <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>

                <!-- Settings -->
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-cog text-gray-600"></i>
                        </div>
                        <h4 class="font-medium text-gray-900">System Settings</h4>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Configure your CMS settings and preferences</p>
                    <a href="/admin/settings" 
                       class="inline-flex items-center text-gray-600 hover:text-gray-800 text-sm font-medium">
                        Open Settings <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & System Info -->
    <div class="space-y-6">
        <!-- Recent Content -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Content</h3>
            
            <?php if (empty($stats['recent_entries'])): ?>
            <div class="text-center py-8">
                <i class="fas fa-file-alt text-gray-300 text-3xl mb-3"></i>
                <p class="text-gray-500 text-sm">No content yet</p>
                <p class="text-gray-400 text-xs">Create your first content type to get started</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($stats['recent_entries'] as $entry): ?>
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                    <div>
                        <p class="font-medium text-gray-900 text-sm"><?= htmlspecialchars($entry['type_name']) ?></p>
                        <p class="text-gray-500 text-xs">by <?= htmlspecialchars($entry['created_by_name'] ?? 'Unknown') ?></p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                   <?= $entry['status'] === 'published' ? 'bg-green-100 text-green-800' : 
                                       ($entry['status'] === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') ?>">
                            <?= ucfirst($entry['status']) ?>
                        </span>
                        <p class="text-gray-400 text-xs mt-1"><?= date('M j', strtotime($entry['created_at'])) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- System Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">System Info</h3>
            
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">PHP Version</span>
                    <span class="font-medium text-gray-900"><?= PHP_VERSION ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">CMS Version</span>
                    <span class="font-medium text-gray-900">1.0.0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Database</span>
                    <span class="font-medium text-green-600">
                        <i class="fas fa-check-circle mr-1"></i>Connected
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">API Status</span>
                    <span class="font-medium text-green-600">
                        <i class="fas fa-check-circle mr-1"></i>Active
                    </span>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-200">
                <a href="/api/content-types" target="_blank" 
                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    <i class="fas fa-external-link-alt mr-2"></i>Test API Endpoint
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>