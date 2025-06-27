<?php
$title = 'Quiz';
ob_start();
?>
<h1 class="text-2xl font-bold mb-4">
    Question <?php echo htmlspecialchars($question_number ?? 1, ENT_QUOTES, 'UTF-8'); ?> / 
    <?php echo htmlspecialchars($total_questions ?? 0, ENT_QUOTES, 'UTF-8'); ?>
</h1>
<?php if ($first_question): ?>
    <form method="POST" action="/quiz" class="bg-white p-6 rounded shadow-md">
        <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($first_question->id ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <p class="mb-4"><?php echo htmlspecialchars($first_question->text ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="mb-4">
            <label><input type="radio" name="option" value="1" required> <?php echo htmlspecialchars($first_question->option1 ?? '', ENT_QUOTES, 'UTF-8'); ?></label><br>
            <label><input type="radio" name="option" value="2" required> <?php echo htmlspecialchars($first_question->option2 ?? '', ENT_QUOTES, 'UTF-8'); ?></label><br>
            <label><input type="radio" name="option" value="3" required> <?php echo htmlspecialchars($first_question->option3 ?? '', ENT_QUOTES, 'UTF-8'); ?></label><br>
            <label><input type="radio" name="option" value="4" required> <?php echo htmlspecialchars($first_question->option4 ?? '', ENT_QUOTES, 'UTF-8'); ?></label>
        </div>
        <button type="submit" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Suivant</button>
    </form>
<?php else: ?>
    <p class="text-red-500">Aucune question disponible. Veuillez contacter l'administrateur.</p>
    <a href="/quiz/results" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Voir les résultats</a>
<?php endif; ?>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>