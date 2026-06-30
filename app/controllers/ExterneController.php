<?php
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/app/models/ExterneModel.php';

class ExterneController extends Controller
{
    private ExterneModel $model;

    public function __construct()
    {
        $this->model = new ExterneModel();
    }

    /**
     * GET /externes
     */
    public function index(): void
    {
        $externes = $this->model->findAll();

        $this->render('externes/index', [
            'titrePage' => 'Tireurs non membres — ' . APP_NAME,
            'externes'  => $externes,
        ]);
    }

    /**
     * GET /externes/get/:id
     * Retourne les données complètes d'un tireur externe en JSON.
     */
    public function get(int $id): void
    {
        $externe = $this->model->findById($id);

        if (!$externe) {
            $this->json(['success' => false, 'message' => 'Tireur introuvable.'], 404);
        }

        $this->json(['success' => true, 'externe' => $externe]);
    }

    /**
     * POST /externes/ajouter
     * Retourne JSON.
     */
    public function ajouter(): void
    {
        $data    = $this->extrairePost();
        $erreurs = $this->valider($data);

        if (!empty($erreurs)) {
            $this->json(['success' => false, 'erreurs' => $erreurs], 422);
        }

        try {
            $this->model->insert($data);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de l\'enregistrement.'], 500);
        }

        $externes = $this->model->findAll();
        $html     = $this->renderPartiel('partials/externes_liste', ['externes' => $externes]);

        $this->json(['success' => true, 'message' => 'Tireur ajouté avec succès.', 'html' => $html]);
    }

    /**
     * POST /externes/modifier
     * Retourne JSON.
     */
    public function modifier(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0 || !$this->model->findById($id)) {
            $this->json(['success' => false, 'message' => 'Tireur introuvable.'], 404);
        }

        $data    = $this->extrairePost();
        $erreurs = $this->valider($data);

        if (!empty($erreurs)) {
            $this->json(['success' => false, 'erreurs' => $erreurs], 422);
        }

        try {
            $this->model->update($id, $data);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la modification.'], 500);
        }

        $externes = $this->model->findAll();
        $html     = $this->renderPartiel('partials/externes_liste', ['externes' => $externes]);

        $this->json(['success' => true, 'message' => 'Tireur modifié avec succès.', 'html' => $html]);
    }

    /**
     * GET /externes/fiche/:id
     * Vue imprimable du tireur externe.
     */
    public function fiche(int $id): void
    {
        $externe = $this->model->findById($id);

        if (!$externe) {
            $this->erreur404();
            return;
        }

        $this->render('externes/fiche', [
            'titrePage' => 'Fiche tireur — ' . htmlspecialchars($externe['nom'] . ' ' . $externe['prenom']),
            'externe'   => $externe,
        ]);
    }

    /**
     * POST /externes/supprimer
     * Retourne JSON.
     */
    public function supprimer(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0 || !$this->model->findById($id)) {
            $this->json(['success' => false, 'message' => 'Tireur introuvable.'], 404);
        }

        try {
            $this->model->delete($id);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Impossible de supprimer ce tireur (il est peut-être inscrit à un challenge).'], 500);
        }

        $externes = $this->model->findAll();
        $html     = $this->renderPartiel('partials/externes_liste', ['externes' => $externes]);

        $this->json(['success' => true, 'message' => 'Tireur supprimé.', 'html' => $html]);
    }

    // ----------------------------------------------------------------
    // Méthodes privées
    // ----------------------------------------------------------------

    private function extrairePost(): array
    {
        return [
            'nom'       => trim($_POST['nom']       ?? ''),
            'prenom'    => trim($_POST['prenom']    ?? ''),
            'club'      => trim($_POST['club']      ?? ''),
            'telephone' => trim($_POST['telephone'] ?? '') ?: null,
            'email'     => trim($_POST['email']     ?? '') ?: null,
            'etranger'  => isset($_POST['etranger']) ? 1 : 0,
        ];
    }

    private function valider(array $data): array
    {
        $erreurs = [];

        if ($data['nom'] === '') {
            $erreurs[] = 'Le nom est obligatoire.';
        }
        if ($data['prenom'] === '') {
            $erreurs[] = 'Le prénom est obligatoire.';
        }
        if ($data['club'] === '') {
            $erreurs[] = 'Le club est obligatoire.';
        }
        if ($data['email'] !== null && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'L\'adresse email n\'est pas valide.';
        }

        return $erreurs;
    }
}
