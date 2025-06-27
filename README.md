# 📚 Quiz Platform (PHP)

Une plateforme de quiz en ligne intégrée à **Asterisk** pour la gestion d’utilisateurs VoIP.
Les utilisateurs peuvent s’inscrire, se connecter, et répondre à un quiz.
Les administrateurs peuvent consulter les résultats et promouvoir d’autres administrateurs.

---

## ✅ Prérequis

Avant de commencer, assurez-vous d’avoir :

* **PHP 8.1+** avec les extensions `pdo_mysql`, `openssl` et `json`.
* **MySQL/MariaDB** pour la gestion des bases de données.
* **Asterisk** configuré avec **PJSIP** :

  * Les fichiers `/etc/asterisk/pjsip.conf` et `/etc/asterisk/extensions.conf` doivent être accessibles en écriture.
* **Composer** pour gérer les dépendances PHP.
* **Serveur web** (Apache ou Nginx recommandé).
* **Zoiper** (ou autre client SIP) pour tester les comptes (facultatif, car l’appel n’est pas requis pour le quiz).
* **Droits** : l’utilisateur exécutant PHP (ex. `www-data`) doit avoir les permissions d’écriture sur les fichiers Asterisk.

### 🔐 Configurer les permissions sur les fichiers Asterisk :

```bash
sudo chown :www-data /etc/asterisk/pjsip.conf /etc/asterisk/extensions.conf
sudo chmod 664 /etc/asterisk/pjsip.conf /etc/asterisk/extensions.conf
```

---

## ⚙️ Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/<ton-username>/quiz_platform.git
cd quiz_platform
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Créer les bases de données et les tables

```bash
mysql -u root -p < config/database.sql
```

### 4. Créer l’utilisateur MySQL et lui accorder les droits

```sql
CREATE USER 'quiz_user'@'localhost' IDENTIFIED BY 'passer';
GRANT ALL PRIVILEGES ON quiz_platform.* TO 'quiz_user'@'localhost';
GRANT ALL PRIVILEGES ON asteriskCDR.* TO 'quiz_user'@'localhost';
FLUSH PRIVILEGES;
```

### 5. Configurer le fichier `.env`

Crée un fichier `.env` à la racine du projet :

```
DB_HOST=localhost
DB_NAME=quiz_platform
DB_USER=quiz_user
DB_PASS=passer
ASTERISK_CDR_DB_NAME=asteriskCDR
```

### 6. Charger les questions depuis le fichier JSON

```bash
php scripts/load_questions.php
```

### 7. Configurer le serveur web (Apache)

Créer un fichier de configuration Apache :

```bash
sudo nano /etc/apache2/sites-available/quiz_platform.conf
```

Contenu :

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/quiz_platform/public
    <Directory /var/www/quiz_platform/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/quiz_platform_error.log
    CustomLog ${APACHE_LOG_DIR}/quiz_platform_access.log combined
</VirtualHost>
```

Activer le site et recharger Apache :

```bash
sudo a2ensite quiz_platform
sudo systemctl reload apache2
```

---

## 🚀 Utilisation

### 🔐 Inscription

Accéder à :

```
http://localhost/auth/register
```

Créer un compte avec un **nom d’utilisateur**, **mot de passe**, et **extension SIP** (ex : `5001`).
L’inscription configure automatiquement le compte dans **Asterisk** (`pjsip.conf`, `extensions.conf`).

### 🔑 Connexion

Se connecter via :

```
http://localhost/auth/login
```

Aucun appel requis pour accéder au quiz.

### 🧠 Quiz

* Accéder à : `/quiz` pour répondre aux questions
* Résultats : `/quiz/results`

### 🛠 Administration

Pour rendre un utilisateur administrateur :

```sql
UPDATE users SET is_admin = 1 WHERE username = '<username>';
```

Un administrateur peut :

* Voir les résultats : `/admin/results`
* Créer un admin : `/admin/create_admin`

### ☎️ Tester avec Zoiper (optionnel)

Configurer un compte SIP dans Zoiper avec :

* Nom d’utilisateur : identique à l’inscription
* Mot de passe : défini à l’inscription
* Extension : ex. `5001`
* IP du serveur : `192.168.0.106`
* Port : `5060`
* Protocole : `UDP`

---

## 🗂 Structure du projet

```
quiz_platform/
├── config/           # Configuration (database.php, database.sql)
├── public/           # Point d’entrée (index.php), CSS (tailwind.css)
├── src/
│   ├── Controllers/  # Contrôleurs (Auth, Quiz, Admin)
│   ├── Models/       # Modèles de données (User, Question, etc.)
│   └── Views/        # Templates (layouts, auth/, quiz/, admin/)
├── data/             # Questions JSON
├── scripts/          # Script de chargement (load_questions.php)
└── .env              # Variables d'environnement
```

---

## 🤝 Contribution

1. **Fork** le dépôt
2. Créer une branche :

```bash
git checkout -b ma-fonctionnalité
```

3. Commit :

```bash
git commit -m "Ajout de ma fonctionnalité"
```

4. Push :

```bash
git push origin ma-fonctionnalité
```

5. **Ouvre une Pull Request** sur GitHub

---

## 📄 Licence

Ce projet est sous licence **Nick via EC2LT**.

