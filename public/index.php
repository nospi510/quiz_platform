<?php

session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use App\Controllers\AuthController;
use App\Controllers\QuizController;
use App\Controllers\AdminController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$authController = new AuthController();
$quizController = new QuizController();
$adminController = new AdminController();

switch ($uri) {
    case '/auth/register':
        if ($method === 'GET') {
            $authController->showRegister();
        } elseif ($method === 'POST') {
            $authController->register();
        }
        break;
    case '/auth/login':
        if ($method === 'GET') {
            $authController->showLogin();
        } elseif ($method === 'POST') {
            $authController->login();
        }
        break;
    case '/auth/logout':
        $authController->logout();
        break;
    case '/quiz':
        if ($method === 'GET' || $method === 'POST') {
            $quizController->showQuiz();
        }
        break;
    case '/quiz/results':
        $quizController->showResults();
        break;
    case '/admin/results':
        if ($method === 'GET' || $method === 'POST') {
            $adminController->showResults();
        }
        break;
    case '/admin/create_admin':
        if ($method === 'GET') {
            $adminController->showCreateAdmin();
        } elseif ($method === 'POST') {
            $adminController->createAdmin();
        }
        break;
    case '/admin/quiz_settings':
        if ($method === 'GET') {
            $adminController->showQuizSettings();
        } elseif ($method === 'POST') {
            $adminController->updateQuizSettings();
        }
        break;
    default:
        header('Location: /auth/login');
        exit;
}