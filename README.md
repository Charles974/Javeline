# Javeline

Application web de gestion des challenges de tir sportif de l'association **Javeline** (discipline silhouette métallique).

## Description

Javeline est une application permettant de gérer l'ensemble du cycle de vie d'un challenge de tir sportif :

- Création des challenges à une date donnée.
- Inscription des tireurs (membres du club ou non-membres) aux disciplines qu'ils souhaitent tirer.
- Génération du plan de tir (créneaux horaires) avec détection des chevauchements.
- Saisie des scores par cible (poulets, cochons, dindons, mouflons) et par discipline.
- Calcul automatique des totaux, des combinés (aggregates) et des classements par catégorie de tir.
- Archivage des challenges et génération de feuilles de score et classements en PDF (FR/EN selon la nationalité du tireur).

Stack technique : **PHP MVC** (sans framework), **MySQL/MariaDB**, **Bootstrap**, **jQuery**.

## Authentification et profils

L'accès à l'application nécessite d'être connecté (identifiant + mot de passe). Au chargement du site, l'utilisateur non connecté est redirigé vers la page de connexion. Les mots de passe sont stockés hashés en base (bcrypt).

Trois profils de compte existent :

| Profil          | Droits                                                                                   |
|-----------------|-------------------------------------------------------------------------------------------|
| Administrateur  | Accès total au site + gestion des comptes (création, suppression, mots de passe, profils) |
| Tour            | Saisie des scores et consultation du planning uniquement                                   |
| Utilisateur     | Consultation des résultats des challenges (en cours ou passés) uniquement                  |

Le script `config/database.sql` crée un compte administrateur initial `admin` (mot de passe : `Javeline!2026`), **à changer immédiatement après la première connexion** via le menu du compte (bandeau haut de page). Le script `config/seed_test.sql` ajoute un compte de test par profil (`test.admin` / `Admin@123`, `test.tour` / `Tour@123`, `test.utilisateur` / `User@123`).

## Prérequis

- PHP 8.0 ou supérieur, avec l'extension **PDO MySQL** activée.
- MySQL ou MariaDB.
- Un serveur web avec le module de réécriture d'URL (`mod_rewrite` pour Apache).
- [WampServer](https://www.wampserver.com/) (ou équivalent : Laragon, XAMPP, MAMP...) pour un environnement de développement local sous Windows.

## Installation avec WampServer

1. **Cloner le dépôt** dans le répertoire `www` de Wamp (généralement `C:\wamp64\www`) :

   ```bash
   cd C:\wamp64\www
   git clone https://github.com/charles974/javeline.git Javeline
   ```

   > Le nom du dossier (`Javeline` ici) doit correspondre à celui utilisé dans l'URL de l'application (voir `APP_URL` ci-dessous).

2. **Démarrer Wamp** et vérifier que les services Apache et MySQL sont bien lancés (icône verte).

3. **Activer le module `mod_rewrite`** d'Apache (clic gauche sur l'icône Wamp → Apache → Modules Apache → cocher `rewrite_module`), nécessaire au routage géré par `.htaccess` et `index.php`.

4. **Créer la base de données** en important le script `config/database.sql` via phpMyAdmin (ou en ligne de commande) :

   ```bash
   mysql -u root -p < config/database.sql
   ```

   Ce script crée la base `javeline` (encodage `utf8mb4`) ainsi que toutes les tables nécessaires.

   Pour disposer de données de démonstration, importer également `config/seed_test.sql` après `database.sql`.

5. **Configurer l'application** en modifiant le fichier `config/config.php` selon votre environnement (voir le détail des variables ci-dessous).

6. **Accéder à l'application** dans un navigateur à l'adresse définie par `APP_URL`, par exemple :

   ```
   http://localhost/Javeline
   ```

### Paramètres à adapter selon l'environnement (`config/config.php`)

Ces valeurs sont propres à chaque poste/serveur et **doivent être vérifiées/adaptées** avant le premier lancement :

| Constante    | Rôle                                                             | Valeur par défaut (Wamp)     | À adapter si...                                                                 |
|--------------|-------------------------------------------------------------------|-------------------------------|----------------------------------------------------------------------------------|
| `APP_URL`    | URL de base de l'application, utilisée pour générer les liens     | `http://localhost/Javeline`   | le dossier du projet dans `www` porte un autre nom, ou le site tourne sur un port/domaine différent (ex. `http://localhost:8080/Javeline`). |
| `APP_ENV`    | Environnement d'exécution (`development` ou `production`)         | `development`                 | mise en production : passer à `production` pour désactiver l'affichage des erreurs PHP. |
| `DB_HOST`    | Hôte du serveur MySQL                                              | `localhost`                   | la base de données est hébergée sur un autre serveur.                            |
| `DB_NAME`    | Nom de la base de données                                          | `javeline`                    | un autre nom de base a été choisi lors de l'import de `database.sql`.            |
| `DB_USER`    | Utilisateur MySQL                                                  | `root`                        | un utilisateur MySQL dédié est utilisé (recommandé en production).               |
| `DB_PASS`    | Mot de passe MySQL                                                 | *(vide)*                      | un mot de passe est défini pour l'utilisateur MySQL (le compte `root` de Wamp n'a par défaut pas de mot de passe). |
| `DB_CHARSET` | Encodage de connexion à la base                                    | `utf8mb4`                     | ne pas modifier, requis pour la gestion correcte des accents.                    |

Le fichier **`.htaccess`** à la racine du projet ne nécessite aucune adaptation : l'URL de base est détectée automatiquement, que le projet soit déployé à la racine du site ou dans un sous-dossier, quel que soit le nom de ce dossier.

## Installation sous Debian/Linux (Apache)

1. **Cloner le dépôt** dans la racine web d'Apache :

   ```bash
   cd /var/www/html
   sudo git clone https://github.com/charles974/javeline.git Javeline
   ```

2. **Activer le module de réécriture d'URL** (désactivé par défaut sur Debian) :

   ```bash
   sudo a2enmod rewrite
   ```

3. **Autoriser les fichiers `.htaccess`** : dans `/etc/apache2/apache2.conf`, remplacer `AllowOverride None` par `AllowOverride All` dans le bloc `<Directory /var/www/>` :

   ```apache
   <Directory /var/www/>
       Options Indexes FollowSymLinks
       AllowOverride All
       Require all granted
   </Directory>
   ```

   > Sans cette étape, Apache ignore le `.htaccess` du projet : la page d'accueil s'affiche mais tous les liens renvoient une erreur 404.

4. **Redémarrer Apache** :

   ```bash
   sudo systemctl restart apache2
   ```

5. **Créer la base de données et configurer l'application** comme décrit aux étapes 4 et 5 de l'installation WampServer ci-dessus.

L'application est alors accessible sur `http://localhost/Javeline`.

## Structure du projet

```
Javeline/
├── app/
│   ├── controllers/     # Contrôleurs MVC
│   ├── models/          # Modèles (accès BDD)
│   └── views/
│       ├── layouts/     # Gabarits (header, footer)
│       └── partials/    # Composants réutilisables
├── config/              # Configuration BDD et app
├── core/                # Classes de base (Router, Controller, Model, Database, helpers)
├── public/              # Racine web publique
│   ├── css/
│   ├── js/
│   ├── img/
│   └── vendor/          # Bibliothèques front-end (jQuery, Bootstrap, Paged.js)
├── routes/              # Définition des routes
└── index.php            # Point d'entrée unique
```

## Contribution

Les contributions sont les bienvenues. Merci d'ouvrir une issue avant de soumettre une pull request.

## Licence

Tous droits réservés.
