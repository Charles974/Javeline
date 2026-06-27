<?php
/**
 * Routeur frontal simple.
 * Associe les URL aux contrôleurs et méthodes.
 */
class Router
{
    private array $routes = [];

    /**
     * Enregistre une route GET.
     */
    public function get(string $chemin, string $controleur, string $methode): void
    {
        $this->routes['GET'][$chemin] = [$controleur, $methode];
    }

    /**
     * Enregistre une route POST.
     */
    public function post(string $chemin, string $controleur, string $methode): void
    {
        $this->routes['POST'][$chemin] = [$controleur, $methode];
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
            [$controleur, $action] = $this->routes[$methodeHttp][$uri];
            $this->appeler($controleur, $action);
            return;
        }

        // Recherche une route avec paramètres (:id, :slug, etc.)
        foreach ($this->routes[$methodeHttp] ?? [] as $patron => $cible) {
            $regex = preg_replace('/:([a-z_]+)/', '([^/]+)', $patron);
            if (preg_match('#^' . $regex . '$#', $uri, $matches)) {
                array_shift($matches);
                [$controleur, $action] = $cible;
                $this->appeler($controleur, $action, $matches);
                return;
            }
        }

        // Aucune route trouvée → 404
        http_response_code(404);
        require APP_ROOT . '/app/views/layouts/main.php';
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
