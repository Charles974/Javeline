<?php
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/app/models/ChallengeModel.php';
require_once APP_ROOT . '/app/models/InscriptionModel.php';
require_once APP_ROOT . '/app/models/DisciplineModel.php';
require_once APP_ROOT . '/app/models/MembreModel.php';
require_once APP_ROOT . '/app/models/ExterneModel.php';

class ChallengeController extends Controller
{
    private ChallengeModel    $model;
    private InscriptionModel  $inscriptions;
    private DisciplineModel   $disciplines;

    public function __construct()
    {
        $this->model        = new ChallengeModel();
        $this->inscriptions = new InscriptionModel();
        $this->disciplines  = new DisciplineModel();
    }

    // ----------------------------------------------------------------
    // POST /challenges/creer
    // ----------------------------------------------------------------
    public function creer(): void
    {
        $libelle   = trim($_POST['libelle']    ?? '');
        $dateDebut = trim($_POST['date_debut'] ?? '');
        $dateFin   = trim($_POST['date_fin']   ?? '');

        $erreurs = $this->validerChallenge($libelle, $dateDebut, $dateFin);

        if (!empty($erreurs)) {
            $this->json(['success' => false, 'erreurs' => $erreurs], 422);
        }

        try {
            $this->model->insert([
                'libelle'    => $libelle,
                'date_debut' => $dateDebut,
                'date_fin'   => $dateFin,
                'statut'     => 'ouvert',
            ]);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la création du challenge.'], 500);
        }

        $challengeActif = $this->model->findActif();
        $html = $this->renderPartiel('partials/challenge_card', ['challengeActif' => $challengeActif]);

        $this->json(['success' => true, 'html' => $html]);
    }

    // ----------------------------------------------------------------
    // GET /challenges/:id — page de gestion des inscriptions
    // ----------------------------------------------------------------
    public function detail(int $id): void
    {
        $challenge = $this->model->findById($id);

        if (!$challenge) {
            $this->erreur404();
            return;
        }

        $membres  = $this->inscriptions->findMembresDispo($id);
        $externes = $this->inscriptions->findExternesDispo($id);
        $inscrits = $this->inscriptions->findByChallenge($id);
        $disciplines = $this->disciplines->findAll();

        $this->render('challenges/inscriptions', [
            'titrePage'   => htmlspecialchars($challenge['libelle']) . ' — ' . APP_NAME,
            'challenge'   => $challenge,
            'membres'     => $membres,
            'externes'    => $externes,
            'inscrits'    => $inscrits,
            'disciplines' => $disciplines,
        ]);
    }

    // ----------------------------------------------------------------
    // POST /challenges/:id/inscrire
    // ----------------------------------------------------------------
    public function inscrire(int $id): void
    {
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->json(['success' => false, 'message' => 'Challenge introuvable.'], 404);
        }

        $type        = $_POST['tireur_type'] ?? '';
        $tireurId    = (int)($_POST['tireur_id'] ?? 0);
        $disciplineIds = array_filter(array_map('intval', $_POST['discipline_ids'] ?? []));

        $erreurs = $this->validerInscription($type, $tireurId, $disciplineIds);
        if (!empty($erreurs)) {
            $this->json(['success' => false, 'erreurs' => $erreurs], 422);
        }

