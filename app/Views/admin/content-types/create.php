<?php
// app/Views/admin/content-types/create.php
ob_start();
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Create Content Type</h1>
        <p class="text-gray-600 mt-1">Define the structure and fields for your content</p>
    </div>
    <a href="/admin/content-types" 
       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Back to Content Types
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="contentTypeBuilder()">
    <!-- Main Form -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form @submit.prevent="submitForm" id="contentTypeForm">
                <!-- Basic Information -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Content Type Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" x-model="form.name" @input="updateSlug" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="e.g., Blog Posts, Products, Events">
                            <p class="text-sm text-gray-500 mt-1">This will be the display name for your content type</p>
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                API Slug
                            </label>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-500 mr-2">/api/</span>
                                <input type="text" id="slug" x-model="form.slug" readonly
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600"
                                       placeholder="auto-generated">
                            </div>
                            <p class="text-sm text-gray-500 mt-1">This will be your API endpoint URL</p>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea id="description" x-model="form.description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Describe what this content type is used for..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Fields Builder -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Fields</h3>
                        <button type="button" @click="addField" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add Field
                        </button>
                    </div>

                    <div x-show="fields.length === 0" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                        <i class="fas fa-list text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500 mb-4">No fields added yet</p>
                        <button type="button" @click="addField" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                            Add Your First Field
                        </button>
                    </div>

                    <!-- Fields List -->
                    <div x-show="fields.length > 0" class="space-y-4">
                        <template x-for="(field, index) in fields" :key="'field-' + index">
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-grip-vertical text-gray-400 mr-3 cursor-move"></i>
                                        <h4 class="font-medium text-gray-900" x-text="field.name || 'New Field'"></h4>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" @click="toggleFieldExpand(index)" 
                                                class="text-gray-500 hover:text-gray-700">
                                            <i class="fas" :class="field.expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                        </button>
                                        <button type="button" @click="removeField(index)" 
                                                class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div x-show="field.expanded" x-collapse>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Field Name -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Field Name *</label>
                                            <input type="text" x-model="field.name" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   placeholder="e.g., Title, Content, Price">
                                        </div>

                                        <!-- Field Type -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Field Type *</label>
                                            <select x-model="field.type" @change="updateFieldSettings(index)"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="text">Text</option>
                                                <option value="textarea">Textarea</option>
                                                <option value="rich_text">Rich Text</option>
                                                <option value="number">Number</option>
                                                <option value="email">Email</option>
                                                <option value="url">URL</option>
                                                <option value="date">Date</option>
                                                <option value="datetime">Date & Time</option>
                                                <option value="boolean">True/False</option>
                                                <option value="select">Select Dropdown</option>
                                                <option value="media">Media/File</option>
                                            </select>
                                        </div>

                                        <!-- Required Checkbox -->
                                        <div class="md:col-span-2">
                                            <label class="flex items-center">
                                                <input type="checkbox" x-model="field.required" 
                                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-gray-700">Required field</span>
                                            </label>
                                        </div>

                                        <!-- Type-specific Settings -->
                                        <div x-show="['text', 'email', 'url'].includes(field.type)" class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Length</label>
                                            <input type="number" x-model="field.settings.max_length" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   placeholder="255" min="1">
                                        </div>

                                        <div x-show="['textarea', 'rich_text'].includes(field.type)" class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Length</label>
                                            <input type="number" x-model="field.settings.max_length" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   placeholder="5000" min="1">
                                        </div>

                                        <div x-show="field.type === 'number'" class="md:col-span-2">
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Value</label>
                                                    <input type="number" x-model="field.settings.min" 
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                           placeholder="0">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Value</label>
                                                    <input type="number" x-model="field.settings.max" 
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                           placeholder="999999">
                                                </div>
                                            </div>
                                        </div>

                                        <div x-show="field.type === 'select'" class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Options (comma-separated)</label>
                                            <input type="text" x-model="field.settings.options" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   placeholder="Option 1, Option 2, Option 3">
                                            <p class="text-sm text-gray-500 mt-1">Separate each option with a comma</p>
                                        </div>

                                        <!-- Help Text -->
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Help Text (optional)</label>
                                            <input type="text" x-model="field.help_text" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   placeholder="Additional instructions for this field...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <span x-text="fields.length"></span> field<span x-show="fields.length !== 1">s</span> defined
                        </div>
                        <div class="flex items-center space-x-4">
                            <button type="button" onclick="window.location.href='/admin/content-types'" 
                                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" :disabled="!isFormValid" :class="isFormValid ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                                    class="px-6 py-2 text-white rounded-md font-medium transition-colors">
                                <span x-show="!submitting">Create Content Type</span>
                                <span x-show="submitting">Creating...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Field Types Reference -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Field Types Reference</h3>
            
            <div class="space-y-4 text-sm">
                <div class="border-b border-gray-200 pb-3">
                    <h4 class="font-medium text-gray-800 mb-2">Text Fields</h4>
                    <ul class="space-y-1 text-gray-600">
                        <li><strong>Text:</strong> Single line text input</li>
                        <li><strong>Textarea:</strong> Multi-line text input</li>
                        <li><strong>Rich Text:</strong> WYSIWYG editor</li>
                        <li><strong>Email:</strong> Email address validation</li>
                        <li><strong>URL:</strong> Website URL validation</li>
                    </ul>
                </div>

                <div class="border-b border-gray-200 pb-3">
                    <h4 class="font-medium text-gray-800 mb-2">Data Types</h4>
                    <ul class="space-y-1 text-gray-600">
                        <li><strong>Number:</strong> Numeric values</li>
                        <li><strong>Date:</strong> Date picker</li>
                        <li><strong>Date & Time:</strong> Date and time picker</li>
                        <li><strong>True/False:</strong> Boolean checkbox</li>
                    </ul>
                </div>

                <div class="border-b border-gray-200 pb-3">
                    <h4 class="font-medium text-gray-800 mb-2">Advanced</h4>
                    <ul class="space-y-1 text-gray-600">
                        <li><strong>Select:</strong> Dropdown with options</li>
                        <li><strong>Media:</strong> File/image upload</li>
                    </ul>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                    <h4 class="font-medium text-blue-800 mb-1">💡 Pro Tip</h4>
                    <p class="text-blue-700 text-xs">
                        Start with basic fields and add more complex ones later. You can always edit your content type.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function contentTypeBuilder() {
    return {
        form: {
            name: '',
            slug: '',
            description: ''
        },
        fields: [],
        submitting: false,
        
        get isFormValid() {
            return this.form.name.trim() && this.fields.length > 0 && this.fields.every(field => 
                field.name.trim() && field.type
            );
        },
        
        updateSlug() {
            this.form.slug = this.form.name
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
        },
        
        addField() {
            this.fields.push({
                name: '',
                type: 'text',
                required: false,
                expanded: true,
                settings: {
                    max_length: 255
                },
                help_text: ''
            });
        },
        
        removeField(index) {
            if (confirm('Are you sure you want to remove this field?')) {
                this.fields.splice(index, 1);
            }
        },
        
        toggleFieldExpand(index) {
            this.fields[index].expanded = !this.fields[index].expanded;
        },
        
        updateFieldSettings(index) {
            const field = this.fields[index];
            
            // Reset settings based on type
            switch (field.type) {
                case 'text':
                case 'email':
                case 'url':
                    field.settings = { max_length: 255 };
                    break;
                case 'textarea':
                case 'rich_text':
                    field.settings = { max_length: 5000 };
                    break;
                case 'number':
                    field.settings = { min: 0, max: 999999 };
                    break;
                case 'select':
                    field.settings = { options: '' };
                    break;
                default:
                    field.settings = {};
            }
        },
        
        async submitForm() {
            if (!this.isFormValid) return;
            
            this.submitting = true;
            
            try {
                const response = await fetch('/admin/content-types', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        name: this.form.name,
                        description: this.form.description,
                        fields: JSON.stringify(this.fields)
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = '/admin/content-types';
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error creating content type: ' + error.message);
            }
            
            this.submitting = false;
        }
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>