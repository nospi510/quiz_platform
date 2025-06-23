from flask import Blueprint, render_template, redirect, url_for, request
from flask_login import login_required, current_user
from models.question import Question
from models.user_answer import UserAnswer
from models import db

quiz_bp = Blueprint('quiz', __name__)

@quiz_bp.route('/', methods=['GET', 'POST'])
@login_required
def quiz():
    total_questions = Question.query.count()
    if request.method == 'POST':
        question_id = request.form.get('question_id')
        selected_option = request.form.get('option')
        if question_id and selected_option:
            answer = UserAnswer(
                user_id=current_user.id,
                question_id=int(question_id),
                selected_option=int(selected_option)
            )
            db.session.add(answer)
            db.session.commit()
        next_question = Question.query.filter(Question.id > int(question_id)).order_by(Question.id).first()
        if not next_question:
            return redirect(url_for('quiz.results'))
        return render_template('quiz.html', question=next_question, question_number=Question.query.filter(Question.id <= next_question.id).count(), total_questions=total_questions)
    first_question = Question.query.order_by(Question.id).first()
    return render_template('quiz.html', question=first_question, question_number=1, total_questions=total_questions)

@quiz_bp.route('/results')
@login_required
def results():
    correct_answers = db.session.query(UserAnswer).join(Question).filter(
        UserAnswer.user_id == current_user.id,
        UserAnswer.selected_option == Question.correct_option
    ).count()
    total_questions = Question.query.count()
    return render_template('results.html', correct=correct_answers, total=total_questions)