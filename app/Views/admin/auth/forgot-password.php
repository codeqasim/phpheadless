<!-- app/Views/admin/auth/reset-password.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Reset Password') ?></title>
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
        <!-- Reset Password Card -->
        <div class="bg-white rounded-lg shadow-xl p-8" x-data="resetForm()">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Reset Password</h1>
                <p class="text-gray-600">Create a new password for your account</p>
                <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($email) ?></p>
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

            <!-- Reset Form -->
            <form method="POST" action="/admin/reset-password" @submit="onSubmit">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div class="space-y-6">
                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            New Password
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" id="password" name="password" x-model="form.password" 
                                   @input="checkPasswordStrength" required minlength="6"
                                   class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="Enter new password">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <button type="button" @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        
                        <!-- Password Strength Indicator -->
                        <div class="mt-2" x-show="form.password.length > 0">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Password Strength:</span>
                                <span :class="passwordStrengthColor" x-text="passwordStrengthText"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300" 
                                     :class="passwordStrengthColor" 
                                     :style="'width: ' + passwordStrength + '%'"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" 
                                   x-model="form.confirmPassword" required
                                   class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="Confirm new password">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        
                        <!-- Password Match Indicator -->
                        <div x-show="form.confirmPassword.length > 0" class="mt-2">
                            <div x-show="form.password === form.confirmPassword && form.password.length > 0" 
                                 class="text-green-600 text-sm flex items-center">
                                <i class="fas fa-check mr-2"></i>Passwords match
                            </div>
                            <div x-show="form.password !== form.confirmPassword" 
                                 class="text-red-600 text-sm flex items-center">
                                <i class="fas fa-times mr-2"></i>Passwords do not match
                            </div>
                        </div>
                    </div>

                    <!-- Password Requirements -->
                    <div class="bg-gray-50 rounded-md p-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Password Requirements:</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li class="flex items-center" :class="form.password.length >= 6 ? 'text-green-600' : ''">
                                <i class="fas fa-check mr-2" x-show="form.password.length >= 6"></i>
                                <i class="fas fa-times mr-2" x-show="form.password.length < 6"></i>
                                At least 6 characters
                            </li>
                            <li class="flex items-center" :class="hasUppercase ? 'text-green-600' : ''">
                                <i class="fas fa-check mr-2" x-show="hasUppercase"></i>
                                <i class="fas fa-times mr-2" x-show="!hasUppercase"></i>
                                One uppercase letter (recommended)
                            </li>
                            <li class="flex items-center" :class="hasNumber ? 'text-green-600' : ''">
                                <i class="fas fa-check mr-2" x-show="hasNumber"></i>
                                <i class="fas fa-times mr-2" x-show="!hasNumber"></i>
                                One number (recommended)
                            </li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" :disabled="!canSubmit || submitting" 
                            :class="canSubmit && !submitting ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-300 cursor-not-allowed'"
                            class="w-full text-white py-3 px-4 rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <span x-show="!submitting" class="flex items-center justify-center">
                            <i class="fas fa-check mr-2"></i>
                            Reset Password
                        </span>
                        <span x-show="submitting" class="flex items-center justify-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Resetting...
                        </span>
                    </button>
                </div>
            </form>

            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <a href="/admin/login" class="inline-flex items-center text-gray-600 hover:text-gray-800 text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Login
                </a>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <div class="text-xs text-gray-500">
                    PHP Headless CMS v1.0
                </div>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <div class="flex">
                <i class="fas fa-shield-alt text-yellow-400 mr-3 mt-0.5"></i>
                <div>
                    <h4 class="text-yellow-800 font-medium text-sm mb-1">Security Notice</h4>
                    <div class="text-yellow-700 text-xs">
                        <p>After resetting your password, you'll be logged out of all devices for security purposes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function resetForm() {
            return {
                form: {
                    password: '',
                    confirmPassword: ''
                },
                showPassword: false,
                showConfirmPassword: false,
                submitting: false,
                passwordStrength: 0,
                passwordStrengthText: 'Weak',
                passwordStrengthColor: 'bg-red-500 text-red-600',
                
                get hasUppercase() {
                    return /[A-Z]/.test(this.form.password);
                },
                
                get hasLowercase() {
                    return /[a-z]/.test(this.form.password);
                },
                
                get hasNumber() {
                    return /\d/.test(this.form.password);
                },
                
                get hasSpecial() {
                    return /[!@#$%^&*(),.?":{}|<>]/.test(this.form.password);
                },
                
                get canSubmit() {
                    return this.form.password.length >= 6 && 
                           this.form.password === this.form.confirmPassword;
                },
                
                checkPasswordStrength() {
                    const password = this.form.password;
                    let strength = 0;
                    
                    if (password.length >= 6) strength += 20;
                    if (password.length >= 8) strength += 10;
                    if (this.hasUppercase) strength += 20;
                    if (this.hasLowercase) strength += 20;
                    if (this.hasNumber) strength += 15;
                    if (this.hasSpecial) strength += 15;
                    
                    this.passwordStrength = strength;
                    
                    if (strength < 40) {
                        this.passwordStrengthText = 'Weak';
                        this.passwordStrengthColor = 'bg-red-500 text-red-600';
                    } else if (strength < 70) {
                        this.passwordStrengthText = 'Medium';
                        this.passwordStrengthColor = 'bg-yellow-500 text-yellow-600';
                    } else {
                        this.passwordStrengthText = 'Strong';
                        this.passwordStrengthColor = 'bg-green-500 text-green-600';
                    }
                },
                
                onSubmit(event) {
                    if (!this.canSubmit) {
                        event.preventDefault();
                        alert('Please ensure passwords match and meet requirements');
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