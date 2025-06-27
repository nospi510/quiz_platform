<?php

namespace App\Controllers;

use App\Models\User;

class AuthController {
    public function showRegister() {
        if (isset($_SESSION['user_id'])) {
            header('Location: /quiz');
            exit;
        }
        require_once __DIR__ . '/../Views/auth/register.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
            $password = htmlspecialchars(trim($_POST['password'] ?? ''), ENT_QUOTES, 'UTF-8');

            if (empty($username) || empty($password)) {
                error_log("Inscription échouée : Tous les champs sont requis.");
                $_SESSION['error'] = 'Tous les champs sont requis.';
                header('Location: /auth/register');
                exit;
            }

            if (User::findByUsername($username)) {
                error_log("Inscription échouée : Nom d’utilisateur $username déjà pris.");
                $_SESSION['error'] = 'Nom d’utilisateur déjà pris.';
                header('Location: /auth/register');
                exit;
            }

            $user = new User();
            $user->username = $username;
            $user->password = password_hash($password, PASSWORD_DEFAULT);
            $user->is_admin = false;

            try {
                if ($user->save()) {
                    error_log("Utilisateur $username créé dans la base de données.");
                    $_SESSION['success'] = 'Inscription réussie ! Vous pouvez vous connecter.';
                    header('Location: /auth/login');
                } else {
                    error_log("Erreur lors de l’inscription de $username dans la base de données.");
                    $_SESSION['error'] = 'Erreur lors de l’inscription dans la base de données.';
                    header('Location: /auth/register');
                }
            } catch (Exception $e) {
                error_log("Erreur lors de l'inscription de $username : " . $e->getMessage());
                $_SESSION['error'] = 'Erreur lors de l’inscription : ' . $e->getMessage();
                header('Location: /auth/register');
            }
            exit;
        }
    }

    public function showLogin() {
        if (isset($_SESSION['user_id'])) {
            header('Location: /quiz');
            exit;
        }
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
            $password = htmlspecialchars(trim($_POST['password'] ?? ''), ENT_QUOTES, 'UTF-8');

            $user = User::findByUsername($username);
            if ($user && password_verify($password, $user->password)) {
                error_log("Connexion réussie pour $username.");
                $_SESSION['user_id'] = $user->id;
                $_SESSION['success'] = 'Connexion réussie !';
                header('Location: /quiz');
            } else {
                error_log("Échec de connexion pour $username : identifiants incorrects.");
                $_SESSION['error'] = 'Identifiants incorrects.';
                header('Location: /auth/login');
            }
            exit;
        }
    }

    public function logout() {
        error_log("Déconnexion de l'utilisateur ID {$_SESSION['user_id']}.");
        session_unset();
        session_destroy();
        $_SESSION['success'] = 'Déconnexion réussie.';
        header('Location: /auth/login');
        exit;
    }
}