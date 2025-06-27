<?php

namespace App\Models;

use PDO;

class User {
    public $id;
    public $username;
    public $password;
    public $extension;
    public $is_admin;

    public static function findByUsername($username) {
        $db = \Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $data = $stmt->fetch();
        if ($data) {
            $user = new self();
            $user->id = $data['id'];
            $user->username = $data['username'];
            $user->password = $data['password'];
            $user->extension = $data['extension'];
            $user->is_admin = (bool)$data['is_admin'];
            return $user;
        }
        return null;
    }

    public static function findById($id) {
        $db = \Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();
        if ($data) {
            $user = new self();
            $user->id = $data['id'];
            $user->username = $data['username'];
            $user->password = $data['password'];
            $user->extension = $data['extension'];
            $user->is_admin = (bool)$data['is_admin'];
            return $user;
        }
        return null;
    }

    public static function getAll() {
        $db = \Database::getConnection();
        $stmt = $db->query("SELECT * FROM users");
        $users = [];
        while ($data = $stmt->fetch()) {
            $user = new self();
            $user->id = $data['id'];
            $user->username = $data['username'];
            $user->password = $data['password'];
            $user->extension = $data['extension'];
            $user->is_admin = (bool)$data['is_admin'];
            $users[] = $user;
        }
        return $users;
    }

    public function save() {
        $db = \Database::getConnection();
        $stmt = $db->prepare("INSERT INTO users (username, password, extension, is_admin) VALUES (:username, :password, :extension, :is_admin)");
        return $stmt->execute([
            'username' => $this->username,
            'password' => $this->password,
            'extension' => $this->extension,
            'is_admin' => $this->is_admin ? 1 : 0
        ]);
    }
}