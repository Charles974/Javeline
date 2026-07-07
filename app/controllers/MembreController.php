<?php
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/app/models/MembreModel.php';

class MembreController extends Controller
{
    private MembreModel $model;

    public function __construct()
    {
        $this->model = new MembreModel();
    }

    /**
     * GET /membres
     */
    public function index(): void
    {
        $membres = $this->model->findAll();

        $this->render('membres/index', [
            'titrePage' => 'Tireurs membres — ' . APP_NAME,
            'membres'   => $membres,
        ]);
    }

    /**
     * GET /membres/imprimer
     * Vue imprimable de la liste des tireurs membres.
     */
    public function imprimerListe(): void
    {
        $membres = $this->model->findAll();

        $this->render('membres/print_liste', [
            'titrePage' => 'Liste des tireurs membres — ' . APP_NAME,
            'membres'   => $membres,
        ], 'print_simple');
    }

    /**
     * POST /membres/ajouter
     * Retourne JSON.
     */
    public function ajouter(): void
    {
        $data    = $this->extrairePost();
        $erreurs = $this->valider($data);

        if ($this->model->licenceExiste($data['numero_licence'])) {
            $erreurs[] = 'Ce numéro de licence est déjà enregistré.';
        }

        if (!empty($erreurs)) {
            $this->json(['success' => false, 'erreurs' => $erreurs], 422);
        }

        try {
            $this->model->insert($data);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de l\'enregistrement.'], 500);
        }

        $membres = $this->model->findAll();
        $html    = $this->renderPartiel('partials/membres_liste', ['membres' => $membres]);

        $this->json(['success' => true, 'message' => 'Membre ajouté avec succès.', 'html' => $html]);
    }

    /**
     * POST /membres/modifier
     * Retourne JSON.
     */
    public function modifier(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0 || !$this->model->findById($id)) {
            $this->json(['success' => false, 'message' => 'Membre introuvable.'], 404);
        }

        $data    = $this->extrairePost();
        $erreurs = $this->valider($data);

        if ($this->model->licenceExiste($data['numero_licence'], $id)) {
            $erreurs[] = 'Ce numéro de licence est déjà utilisé par un autre membre.';
        }

        if (!empty($erreurs)) {
            $this->json(['success' => false, 'erreurs' => $erreurs], 422);
        }

        try {
            $this->model->update($id, $data);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la modification.'], 500);
        }

        $membres = $this->model->findAll();
        $html    = $this->renderPartiel('partials/membres_liste', ['membres' => $membres]);

        $this->json(['success' => true, 'message' => 'Membre modifié avec succès.', 'html' => $html]);
    }

    /**
     * GET /membres/get/:id
     * Retourne les données complètes d'un membre en JSON (pour remplir le formulaire).
     */
    public function get(int $id): void
    {
        $membre = $this->model->findById($id);

        if (!$membre) {
            $this->json(['success' => false, 'message' => 'Membre introuvable.'], 404);
        }

        $this->json(['success' => true, 'membre' => $membre]);
    }

    /**
     * GET /membres/fiche/:id
     * Vue imprimable pour génération PDF navigateur.
     */
    public function fiche(int $id): void
    {
        $membre = $this->model->findById($id);

        if (!$membre) {
            $this->erreur404();
            return;
        }

        $this->render('membres/fiche', [
            'titrePage' => 'Fiche membre — ' . htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']),
            'membre'    => $membre,
        ], 'print_fiche');
    }

    /**
     * GET /membres/historique/:id
     * Liste des challenges auxquels le membre a participé, avec son score.
     */
    public function historique(int $id): void
    {
        $membre = $this->model->findById($id);

        if (!$membre) {
            $this->erreur404();
            return;
        }

        $challenges = $this->model->findHistoriqueChallenges($id);

        $this->render('membres/historique', [
            'titrePage'  => 'Historique — ' . htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) . ' — ' . APP_NAME,
            'membre'     => $membre,
            'challenges' => $challenges,
        ]);
    }

    /**
     * POST /membres/supprimer
     * Retourne JSON.
     */
    public function supprimer(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0 || !$this->model->findById($id)) {
            $this->json(['success' => false, 'message' => 'Membre introuvable.'], 404);
        }

        try {
            $this->model->delete($id);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Impossible de supprimer ce membre (il est peut-être inscrit à un challenge).'], 500);
        }

        $membres = $this->model->findAll();
        $html    = $this->renderPartiel('partials/membres_liste', ['membres' => $membres]);

        $this->json(['success' => true, 'message' => 'Membre supprimé.', 'html' => $html]);
    }

    // ----------------------------------------------------------------
    // Méthodes privées
    // ----------------------------------------------------------------

    private function extrairePost(): array
    {
        return [
            'nom'               => trim($_POST['nom']              ?? ''),
            'prenom'            => trim($_POST['prenom']           ?? ''),
            'date_naissance'    => trim($_POST['date_naissance']   ?? ''),
            'lieu_naissance'    => trim($_POST['lieu_naissance']   ?? ''),
            'numero_licence'    => trim($_POST['numero_licence']   ?? ''),
            'adresse1'          => trim($_POST['adresse1']         ?? ''),
            'adresse2'          => trim($_POST['adresse2']         ?? '') ?: null,
            'code_postal'       => trim($_POST['code_postal']      ?? ''),
            'ville'             => trim($_POST['ville']            ?? ''),
            'telephone'         => str_replace(' ', '', trim($_POST['telephone'] ?? '')),
            'email'             => trim($_POST['email']            ?? ''),
            'certificat_medical'=> isset($_POST['certificat_medical']) ? 1 : 0,
            'coach'             => trim($_POST['coach']             ?? '') ?: null,
        ];
    }

    private function valider(array $data): array
    {
        $erreurs = [];

        $obligatoires = [
            'nom'            => 'Le nom',
            'prenom'         => 'Le prénom',
            'date_naissance' => 'La date de naissance',
            'lieu_naissance' => 'Le lieu de naissance',
            'numero_licence' => 'Le numéro de licence',
            'adresse1'       => 'L\'adresse',
            'code_postal'    => 'Le code postal',
            'ville'          => 'La ville',
            'telephone'      => 'Le téléphone',
            'email'          => 'L\'email',
        ];

        foreach ($obligatoires as $champ => $libelle) {
            if ($data[$champ] === '' || $data[$champ] === null) {
                $erreurs[] = $libelle . ' est obligatoire.';
            }
        }

        if ($data['code_postal'] !== '' && !ctype_digit($data['code_postal'])) {
            $erreurs[] = 'Le code postal doit contenir uniquement des chiffres.';
        }
        if ($data['telephone'] !== '' && !ctype_digit($data['telephone'])) {
            $erreurs[] = 'Le téléphone doit contenir uniquement des chiffres.';
        }
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'L\'adresse email n\'est pas valide.';
        }

        if ($data['date_naissance'] !== '') {
            $d = DateTime::createFromFormat('Y-m-d', $data['date_naissance']);
            if (!$d || $d->format('Y-m-d') !== $data['date_naissance']) {
                $erreurs[] = 'La date de naissance est invalide.';
            }
        }

        return $erreurs;
    }
}
