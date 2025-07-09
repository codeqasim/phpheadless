<!-- app/Views/install/layout.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'CMS Installation' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .progress-step {
            transition: all 0.3s ease;
        }
        .progress-step.active {
            background-color: #3b82f6;
            color: white;
        }
        .progress-step.completed {
            background-color: #10b981;
            color: white;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">🚀 CMS Installation</h1>
            <p class="text-gray-600">Set up your headless CMS in a few simple steps</p>
        </div>

        <!-- Progress Steps -->
        <div class="max-w-4xl mx-auto mb-8">
            <div class="flex justify-between items-center">
                <?php 
                $steps = [
                    1 => 'Requirements',
                    2 => 'Database',
                    3 => 'Admin User',
                    4 => 'Complete'
                ];
                $currentStep = $_GET['step'] ?? 1;
                
                foreach ($steps as $stepNum => $stepName): 
                    $isActive = $stepNum == $currentStep;
                    $isCompleted = $stepNum < $currentStep;
                    $class = $isActive ? 'active' : ($isCompleted ? 'completed' : '');
                ?>
                <div class="flex items-center">
                    <div class="progress-step <?= $class ?> w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 border-gray-300">
                        <?= $stepNum ?>
                    </div>
                    <div class="ml-2 text-sm font-medium text-gray-700">
                        <?= $stepName ?>
                    </div>
                    <?php if ($stepNum < count($steps)): ?>
                    <div class="flex-1 h-0.5 bg-gray-300 mx-4 <?= $isCompleted ? 'bg-green-500' : '' ?>"></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <?= $content ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500 text-sm">
            <p>PHP Headless CMS © <?= date('Y') ?></p>
        </div>
    </div>

    <script>
        // Form validation helper
        function validateForm(formId) {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('input[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('border-red-500');
                    isValid = false;
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            return isValid;
        }

        // Show loading state
        function showLoading(buttonId, text = 'Processing...') {
            const button = document.getElementById(buttonId);
            button.disabled = true;
            button.innerHTML = text;
            button.classList.add('opacity-50');
        }
    </script>
</body>
</html>