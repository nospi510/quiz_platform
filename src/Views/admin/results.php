<?php
$title = 'Résultats Admin';
ob_start();
?>
<h1 class="text-2xl font-bold mb-4">Résultats des Participants</h1>
<table class="w-full border-collapse border">
    <thead>
        <tr class="bg-gray-200">
            <th class="border p-2">Utilisateur</th>
            <th class="border p-2">Bonnes réponses</th>
            <th class="border p-2">Mauvaises réponses</th>
            <th class="border p-2">Détails</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $result): ?>
            <tr>
                <td class="border p-2"><?php echo htmlspecialchars($result['username']); ?></td>
                <td class="border p-2"><?php echo $result['correct']; ?></td>
                <td class="border p-2"><?php echo $result['incorrect']; ?></td>
                <td class="border p-2">
                    <ul>
                        <?php foreach ($result['answers'] as $answer): ?>
                            <li>
                                Question <?php echo $answer['user_answer']->question_id; ?> : <?php echo htmlspecialchars($answer['question']->text); ?><br>
                                Réponse : <?php echo htmlspecialchars($answer['selected_option_text']); ?>
                                <?php if ($answer['user_answer']->selected_option == $answer['question']->correct_option): ?>
                                    (Correcte)
                                <?php else: ?>
                                    (Incorrecte, Bonne réponse : <?php echo htmlspecialchars($answer['correct_option_text']); ?>)
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>