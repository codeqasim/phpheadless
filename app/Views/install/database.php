<?php
// app/Views/install/database.php
$title = 'Database Configuration';
ob_start();
?>

<div class="text-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Database Configuration</h2>
    <p class="text-gray-600">Enter your MySQL database connection details</p>
</div>

<form method="POST" action="/install" id="databaseForm" x-data="databaseConfig()" @submit="onSubmit">
    <input type="hidden" name="step" value="2">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="db_host" class="block text-sm font-medium text-gray-700 mb-2">Database Host</label>
            <input type="text" id="db_host" name="db_host" x-model="form.host" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="localhost">
        </div>
        
        <div>
            <label for="db_port" class="block text-sm font-medium text-gray-700 mb-2">Port</label>
            <input type="number" id="db_port" name="db_port" x-model="form.port" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="3306" value="3306">
        </div>
    </div>

    <div class="mb-6">
        <label for="db_name" class="block text-sm font-medium text-gray-700 mb-2">Database Name</label>
        <input type="text" id="db_name" name="db_name" x-model="form.database" required
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="cms_database">
        <p class="text-sm text-gray-500 mt-1">If the database doesn't exist, we'll create it for you</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="db_user" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
            <input type="text" id="db_user" name="db_user" x-model="form.username" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="root">
        </div>
        
        <div>
            <label for="db_pass" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <input type="password" id="db_pass" name="db_pass" x-model="form.password"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Enter password (leave empty if no password)">
        </div>
    </div>

    <!-- Test Connection Button -->
    <div class="mb-6">
        <button type="button" @click="testConnection()" 
                :disabled="testing" :class="testing ? 'opacity-50 cursor-not-allowed' : ''"
                class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md transition duration-200 mb-4">
            <span x-show="!testing">🔗 Test Database Connection</span>
            <span x-show="testing">Testing Connection...</span>
        </button>
        
        <!-- Connection Status -->
        <div x-show="connectionStatus" class="p-4 rounded-md" 
             :class="connectionSuccess ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'">
            <div class="flex items-center">
                <div class="w-5 h-5 rounded-full flex items-center justify-center mr-3 text-sm"
                     :class="connectionSuccess ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
                    <span x-text="connectionSuccess ? '✓' : '✗'"></span>
                </div>
                <span x-text="connectionMessage"></span>
            </div>
        </div>
    </div>

    <div class="flex justify-between">
        <a href="/install?step=1" 
           class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
            ← Back to Requirements
        </a>
        
        <button type="submit" id="databaseBtn" 
                :disabled="!isFormValid"
                :class="isFormValid ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                class="text-white font-bold py-3 px-6 rounded-lg transition duration-200">
            Continue to Admin Setup →
        </button>
    </div>
</form>

<script>
function databaseConfig() {
    return {
        form: {
            host: 'localhost',
            port: 3306,
            database: '',
            username: 'root',
            password: ''
        },
        testing: false,
        connectionStatus: false,
        connectionSuccess: false,
        connectionMessage: '',
        
        get isFormValid() {
            return this.form.host && this.form.database && this.form.username;
        },
        
        onSubmit(event) {
            if (!this.isFormValid) {
                event.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
            
            // Show loading state
            const button = document.getElementById('databaseBtn');
            button.disabled = true;
            button.innerHTML = 'Setting up Database...';
            button.classList.add('opacity-50');
            
            // Form will submit normally
        },
        
        async testConnection() {
            // Validate required fields first
            if (!this.form.host || !this.form.username) {
                this.connectionStatus = true;
                this.connectionSuccess = false;
                this.connectionMessage = 'Please enter host and username';
                return;
            }
            
            this.testing = true;
            this.connectionStatus = false;
            
            try {
                const response = await fetch('/install/test-connection', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        host: this.form.host,
                        port: parseInt(this.form.port) || 3306,
                        database: this.form.database,
                        username: this.form.username,
                        password: this.form.password
                    })
                });
                
                const result = await response.json();
                
                this.connectionStatus = true;
                this.connectionSuccess = result.success;
                this.connectionMessage = result.message;
                
            } catch (error) {
                this.connectionStatus = true;
                this.connectionSuccess = false;
                this.connectionMessage = 'Failed to test connection: ' + error.message;
            }
            
            this.testing = false;
        }
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>