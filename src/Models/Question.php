<?php

namespace App\Models;

use PDO;

class Question {
    public $id;
    public $text;
    public $option1;
    public $option2;
    public $option3;
    public $option4;
    public $correct_option;

    public static function getAll() {
        $db = \Database::getConnection();
        $stmt = $db->query("SELECT * FROM questions ORDER BY id");
        $questions = [];
        while ($data = $stmt->fetch()) {
            $question = new self();
            $question->id = $data['id'];
            $question->text = $data['text'];
            $question->option1 = $data['option1'];
            $question->option2 = $data['option2'];
            $question->option3 = $data['option3'];
            $question->option4 = $data['option4'];
            $question->correct_option = $data['correct_option'];
            $questions[] = $question;
        }
        return $questions;
    }

    public static function findById($id) {
        $db = \Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM questions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();
        if ($data) {
            $question = new self();
            $question->id = $data['id'];
            $question->text = $data['text'];
            $question->option1 = $data['option1'];
            $question->option2 = $data['option2'];
            $question->option3 = $data['option3'];
            $question->option4 = $data['option4'];
            $question->correct_option = $data['correct_option'];
            return $question;
        }
        return null;
    }

    public static function count() {
        $db = \Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM questions");
        return $stmt->fetchColumn();
    }

    public function save() {
        $db = \Database::getConnection();
        $stmt = $db->prepare("INSERT INTO questions (text, option1, option2, option3, option4, correct_option) VALUES (:text, :option1, :option2, :option3, :option4, :correct_option)");
        return $stmt->execute([
            'text' => $this->text,
            'option1' => $this->option1,
            'option2' => $this->option2,
            'option3' => $this->option3,
            'option4' => $this->option4,
            'correct_option' => $this->correct_option
        ]);
    }
}