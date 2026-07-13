<?php
// Point d'entrée unique de l'application

define('APP_ROOT', __DIR__);

// Chargement de la configuration
require_once APP_ROOT . '/config/config.php';

// Chargement des classes du noyau
require_once APP_ROOT . '/core/helpers.php';
require_once APP_ROOT . '/core/Database.php';
require_once APP_ROOT . '/core/Model.php';
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Auth.php';
require_once APP_ROOT . '/core/Router.php';

// Session utilisateur (cookies sécurisés) — avant tout contrôle d'accès
Auth::demarrerSession();

// Synchronise la session avec la base : un compte supprimé est déconnecté
// immédiatement, un changement de profil prend effet sans reconnexion.
if (Auth::estConnecte()) {
    require_once APP_ROOT . '/app/models/UtilisateurModel.php';
    $compteCourant = (new UtilisateurModel())->findById(Auth::id());
    if (!$compteCourant) {
        Auth::deconnecter();
    } else {
        $_SESSION['utilisateur']['identifiant'] = $compteCourant['identifiant'];
        $_SESSION['utilisateur']['role']        = $compteCourant['role'];
    }
    unset($compteCourant);
}

// Initialisation du routeur et chargement des routes
$router = new Router();
require_once APP_ROOT . '/routes/web.php';

// Traitement de la requête
$router->dispatch();
