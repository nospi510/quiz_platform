from flask import Blueprint, render_template, redirect, url_for, flash
from flask_login import login_user, logout_user, current_user
from werkzeug.security import generate_password_hash, check_password_hash
from forms.auth_forms import RegistrationForm, LoginForm
from models.user import User
from models import db
import mysql.connector
import os
import logging
import sys

# Configure logging
logging.basicConfig(
    filename='asterisk_config.log',
    level=logging.DEBUG,
    format='%(asctime)s %(levelname)s: %(message)s'
)

auth_bp = Blueprint('auth', __name__)

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

def check_user_calls(extension):
    try:
        conn = mysql.connector.connect(
            host='localhost',
            user='nick',
            password='passer',
            database='asteriskCDR'
        )
        cursor = conn.cursor()
        cursor.execute("SELECT COUNT(*) FROM cdr WHERE src = %s OR dst = %s", (extension, extension))
        count = cursor.fetchone()[0]
        cursor.close()
        conn.close()
        logging.debug(f"Vérification des appels pour l'extension {extension}: {count} appels trouvés")
        return count
    except Exception as e:
        logging.error(f"Erreur lors de la vérification des appels pour {extension}: {str(e)}")
        raise

@auth_bp.route('/register', methods=['GET', 'POST'])
def register():
    if current_user.is_authenticated:
        return redirect(url_for('quiz.quiz'))
    form = RegistrationForm()
    if form.validate_on_submit():
        try:
            username = form.username.data
            password = generate_password_hash(form.password.data)
            extension = form.extension.data
            user = User(username=username, password=password, extension=extension)
            db.session.add(user)
            db.session.commit()
            logging.info(f"Utilisateur {username} ajouté à la base de données avec l'extension {extension}")
            write_asterisk_config(username, form.password.data, extension)
            flash('Inscription réussie ! Vous pouvez maintenant vous connecter.', 'success')
            return redirect(url_for('auth.login'))
        except Exception as e:
            db.session.rollback()
            logging.error(f"Erreur lors de l'inscription de {username}: {str(e)}")
            flash(f"Erreur lors de l'inscription: {str(e)}", 'error')
    return render_template('register.html', form=form)

@auth_bp.route('/login', methods=['GET', 'POST'])
def login():
    if current_user.is_authenticated:
        return redirect(url_for('quiz.quiz'))
    form = LoginForm()
    if form.validate_on_submit():
        try:
            user = User.query.filter_by(username=form.username.data).first()
            if user and check_password_hash(user.password, form.password.data):
                user.calls_made = check_user_calls(user.extension)
                db.session.commit()
                if user.calls_made > 0:
                    login_user(user)
                    logging.info(f"Connexion réussie pour l'utilisateur {user.username}")
                    flash('Connexion réussie ! Vous pouvez participer au quiz.', 'success')
                    return redirect(url_for('quiz.quiz'))
                else:
                    logging.warning(f"Échec de connexion pour {user.username}: aucun appel enregistré")
                    flash('Vous devez passer au moins un appel avec votre extension avant de participer au quiz.', 'error')
            else:
                logging.warning(f"Échec de connexion: identifiants incorrects pour {form.username.data}")
                flash('Identifiants incorrects.', 'error')
        except Exception as e:
            logging.error(f"Erreur lors de la connexion pour {form.username.data}: {str(e)}")
            flash(f"Erreur lors de la connexion: {str(e)}", 'error')
    return render_template('login.html', form=form)

@auth_bp.route('/logout')
def logout():
    username = current_user.username if current_user.is_authenticated else 'anonyme'
    logout_user()
    logging.info(f"Déconnexion de l'utilisateur {username}")
    flash('Déconnexion réussie.', 'success')
    return redirect(url_for('auth.login'))