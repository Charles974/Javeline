<?php
// Point d'entrée unique de l'application

define('APP_ROOT', __DIR__);

// Chargement de la configuration
require_once APP_ROOT . '/config/config.php';

// Chargement des classes du noyau
require_once APP_ROOT . '/core/Database.php';
require_once APP_ROOT . '/core/Model.php';
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Router.php';

// Initialisation du routeur et chargement des routes
$router = new Router();
require_once APP_ROOT . '/routes/web.php';

// Traitement de la requête
$router->dispatch();
