<?php
$title = 'Quiz';
ob_start();
?>
<div class="quiz-card flex flex-col items-center">
    <?php if (!$settings->is_active): ?>
        <p class="text-red-500 text-center text-lg mb-4">Veuillez patienter jusqu'à l'activation du quiz par l'administrateur.</p>
        <a href="/quiz" class="btn-primary w-full max-w-xs mx-auto">Recharger</a>
    <?php else: ?>
        <div class="w-full">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-center text-blue-800">
                    Question <?php echo htmlspecialchars($question_number ?? 1, ENT_QUOTES, 'UTF-8'); ?> /
                    <?php echo htmlspecialchars($total_questions ?? 0, ENT_QUOTES, 'UTF-8'); ?>
                </h1>
                <div id="timer" class="text-lg font-semibold text-red-600"></div>
            </div>
            <?php if ($first_question): ?>
                <form method="POST" action="/quiz" class="space-y-6 w-full">
                    <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($first_question->id ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <p class="text-lg text-gray-700 text-center"><?php echo htmlspecialchars($first_question->text ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    <div class="space-y-4">
                        <label class="flex items-center justify-center text-gray-600">
                            <input type="radio" name="option" value="1" required class="mr-2 accent-blue-600">
                            <span><?php echo htmlspecialchars($first_question->option1 ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        </label>
                        <label class="flex items-center justify-center text-gray-600">
                            <input type="radio" name="option" value="2" required class="mr-2 accent-blue-600">
                            <span><?php echo htmlspecialchars($first_question->option2 ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        </label>
                        <label class="flex items-center justify-center text-gray-600">
                            <input type="radio" name="option" value="3" required class="mr-2 accent-blue-600">
                            <span><?php echo htmlspecialchars($first_question->option3 ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        </label>
                        <label class="flex items-center justify-center text-gray-600">
                            <input type="radio" name="option" value="4" required class="mr-2 accent-blue-600">
                            <span><?php echo htmlspecialchars($first_question->option4 ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        </label>
                    </div>
                    <button type="submit" class="btn-primary w-full ">Suivant</button>
                </form>
            <?php else: ?>
                <p class="text-red-500 text-center text-lg">Aucune question disponible. Veuillez contacter l'administrateur.</p>
                <a href="/quiz/results" class="btn-primary w-full max-w-xs mx-auto mt-4 text-center">Voir les résultats</a>
            <?php endif; ?>
            <script>
                const timeLimit = 300; // 5 minutes en secondes
                let remainingTime = <?php echo $remaining_time ?? 300; ?>;
                const timerElement = document.getElementById('timer');

                function updateTimer() {
                    if (remainingTime <= 0) {
                        window.location.href = '/quiz/results';
                        return;
                    }
                    const minutes = Math.floor(remainingTime / 60);
                    const seconds = remainingTime % 60;
                    timerElement.textContent = `Temps restant : ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                    remainingTime--;
                }

                updateTimer();
                setInterval(updateTimer, 1000);
            </script>
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>