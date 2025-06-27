<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use App\Models\Question;

$questions = json_decode(file_get_contents(__DIR__ . '/../data/questions.json'), true);

foreach ($questions as $q) {
    $existing = \App\Models\Question::findById($q['id'] ?? 0);
    if (!$existing) {
        $question = new Question();
        $question->text = $q['text'];
        $question->option1 = $q['options'][0];
        $question->option2 = $q['options'][1];
        $question->option3 = $q['options'][2];
        $question->option4 = $q['options'][3];
        $question->correct_option = $q['correct_option'];
        $question->save();
    }
}

echo "Questions chargées avec succès.\n";