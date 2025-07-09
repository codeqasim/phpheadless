<?php
// app/Views/install/admin.php
$title = 'Admin User Setup';
ob_start();
?>

<div class="text-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Create Admin User</h2>
    <p class="text-gray-600">Set up your administrator account to manage the CMS</p>
</div>

<form method="POST" action="/install" id="adminForm" x-data="adminSetup()">
    <input type="hidden" name="step" value="3">
    
    <div class="mb-6">
        <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
        <input type="text" id="site_name" name="site_name" x-model="form.siteName" required
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="My Awesome CMS" value="My CMS">
        <p class="text-sm text-gray-500 mt-1">This will be displayed in the admin panel</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="admin_user" class="block text-sm font-medium text-gray-700 mb-2">Admin Username</label>
            <input type="text" id="admin_user" name="admin_user" x-model="form.username" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="admin">
        </div>
        
        <div>
            <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">Admin Email</label>
            <input type="email" id="admin_email" name="admin_email" x-model="form.email" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="admin@example.com">
        </div>
    </div>

    <div class="mb-6">
        <label for="admin_pass" class="block text-sm font-medium text-gray-700 mb-2">Admin Password</label>
        <div class="relative">
            <input :type="showPassword ? 'text' : 'password'" id="admin_pass" name="admin_pass" 
                   x-model="form.password" @input="checkPasswordStrength()" required
                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Enter a strong password">
            <button type="button" @click="showPassword = !showPassword" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                <span x-show="!showPassword">👁️</span>
                <span x-show="showPassword">🙈</span>
            </button>
        </div>
        
        <!-- Password Strength Indicator -->
        <div class="mt-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Password Strength:</span>
                <span :class="passwordStrengthColor" x-text="passwordStrengthText"></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                <div class="h-2 rounded-full transition-all duration-300" 
                     :class="passwordStrengthColor" 
                     :style="'width: ' + passwordStrength + '%'"></div>
            </div>
        </div>
        
        <div class="mt-2 text-sm text-gray-600">
            <p>Password requirements:</p>
            <ul class="list-disc list-inside space-y-1">
                <li :class="form.password.length >= 6 ? 'text-green-600' : 'text-gray-400'">At least 6 characters</li>
                <li :class="hasUppercase ? 'text-green-600' : 'text-gray-400'">One uppercase letter</li>
                <li :class="hasLowercase ? 'text-green-600' : 'text-gray-400'">One lowercase letter</li>
                <li :class="hasNumber ? 'text-green-600' : 'text-gray-400'">One number</li>
            </ul>
        </div>
    </div>

    <div class="mb-6">
        <label for="admin_pass_confirm" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
        <input type="password" id="admin_pass_confirm" x-model="confirmPassword" required
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="Confirm your password">
        <div x-show="confirmPassword && form.password !== confirmPassword" 
             class="text-red-600 text-sm mt-1">
            Passwords do not match
        </div>
    </div>

    <!-- Security Notice -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <span class="text-yellow-400 text-xl">⚠️</span>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Security Notice</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>This admin account will have full access to your CMS. Please:</p>
                    <ul class="list-disc list-inside mt-1 space-y-1">
                        <li>Use a strong, unique password</li>
                        <li>Keep your login credentials secure</li>
                        <li>Consider changing the default username</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-between">
        <a href="/install?step=2" 
           class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
            ← Back to Database
        </a>
        
        <button type="submit" id="adminBtn" 
                :disabled="!canSubmit"
                :class="canSubmit ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                class="text-white font-bold py-3 px-6 rounded-lg transition duration-200"
                onclick="if(validateForm('adminForm') && this.checkFormValid()) showLoading('adminBtn', 'Creating Admin User...')">
            Complete Installation →
        </button>
    </div>
</form>

<script>
function adminSetup() {
    return {
        form: {
            siteName: 'My CMS',
            username: 'admin',
            email: '',
            password: ''
        },
        confirmPassword: '',
        showPassword: false,
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
        
        get canSubmit() {
            return this.form.username && 
                   this.form.email && 
                   this.form.password.length >= 6 && 
                   this.form.password === this.confirmPassword &&
                   this.form.siteName;
        },
        
        checkPasswordStrength() {
            const password = this.form.password;
            let strength = 0;
            
            if (password.length >= 6) strength += 20;
            if (password.length >= 8) strength += 10;
            if (this.hasUppercase) strength += 20;
            if (this.hasLowercase) strength += 20;
            if (this.hasNumber) strength += 15;
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 15;
            
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
        
        checkFormValid() {
            return this.canSubmit;
        }
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>