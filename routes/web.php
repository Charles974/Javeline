<?php
/**
 * Définition de toutes les routes de l'application.
 * $router est injecté depuis index.php.
 *
 * Contrôle d'accès (4e paramètre) :
 *   - omis                     → administrateur uniquement
 *   - ['tour', 'utilisateur']  → administrateur + profils listés
 *   - Router::ACCES_PUBLIC     → accessible sans connexion
 */

// Authentification
$router->get('/connexion',     'AuthController', 'formulaire',        Router::ACCES_PUBLIC);
$router->post('/connexion',    'AuthController', 'connecter',         Router::ACCES_PUBLIC);
$router->get('/deconnexion',   'AuthController', 'deconnecter',       ['tour', 'utilisateur']);
$router->post('/mot-de-passe', 'AuthController', 'changerMotDePasse', ['tour', 'utilisateur']);

// Gestion des comptes (administrateur uniquement)
$router->get('/utilisateurs',            'UtilisateurController', 'index');
$router->post('/utilisateurs/ajouter',   'UtilisateurController', 'ajouter');
$router->post('/utilisateurs/modifier',  'UtilisateurController', 'modifier');
$router->post('/utilisateurs/supprimer', 'UtilisateurController', 'supprimer');

// Page d'accueil (tous les profils connectés)
$router->get('/', 'HomeController', 'index', ['tour', 'utilisateur']);

// Challenges
$router->get('/challenges/historique',                          'ChallengeController', 'historique', ['utilisateur']);
$router->post('/challenges/creer',                              'ChallengeController', 'creer');
$router->get('/challenges/:id',                                 'ChallengeController', 'detail');
$router->get('/challenges/:id/resume',                          'ChallengeController', 'resume', ['tour']);
$router->get('/challenges/:id/classements',                     'ChallengeController', 'classements', ['utilisateur']);
$router->get('/challenges/:id/classements-combines',            'ChallengeController', 'classementsCombines', ['utilisateur']);
$router->post('/challenges/:id/saisir-score',                   'ChallengeController', 'saisirScore', ['tour']);
$router->post('/challenges/:id/modifier-horaire',               'ChallengeController', 'modifierHoraire');
$router->post('/challenges/:id/retirer-horaire',                'ChallengeController', 'retirerHoraire');
$router->get('/challenges/:id/plan-de-tir',                     'ChallengeController', 'planDeTir');
$router->get('/challenges/:id/planning',                        'ChallengeController', 'planning', ['tour']);
$router->post('/challenges/:id/plan-de-tir/blocs',               'ChallengeController', 'ajouterBlocHoraire');
$router->post('/challenges/:id/plan-de-tir/blocs/supprimer',     'ChallengeController', 'supprimerBlocHoraire');
$router->get('/challenges/:id/disciplines-tireur',              'ChallengeController', 'disciplinesTireur');
$router->get('/challenges/:id/panneaux',                        'ChallengeController', 'panneaux');
$router->get('/challenges/:id/imprimer',                        'ChallengeController', 'imprimer');
$router->post('/challenges/:id/inscrire',                       'ChallengeController', 'inscrire');
$router->post('/challenges/:id/modifier-inscriptions',          'ChallengeController', 'modifierInscriptions');
$router->post('/challenges/:id/supprimer-inscription',          'ChallengeController', 'supprimerInscription');

// Disciplines
$router->get('/disciplines',           'DisciplineController', 'index');
$router->get('/disciplines/imprimer',  'DisciplineController', 'imprimerListe');

// Membres
$router->get('/membres',              'MembreController', 'index');
$router->get('/membres/imprimer',     'MembreController', 'imprimerListe');
$router->get('/membres/get/:id',      'MembreController', 'get');
$router->get('/membres/fiche/:id',    'MembreController', 'fiche');
$router->get('/membres/historique/:id', 'MembreController', 'historique');
$router->post('/membres/ajouter',     'MembreController', 'ajouter');
$router->post('/membres/modifier',    'MembreController', 'modifier');
$router->post('/membres/supprimer',   'MembreController', 'supprimer');

// Tireurs non membres (externes)
$router->get('/externes',             'ExterneController', 'index');
$router->get('/externes/imprimer',    'ExterneController', 'imprimerListe');
$router->get('/externes/get/:id',     'ExterneController', 'get');
$router->get('/externes/fiche/:id',   'ExterneController', 'fiche');
$router->get('/externes/historique/:id', 'ExterneController', 'historique');
$router->post('/externes/ajouter',    'ExterneController', 'ajouter');
$router->post('/externes/modifier',   'ExterneController', 'modifier');
$router->post('/externes/supprimer',  'ExterneController', 'supprimer');
