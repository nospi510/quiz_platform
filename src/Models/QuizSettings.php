<?php

namespace App\Models;

use PDO;

class QuizSettings {
    private $db;

    public $id;
    public $is_active;
    public $start_time;

    public function __construct() {
        $this->db = \Database::getConnection();
    }

    public static function getSettings() {
        $db = \Database::getConnection();
        $stmt = $db->query("SELECT * FROM quiz_settings WHERE id = 1");
        $settings = $stmt->fetch(PDO::FETCH_OBJ);
        $instance = new self();
        $instance->id = $settings->id;
        $instance->is_active = (bool) $settings->is_active;
        $instance->start_time = $settings->start_time;
        return $instance;
    }

    public function save() {
        $stmt = $this->db->prepare("UPDATE quiz_settings SET is_active = ?, start_time = ? WHERE id = 1");
        $is_active = $this->is_active ? 1 : 0;
        return $stmt->execute([$is_active, $this->start_time]);
    }
}