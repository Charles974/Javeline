<?php
/**
 * Gestion de l'authentification et des autorisations.
 *
 * Trois profils :
 *   - administrateur : accès total au site + gestion des comptes
 *   - tour           : saisie des scores + consultation du planning
 *   - utilisateur    : consultation des résultats des challenges
 *
 * L'administrateur est implicitement autorisé partout : les listes de
 * rôles passées aux routes ne mentionnent que les profils supplémentaires.
 */
class Auth
{
    public const ROLE_ADMIN       = 'administrateur';
    public const ROLE_TOUR        = 'tour';
    public const ROLE_UTILISATEUR = 'utilisateur';

    // Libellés lisibles des profils (affichage UI)
    public const ROLES = [
        self::ROLE_ADMIN       => 'Administrateur',
        self::ROLE_TOUR        => 'Tour',
        self::ROLE_UTILISATEUR => 'Utilisateur',
    ];

    // Longueur minimale imposée aux mots de passe
    public const LONGUEUR_MIN_MDP = 8;

    /**
     * Démarre la session avec des cookies sécurisés.
     * À appeler une seule fois, avant tout accès à $_SESSION.
     */
    public static function demarrerSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,          // cookie de session (expire à la fermeture du navigateur)
            'path'     => '/',
            'httponly' => true,       // inaccessible en JavaScript
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off',
        ]);

        session_start();
    }

    /**
     * Ouvre la session d'un utilisateur après vérification de ses identifiants.
     * Régénère l'identifiant de session (protection contre la fixation de session).
     */
    public static function connecter(array $utilisateur): void
    {
        session_regenerate_id(true);

        $_SESSION['utilisateur'] = [
            'id'          => (int) $utilisateur['id'],
            'identifiant' => $utilisateur['identifiant'],
            'role'        => $utilisateur['role'],
        ];
    }

    /**
     * Ferme la session et supprime le cookie associé.
     */
    public static function deconnecter(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'],
            ]);
        }

        session_destroy();
    }

    public static function estConnecte(): bool
    {
        return isset($_SESSION['utilisateur']['id']);
    }

    /**
     * Identifiant (clé primaire) de l'utilisateur connecté, 0 sinon.
     */
    public static function id(): int
    {
        return (int) ($_SESSION['utilisateur']['id'] ?? 0);
    }

    /**
     * Nom de connexion de l'utilisateur connecté, chaîne vide sinon.
     */
    public static function identifiant(): string
    {
        return $_SESSION['utilisateur']['identifiant'] ?? '';
    }

    /**
     * Rôle de l'utilisateur connecté, chaîne vide sinon.
     */
    public static function role(): string
    {
        return $_SESSION['utilisateur']['role'] ?? '';
    }

    public static function estAdmin(): bool
    {
        return self::role() === self::ROLE_ADMIN;
    }

    /**
     * Vérifie que le rôle courant est autorisé.
     * L'administrateur est toujours autorisé ; $rolesSupplementaires liste
     * les autres profils acceptés (ex : ['tour', 'utilisateur']).
     */
    public static function roleAutorise(array $rolesSupplementaires): bool
    {
        if (!self::estConnecte()) {
            return false;
        }

        return self::estAdmin() || in_array(self::role(), $rolesSupplementaires, true);
    }

    /**
     * Libellé lisible d'un rôle (repli sur le code brut si inconnu).
     */
    public static function libelleRole(string $role): string
    {
        return self::ROLES[$role] ?? $role;
    }
}
