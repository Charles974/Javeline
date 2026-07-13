<?php
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/app/models/UtilisateurModel.php';

/**
 * Connexion, déconnexion et changement de son propre mot de passe.
 */
class AuthController extends Controller
{
    private UtilisateurModel $model;

    public function __construct()
    {
        $this->model = new UtilisateurModel();
    }

    // ----------------------------------------------------------------
    // GET /connexion — formulaire de connexion
    // ----------------------------------------------------------------
    public function formulaire(): void
    {
        // Déjà connecté → retour à l'accueil
        if (Auth::estConnecte()) {
            $this->redirect('/');
        }

        $this->render('auth/connexion', [
            'titrePage'   => 'Connexion — ' . APP_NAME,
            'erreur'      => null,
            'identifiant' => '',
        ], 'auth');
    }

    // ----------------------------------------------------------------
    // POST /connexion — vérification des identifiants
    // ----------------------------------------------------------------
    public function connecter(): void
    {
        if (Auth::estConnecte()) {
            $this->redirect('/');
        }

        $identifiant = trim($_POST['identifiant'] ?? '');
        $motDePasse  = $_POST['mot_de_passe'] ?? '';

        $utilisateur = false;
        if ($identifiant !== '' && $motDePasse !== '') {
            $utilisateur = $this->model->verifierIdentifiants($identifiant, $motDePasse);
        }

        if (!$utilisateur) {
            // Message volontairement identique quel que soit le champ fautif
            // (ne pas révéler si l'identifiant existe).
            $this->render('auth/connexion', [
                'titrePage'   => 'Connexion — ' . APP_NAME,
                'erreur'      => 'Identifiant ou mot de passe incorrect.',
                'identifiant' => $identifiant,
            ], 'auth');
            return;
        }

        Auth::connecter($utilisateur);
        $this->redirect('/');
    }

    // ----------------------------------------------------------------
    // GET /deconnexion
    // ----------------------------------------------------------------
    public function deconnecter(): void
    {
        Auth::deconnecter();
        $this->redirect('/connexion');
    }

    // ----------------------------------------------------------------
    // POST /mot-de-passe — changement de son propre mot de passe (AJAX)
    // ----------------------------------------------------------------
    public function changerMotDePasse(): void
    {
        $actuel       = $_POST['mot_de_passe_actuel'] ?? '';
        $nouveau      = $_POST['nouveau_mot_de_passe'] ?? '';
        $confirmation = $_POST['confirmation_mot_de_passe'] ?? '';

        $erreurs = [];

        // Le mot de passe actuel doit être re-vérifié même en session active
        $utilisateur = $this->model->findById(Auth::id());
        if (!$utilisateur || !password_verify($actuel, $utilisateur['mot_de_passe'])) {
            $erreurs[] = 'Le mot de passe actuel est incorrect.';
        }

        $erreurs = array_merge($erreurs, $this->validerNouveauMotDePasse($nouveau, $confirmation));

        if (!empty($erreurs)) {
            $this->json(['success' => false, 'erreurs' => $erreurs], 422);
        }

        try {
            $this->model->changerMotDePasse(Auth::id(), $nouveau);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de l\'enregistrement du mot de passe.'], 500);
        }

        $this->json(['success' => true, 'message' => 'Mot de passe modifié avec succès.']);
    }

    /**
     * Règles communes de validation d'un nouveau mot de passe.
     */
    private function validerNouveauMotDePasse(string $nouveau, string $confirmation): array
    {
        $erreurs = [];

        if (mb_strlen($nouveau) < Auth::LONGUEUR_MIN_MDP) {
            $erreurs[] = 'Le nouveau mot de passe doit contenir au moins '
                . Auth::LONGUEUR_MIN_MDP . ' caractères.';
        }
        if ($nouveau !== $confirmation) {
            $erreurs[] = 'La confirmation ne correspond pas au nouveau mot de passe.';
        }

        return $erreurs;
    }
}
