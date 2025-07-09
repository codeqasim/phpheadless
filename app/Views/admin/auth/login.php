<!-- app/Views/admin/auth/login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin Login') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bg-gradient-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gradient-custom min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Login Card -->
        <div class="bg-white rounded-lg shadow-xl p-8" x-data="loginForm()">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cube text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Admin Login</h1>
                <p class="text-gray-600">Sign in to access your CMS</p>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-400 mr-3 mt-0.5"></i>
                    <div class="text-red-700 text-sm"><?= htmlspecialchars($error) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-400 mr-3 mt-0.5"></i>
                    <div class="text-green-700 text-sm"><?= htmlspecialchars($success) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="/admin/login" @submit="onSubmit">
                <div class="space-y-6">
                    <!-- Username/Email -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username or Email
                        </label>
                        <div class="relative">
                            <input type="text" id="username" name="username" x-model="form.username" required
                                   class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Enter your username or email">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" id="password" name="password" x-model="form.password" required
                                   class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Enter your password">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <button type="button" @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" x-model="form.remember" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Remember me</span>
                        </label>
                        <a href="/admin/forgot-password" class="text-sm text-blue-600 hover:text-blue-800">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" :disabled="submitting" 
                            :class="submitting ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <span x-show="!submitting" class="flex items-center justify-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Sign In
                        </span>
                        <span x-show="submitting" class="flex items-center justify-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Signing In...
                        </span>
                    </button>
                </div>
            </form>

            <!-- Footer Links -->
            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <div class="space-y-2">
                    <a href="/" class="text-sm text-gray-600 hover:text-gray-800">
                        <i class="fas fa-home mr-1"></i>
                        Back to Website
                    </a>
                    <div class="text-xs text-gray-500">
                        PHP Headless CMS v1.0
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Login Info (Development Only) -->
        <?php if (($_ENV['APP_DEBUG'] ?? false) && empty($error)): ?>
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <div class="flex">
                <i class="fas fa-info-circle text-yellow-400 mr-3 mt-0.5"></i>
                <div>
                    <h4 class="text-yellow-800 font-medium text-sm mb-1">Development Mode</h4>
                    <div class="text-yellow-700 text-xs">
                        <p>Default admin credentials:</p>
                        <p><strong>Username:</strong> admin</p>
                        <p><strong>Password:</strong> [Your install password]</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function loginForm() {
            return {
                form: {
                    username: '',
                    password: '',
                    remember: false
                },
                showPassword: false,
                submitting: false,
                
                onSubmit(event) {
                    if (!this.form.username.trim() || !this.form.password.trim()) {
                        event.preventDefault();
                        alert('Please enter both username and password');
                        return;
                    }
                    
                    this.submitting = true;
                    // Form will submit normally
                }
            }
        }
    </script>
</body>
</html>