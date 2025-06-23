from flask import Flask
from flask_sqlalchemy import SQLAlchemy
from flask_login import LoginManager
from flask_migrate import Migrate
from config import Config
from models import db
from routes.auth import auth_bp
from routes.quiz import quiz_bp
from routes.admin import admin_bp
import json

app = Flask(__name__)
app.config.from_object(Config)
db.init_app(app)
migrate = Migrate(app, db)
login_manager = LoginManager(app)
login_manager.login_view = 'auth.login'

# Charger l'utilisateur pour Flask-Login
from models.user import User
@login_manager.user_loader
def load_user(user_id):
    return db.session.get(User, int(user_id))

# Charger les questions depuis JSON
def load_questions():
    from models.question import Question
    with open('questions.json', 'r') as f:
        questions = json.load(f)
    for q in questions:
        if not Question.query.filter_by(text=q['text']).first():
            question = Question(
                text=q['text'],
                option1=q['options'][0],
                option2=q['options'][1],
                option3=q['options'][2],
                option4=q['options'][3],
                correct_option=q['correct_option']
            )
            db.session.add(question)
    db.session.commit()

# Enregistrer les blueprints
app.register_blueprint(auth_bp, url_prefix='/auth')
app.register_blueprint(quiz_bp, url_prefix='/quiz')
app.register_blueprint(admin_bp, url_prefix='/admin')

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
        load_questions()
    app.run(debug=True, host="0.0.0.0", port=5001)