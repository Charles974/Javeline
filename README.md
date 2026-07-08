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

L'accès à l'application est libre : il n'y a pas de gestion de compte ni d'authentification.

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

De plus, dans le fichier **`.htaccess`** à la racine du projet :

```apache
RewriteBase /Javeline/
```

Cette ligne doit correspondre au chemin de l'application dans l'URL (le nom du dossier sous `www`). Si le projet est cloné dans un dossier différemment nommé, ou déployé à la racine d'un site (`RewriteBase /`), il faut adapter cette valeur en conséquence.

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
