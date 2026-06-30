<?php
/**
 * Classe de base pour tous les contrôleurs.
 * Fournit le rendu des vues avec layout.
 */
abstract class Controller
{
    /**
     * Charge et affiche une vue avec des variables injectées.
     *
     * @param string $vue     Chemin relatif depuis app/views/ (ex: 'home/index')
     * @param array  $donnees Variables à extraire dans la vue
     * @param string $layout  Nom du layout à utiliser (sans extension)
     */
    protected function render(string $vue, array $donnees = [], string $layout = 'main'): void
    {
        // Rend les variables disponibles dans la vue
        extract($donnees);

        // Capture le contenu de la vue
        ob_start();
        $fichierVue = APP_ROOT . '/app/views/' . $vue . '.php';
        if (!file_exists($fichierVue)) {
            ob_end_clean();
            $this->erreur404();
            return;
        }
        require $fichierVue;
        $contenu = ob_get_clean();

        // Injecte le contenu dans le layout
        $fichierLayout = APP_ROOT . '/app/views/layouts/' . $layout . '.php';
        if (file_exists($fichierLayout)) {
            require $fichierLayout;
        } else {
            echo $contenu;
        }
    }

    /**
     * Redirige vers une URL interne.
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . APP_URL . $url);
        exit;
    }

    /**
     * Affiche la page d'erreur 404.
     */
    protected function erreur404(): void
    {
        http_response_code(404);
        $contenu = '<h1>Page introuvable</h1><p>La page demandée n\'existe pas.</p>';
        require APP_ROOT . '/app/views/layouts/main.php';
        exit;
    }

    /**
     * Capture et retourne le HTML d'un partial (sans layout).
     *
     * @param string $partial Chemin relatif depuis app/views/ (ex: 'partials/challenge_card')
     * @param array  $donnees Variables à extraire dans le partial
     */
    protected function renderPartiel(string $partial, array $donnees = []): string
    {
        extract($donnees);
        ob_start();
        $fichier = APP_ROOT . '/app/views/' . $partial . '.php';
        if (file_exists($fichier)) {
            require $fichier;
        }
        return ob_get_clean();
    }

    /**
     * Retourne une réponse JSON (pour les appels AJAX).
     */
    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
