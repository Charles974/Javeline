<?php
// Configuration générale de l'application

define('APP_NAME', 'Javeline');

// Detection automatique de l'URL de base : fonctionne quel que soit le dossier
// de deploiement (racine web ou sous-dossier), sans edition manuelle apres clonage.
// Surcharge possible via la variable d'environnement APP_URL (deploiement particulier).
$appUrlEnv = getenv('APP_URL');
if ($appUrlEnv !== false && $appUrlEnv !== '') {
    define('APP_URL', rtrim($appUrlEnv, '/'));
} else {
    // Schema http/https selon le contexte (proxy inverse pris en compte)
    $estHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $schema = $estHttps ? 'https' : 'http';

    // Nom d'hote de la requete, repli sur localhost (contexte CLI)
    $hote = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Dossier contenant index.php, relatif a la racine web ('' si a la racine)
    $cheminBase = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $cheminBase = ($cheminBase === '/' || $cheminBase === '.') ? '' : rtrim($cheminBase, '/');

    define('APP_URL', $schema . '://' . $hote . $cheminBase);
}

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
