<?php
$title = 'Résultats du Quiz';
ob_start();
?>
<div class="card text-center relative">
    <h2 class="text-3xl font-bold mb-6 text-blue-800">Résultats du Quiz</h2>
    <p class="text-xl mb-4 text-gray-700">
        Vous avez obtenu <span class="font-bold text-blue-600"><?php echo htmlspecialchars($correct_answers ?? 0, ENT_QUOTES, 'UTF-8'); ?></span> bonnes réponses sur <span class="font-bold text-blue-600"><?php echo htmlspecialchars($total_questions ?? 0, ENT_QUOTES, 'UTF-8'); ?></span>.
    </p>
    <p class="text-2xl font-semibold text-yellow-500 animate-pulse">Félicitations pour avoir complété le quiz !</p>
    <a href="/quiz" class="btn-primary mt-6 inline-block">Recommencer</a>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>