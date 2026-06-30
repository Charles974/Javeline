<?php
/**
 * Définition de toutes les routes de l'application.
 * $router est injecté depuis index.php.
 */

// Page d'accueil
$router->get('/', 'HomeController', 'index');

// Challenges
$router->post('/challenges/creer', 'ChallengeController', 'creer');

// Membres
$router->get('/membres',              'MembreController', 'index');
$router->get('/membres/get/:id',      'MembreController', 'get');
$router->get('/membres/fiche/:id',    'MembreController', 'fiche');
$router->post('/membres/ajouter',     'MembreController', 'ajouter');
$router->post('/membres/modifier',    'MembreController', 'modifier');

// Tireurs non membres (externes)
$router->get('/externes',             'ExterneController', 'index');
$router->get('/externes/get/:id',     'ExterneController', 'get');
$router->get('/externes/fiche/:id',   'ExterneController', 'fiche');
$router->post('/externes/ajouter',    'ExterneController', 'ajouter');
$router->post('/externes/modifier',   'ExterneController', 'modifier');
