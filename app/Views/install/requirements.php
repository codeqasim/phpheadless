<?php
// app/Views/install/requirements.php
$title = 'System Requirements';
ob_start();
?>

<div class="text-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">System Requirements Check</h2>
    <p class="text-gray-600">We need to verify that your server meets the minimum requirements</p>
</div>

<div class="space-y-4 mb-8">
    <?php foreach ($requirements as $req): ?>
    <div class="flex items-center justify-between p-4 border rounded-lg <?= $req['status'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' ?>">
        <div class="flex items-center">
            <div class="w-6 h-6 rounded-full flex items-center justify-center mr-3 <?= $req['status'] ? 'bg-green-500' : 'bg-red-500' ?>">
                <?= $req['status'] ? '✓' : '✗' ?>
            </div>
            <div>
                <div class="font-medium text-gray-800"><?= htmlspecialchars($req['name']) ?></div>
                <div class="text-sm text-gray-600">Current: <?= htmlspecialchars($req['current']) ?></div>
            </div>
        </div>
        <div class="text-right">
            <span class="px-3 py-1 rounded-full text-sm font-medium <?= $req['status'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= $req['status'] ? 'Pass' : 'Fail' ?>
            </span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($canProceed): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
    <div class="flex items-center">
        <div class="w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center mr-3 text-sm">✓</div>
        <div>
            <strong>Great!</strong> Your server meets all requirements. You can proceed with the installation.
        </div>
    </div>
</div>

<form method="POST" action="/install" id="requirementsForm">
    <input type="hidden" name="step" value="1">
    <div class="flex justify-end">
        <button type="submit" id="proceedBtn" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
            Continue to Database Setup
        </button>
    </div>
</form>

<script>
document.getElementById('requirementsForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('proceedBtn');
    btn.disabled = true;
    btn.innerHTML = 'Proceeding...';
    btn.classList.add('opacity-50');
});
</script>

<?php else: ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
    <div class="flex items-center">
        <div class="w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center mr-3 text-sm">!</div>
        <div>
            <strong>Requirements Not Met</strong><br>
            Please fix the issues above before proceeding with the installation.
        </div>
    </div>
</div>

<div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6">
    <h4 class="font-semibold text-blue-800 mb-2">💡 How to Fix Common Issues:</h4>
    <ul class="text-sm text-blue-700 space-y-1">
        <li>• <strong>PHP Version:</strong> Update your PHP version to 8.2 or higher</li>
        <li>• <strong>Extensions:</strong> Enable missing extensions in your php.ini file</li>
        <li>• <strong>Permissions:</strong> Run <code>chmod 755 storage/</code> to make directories writable</li>
    </ul>
</div>

<div class="flex justify-between">
    <button onclick="location.reload()" 
            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
        Recheck Requirements
    </button>
    <button disabled 
            class="bg-gray-300 text-gray-500 font-bold py-3 px-6 rounded-lg cursor-not-allowed">
        Cannot Proceed
    </button>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>