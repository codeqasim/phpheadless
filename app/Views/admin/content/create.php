<?php
// app/Views/admin/content/create.php
ob_start();
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Create <?= htmlspecialchars($contentType['name']) ?></h1>
        <p class="text-gray-600 mt-1">Fill in the fields to create a new entry</p>
    </div>
    <a href="/admin/content?type=<?= urlencode($contentType['slug']) ?>" 
       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Back to <?= htmlspecialchars($contentType['name']) ?>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8" x-data="contentForm()">
    <!-- Main Form -->
    <div class="lg:col-span-3">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form @submit.prevent="submitForm" id="contentForm">
                <input type="hidden" name="content_type_id" value="<?= $contentType['id'] ?>">
                
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Content Fields</h3>
                    
                    <div class="space-y-6">
                        <?php foreach ($contentType['fields'] as $field): ?>
                        <div class="form-field">
                            <label for="<?= htmlspecialchars($field['key']) ?>" 
                                   class="block text-sm font-medium text-gray-700 mb-2">
                                <?= htmlspecialchars($field['name']) ?>
                                <?php if ($field['required']): ?>
                                <span class="text-red-500">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php
                            $fieldId = htmlspecialchars($field['key']);
                            $fieldName = htmlspecialchars($field['key']);
                            $required = $field['required'] ? 'required' : '';
                            $xModel = "form.{$field['key']}";
                            ?>
                            
                            <?php switch ($field['type']): 
                                case 'text': 
                                case 'email': 
                                case 'url': ?>
                                <input type="<?= $field['type'] ?>" 
                                       id="<?= $fieldId ?>" 
                                       name="<?= $fieldName ?>" 
                                       x-model="<?= $xModel ?>"
                                       <?= $required ?>
                                       maxlength="<?= $field['settings']['max_length'] ?? 255 ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Enter <?= strtolower($field['name']) ?>">
                                <?php break; ?>
                                
                            <?php case 'textarea': ?>
                                <textarea id="<?= $fieldId ?>" 
                                          name="<?= $fieldName ?>" 
                                          x-model="<?= $xModel ?>"
                                          <?= $required ?>
                                          maxlength="<?= $field['settings']['max_length'] ?? 5000 ?>"
                                          rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Enter <?= strtolower($field['name']) ?>"></textarea>
                                <?php break; ?>
                                
                            <?php case 'rich_text': ?>
                                <textarea id="<?= $fieldId ?>" 
                                          name="<?= $fieldName ?>" 
                                          x-model="<?= $xModel ?>"
                                          <?= $required ?>
                                          maxlength="<?= $field['settings']['max_length'] ?? 5000 ?>"
                                          rows="6"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Enter <?= strtolower($field['name']) ?>"></textarea>
                                <p class="text-xs text-gray-500 mt-1">Rich text editor (basic for now)</p>
                                <?php break; ?>
                                
                            <?php case 'number': ?>
                                <input type="number" 
                                       id="<?= $fieldId ?>" 
                                       name="<?= $fieldName ?>" 
                                       x-model="<?= $xModel ?>"
                                       <?= $required ?>
                                       min="<?= $field['settings']['min'] ?? '' ?>"
                                       max="<?= $field['settings']['max'] ?? '' ?>"
                                       step="any"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Enter <?= strtolower($field['name']) ?>">
                                <?php break; ?>
                                
                            <?php case 'date': ?>
                                <input type="date" 
                                       id="<?= $fieldId ?>" 
                                       name="<?= $fieldName ?>" 
                                       x-model="<?= $xModel ?>"
                                       <?= $required ?>
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <?php break; ?>
                                
                            <?php case 'datetime': ?>
                                <input type="datetime-local" 
                                       id="<?= $fieldId ?>" 
                                       name="<?= $fieldName ?>" 
                                       x-model="<?= $xModel ?>"
                                       <?= $required ?>
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <?php break; ?>
                                
                            <?php case 'boolean': ?>
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           id="<?= $fieldId ?>" 
                                           name="<?= $fieldName ?>" 
                                           x-model="<?= $xModel ?>"
                                           value="1"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <label for="<?= $fieldId ?>" class="ml-2 text-sm text-gray-700">
                                        Enable <?= strtolower($field['name']) ?>
                                    </label>
                                </div>
                                <?php break; ?>
                                
                            <?php case 'select': ?>
                                <select id="<?= $fieldId ?>" 
                                        name="<?= $fieldName ?>" 
                                        x-model="<?= $xModel ?>"
                                        <?= $required ?>
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select <?= strtolower($field['name']) ?></option>
                                    <?php foreach ($field['settings']['options'] ?? [] as $option): ?>
                                    <option value="<?= htmlspecialchars($option) ?>">
                                        <?= htmlspecialchars($option) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php break; ?>
                                
                            <?php case 'media': ?>
                                <div class="border-2 border-dashed border-gray-300 rounded-md p-4 text-center">
                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-2xl mb-2"></i>
                                    <p class="text-gray-500 mb-2">Media upload coming soon</p>
                                    <input type="text" 
                                           id="<?= $fieldId ?>" 
                                           name="<?= $fieldName ?>" 
                                           x-model="<?= $xModel ?>"
                                           placeholder="Enter media URL for now"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <?php break; ?>
                                
                            <?php endswitch; ?>
                            
                            <?php if (!empty($field['help_text'])): ?>
                            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($field['help_text']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <?= count($contentType['fields']) ?> field<?= count($contentType['fields']) !== 1 ? 's' : '' ?> to complete
                        </div>
                        <div class="flex items-center space-x-4">
                            <button type="button" @click="saveAsDraft" :disabled="submitting"
                                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                                <span x-show="!submitting">Save as Draft</span>
                                <span x-show="submitting">Saving...</span>
                            </button>
                            <button type="submit" :disabled="submitting"
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition-colors">
                                <span x-show="!submitting">Publish</span>
                                <span x-show="submitting">Publishing...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1">
        <div class="space-y-6">
            <!-- Content Type Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <?= htmlspecialchars($contentType['name']) ?>
                </h3>
                
                <?php if (!empty($contentType['description'])): ?>
                <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars($contentType['description']) ?></p>
                <?php endif; ?>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Fields:</span>
                        <span class="font-medium"><?= count($contentType['fields']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">API Endpoint:</span>
                        <code class="text-xs bg-gray-100 px-1 rounded">/api/<?= htmlspecialchars($contentType['slug']) ?></code>
                    </div>
                </div>
            </div>

            <!-- Field Summary -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Field Summary</h3>
                
                <div class="space-y-3">
                    <?php foreach ($contentType['fields'] as $field): ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="text-sm text-gray-900"><?= htmlspecialchars($field['name']) ?></span>
                            <?php if ($field['required']): ?>
                            <span class="text-red-500 ml-1">*</span>
                            <?php endif; ?>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                            <?= htmlspecialchars($field['type']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tips -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-800 mb-2">💡 Tips</h4>
                <ul class="text-blue-700 text-sm space-y-1">
                    <li>• Fill required fields (marked with *)</li>
                    <li>• Save as draft to continue later</li>
                    <li>• Published content appears in API</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function contentForm() {
    return {
        form: {
            <?php foreach ($contentType['fields'] as $field): ?>
            <?= $field['key'] ?>: <?= $field['type'] === 'boolean' ? 'false' : "''" ?>,
            <?php endforeach; ?>
        },
        submitting: false,
        
        async submitForm() {
            await this.saveContent('published');
        },
        
        async saveAsDraft() {
            await this.saveContent('draft');
        },
        
        async saveContent(status) {
            this.submitting = true;
            
            try {
                const formData = new FormData();
                formData.append('content_type_id', '<?= $contentType['id'] ?>');
                formData.append('status', status);
                
                // Add form fields
                <?php foreach ($contentType['fields'] as $field): ?>
                formData.append('<?= $field['key'] ?>', this.form.<?= $field['key'] ?>);
                <?php endforeach; ?>
                
                const response = await fetch('/admin/content', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = '/admin/content?type=<?= urlencode($contentType['slug']) ?>';
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error creating content: ' + error.message);
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