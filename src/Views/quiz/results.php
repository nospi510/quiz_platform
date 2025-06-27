<?php
$title = 'Résultats';
ob_start();
?>
<h1 class="text-2xl font-bold mb-4">Résultats</h1>
<p class="mb-4">Vous avez <?php echo $correct_answers; ?> bonnes réponses sur <?php echo $total_questions; ?>.</p>
<a href="/quiz" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Recommencer le quiz</a>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>