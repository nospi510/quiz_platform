<?php
$title = 'Paramètres du Quiz';
ob_start();
?>
<div class="card">
    <h2 class="text-2xl font-bold mb-6 text-center text-blue-800">Paramètres du Quiz</h2>
    <form method="POST" action="/admin/quiz_settings" class="space-y-5">
        <div class="text-center">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Statut du Quiz</label>
            <select name="is_active" class="form-input max-w-xs mx-auto">
                <option value="0" <?php echo !$settings->is_active ? 'selected' : ''; ?>>Désactivé</option>
                <option value="1" <?php echo $settings->is_active ? 'selected' : ''; ?>>Activé</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white font-semibold py-2 px-6 rounded-full border border-blue-600 shadow-lg hover:bg-blue-700 hover:scale-105 transition-all duration-300 ease-in-out w-full">Mettre à jour</button>
    </form>
    <?php if ($settings->is_active && $settings->start_time): ?>
        <p class="text-center text-sm text-gray-600 mt-4">
            Quiz activé depuis : <?php echo htmlspecialchars($settings->start_time, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>