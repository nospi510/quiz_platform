<?php

namespace App\Controllers;

use App\Models\User;
use Exception;

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
            $extension = htmlspecialchars(trim($_POST['extension'] ?? ''), ENT_QUOTES, 'UTF-8');

            if (empty($username) || empty($password) || empty($extension)) {
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
            $user->extension = $extension;
            $user->is_admin = false;

            try {
                if ($user->save()) {
                    error_log("Utilisateur $username créé dans la base de données.");
                    $this->writeAsteriskConfig($username, $password, $extension);
                    error_log("Configuration Asterisk terminée pour $username.");
                    $_SESSION['success'] = 'Inscription réussie ! Vous pouvez vous connecter.';
                    header('Location: /auth/login');
                } else {
                    error_log("Erreur lors de l’inscription de $username dans la base de données.");
                    $_SESSION['error'] = 'Erreur lors de l’inscription dans la base de données.';
                    header('Location: /auth/register');
                }
            } catch (Exception $e) {
                error_log("Erreur lors de l'inscription de $username : " . $e->getMessage());
                $_SESSION['error'] = 'Erreur lors de la configuration Asterisk : ' . $e->getMessage();
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

    private function writeAsteriskConfig($username, $password, $extension) {
        $pjsip_file = '/etc/asterisk/pjsip.conf';
        error_log("Vérification des permissions pour $pjsip_file...");
        if (!is_writable($pjsip_file)) {
            $error = "Permission d’écriture refusée pour $pjsip_file";
            error_log($error);
            throw new Exception($error);
        }

        // Vérifier si l’utilisateur existe déjà dans pjsip.conf
        $content = file_exists($pjsip_file) ? file_get_contents($pjsip_file) : '';
        if (strpos($content, "[$username]") !== false) {
            error_log("Endpoint $username déjà présent dans $pjsip_file");
            $this->updateExtensionsConf($username, $extension);
            return;
        }

        // Configuration PJSIP
        $pjsip_conf = <<<EOT

[$username](endpoint_internal)
auth=$username
aors=$username

[$username](auth_userpass)
password=$password
username=$username

[$username](aor_dynamic)

EOT;
        error_log("Écriture de la configuration PJSIP pour $username...");
        if (file_put_contents($pjsip_file, $pjsip_conf, FILE_APPEND) === false) {
            $error = "Échec de l’écriture dans $pjsip_file";
            error_log($error);
            throw new Exception($error);
        }
        error_log("Configuration PJSIP écrite pour $username");

        // Mettre à jour extensions.conf
        $this->updateExtensionsConf($username, $extension);

        // Recharger Asterisk
        error_log("Rechargement d’Asterisk...");
        $result = shell_exec('sudo asterisk -rx "core reload" 2>&1');
        if ($result === null || strpos($result, 'error') !== false) {
            $error = "Échec du rechargement d’Asterisk : $result";
            error_log($error);
            throw new Exception($error);
        }
        error_log("Asterisk rechargé avec succès");
    }

    private function updateExtensionsConf($username, $extension) {
        $extensions_file = '/etc/asterisk/extensions.conf';
        error_log("Vérification des permissions pour $extensions_file...");
        if (!is_writable($extensions_file)) {
            $error = "Permission d’écriture refusée pour $extensions_file";
            error_log($error);
            throw new Exception($error);
        }

        $context = '[from-internal]';
        $new_line = "exten => $extension,1,Dial(PJSIP/$username,10)\n";

        $content = file_exists($extensions_file) ? file($extensions_file) : [];
        if (in_array($new_line, $content) || strpos(implode('', $content), "exten => $extension,") !== false) {
            error_log("Ligne pour l’extension $extension déjà présente dans $extensions_file");
            return;
        }

        $new_lines = [];
        $context_found = false;
        $in_context = false;

        foreach ($content as $line) {
            $stripped_line = trim($line);
            if ($stripped_line === $context) {
                $in_context = true;
                $context_found = true;
            } elseif (strpos($stripped_line, '[') === 0 && $in_context) {
                $new_lines[] = $new_line;
                $in_context = false;
            }
            $new_lines[] = $line;
        }

        if ($in_context) {
            $new_lines[] = $new_line;
        } elseif (!$context_found) {
            $new_lines[] = "\n$context\n";
            $new_lines[] = $new_line;
        }

        error_log("Écriture de l’extension $extension dans $extensions_file...");
        if (file_put_contents($extensions_file, implode('', $new_lines)) === false) {
            $error = "Échec de l’écriture dans $extensions_file";
            error_log($error);
            throw new Exception($error);
        }
        error_log("Ligne pour l’extension $extension ajoutée à $extensions_file");
    }
}