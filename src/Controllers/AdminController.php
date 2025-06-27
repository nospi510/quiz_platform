<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserAnswer;

class AdminController {
    public function showResults() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Vous devez être connecté pour accéder à cette page.';
            header('Location: /auth/login');
            exit;
        }

        $user = User::findById($_SESSION['user_id']);
        if (!$user->is_admin) {
            $_SESSION['error'] = 'Accès réservé aux administrateurs.';
            header('Location: /quiz');
            exit;
        }

        $users = User::getAll();
        $results = [];
        foreach ($users as $user) {
            $correct = UserAnswer::countCorrectByUserId($user->id);
            $total = UserAnswer::countByUserId($user->id);
            $answers = UserAnswer::findByUserId($user->id);
            $formatted_answers = [];
            foreach ($answers as $answer) {
                $formatted_answers[] = [
                    'user_answer' => $answer,
                    'question' => $answer->question,
                    'selected_option_text' => $answer->question->{"option{$answer->selected_option}"},
                    'correct_option_text' => $answer->question->{"option{$answer->question->correct_option}"}
                ];
            }
            $results[] = [
                'username' => $user->username,
                'correct' => $correct,
                'incorrect' => $total - $correct,
                'answers' => $formatted_answers
            ];
        }

        require_once __DIR__ . '/../Views/admin/results.php';
    }

    public function showCreateAdmin() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Vous devez être connecté pour accéder à cette page.';
            header('Location: /auth/login');
            exit;
        }

        $user = User::findById($_SESSION['user_id']);
        if (!$user->is_admin) {
            $_SESSION['error'] = 'Accès réservé aux administrateurs.';
            header('Location: /quiz');
            exit;
        }

        require_once __DIR__ . '/../Views/auth/create_admin.php';
    }

    public function createAdmin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Vous devez être connecté pour créer un admin.';
                header('Location: /auth/login');
                exit;
            }

            $user = User::findById($_SESSION['user_id']);
            if (!$user->is_admin) {
                $_SESSION['error'] = 'Accès réservé aux administrateurs.';
                header('Location: /quiz');
                exit;
            }

            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
            $extension = filter_input(INPUT_POST, 'extension', FILTER_SANITIZE_STRING);

            if (empty($username) || empty($password) || empty($extension)) {
                $_SESSION['error'] = 'Tous les champs sont requis.';
                header('Location: /admin/create_admin');
                exit;
            }

            if (User::findByUsername($username)) {
                $_SESSION['error'] = 'Nom d’utilisateur déjà pris.';
                header('Location: /admin/create_admin');
                exit;
            }

            $new_user = new User();
            $new_user->username = $username;
            $new_user->password = password_hash($password, PASSWORD_DEFAULT);
            $new_user->extension = $extension;
            $new_user->is_admin = true;

            try {
                if ($new_user->save()) {
                    $auth_controller = new AuthController();
                    $auth_controller->writeAsteriskConfig($username, $password, $extension);
                    $_SESSION['success'] = "Administrateur $username créé avec succès !";
                    header('Location: /admin/results');
                } else {
                    $_SESSION['error'] = 'Erreur lors de la création de l’admin.';
                    header('Location: /admin/create_admin');
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
                header('Location: /admin/create_admin');
            }
            exit;
        }
    }
}