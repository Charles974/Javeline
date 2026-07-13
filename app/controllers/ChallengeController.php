<?php
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/app/models/ChallengeModel.php';
require_once APP_ROOT . '/app/models/InscriptionModel.php';
require_once APP_ROOT . '/app/models/DisciplineModel.php';
require_once APP_ROOT . '/app/models/MembreModel.php';
require_once APP_ROOT . '/app/models/ExterneModel.php';
require_once APP_ROOT . '/app/models/BlocHoraireModel.php';

class ChallengeController extends Controller
{
    // Definition fixe des combines (aggregates) — cahier des charges §Combinés
    private const COMBINES = [
        'gros-calibre'  => ['libelle_fr' => 'Combiné Gros Calibre',             'libelle_en' => 'Aggregate Big Bore',        'codes' => [400, 401, 402, 403]],
        'petit-calibre' => ['libelle_fr' => 'Combiné Petit Calibre',            'libelle_en' => 'Aggregate Small Bore',      'codes' => [404, 405, 406, 407]],
        'field'         => ['libelle_fr' => 'Combiné Field',                   'libelle_en' => 'Aggregate Field',           'codes' => [408, 409]],
        'carabine-pc'   => ['libelle_fr' => 'Combiné Carabine Petit Calibre',   'libelle_en' => 'Aggregate Small Bore Rifle','codes' => [410, 411]],
        'carabine-gc'   => ['libelle_fr' => 'Combiné Carabine Gros Calibre',    'libelle_en' => 'Aggregate Big Bore Rifle',  'codes' => [412, 413]],
        'debout'        => ['libelle_fr' => 'Combiné Debout',                  'libelle_en' => 'Aggregate Standing',        'codes' => [403, 407, 408, 409]],
    ];

    // Durée forfaitaire d'occupation d'un tireur par discipline, utilisée pour
    // détecter les créneaux trop proches (avertissement non bloquant).
    private const DUREE_CONFLIT_MINUTES = 60;

    private ChallengeModel    $model;
    private InscriptionModel  $inscriptions;
    private DisciplineModel   $disciplines;
    private BlocHoraireModel  $blocsHoraires;

