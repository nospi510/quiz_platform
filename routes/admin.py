from flask import Blueprint, render_template, redirect, url_for, flash, request
from flask_login import login_required, current_user
from models.user import User
from models.question import Question
from models.user_answer import UserAnswer
from models import db
from forms.auth_forms import RegistrationForm
from werkzeug.security import generate_password_hash
import os
import logging

# Configure logging
logging.basicConfig(
    filename='asterisk_config.log',
    level=logging.DEBUG,
    format='%(asctime)s %(levelname)s: %(message)s'
)

admin_bp = Blueprint('admin', __name__)

def write_asterisk_config(username, password, extension):
    logging.debug(f"Tentative d'écriture de la configuration Asterisk pour l'utilisateur {username} avec l'extension {extension}")
    
    # Vérifier les permissions des fichiers
    pjsip_file = '/etc/asterisk/pjsip.conf'
    try:
        if not os.access(pjsip_file, os.W_OK):
            logging.error(f"Permission d'écriture refusée pour {pjsip_file}")
            raise PermissionError(f"Impossible d'écrire dans {pjsip_file}")
    except Exception as e:
        logging.error(f"Erreur lors de la vérification des permissions pour {pjsip_file}: {str(e)}")
        raise

    # Vérifier si l'utilisateur existe déjà dans pjsip.conf
    try:
        if os.path.exists(pjsip_file):
            with open(pjsip_file, 'r') as f:
                content = f.read()
            if f"[{username}]" in content:
                logging.debug(f"Endpoint {username} déjà présent dans {pjsip_file}, aucune modification nécessaire")
                # Vérifier extensions.conf même si l'endpoint existe
                update_extensions_conf(username, extension)
                return
    except Exception as e:
        logging.error(f"Erreur lors de la lecture de {pjsip_file}: {str(e)}")
        raise

    # Configuration PJSIP
    pjsip_conf = f"""
[{username}](endpoint_internal)
auth={username}
aors={username}

[{username}](auth_userpass)
password={password}
username={username}

[{username}](aor_dynamic)
"""
    try:
        with open(pjsip_file, 'a') as f:
            f.write(pjsip_conf)
        logging.info(f"Configuration PJSIP écrite avec succès pour {username}")
    except Exception as e:
        logging.error(f"Erreur lors de l'écriture dans {pjsip_file}: {str(e)}")
        raise

    # Mettre à jour extensions.conf
    update_extensions_conf(username, extension)

def update_extensions_conf(username, extension):
    extensions_file = '/etc/asterisk/extensions.conf'
    context = '[from-internal]'
    new_line = f"exten => {extension},1,Dial(PJSIP/{username},10)\n"
    
    try:
        # Lire le contenu actuel
        if os.path.exists(extensions_file):
            with open(extensions_file, 'r') as f:
                lines = f.readlines()
        else:
            lines = []

        # Vérifier si la ligne existe déjà
        if new_line in lines or any(f"exten => {extension}," in line for line in lines):
            logging.debug(f"Ligne pour l'extension {extension} déjà présente dans {extensions_file}")
            return

        # Trouver ou créer le contexte [from-internal]
        context_exists = any(context in line for line in lines)
        new_lines = []
        context_found = False
        in_context = False

        for line in lines:
            stripped_line = line.strip()
            if stripped_line == context:
                in_context = True
                context_found = True
            elif stripped_line.startswith('[') and in_context:
                # Fin du contexte, ajouter la nouvelle ligne avant le nouveau contexte
                new_lines.append(new_line)
                in_context = False
            new_lines.append(line)

        if in_context:
            # Ajouter à la fin du contexte si on est toujours dedans
            new_lines.append(new_line)
        elif not context_found:
            # Créer le contexte si inexistant
            new_lines.append(f"\n{context}\n")
            new_lines.append(new_line)

        # Écrire le nouveau contenu
        with open(extensions_file, 'w') as f:
            f.writelines(new_lines)
        logging.info(f"Ligne pour l'extension {extension} ajoutée à {extensions_file} dans le contexte {context}")
    except Exception as e:
        logging.error(f"Erreur lors de la gestion de {extensions_file}: {str(e)}")
        raise

    # Recharger Asterisk
    try:
        result = os.system('asterisk -rx "core reload"')
        if result != 0:
            logging.error("Échec du rechargement d'Asterisk")
            raise RuntimeError("Échec du rechargement d'Asterisk")
        logging.info("Asterisk rechargé avec succès")
    except Exception as e:
        logging.error(f"Erreur lors du rechargement d'Asterisk: {str(e)}")
        raise

@admin_bp.route('/results')
@login_required
def admin_results():
    if not current_user.is_admin:
        flash('Accès réservé à l’administrateur.', 'error')
        return redirect(url_for('quiz.quiz'))
    users = User.query.all()
    results = []
    for user in users:
        correct = db.session.query(UserAnswer).join(Question).filter(
            UserAnswer.user_id == user.id,
            UserAnswer.selected_option == Question.correct_option
        ).count()
        total = UserAnswer.query.filter_by(user_id=user.id).count()
        # Charger les réponses avec les questions associées
        answers = db.session.query(UserAnswer, Question).join(Question, UserAnswer.question_id == Question.id).filter(
            UserAnswer.user_id == user.id
        ).all()
        formatted_answers = []
        for user_answer, question in answers:
            # Récupérer le texte de l'option sélectionnée
            selected_option_text = getattr(question, f'option{user_answer.selected_option}')
            # Récupérer le texte de l'option correcte
            correct_option_text = getattr(question, f'option{question.correct_option}')
            formatted_answers.append({
                'user_answer': user_answer,
                'question': question,
                'selected_option_text': selected_option_text,
                'correct_option_text': correct_option_text
            })
        results.append({
            'username': user.username,
            'correct': correct,
            'incorrect': total - correct,
            'answers': formatted_answers
        })
    return render_template('admin_results.html', results=results)

@admin_bp.route('/create_admin', methods=['GET', 'POST'])
@login_required
def create_admin():
    if not current_user.is_admin:
        flash('Accès réservé à l’administrateur.', 'error')
        return redirect(url_for('quiz.quiz'))
    form = RegistrationForm()
    if form.validate_on_submit():
        try:
            username = form.username.data
            password = generate_password_hash(form.password.data)
            extension = form.extension.data
            user = User(username=username, password=password, extension=extension, is_admin=True)
            db.session.add(user)
            db.session.commit()
            logging.info(f"Administrateur {username} ajouté à la base de données avec l'extension {extension}")
            write_asterisk_config(username, form.password.data, extension)
            flash(f'Administrateur {username} créé avec succès !', 'success')
            return redirect(url_for('admin.admin_results'))
        except Exception as e:
            db.session.rollback()
            logging.error(f"Erreur lors de la création de l'admin {username}: {str(e)}")
            flash(f"Erreur lors de la création de l'admin: {str(e)}", 'error')
    return render_template('create_admin.html', form=form)