<!-- app/Views/admin/layout.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'CMS Admin') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-item {
            transition: all 0.2s ease;
        }
        .sidebar-item:hover {
            background-color: rgb(243 244 246);
            transform: translateX(4px);
        }
        .sidebar-item.active {
            background-color: rgb(59 130 246);
            color: white;
        }
        .sidebar-item.active:hover {
            background-color: rgb(37 99 235);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen" x-data="adminPanel()">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg flex flex-col" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" 
             style="transition: transform 0.3s ease">
            <!-- Logo Section -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-cube text-white text-sm"></i>
                    </div>
                    <div>
                        <h1 class="text-sm font-bold text-gray-800">PHPHEADLESS</h1>
                        <p class="text-xs text-gray-500">Control Panel</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <div class="px-4 space-y-2">
                    <!-- Dashboard -->
                    <a href="/admin" class="sidebar-item flex items-center px-3 py-2 rounded-lg text-gray-700 <?= ($_SERVER['REQUEST_URI'] === '/admin') ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt w-5 text-center mr-3"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- Content Types -->
                    <div class="space-y-1">
                        <div class="flex items-center px-3 py-2 text-gray-500 text-sm font-medium">
                            <i class="fas fa-layer-group w-5 text-center mr-3"></i>
                            <span>Content</span>
                        </div>
                        <a href="/admin/content-types" class="sidebar-item flex items-center px-6 py-2 rounded-lg text-gray-600 text-sm">
                            <i class="fas fa-shapes w-4 text-center mr-3"></i>
                            <span>Content Types</span>
                        </a>
                        <a href="/admin/content" class="sidebar-item flex items-center px-6 py-2 rounded-lg text-gray-600 text-sm">
                            <i class="fas fa-file-alt w-4 text-center mr-3"></i>
                            <span>Content Entries</span>
                        </a>
                    </div>

                    <!-- Media -->
                    <a href="/admin/media" class="sidebar-item flex items-center px-3 py-2 rounded-lg text-gray-700">
                        <i class="fas fa-images w-5 text-center mr-3"></i>
                        <span>Media Library</span>
                    </a>

                    <!-- API -->
                    <div class="space-y-1">
                        <div class="flex items-center px-3 py-2 text-gray-500 text-sm font-medium">
                            <i class="fas fa-code w-5 text-center mr-3"></i>
                            <span>API</span>
                        </div>
                        <a href="/admin/api/documentation" class="sidebar-item flex items-center px-6 py-2 rounded-lg text-gray-600 text-sm">
                            <i class="fas fa-book w-4 text-center mr-3"></i>
                            <span>Documentation</span>
                        </a>
                        <a href="/admin/api/endpoints" class="sidebar-item flex items-center px-6 py-2 rounded-lg text-gray-600 text-sm">
                            <i class="fas fa-plug w-4 text-center mr-3"></i>
                            <span>Endpoints</span>
                        </a>
                    </div>

                    <!-- Settings -->
                    <a href="/admin/settings" class="sidebar-item flex items-center px-3 py-2 rounded-lg text-gray-700">
                        <i class="fas fa-cog w-5 text-center mr-3"></i>
                        <span>Settings</span>
                    </a>
                </div>
            </nav>

            <!-- User Section -->
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-user text-gray-600 text-sm"></i>
                        </div>
                        <div class="text-sm">
                            <div class="font-medium text-gray-800"><?= \App\Helpers\Session::user('username') ?? 'Admin' ?></div>
                            <div class="text-gray-500"><?= ucfirst(\App\Helpers\Session::user('role') ?? 'Administrator') ?></div>
                        </div>
                    </div>
                    <a href="/admin/logout" class="text-gray-400 hover:text-red-600 transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <!-- Mobile Menu Button -->
                            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden mr-4 text-gray-600">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            
                            <div>
                                <h2 class="text-2xl font-semibold text-gray-800"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h2>
                                <p class="text-sm text-gray-600 mt-1">Manage your headless CMS</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- Quick Actions -->
                            <div class="hidden md:flex items-center space-x-3">
                                <a href="/admin/content/create" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <i class="fas fa-plus mr-2"></i>New Content
                                </a>
                                <a href="/api" target="_blank"
                                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <i class="fas fa-external-link-alt mr-2"></i>View API
                                </a>
                            </div>

                            <!-- Notifications -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="text-gray-600 hover:text-gray-800 relative">
                                    <i class="fas fa-bell text-xl"></i>
                                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" 
                                     class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                                     style="display: none;">
                                    <div class="p-4 border-b border-gray-200">
                                        <h3 class="font-semibold text-gray-800">Notifications</h3>
                                    </div>
                                    <div class="p-4">
                                        <div class="text-center text-gray-500 text-sm">
                                            <i class="fas fa-bell-slash text-2xl mb-2"></i>
                                            <p>No new notifications</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <?= $content ?>
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" 
         class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
         style="display: none;"></div>

    <script>
        function adminPanel() {
            return {
                sidebarOpen: false
            }
        }
    </script>
</body>
</html>