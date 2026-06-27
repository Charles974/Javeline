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

## Conventions

- Nommage fichiers controllers : `NomController.php` (PascalCase)
- Nommage fichiers models : `NomModel.php` (PascalCase)
- Nommage fichiers views : `nom_action.php` (snake_case)
- Toutes les requêtes SQL : via PDO avec requêtes préparées (jamais de concaténation directe).
- Pas de logique métier dans les vues.
- Variables PHP dans les vues : préfixées par `$` et échappées avec `htmlspecialchars()`.
