<?php

namespace App\Controllers;

use App\Models\Question;
use App\Models\UserAnswer;

class QuizController {
    public function showQuiz() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Vous devez être connecté pour accéder au quiz.';
            header('Location: /auth/login');
            exit;
        }

        $total_questions = Question::count();
        if ($total_questions === 0) {
            $_SESSION['error'] = 'Aucune question disponible dans le quiz.';
            header('Location: /quiz/results');
            exit;
        }

        // Vérifier si l'utilisateur a déjà répondu à toutes les questions
        $user_answers_count = UserAnswer::countByUserId($_SESSION['user_id']);
        if ($user_answers_count >= $total_questions) {
            error_log("Utilisateur {$_SESSION['user_id']} a répondu à toutes les questions ($user_answers_count/$total_questions), redirection vers les résultats.");
            $_SESSION['error'] = 'Vous avez déjà complété le quiz. Consultez vos résultats.';
            header('Location: /quiz/results');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $question_id = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);
            $selected_option = filter_input(INPUT_POST, 'option', FILTER_VALIDATE_INT);

            if ($question_id && $selected_option) {
                // Vérifier si la réponse n'a pas déjà été enregistrée pour cette question
                $db = \Database::getConnection();
                $stmt = $db->prepare("SELECT COUNT(*) FROM user_answers WHERE user_id = :user_id AND question_id = :question_id");
                $stmt->execute(['user_id' => $_SESSION['user_id'], 'question_id' => $question_id]);
                if ($stmt->fetchColumn() == 0) {
                    $answer = new UserAnswer();
                    $answer->user_id = $_SESSION['user_id'];
                    $answer->question_id = $question_id;
                    $answer->selected_option = $selected_option;
                    if ($answer->save()) {
                        error_log("Réponse enregistrée pour l'utilisateur {$_SESSION['user_id']}, question $question_id, option $selected_option");
                    } else {
                        error_log("Échec de l'enregistrement de la réponse pour l'utilisateur {$_SESSION['user_id']}, question $question_id");
                        $_SESSION['error'] = 'Erreur lors de l’enregistrement de la réponse.';
                    }
                } else {
                    error_log("Réponse déjà enregistrée pour l'utilisateur {$_SESSION['user_id']}, question $question_id");
                }

                // Trouver la prochaine question non répondue
                $next_question_id = $this->getNextUnansweredQuestionId($_SESSION['user_id'], $total_questions);
                if ($next_question_id === null) {
                    error_log("Aucune question non répondue pour l'utilisateur {$_SESSION['user_id']}, redirection vers les résultats");
                    header('Location: /quiz/results');
                    exit;
                }

                $first_question = Question::findById($next_question_id);
                $question_number = $next_question_id;
            } else {
                error_log("Données POST invalides : question_id=$question_id, option=$selected_option");
                $_SESSION['error'] = 'Veuillez sélectionner une option.';
                $first_question = Question::findById($question_id ?: $this->getNextUnansweredQuestionId($_SESSION['user_id'], $total_questions) ?: 1);
                $question_number = $question_id ?: $this->getNextUnansweredQuestionId($_SESSION['user_id'], $total_questions) ?: 1;
            }
        } else {
            // Afficher la première question non répondue
            $next_question_id = $this->getNextUnansweredQuestionId($_SESSION['user_id'], $total_questions) ?: 1;
            $first_question = Question::findById($next_question_id);
            $question_number = $next_question_id;

            if (!$first_question) {
                error_log("Aucune question trouvée pour ID=$next_question_id, redirection vers les résultats");
                $_SESSION['error'] = 'Aucune question disponible.';
                header('Location: /quiz/results');
                exit;
            }
        }

        require_once __DIR__ . '/../Views/quiz/quiz.php';
    }

    private function getNextUnansweredQuestionId($user_id, $total_questions) {
        $db = \Database::getConnection();
        for ($i = 1; $i <= $total_questions; $i++) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM user_answers WHERE user_id = :user_id AND question_id = :question_id");
            $stmt->execute(['user_id' => $user_id, 'question_id' => $i]);
            if ($stmt->fetchColumn() == 0) {
                return $i;
            }
        }
        return null; // Toutes les questions ont été répondues
    }

    public function showResults() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Vous devez être connecté pour voir vos résultats.';
            header('Location: /auth/login');
            exit;
        }

        $correct_answers = UserAnswer::countCorrectByUserId($_SESSION['user_id']);
        $total_questions = UserAnswer::countByUserId($_SESSION['user_id']);
        require_once __DIR__ . '/../Views/quiz/results.php';
    }
}