    public function __construct()
    {
        $this->model         = new ChallengeModel();
        $this->inscriptions  = new InscriptionModel();
        $this->disciplines   = new DisciplineModel();
        $this->blocsHoraires = new BlocHoraireModel();
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
    public function detail(string $id): void
    {
        $id = (int) $id;
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
            'titrePage'             => htmlspecialchars($challenge['libelle']) . ' — ' . APP_NAME,
            'challenge'             => $challenge,
            'membres'               => $membres,
            'externes'              => $externes,
            'inscrits'              => $inscrits,
            'disciplinesParFamille' => $this->grouperDisciplinesParFamille($disciplines),
        ]);
    }

    // ----------------------------------------------------------------
    // GET /challenges/:id/panneaux — rafraîchit les listes après une
    // modification de fiche tireur (sans recharger la page)
    // ----------------------------------------------------------------
    public function panneaux(string $id): void
    {
        $id = (int) $id;
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->json(['success' => false, 'message' => 'Challenge introuvable.'], 404);
        }

        $this->json(['success' => true, 'panneaux' => $this->htmlPanneaux($id)]);
    }

    // ----------------------------------------------------------------
    // POST /challenges/:id/inscrire
    // ----------------------------------------------------------------
    public function inscrire(string $id): void
    {
        $id = (int) $id;
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
    public function modifierInscriptions(string $id): void
    {
        $id = (int) $id;
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
    public function supprimerInscription(string $id): void
    {
        $id = (int) $id;
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
    public function disciplinesTireur(string $id): void
    {
        $id = (int) $id;
        $type     = $_GET['type']      ?? '';
        $tireurId = (int)($_GET['tid'] ?? 0);

        $ids = $this->inscriptions->findDisciplinesByTireur($id, $type, $tireurId);
        $this->json(['success' => true, 'discipline_ids' => $ids]);
    }

    // ----------------------------------------------------------------
    // POST /challenges/:id/saisir-score
    // ----------------------------------------------------------------
    public function saisirScore(string $id): void
    {
        $id = (int) $id;
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->json(['success' => false, 'message' => 'Challenge introuvable.'], 404);
        }
        if ($challenge['statut'] === 'archive') {
            $this->json(['success' => false, 'message' => 'Ce challenge est archivé.'], 403);
        }

        $inscriptionId = (int)($_POST['inscription_id'] ?? 0);
        $poulets       = max(0, (int)($_POST['poulets']  ?? 0));
        $cochons       = max(0, (int)($_POST['cochons']  ?? 0));
        $dindons       = max(0, (int)($_POST['dindons']  ?? 0));
        $mouflons      = max(0, (int)($_POST['mouflons'] ?? 0));

        if ($inscriptionId <= 0) {
            $this->json(['success' => false, 'message' => 'Inscription invalide.'], 400);
        }

        try {
            $res = $this->inscriptions->saisirScore(
                $inscriptionId,
                $challenge['date_debut'],
                $poulets, $cochons, $dindons, $mouflons
            );
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }

        $this->json([
            'success'       => true,
            'message'       => 'Score enregistré.',
            'inscription_id'=> $inscriptionId,
            'match_id'      => $res['match_id'],
            'score_id'      => $res['score_id'],
            'poulets'       => $poulets,
            'cochons'       => $cochons,
            'dindons'       => $dindons,
            'mouflons'      => $mouflons,
            'total'         => $poulets + $cochons + $dindons + $mouflons,
        ]);
    }

    // ----------------------------------------------------------------
    // POST /challenges/:id/modifier-horaire
    // Met à jour le plan de tir (date/heure) d'une inscription.
    // N'empêche jamais l'enregistrement : un chevauchement d'horaires pour
    // le même tireur ne déclenche qu'un avertissement (non bloquant).
    // ----------------------------------------------------------------
    public function modifierHoraire(string $id): void
    {
        $id = (int) $id;
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->json(['success' => false, 'message' => 'Challenge introuvable.'], 404);
        }
        if ($challenge['statut'] === 'archive') {
            $this->json(['success' => false, 'message' => 'Ce challenge est archivé.'], 403);
        }

        $inscriptionId = (int)($_POST['inscription_id'] ?? 0);
        $dateMatch     = trim($_POST['date_match']  ?? '');
        $heureDebut    = trim($_POST['heure_debut'] ?? '');
        $heureFin      = trim($_POST['heure_fin']   ?? '');

        $infos = $inscriptionId > 0 ? $this->inscriptions->findInfosTireur($inscriptionId) : false;
        if (!$infos || (int)$infos['challenge_id'] !== $id) {
            $this->json(['success' => false, 'message' => 'Inscription introuvable pour ce challenge.'], 404);
        }

        $erreurs = [];
        if (!$this->estDateValide($dateMatch)) {
            $erreurs[] = 'La date du match est invalide.';
        }
        if (!$this->estHeureValide($heureDebut)) {
            $erreurs[] = 'L\'heure de début est invalide.';
        }
        if (!$this->estHeureValide($heureFin)) {
            $erreurs[] = 'L\'heure de fin est invalide.';
        }
        if (empty($erreurs) && $heureFin <= $heureDebut) {
            $erreurs[] = 'L\'heure de fin doit être postérieure à l\'heure de début.';
        }

        if (!empty($erreurs)) {
            $this->json(['success' => false, 'erreurs' => $erreurs], 422);
        }

        // Un bloc horaire (ouverture, pause déjeuner, rangement...) bloque
        // strictement l'assignation d'un tireur sur ce créneau.
        $bloc = $this->blocsHoraires->trouveBlocCouvrant($id, $dateMatch, $heureDebut, $heureFin);
        if ($bloc) {
            $this->json([
                'success' => false,
                'message' => 'Ce créneau est occupé par « ' . $bloc['libelle'] . ' » ('
                    . substr($bloc['heure_debut'], 0, 5) . '–' . substr($bloc['heure_fin'], 0, 5) . ').',
            ], 422);
        }

        $this->inscriptions->modifierHoraire($inscriptionId, $dateMatch, $heureDebut, $heureFin);

        // Avertissement de proximité (non bloquant) : deux disciplines du même
        // tireur démarrées à moins de DUREE_CONFLIT_MINUTES l'une de l'autre.
        $autresMatchs = $this->inscriptions->findAutresMatchsTireur(
            $id, $infos['tireur_type'], (int)$infos['tireur_id'], $inscriptionId
        );

        $conflits = $this->detecterConflitsProximite($dateMatch, $heureDebut, $autresMatchs);

        $this->json([
            'success'        => true,
            'message'        => 'Horaire mis à jour.',
            'inscription_id' => $inscriptionId,
            'date_match'     => $dateMatch,
            'heure_debut'    => $heureDebut,
            'heure_fin'      => $heureFin,
            'chevauchement'  => !empty($conflits),
            'avertissement'  => !empty($conflits)
                ? 'Chevauchement d\'horaire avec : ' . implode(', ', $conflits)
                : null,
        ]);
    }

    // ----------------------------------------------------------------
    // POST /challenges/:id/retirer-horaire
    // Retire l'horaire d'une inscription (remet le tireur en attente).
    // ----------------------------------------------------------------
    public function retirerHoraire(string $id): void
    {
        $id = (int) $id;
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->json(['success' => false, 'message' => 'Challenge introuvable.'], 404);
        }
        if ($challenge['statut'] === 'archive') {
            $this->json(['success' => false, 'message' => 'Ce challenge est archivé.'], 403);
        }

        $inscriptionId = (int)($_POST['inscription_id'] ?? 0);
        $infos = $inscriptionId > 0 ? $this->inscriptions->findInfosTireur($inscriptionId) : false;
        if (!$infos || (int)$infos['challenge_id'] !== $id) {
            $this->json(['success' => false, 'message' => 'Inscription introuvable pour ce challenge.'], 404);
        }

        $retire = $this->inscriptions->supprimerHoraire($inscriptionId);
        if (!$retire) {
            $this->json([
                'success' => false,
                'message' => 'Impossible de retirer cet horaire : un score a déjà été saisi pour ce tireur.',
            ], 409);
        }

        $this->json([
            'success'        => true,
            'message'        => 'Horaire retiré, le tireur est de nouveau en attente.',
            'inscription_id' => $inscriptionId,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /challenges/:id/plan-de-tir — grille d'attribution des horaires
    // ----------------------------------------------------------------
    public function planDeTir(string $id): void
    {
        $id = (int) $id;
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->erreur404();
            return;
        }

        $this->render('challenges/plan_de_tir', [
            'titrePage'    => 'Plan de tir — ' . htmlspecialchars($challenge['libelle']) . ' — ' . APP_NAME,
            'challenge'    => $challenge,
            'jours'        => $this->listerJoursChallenge($challenge),
            'grille'       => $this->inscriptions->findGrille($id),
            'blocsHoraires'=> $this->blocsHoraires->findByChallenge($id),
            'dureeConflit' => self::DUREE_CONFLIT_MINUTES,
        ]);
    }

    // ----------------------------------------------------------------
    // POST /challenges/:id/plan-de-tir/blocs — ajoute un bloc horaire libre
    // ----------------------------------------------------------------
    public function ajouterBlocHoraire(string $id): void
    {
        $id = (int) $id;
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->json(['success' => false, 'message' => 'Challenge introuvable.'], 404);
        }
        if ($challenge['statut'] === 'archive') {
            $this->json(['success' => false, 'message' => 'Ce challenge est archivé.'], 403);
        }

        $jour       = trim($_POST['jour']        ?? '');
        $libelle    = trim($_POST['libelle']     ?? '');
        $heureDebut = trim($_POST['heure_debut'] ?? '');
        $heureFin   = trim($_POST['heure_fin']   ?? '');

        $erreurs = [];
        if (!$this->estDateValide($jour) || $jour < $challenge['date_debut'] || $jour > $challenge['date_fin']) {
            $erreurs[] = 'Le jour doit faire partie des dates du challenge.';
        }
        if ($libelle === '') {
            $erreurs[] = 'Le libellé est obligatoire.';
        } elseif (mb_strlen($libelle) > 150) {
            $erreurs[] = 'Le libellé ne peut pas dépasser 150 caractères.';
        }
        if (!$this->estHeureValide($heureDebut)) {
            $erreurs[] = 'L\'heure de début est invalide.';
        }
        if (!$this->estHeureValide($heureFin)) {
            $erreurs[] = 'L\'heure de fin est invalide.';
        }
        if (empty($erreurs) && $heureFin < $heureDebut) {
            $erreurs[] = 'L\'heure de fin doit être égale ou postérieure à l\'heure de début.';
        }

        if (!empty($erreurs)) {
            $this->json(['success' => false, 'erreurs' => $erreurs], 422);
        }

        // Un bloc ne peut pas être posé par-dessus des tireurs déjà programmés
        // sur ce créneau (ils seraient masqués sans être réellement retirés).
        $occupants = $this->inscriptions->findMatchsDansPlage($id, $jour, $heureDebut, $heureFin);
        if (!empty($occupants)) {
            $noms = array_map(
                fn($o) => $o['nom'] . ' (' . $o['discipline_code'] . ' — ' . $o['discipline_fr'] . ')',
                $occupants
            );
            $this->json([
                'success' => false,
                'message' => 'Ce créneau contient déjà des tireurs programmés : ' . implode(', ', $noms) . '. Retirez-les d\'abord.',
            ], 422);
        }

        $blocId = $this->blocsHoraires->creer($id, $jour, $libelle, $heureDebut, $heureFin);

        $this->json([
            'success' => true,
            'message' => 'Bloc horaire ajouté.',
            'bloc'    => [
                'id'          => $blocId,
                'jour'        => $jour,
                'libelle'     => $libelle,
                'heure_debut' => $heureDebut,
                'heure_fin'   => $heureFin,
            ],
        ]);
    }

    // ----------------------------------------------------------------
    // POST /challenges/:id/plan-de-tir/blocs/supprimer
    // ----------------------------------------------------------------
    public function supprimerBlocHoraire(string $id): void
    {
        $id = (int) $id;
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->json(['success' => false, 'message' => 'Challenge introuvable.'], 404);
        }
        if ($challenge['statut'] === 'archive') {
            $this->json(['success' => false, 'message' => 'Ce challenge est archivé.'], 403);
        }

        $blocId = (int)($_POST['bloc_id'] ?? 0);
        if ($blocId <= 0) {
            $this->json(['success' => false, 'message' => 'Bloc invalide.'], 400);
        }

        $this->blocsHoraires->supprimerPourChallenge($blocId, $id);

        $this->json(['success' => true, 'message' => 'Bloc horaire supprimé.']);
    }

    // ----------------------------------------------------------------
    // GET /challenges/:id/resume — tableau de bord des participants
    // ----------------------------------------------------------------
    public function resume(string $id): void
    {
        $id = (int) $id;
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->erreur404();
            return;
        }

        $participants = $this->inscriptions->findResume($id);

        // Construit la liste des disciplines présentes dans ce challenge (pour le filtre)
        $disciplinesFiltres = [];
        foreach ($participants as $p) {
            $code = (int)$p['discipline_code'];
            if (!isset($disciplinesFiltres[$code])) {
                $disciplinesFiltres[$code] = $p['discipline_fr'];
            }
        }
        ksort($disciplinesFiltres);

        $this->render('challenges/resume', [
            'titrePage'          => 'Résumé — ' . htmlspecialchars($challenge['libelle']) . ' — ' . APP_NAME,
            'challenge'          => $challenge,
            'participants'       => $participants,
            'disciplinesFiltres' => $disciplinesFiltres,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /challenges/:id/classements — classements imprimables (PDF)
    // ----------------------------------------------------------------
    public function classements(string $id): void
    {
        $id = (int) $id;
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->erreur404();
            return;
        }

        $disciplineCode = (isset($_GET['discipline']) && $_GET['discipline'] !== '')
            ? (int)$_GET['discipline']
            : null;

        $rows = $this->inscriptions->findClassements($id, $disciplineCode);

        // Grouper par discipline, en separant les tireurs notes des DEFECT (pas de score).
        $groupes = [];
        foreach ($rows as $row) {
            $code = (int)$row['discipline_code'];
            if (!isset($groupes[$code])) {
                $groupes[$code] = [
                    'libelle_fr' => $row['discipline_fr'],
                    'libelle_en' => $row['discipline_en'],
                    'tireurs'    => [],
                    'defects'    => [],
                ];
            }
            if ($row['total'] === null) {
                $groupes[$code]['defects'][] = $row;
            } else {
                $groupes[$code]['tireurs'][] = $row;
            }
        }

        foreach ($groupes as &$groupe) {
            $this->calculerRangsEtMedailles($groupe['tireurs']);
            $rangSuivant = count($groupe['tireurs']) + 1;
            foreach ($groupe['defects'] as &$defect) {
                $defect['rang'] = $rangSuivant++;
            }
            unset($defect);
        }
        unset($groupe);

        $this->render('challenges/classements', [
            'titrePage'      => 'Classements — ' . htmlspecialchars($challenge['libelle']),
            'challenge'      => $challenge,
            'groupes'        => $groupes,
            'disciplineCode' => $disciplineCode,
        ], 'print');
    }

    // ----------------------------------------------------------------
    // GET /challenges/:id/classements-combines — classements combines (aggregates)
    // ----------------------------------------------------------------
    public function classementsCombines(string $id): void
    {
        $id = (int) $id;
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->erreur404();
            return;
        }

        $groupes = [];
        foreach (self::COMBINES as $slug => $combine) {
            $tireurs = $this->inscriptions->findClassementCombine($id, $combine['codes']);
            if (empty($tireurs)) {
                continue;
            }
            $this->calculerRangsEtMedailles($tireurs);
            $groupes[$slug] = [
                'libelle_fr'   => $combine['libelle_fr'],
                'libelle_en'   => $combine['libelle_en'],
                'codes'        => $combine['codes'],
                'nb_epreuves'  => count($combine['codes']),
                'tireurs'      => $tireurs,
            ];
        }

        $this->render('challenges/classements_combines', [
            'titrePage' => 'Classements combinés — ' . htmlspecialchars($challenge['libelle']),
            'challenge' => $challenge,
            'groupes'   => $groupes,
        ], 'print');
    }

    // ----------------------------------------------------------------
    // GET /challenges/:id/imprimer — fiche imprimable des inscrits
    // ----------------------------------------------------------------
    public function imprimer(string $id): void
    {
        $id = (int) $id;
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
            // Meme style graphique que la liste des categories (fiche paysage)
            'ficheCss'  => 'fiche-liste-disciplines',
        ], 'print_fiche');
    }

    // ----------------------------------------------------------------
    // GET /challenges/:id/planning — plan de tir imprimable (PDF)
    // ----------------------------------------------------------------
    public function planning(string $id): void
    {
        $id = (int) $id;
        $challenge = $this->model->findById($id);
        if (!$challenge) {
            $this->erreur404();
            return;
        }

        $this->render('challenges/print_planning', [
            'titrePage'     => 'Plan de tir — ' . htmlspecialchars($challenge['libelle']),
            'challenge'     => $challenge,
            'jours'         => $this->listerJoursChallenge($challenge),
            'grille'        => $this->inscriptions->findGrille($id),
            'blocsHoraires' => $this->blocsHoraires->findByChallenge($id),
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

    /**
     * Calcule les rangs, médailles et ex-æquos d'une liste de tireurs déjà triée
     * par les règles de classement (total, mouflons, dindons, cochons, poulets).
     * Modifie le tableau passé par référence.
     */
    private function calculerRangsEtMedailles(array &$tireurs): void
    {
        // Passe 1 : assigner les rangs (ex-æquo = même rang, rang suivant = position réelle)
        foreach ($tireurs as $i => &$t) {
            if ($i === 0) {
                $t['rang'] = 1;
            } else {
                $prev      = $tireurs[$i - 1];
                $memeScore = (int)$t['total']    === (int)$prev['total']
                          && (int)$t['mouflons'] === (int)$prev['mouflons']
                          && (int)$t['dindons']  === (int)$prev['dindons']
                          && (int)$t['cochons']  === (int)$prev['cochons']
                          && (int)$t['poulets']  === (int)$prev['poulets'];
                $t['rang'] = $memeScore ? $prev['rang'] : $i + 1;
            }
            $t['medaille'] = match ($t['rang']) {
                1       => 'or',
                2       => 'argent',
                3       => 'bronze',
                default => null,
            };
        }
        unset($t);

        // Passe 2 : marquer les ex-æquos (plusieurs tireurs au même rang)
        $compteRangs = array_count_values(array_column($tireurs, 'rang'));
        foreach ($tireurs as &$t) {
            $t['exaequo'] = $compteRangs[$t['rang']] > 1;
        }
        unset($t);
    }

    /**
     * Détecte les créneaux trop proches (avertissement non bloquant) : même
     * date et écart entre heures de début inférieur à DUREE_CONFLIT_MINUTES,
     * quelle que soit l'heure de fin réelle du match.
     */
    private function detecterConflitsProximite(string $dateMatch, string $heureDebut, array $autresMatchs): array
    {
        $conflits    = [];
        $debutMinutes = $this->heureEnMinutes($heureDebut);

        foreach ($autresMatchs as $m) {
            if ($m['date_match'] !== $dateMatch) {
                continue;
            }
            $ecart = abs($debutMinutes - $this->heureEnMinutes($m['heure_debut']));
            if ($ecart < self::DUREE_CONFLIT_MINUTES) {
                $conflits[] = $m['discipline_code'] . ' — ' . $m['discipline_fr']
                    . ' (' . substr($m['heure_debut'], 0, 5) . '–' . substr($m['heure_fin'], 0, 5) . ')';
            }
        }

        return $conflits;
    }

    private function heureEnMinutes(string $heure): int
    {
        [$h, $m] = array_map('intval', explode(':', $heure));
        return $h * 60 + $m;
    }

    /**
     * Retourne la liste des jours (Y-m-d) couverts par un challenge.
     */
    private function listerJoursChallenge(array $challenge): array
    {
        $jours   = [];
        $curseur = strtotime($challenge['date_debut']);
        $fin     = strtotime($challenge['date_fin']);
        while ($curseur <= $fin) {
            $jours[] = date('Y-m-d', $curseur);
            $curseur = strtotime('+1 day', $curseur);
        }
        return $jours;
    }

    /**
     * Regroupe les disciplines par famille (mêmes bornes que le filtre de la liste des inscrits).
     */
    private function grouperDisciplinesParFamille(array $disciplines): array
    {
        $familles = [
            'Gros Calibre'  => [400, 403],
            'Petit Calibre' => [404, 407],
            'Field'         => [408, 409],
            'Carabine PC'   => [410, 411],
            'Carabine GC'   => [412, 413],
        ];

        $groupes = array_fill_keys(array_keys($familles), []);

        foreach ($disciplines as $d) {
            $code = (int) $d['code'];
            foreach ($familles as $nom => [$min, $max]) {
                if ($code >= $min && $code <= $max) {
                    $groupes[$nom][] = $d;
                    break;
                }
            }
        }

        return array_filter($groupes);
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

    // ----------------------------------------------------------------
    // GET /challenges/historique
    // ----------------------------------------------------------------
    public function historique(): void
    {
        $challenges = $this->model->findTous();

        $this->render('challenges/historique', [
            'titrePage'  => 'Historique des challenges — ' . APP_NAME,
            'challenges' => $challenges,
        ]);
    }

    private function estDateValide(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function estHeureValide(string $heure): bool
    {
        $h = DateTime::createFromFormat('H:i', $heure);
        return $h && $h->format('H:i') === $heure;
    }
}
