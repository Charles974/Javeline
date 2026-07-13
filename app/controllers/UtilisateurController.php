<?php
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/app/models/UtilisateurModel.php';

/**
 * Gestion des comptes utilisateurs (réservée à l'administrateur) :
 * ajout, suppression, changement de profil et de mot de passe.
 */
class UtilisateurController extends Controller
{
    private UtilisateurModel $model;

    public function __construct()
    {
        $this->model = new UtilisateurModel();
    }

    // ----------------------------------------------------------------
    // GET /utilisateurs — page de gestion des comptes
    // ----------------------------------------------------------------
    public function index(): void
    {
        $this->render('utilisateurs/index', [
            'titrePage'    => 'Gestion des comptes — ' . APP_NAME,
            'utilisateurs' => $this->model->findAllSansMotDePasse(),
        ]);
    }

    // ----------------------------------------------------------------
    // POST /utilisateurs/ajouter (AJAX)
    // ----------------------------------------------------------------
    public function ajouter(): void
    {
        $identifiant = trim($_POST['identifiant'] ?? '');
        $motDePasse  = $_POST['mot_de_passe'] ?? '';
        $role        = $_POST['role'] ?? '';

        $erreurs = $this->validerIdentifiant($identifiant);
        $erreurs = array_merge($erreurs, $this->validerRole($role));

        if (mb_strlen($motDePasse) < Auth::LONGUEUR_MIN_MDP) {
            $erreurs[] = 'Le mot de passe doit contenir au moins ' . Auth::LONGUEUR_MIN_MDP . ' caractères.';
        }
        if (empty($erreurs) && $this->model->identifiantExiste($identifiant)) {
            $erreurs[] = 'Cet identifiant est déjà utilisé.';
        }

        if (!empty($erreurs)) {
            $this->json(['success' => false, 'erreurs' => $erreurs], 422);
        }

        try {
            $this->model->creer($identifiant, $motDePasse, $role);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la création du compte.'], 500);
        }

        $this->json([
            'success' => true,
            'message' => 'Compte créé avec succès.',
            'html'    => $this->htmlListe(),
        ]);
    }

    // ----------------------------------------------------------------
    // POST /utilisateurs/modifier (AJAX)
    // Change le profil et/ou réinitialise le mot de passe d'un compte.
    // ----------------------------------------------------------------
    public function modifier(): void
    {
        $id          = (int) ($_POST['id'] ?? 0);
        $role        = $_POST['role'] ?? '';
        $motDePasse  = $_POST['mot_de_passe'] ?? '';

        $utilisateur = $id > 0 ? $this->model->findById($id) : false;
        if (!$utilisateur) {
            $this->json(['success' => false, 'message' => 'Compte introuvable.'], 404);
        }

        $erreurs = $this->validerRole($role);

        // Un administrateur ne peut pas changer son propre profil : cela
        // garantit qu'il reste toujours au moins un compte administrateur.
        if ($id === Auth::id() && $role !== $utilisateur['role']) {
            $erreurs[] = 'Vous ne pouvez pas changer le profil de votre propre compte.';
        }

        // Mot de passe laissé vide = inchangé
        if ($motDePasse !== '' && mb_strlen($motDePasse) < Auth::LONGUEUR_MIN_MDP) {
            $erreurs[] = 'Le mot de passe doit contenir au moins ' . Auth::LONGUEUR_MIN_MDP . ' caractères.';
        }

        if (!empty($erreurs)) {
            $this->json(['success' => false, 'erreurs' => $erreurs], 422);
        }

        try {
            $this->model->changerRole($id, $role);
            if ($motDePasse !== '') {
                $this->model->changerMotDePasse($id, $motDePasse);
            }
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la modification du compte.'], 500);
        }

        $this->json([
            'success' => true,
            'message' => 'Compte modifié avec succès.',
            'html'    => $this->htmlListe(),
        ]);
    }

    // ----------------------------------------------------------------
    // POST /utilisateurs/supprimer (AJAX)
    // ----------------------------------------------------------------
    public function supprimer(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0 || !$this->model->findById($id)) {
            $this->json(['success' => false, 'message' => 'Compte introuvable.'], 404);
        }

        // Interdit de supprimer son propre compte : la session deviendrait
        // orpheline et il resterait toujours au moins un administrateur.
        if ($id === Auth::id()) {
            $this->json(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }

        try {
            $this->model->delete($id);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la suppression du compte.'], 500);
        }

        $this->json([
            'success' => true,
            'message' => 'Compte supprimé.',
            'html'    => $this->htmlListe(),
        ]);
    }

    // ----------------------------------------------------------------
    // Méthodes privées
    // ----------------------------------------------------------------

    private function htmlListe(): string
    {
        return $this->renderPartiel('partials/utilisateurs_liste', [
            'utilisateurs' => $this->model->findAllSansMotDePasse(),
        ]);
    }

    private function validerIdentifiant(string $identifiant): array
    {
        $erreurs = [];

        if ($identifiant === '') {
            $erreurs[] = 'L\'identifiant est obligatoire.';
        } elseif (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $identifiant)) {
            $erreurs[] = 'L\'identifiant doit contenir de 3 à 50 caractères '
                . '(lettres, chiffres, point, tiret ou underscore).';
        }

        return $erreurs;
    }

    private function validerRole(string $role): array
    {
        if (!array_key_exists($role, Auth::ROLES)) {
            return ['Le profil sélectionné est invalide.'];
        }
        return [];
    }
}
