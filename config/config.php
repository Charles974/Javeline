<?php
// Configuration générale de l'application

define('APP_NAME', 'Javeline');
define('APP_URL', 'http://localhost/Javeline');
define('APP_LANG', 'fr');
define('APP_CHARSET', 'UTF-8');

// Environnement : 'development' ou 'production'
define('APP_ENV', 'development');

// Affichage des erreurs selon l'environnement
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Configuration base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'javeline');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