        try {
            $this->inscriptions->inscrireTireur($id, $type, $tireurId, $disciplineIds);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de l\'inscription.'], 500);
        }

        $this->json([
            'success'  => true,
            'message'  => 'Tireur inscrit avec succès.',
            'panneaux' => $this->htmlPanneaux($id),
        ]);
    }

    // ----------------------------------------------------------------
    // POST /challenges/:id/modifier-inscriptions
    // ----------------------------------------------------------------
    public function modifierInscriptions(int $id): void
    {
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->json(['success' => false, 'message' => 'Challenge introuvable.'], 404);
        }

        $type        = $_POST['tireur_type'] ?? '';
        $tireurId    = (int)($_POST['tireur_id'] ?? 0);
        $disciplineIds = array_filter(array_map('intval', $_POST['discipline_ids'] ?? []));

        $erreurs = $this->validerInscription($type, $tireurId, $disciplineIds);
        if (!empty($erreurs)) {
            $this->json(['success' => false, 'erreurs' => $erreurs], 422);
        }

        try {
            $this->inscriptions->modifierTireur($id, $type, $tireurId, $disciplineIds);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la modification.'], 500);
        }

        $this->json([
            'success'  => true,
            'message'  => 'Inscriptions mises à jour.',
            'panneaux' => $this->htmlPanneaux($id),
        ]);
    }

    // ----------------------------------------------------------------
    // POST /challenges/:id/supprimer-inscription
    // ----------------------------------------------------------------
    public function supprimerInscription(int $id): void
    {
        $inscriptionId = (int)($_POST['inscription_id'] ?? 0);

        if ($inscriptionId <= 0) {
            $this->json(['success' => false, 'message' => 'Inscription invalide.'], 400);
        }

        try {
            $this->inscriptions->supprimerInscription($inscriptionId);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la suppression.'], 500);
        }

        $this->json([
            'success'  => true,
            'message'  => 'Inscription supprimée.',
            'panneaux' => $this->htmlPanneaux($id),
        ]);
    }

    // ----------------------------------------------------------------
    // GET /challenges/:id/disciplines-tireur — JSON pour le formulaire
    // ----------------------------------------------------------------
    public function disciplinesTireur(int $id): void
    {
        $type     = $_GET['type']      ?? '';
        $tireurId = (int)($_GET['tid'] ?? 0);

        $ids = $this->inscriptions->findDisciplinesByTireur($id, $type, $tireurId);
        $this->json(['success' => true, 'discipline_ids' => $ids]);
    }

    // ----------------------------------------------------------------
    // GET /challenges/:id/imprimer — fiche imprimable des inscrits
    // ----------------------------------------------------------------
    public function imprimer(int $id): void
    {
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->erreur404();
            return;
        }

        $inscrits = $this->inscriptions->findByChallenge($id);

        $this->render('challenges/print_inscrits', [
            'titrePage' => 'Inscrits — ' . htmlspecialchars($challenge['libelle']),
            'challenge' => $challenge,
            'inscrits'  => $inscrits,
        ], 'print');
    }

    // ----------------------------------------------------------------
    // Méthodes privées
    // ----------------------------------------------------------------

    /**
     * Retourne le HTML des deux panneaux (disponibles + inscrits) pour la mise à jour AJAX.
     */
    private function htmlPanneaux(int $challengeId): array
    {
        $membres  = $this->inscriptions->findMembresDispo($challengeId);
        $externes = $this->inscriptions->findExternesDispo($challengeId);
        $inscrits = $this->inscriptions->findByChallenge($challengeId);

        return [
            'membres'  => $this->renderPartiel('partials/challenge_membres_dispo',  ['membres'  => $membres,  'challengeId' => $challengeId]),
            'externes' => $this->renderPartiel('partials/challenge_externes_dispo',  ['externes' => $externes, 'challengeId' => $challengeId]),
            'inscrits' => $this->renderPartiel('partials/challenge_inscrits_liste', ['inscrits' => $inscrits,  'challengeId' => $challengeId]),
        ];
    }

    private function validerInscription(string $type, int $tireurId, array $disciplineIds): array
    {
        $erreurs = [];

        if (!in_array($type, ['membre', 'externe'], true)) {
            $erreurs[] = 'Type de tireur invalide.';
        }
        if ($tireurId <= 0) {
            $erreurs[] = 'Tireur invalide.';
        }
        if (empty($disciplineIds)) {
            $erreurs[] = 'Veuillez sélectionner au moins une discipline.';
        }

        return $erreurs;
    }

    private function validerChallenge(string $libelle, string $dateDebut, string $dateFin): array
    {
        $erreurs = [];

        if ($libelle === '') {
            $erreurs[] = 'Le nom du challenge est obligatoire.';
        } elseif (mb_strlen($libelle) > 200) {
            $erreurs[] = 'Le nom du challenge ne peut pas dépasser 200 caractères.';
        }
        if ($dateDebut === '') {
            $erreurs[] = 'La date de début est obligatoire.';
        } elseif (!$this->estDateValide($dateDebut)) {
            $erreurs[] = 'La date de début est invalide.';
        }
        if ($dateFin === '') {
            $erreurs[] = 'La date de fin est obligatoire.';
        } elseif (!$this->estDateValide($dateFin)) {
            $erreurs[] = 'La date de fin est invalide.';
        }
        if (empty($erreurs) && $dateFin < $dateDebut) {
            $erreurs[] = 'La date de fin doit être égale ou postérieure à la date de début.';
        }

        return $erreurs;
    }

    private function estDateValide(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
