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
$router->get('/membres',                  'MembreController', 'index');
$router->get('/membres/get/:id',          'MembreController', 'get');
$router->get('/membres/fiche/:id',        'MembreController', 'fiche');
$router->post('/membres/ajouter',         'MembreController', 'ajouter');
$router->post('/membres/modifier',        'MembreController', 'modifier');
