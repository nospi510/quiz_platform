<?php

namespace App\Models;

use PDO;

class UserAnswer {
    public $id;
    public $user_id;
    public $question_id;
    public $selected_option;
    public $question; 

    public static function findByUserId($user_id) {
        $db = \Database::getConnection();
        $stmt = $db->prepare("SELECT ua.*, q.text, q.option1, q.option2, q.option3, q.option4, q.correct_option 
                              FROM user_answers ua 
                              JOIN questions q ON ua.question_id = q.id 
                              WHERE ua.user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $answers = [];
        while ($data = $stmt->fetch()) {
            $answer = new self();
            $answer->id = $data['id'];
            $answer->user_id = $data['user_id'];
            $answer->question_id = $data['question_id'];
            $answer->selected_option = $data['selected_option'];
            $answer->question = new Question();
            $answer->question->id = $data['question_id'];
            $answer->question->text = $data['text'];
            $answer->question->option1 = $data['option1'];
            $answer->question->option2 = $data['option2'];
            $answer->question->option3 = $data['option3'];
            $answer->question->option4 = $data['option4'];
            $answer->question->correct_option = $data['correct_option'];
            $answers[] = $answer;
        }
        return $answers;
    }

    public static function countCorrectByUserId($user_id) {
        $db = \Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) 
                              FROM user_answers ua 
                              JOIN questions q ON ua.question_id = q.id 
                              WHERE ua.user_id = :user_id AND ua.selected_option = q.correct_option");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchColumn();
    }

    public static function countByUserId($user_id) {
        $db = \Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM user_answers WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchColumn();
    }

    public function save() {
        $db = \Database::getConnection();
        $stmt = $db->prepare("INSERT INTO user_answers (user_id, question_id, selected_option) VALUES (:user_id, :question_id, :selected_option)");
        return $stmt->execute([
            'user_id' => $this->user_id,
            'question_id' => $this->question_id,
            'selected_option' => $this->selected_option
        ]);
    }
}