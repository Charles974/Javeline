<?php
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/app/models/ChallengeModel.php';

class ChallengeController extends Controller
{
    private ChallengeModel $model;

    public function __construct()
    {
        $this->model = new ChallengeModel();
    }

    /**
     * POST /challenges/creer
     * Reçoit les données JSON/form, valide, insère et retourne du JSON.
     */
    public function creer(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
        }

        $libelle    = trim($_POST['libelle']    ?? '');
        $dateDebut  = trim($_POST['date_debut'] ?? '');
        $dateFin    = trim($_POST['date_fin']   ?? '');

        $erreurs = $this->valider($libelle, $dateDebut, $dateFin);

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

        // Retourne le HTML mis à jour de la carte challenge
        $challengeActif = $this->model->findActif();
        $html = $this->renderPartiel('partials/challenge_card', ['challengeActif' => $challengeActif]);

        $this->json(['success' => true, 'html' => $html]);
    }

    /**
     * Valide les données du formulaire challenge.
     * Retourne un tableau d'erreurs (vide si tout est valide).
     */
    private function valider(string $libelle, string $dateDebut, string $dateFin): array
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
