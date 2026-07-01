<?php
/**
 * Définition de toutes les routes de l'application.
 * $router est injecté depuis index.php.
 */

// Page d'accueil
$router->get('/', 'HomeController', 'index');

// Challenges
$router->get('/challenges/historique',                          'ChallengeController', 'historique');
$router->post('/challenges/creer',                              'ChallengeController', 'creer');
$router->get('/challenges/:id',                                 'ChallengeController', 'detail');
$router->get('/challenges/:id/resume',                          'ChallengeController', 'resume');
$router->get('/challenges/:id/classements',                     'ChallengeController', 'classements');
$router->post('/challenges/:id/saisir-score',                   'ChallengeController', 'saisirScore');
$router->get('/challenges/:id/disciplines-tireur',              'ChallengeController', 'disciplinesTireur');
$router->get('/challenges/:id/panneaux',                        'ChallengeController', 'panneaux');
$router->get('/challenges/:id/imprimer',                        'ChallengeController', 'imprimer');
$router->post('/challenges/:id/inscrire',                       'ChallengeController', 'inscrire');
$router->post('/challenges/:id/modifier-inscriptions',          'ChallengeController', 'modifierInscriptions');
$router->post('/challenges/:id/supprimer-inscription',          'ChallengeController', 'supprimerInscription');

// Disciplines
$router->get('/disciplines', 'DisciplineController', 'index');

// Membres
$router->get('/membres',              'MembreController', 'index');
$router->get('/membres/get/:id',      'MembreController', 'get');
$router->get('/membres/fiche/:id',    'MembreController', 'fiche');
$router->post('/membres/ajouter',     'MembreController', 'ajouter');
$router->post('/membres/modifier',    'MembreController', 'modifier');
$router->post('/membres/supprimer',   'MembreController', 'supprimer');

// Tireurs non membres (externes)
$router->get('/externes',             'ExterneController', 'index');
$router->get('/externes/get/:id',     'ExterneController', 'get');
$router->get('/externes/fiche/:id',   'ExterneController', 'fiche');
$router->post('/externes/ajouter',    'ExterneController', 'ajouter');
$router->post('/externes/modifier',   'ExterneController', 'modifier');
$router->post('/externes/supprimer',  'ExterneController', 'supprimer');
