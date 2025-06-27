<?php
$title = 'Résultats Admin';
ob_start();
?>
<div class="card">
    <h2 class="text-2xl font-bold mb-6 text-center text-blue-800">Résultats des Participants</h2>
    <div class="overflow-x-auto">
        <table class="w-full table-auto border-collapse">
            <thead>
                <tr class="bg-blue-600 text-white">
                    <th class="p-3 text-left">Utilisateur</th>
                    <th class="p-3 text-left">Bonnes réponses</th>
                    <th class="p-3 text-left">Mauvaises réponses</th>
                    <th class="p-3 text-left">Détails</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr class="border-b hover:bg-gray-50 transition-all duration-200">
                        <td class="p-3"><?php echo htmlspecialchars($result['username']); ?></td>
                        <td class="p-3"><?php echo $result['correct']; ?></td>
                        <td class="p-3"><?php echo $result['incorrect']; ?></td>
                        <td class="p-3">
                            <ul class="list-disc pl-5 space-y-2">
                                <?php foreach ($result['answers'] as $answer): ?>
                                    <li class="text-sm">
                                        <span class="font-medium">Question <?php echo $answer['user_answer']->question_id; ?> :</span> 
                                        <?php echo htmlspecialchars($answer['question']->text); ?><br>
                                        <span class="text-gray-600">Réponse : <?php echo htmlspecialchars($answer['selected_option_text']); ?></span>
                                        <span class="<?php echo $answer['user_answer']->selected_option == $answer['question']->correct_option ? 'text-green-500' : 'text-red-500'; ?>">
                                            <?php echo $answer['user_answer']->selected_option == $answer['question']->correct_option ? '(Correcte)' : '(Incorrecte, Bonne réponse : ' . htmlspecialchars($answer['correct_option_text']) . ')'; ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>