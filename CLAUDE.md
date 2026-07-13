# CLAUDE.md — Règles du projet Javeline

## Identité du projet
Site web de l'association **Javeline**.
Stack : HTML, CSS, PHP MVC, Bootstrap, jQuery, MySQL.

---

## Rôle de l'assistant

Tu es un assistant de développement senior pour ce projet web en PHP, Bootstrap et jQuery.
**Objectif :** générer ou modifier du code compilable immédiatement, simple, organisé, commenté, sécurisé, et conforme aux règles ci-dessous.
Tu produis la sortie rapidement, avec un maximum de précision, sans digression.

---

## Contraintes prioritaires (NE JAMAIS OUBLIER NI ÉCRASER)

- **Encodage :** UTF-8 partout (fichiers, base de données, en-têtes HTTP).
- **Interdiction stricte :** ne jamais remplacer les accents français (é, è, à, ç, ô, ï, etc.) par des séquences Unicode (ex : `é`). Les accents doivent rester **lisibles** dans le code source.
- **Identifiants** (variables, fonctions, classes, fichiers) : uniquement ASCII (pas d'accents). Translitère si besoin (é→e, è→e, à→a, ç→c, ô→o).
- **Chaînes destinées aux humains** (UI, logs, messages d'erreur) : conserver les accents en clair (lisibles).
- Aucun caractère échappé comme `\n` `\t` `\r` ; vrais retours à la ligne.
- Pas de CSS-in-JS ni de style inline dans le HTML. **Tout le style dans le CSS, rien dans le HTML.**

---

## Réutilisation obligatoire du code existant

AVANT de créer tout nouveau code, tu DOIS :
1. Rechercher dans le codebase si une fonction, service, composant, helper ou utilitaire fait déjà le travail demandé.
2. Réutiliser l'existant plutôt que de dupliquer.
3. Si un service existe pour une tâche similaire, l'étendre ou l'utiliser.
4. Ne jamais créer de doublons : si tu trouves du code qui fait 80 % du job, adapte-le ou étends-le plutôt que de réécrire.
5. Signaler dans le récapitulatif si du code existant a été réutilisé.

---

## Processus de vérification avant livraison

1. Générer le code.
2. Relire et détecter les erreurs potentielles.
3. Corriger les erreurs trouvées.
4. Re-vérifier.
5. Répéter jusqu'à 0 erreur détectable.
6. Rendre le code final.

---

## Lignes directrices de sortie

- HTTP fortement typé ; gestion d'erreurs avec commentaires sur les cas courants.
- Code clair et modulaire.
- HTML : accessibilité de base (`aria-label`, `role`, `alt`), pas de logique lourde dans le template.
- Tout le style dans le CSS, rien dans le HTML (pas de `style=""` inline).

---

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
├── core/                # Classes de base (Router, Controller, Model, Database)
├── public/              # Racine web publique
│   ├── css/
│   ├── js/
│   └── img/
├── routes/              # Définition des routes
└── index.php            # Point d'entrée unique
```

---

## Cahier des charges — Objectif du site

### Contexte
Site de gestion des scores pour l'association de tir sportif **Javeline**, discipline **silhouette métallique**.
- 40 cibles par épreuve : 10 poulets, 10 cochons, 10 dindons, 10 mouflons (distance croissante).
- Les épreuves s'appellent des **Challenges**.
- Deux types de tireurs : **membres du club** et **non-membres**.
- Génération de PDF prévue.

### Fonctionnement

1. **Création du challenge** : à l'avance, on crée un challenge à une date précise.
2. **Inscriptions** : on ajoute chaque tireur au challenge avec les disciplines qu'il veut tirer.
   - Case à cocher si le tireur n'est pas français → libellé de la discipline en anglais sur la feuille de score.
3. **Plan de tir** (~1 semaine avant) : on revient sur chaque match pour indiquer le jour et l'heure, puis on génère les feuilles de score automatiquement.
4. **Saisie des scores** : pendant le challenge, on saisit le score de chaque bête (poulet, cochon, dindon, mouflon) par discipline.
5. **Calculs** : le logiciel calcule les totaux et effectue les classements.
6. **Archivage** : possibilité d'archiver les résultats et de les rouvrir pour récupérer les scores.

### Disciplines

| Code | Français                         | Anglais                        |
|------|----------------------------------|--------------------------------|
| 400  | Gros Calibre Revolver            | Big Bore Revolver              |
| 401  | Gros Calibre Production          | Big Bore Production            |
| 402  | Gros Calibre Unlimited           | Big Bore Unlimited             |
| 403  | Gros Calibre Debout              | Big Bore Standing              |
| 404  | Petit Calibre Revolver           | Small Bore Revolver            |
| 405  | Petit Calibre Production         | Small Bore Production          |
| 406  | Petit Calibre Unlimited          | Small Bore Unlimited           |
| 407  | Petit Calibre Debout             | Small Bore Standing            |
| 408  | Field Visée Ouverte              | Field Pistol Any Sight         |
| 409  | Field Optique                    | Field Pistol Production        |
| 410  | Carabine Petit Calibre Légère    | Small Bore Light Rifle         |
| 411  | Carabine Petit Calibre Silhouette| Small Bore Silhouette Rifle    |
| 412  | Carabine Gros Calibre Hunting    | Big Bore Hunting Rifle         |
| 413  | Carabine Gros Calibre Silhouette | Big Bore Silhouette Rifle      |

### Combinés (Aggregates)

| Nom FR                          | Nom EN                    | Disciplines        |
|---------------------------------|---------------------------|--------------------|
| Combiné Gros Calibre            | Aggregate Big Bore        | 400+401+402+403    |
| Combiné Petit Calibre           | Aggregate Small Bore      | 404+405+406+407    |
| Combiné Field                   | Aggregate Field           | 408+409            |
| Combiné Carabine Petit Calibre  | Aggregate Small Bore Rifle| 410+411            |
| Combiné Carabine Gros Calibre   | Aggregate Big Bore Rifle  | 412+413            |
| Combiné Debout                  | Aggregate Standing        | 403+407+408+409    |

### Catégories de tir
- Table de référence **fixe** (pas d'interface Ajouter/Modifier/Supprimer).
- Le classement individuel se fait **par catégorie en premier**, puis par score décroissant à l'intérieur de chaque catégorie.
- La catégorie est choisie lors de l'inscription d'un tireur à un challenge (liste déroulante).

### Règle de classement
1. Classement **par catégorie de tir** d'abord.
2. À l'intérieur de chaque catégorie : score total le plus élevé.
3. En cas d'égalité : nombre de mouflons (le plus élevé).
4. Puis nombre de dindons, puis cochons, puis poulets.
5. Si égalité parfaite sur tout : déclarer **ex-æquo**.
6. Afficher la médaille **Or, Argent, Bronze** à côté des 3 premiers de chaque catégorie.
Applicable aussi bien aux classements individuels qu'aux combinés.

### Accès et authentification
- **Authentification obligatoire** : rien n'est accessible sans être connecté (identifiant + mot de passe).
- Au chargement du site, redirection automatique vers la page de connexion.
- Mots de passe stockés **hashés** en base (`password_hash` / bcrypt), jamais en clair.
- Bandeau haut de page : menu déroulant du compte (se déconnecter, changer son mot de passe).
- Trois profils :

| Profil          | Droits                                                                 |
|-----------------|------------------------------------------------------------------------|
| Administrateur  | Accès total au site + gestion des comptes (créer/supprimer un compte, changer les mots de passe et les profils) |
| Tour            | Saisie des scores + consultation du planning uniquement                 |
| Utilisateur     | Consultation des résultats des challenges (en cours ou passés) uniquement |

- Règle de sécurité des routes : par défaut une route est réservée à l'administrateur ; les autres profils doivent être autorisés explicitement (voir `routes/web.php` et `core/Auth.php`).

### Gestion des tireurs
- Deux types distincts en base : **membres du club** et **non-membres**.
- Un tireur (membre ou non) possède un profil réutilisable d'un challenge à l'autre.
- Lors de l'inscription à un challenge, on recherche et sélectionne un tireur existant (pas de création à la volée).
- Case à cocher "tireur étranger" sur le profil → libellés en anglais sur les feuilles de score.

#### Tireur membre — champs du formulaire
| Champ               | Obligatoire |
|---------------------|-------------|
| Nom                 | Oui         |
| Prénom              | Oui         |
| Date de naissance   | Oui         |
| Lieu de naissance   | Oui         |
| Catégorie d'âge     | Oui         |
| Numéro de licence   | Oui         |
| Adresse 1           | Oui         |
| Adresse 2           | Non         |
| Code postal         | Oui         |
| Ville               | Oui         |
| Téléphone           | Oui         |
| Email               | Oui         |
| Certificat médical  | Non         |

#### Tireur externe (non-membre) — champs du formulaire
| Champ      | Obligatoire |
|------------|-------------|
| Nom        | Oui         |
| Prénom     | Oui         |
| Club       | Oui         |
| Téléphone  | Non         |
| Email      | Non         |
| Étranger   | Non         |

### Plan de tir et créneaux horaires
- Un tireur peut s'inscrire à toutes les disciplines d'un challenge.
- Chaque discipline occupe environ **1 heure** de créneau.
- Lors de l'attribution des créneaux (plan de tir), le système **vérifie les chevauchements** pour un même tireur et bloque l'enregistrement en cas de conflit.

### Combinés (Aggregates)
- Calculés **automatiquement** dès qu'un tireur a des scores dans les disciplines concernées.
- ⚠️ Cette partie est susceptible d'évoluer.

### Éditions PDF
- Feuilles de score (par match, avec libellés FR ou EN selon nationalité du tireur).
- Classements individuels par discipline (libellés FR et EN).
- Classements combinés.

---

## Conventions

- Nommage fichiers controllers : `NomController.php` (PascalCase)
- Nommage fichiers models : `NomModel.php` (PascalCase)
- Nommage fichiers views : `nom_action.php` (snake_case)
- Toutes les requêtes SQL : via PDO avec requêtes préparées (jamais de concaténation directe).
- Pas de logique métier dans les vues.
- Variables PHP dans les vues : préfixées par `$` et échappées avec `htmlspecialchars()`.
