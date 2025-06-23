# Quiz Platform

Une plateforme de quiz en ligne intégrée à Asterisk pour la gestion des appels VoIP. Les utilisateurs doivent passer un appel via leur extension SIP pour accéder au quiz. Les administrateurs peuvent consulter les résultats des participants.

## Fonctionnalités
- Inscription et connexion des utilisateurs avec validation d'appel via Asterisk.
- Quiz interactif avec questions chargées depuis un fichier JSON.
- Tableau de bord admin pour créer d'autres admins et voir les résultats des utilisateurs.
- Gestion des configurations Asterisk (`pjsip.conf` et `extensions.conf`) pour les endpoints SIP.

## Prérequis
- Python 3.10+
- MySQL
- Asterisk (configuré avec PJSIP)
- Zoiper ou autre client SIP pour tester les appels
- Debian/Ubuntu (recommandé)

## Installation
1. Cloner le dépôt :
   
   git clone https://github.com/nospi510/quiz_platform.git
   cd quiz_platform
   

2. Créer un environnement virtuel et installer les dépendances :
   
   python -m venv .venv
   source .venv/bin/activate
   pip install -r requirements.txt
   

3. Configurer la base de données MySQL :
   ```sql
   CREATE DATABASE quiz_platform;
   CREATE DATABASE asteriskCDR;
   ```
   Mettre à jour `config.py` avec les informations de connexion :
   ```python
   SQLALCHEMY_DATABASE_URI = 'mysql+mysqlconnector://<user>:<password>@localhost/quiz_platform'
   ```

4. Configurer Asterisk :
   - Assure-toi que `/etc/asterisk/pjsip.conf` et `/etc/asterisk/extensions.conf` sont accessibles en écriture par l'utilisateur exécutant l'application.

   ```bash
   sudo chown :asterisk /etc/asterisk/pjsip.conf /etc/asterisk/extensions.conf
   sudo chmod 664 /etc/asterisk/pjsip.conf /etc/asterisk/extensions.conf
   ```

5. Initialiser la base de données et charger les questions :
   ```bash
   python app.py
   ```
   Cela crée les tables et charge les questions depuis `questions.json`.

## Utilisation
1. Lancer l'application :
   ```bash
   python app.py
   ```
   L'application est accessible à `http://localhost:5001`.

2. Inscription :
   - Accède à `/auth/register` pour créer un utilisateur avec un nom d'utilisateur, mot de passe et extension (par exemple, `5001`).
   - Configure Zoiper avec les informations de l'utilisateur (IP du serveur Asterisk : `192.168.0.106`, port : `5060`, protocole : UDP).

3. Connexion :
   - Passe un appel avec l'extension via Zoiper (par exemple, vers une autre extension comme `5000`).
   - Connecte-toi à `/auth/login` pour accéder au quiz.

4. Admin :
   - Rends un utilisateur admin via SQL :
     ```sql
     UPDATE user SET is_admin = 1 WHERE username = '<username>';
     ```
   - Accède à `/admin/create_admin` pour créer d'autres admins ou à `/admin/results` pour voir les résultats.

## Structure du projet
- `app.py` : Point d'entrée de l'application.
- `routes/` : Routes Flask pour l'authentification, le quiz et l'admin.
- `templates/` : Templates HTML avec Tailwind CSS.
- `models/` : Modèles SQLAlchemy pour les utilisateurs, questions et réponses.
- `questions.json` : Fichier de questions pour le quiz.

## Contribution
1. Fork le dépôt.
2. Crée une branche : `git checkout -b ma-fonctionnalité`.
3. Commit tes changements : `git commit -m "Ajout de ma fonctionnalité"`.
4. Push : `git push origin ma-fonctionnalité`.
5. Ouvre une Pull Request.

