<?php
// app/Views/install/completion.php
$title = 'Installation Complete';
ob_start();
?>

<div class="text-center">
    <div class="mb-8">
        <div class="mx-auto w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-4">
            <span class="text-4xl">🎉</span>
        </div>
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Installation Complete!</h2>
        <p class="text-gray-600">Your PHP Headless CMS has been successfully installed and configured.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-green-800 mb-3">✅ What's Been Set Up</h3>
            <ul class="text-left text-green-700 space-y-2 text-sm">
                <li>• Database tables created</li>
                <li>• Admin user account configured</li>
                <li>• Environment settings saved</li>
                <li>• Security keys generated</li>
                <li>• Core system initialized</li>
            </ul>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-3">🚀 Default Credentials</h3>
            <div class="text-left text-blue-700 space-y-2 text-sm">
                <div><strong>Admin Username:</strong> admin</div>
                <div><strong>Admin Password:</strong> [Your chosen password]</div>
                <div><strong>Database:</strong> Connected ✓</div>
                <div><strong>API Endpoint:</strong> /api/</div>
            </div>
        </div>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-yellow-800 mb-3">🔒 Security Recommendations</h3>
        <div class="text-left text-yellow-700 space-y-2 text-sm">
            <p><strong>For production environments:</strong></p>
            <ul class="list-disc list-inside space-y-1 mt-2">
                <li>Change APP_DEBUG to false in your .env file</li>
                <li>Set up SSL/HTTPS for your domain</li>
                <li>Configure proper file permissions</li>
                <li>Set up regular database backups</li>
                <li>Consider removing the /install directory</li>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
            <div class="text-2xl mb-2">🎛️</div>
            <h4 class="font-semibold text-gray-800 mb-1">Admin Panel</h4>
            <p class="text-sm text-gray-600 mb-3">Manage your content and settings</p>
            <a href="/admin" 
               class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded transition duration-200">
                Open Admin Panel
            </a>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
            <div class="text-2xl mb-2">🔗</div>
            <h4 class="font-semibold text-gray-800 mb-1">API Endpoints</h4>
            <p class="text-sm text-gray-600 mb-3">Test your headless API</p>
            <a href="/api/content-types" 
               class="inline-block bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-4 rounded transition duration-200">
                View API
            </a>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
            <div class="text-2xl mb-2">📚</div>
            <h4 class="font-semibold text-gray-800 mb-1">Documentation</h4>
            <p class="text-sm text-gray-600 mb-3">Learn how to use your CMS</p>
            <button onclick="showDocumentation()"
                    class="inline-block bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2 px-4 rounded transition duration-200">
                Get Started
            </button>
        </div>
    </div>

    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">📋 Next Steps</h3>
        <div class="text-left text-gray-700 space-y-3 text-sm">
            <div class="flex items-start">
                <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-3 mt-0.5">1</span>
                <div>
                    <strong>Create your first content type</strong><br>
                    Go to Admin Panel → Content Types → Create to define your data structure
                </div>
            </div>
            <div class="flex items-start">
                <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-3 mt-0.5">2</span>
                <div>
                    <strong>Add some content</strong><br>
                    Create content entries using your newly defined content types
                </div>
            </div>
            <div class="flex items-start">
                <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-3 mt-0.5">3</span>
                <div>
                    <strong>Test your API</strong><br>
                    Use the auto-generated API endpoints to fetch your content
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="/admin" 
           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200">
            🚀 Launch Admin Panel
        </a>
        <button onclick="removeInstaller()"
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200">
            🗑️ Remove Installer
        </button>
    </div>
</div>

<script>
function showDocumentation() {
    alert(`🎯 Quick Start Guide:

1. **Content Types**: Define your data structure (like "Posts", "Products")
2. **Content Entries**: Add actual content using your types
3. **API Access**: Use /api/{content-type} to fetch data
4. **Media Upload**: Upload files via Admin Panel → Media

Example API calls:
• GET /api/posts - List all posts
• GET /api/posts/1 - Get specific post
• POST /api/posts - Create new post

Happy building! 🚀`);
}

function removeInstaller() {
    if (confirm('This will remove the installer for security. Continue?')) {
        fetch('/install/remove', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Installer removed successfully!');
                    window.location.href = '/admin';
                } else {
                    alert('❌ Could not remove installer: ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error removing installer: ' + error.message);
            });
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>