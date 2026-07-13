<?php
/**
 * Routeur frontal simple.
 * Associe les URL aux contrôleurs et méthodes, et applique le contrôle
 * d'accès par profil avant d'exécuter l'action.
 *
 * Règles d'accès (paramètre $roles de get()/post()) :
 *   - omis / []                → réservé à l'administrateur (sécurisé par défaut)
 *   - ['tour', 'utilisateur']  → administrateur + profils listés
 *   - Router::ACCES_PUBLIC     → accessible sans être connecté (page de connexion)
 */
class Router
{
    // Marqueur des routes accessibles sans authentification
    public const ACCES_PUBLIC = ['public'];

    private array $routes = [];

    /**
     * Enregistre une route GET.
     *
     * @param array $roles Profils non-admin autorisés (voir en-tête de classe)
     */
    public function get(string $chemin, string $controleur, string $methode, array $roles = []): void
    {
        $this->routes['GET'][$chemin] = [$controleur, $methode, $roles];
    }

    /**
     * Enregistre une route POST.
     *
     * @param array $roles Profils non-admin autorisés (voir en-tête de classe)
     */
    public function post(string $chemin, string $controleur, string $methode, array $roles = []): void
    {
        $this->routes['POST'][$chemin] = [$controleur, $methode, $roles];
    }

    /**
     * Résout l'URL courante et appelle le bon contrôleur.
     */
    public function dispatch(): void
    {
        $methodeHttp = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri         = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Supprime le préfixe du sous-dossier si l'app n'est pas à la racine
        $base = parse_url(APP_URL, PHP_URL_PATH);
        if ($base && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $uri = '/' . ltrim($uri, '/');

        // Recherche une route exacte d'abord
        if (isset($this->routes[$methodeHttp][$uri])) {
            [$controleur, $action, $roles] = $this->routes[$methodeHttp][$uri];
            $this->verifierAcces($roles);
            $this->appeler($controleur, $action);
            return;
        }

        // Recherche une route avec paramètres (:id, :slug, etc.)
        foreach ($this->routes[$methodeHttp] ?? [] as $patron => $cible) {
            $regex = preg_replace('/:([a-z_]+)/', '([^/]+)', $patron);
            if (preg_match('#^' . $regex . '$#', $uri, $matches)) {
                array_shift($matches);
                [$controleur, $action, $roles] = $cible;
                $this->verifierAcces($roles);
                $this->appeler($controleur, $action, $matches);
                return;
            }
        }

        // Aucune route trouvée → connexion demandée avant d'afficher le 404
        if (!Auth::estConnecte()) {
            $this->rediriger('/connexion');
        }
        http_response_code(404);
        require APP_ROOT . '/app/views/layouts/main.php';
    }

    /**
     * Applique le contrôle d'accès d'une route : redirige vers la page de
     * connexion si non connecté, renvoie un 403 si le profil est insuffisant.
     * Les appels AJAX reçoivent une réponse JSON (401/403) au lieu d'une
     * redirection, pour que le JavaScript puisse réagir proprement.
     */
    private function verifierAcces(array $roles): void
    {
        if ($roles === self::ACCES_PUBLIC) {
            return;
        }

        if (!Auth::estConnecte()) {
            if ($this->estRequeteAjax()) {
                $this->repondreJson(401, 'Session expirée. Veuillez vous reconnecter.');
            }
            $this->rediriger('/connexion');
        }

        if (!Auth::roleAutorise($roles)) {
            if ($this->estRequeteAjax()) {
                $this->repondreJson(403, 'Accès refusé : votre profil ne permet pas cette action.');
            }
            $this->refuserAcces();
        }
    }

    private function estRequeteAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    private function repondreJson(int $code, string $message): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function rediriger(string $chemin): void
    {
        header('Location: ' . APP_URL . $chemin);
        exit;
    }

    /**
     * Affiche la page 403 (profil connecté mais insuffisant).
     */
    private function refuserAcces(): void
    {
        http_response_code(403);
        $titrePage = 'Accès refusé — ' . APP_NAME;
        $contenu   = '<div class="acces-refuse">'
            . '<h1>Accès refusé</h1>'
            . '<p>Votre profil « ' . htmlspecialchars(Auth::libelleRole(Auth::role()))
            . ' » ne permet pas d\'accéder à cette page.</p>'
            . '<a class="btn btn-primary" href="' . APP_URL . '/">Retour à l\'accueil</a>'
            . '</div>';
        require APP_ROOT . '/app/views/layouts/main.php';
        exit;
    }

    /**
     * Instancie le contrôleur et appelle la méthode avec les paramètres.
     */
    private function appeler(string $controleur, string $action, array $params = []): void
    {
        $fichier = APP_ROOT . '/app/controllers/' . $controleur . '.php';
        if (!file_exists($fichier)) {
            die('Contrôleur introuvable : ' . htmlspecialchars($controleur));
        }

        require_once $fichier;

        if (!class_exists($controleur)) {
            die('Classe introuvable : ' . htmlspecialchars($controleur));
        }

        $instance = new $controleur();

        if (!method_exists($instance, $action)) {
            die('Méthode introuvable : ' . htmlspecialchars($action));
        }

        call_user_func_array([$instance, $action], $params);
    }
}
