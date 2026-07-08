<?php
$debut   = date('d/m/Y', strtotime($challenge['date_debut']));
$fin     = date('d/m/Y', strtotime($challenge['date_fin']));
$archive = ($challenge['statut'] === 'archive');

// Disciplines effectivement inscrites au challenge (colonnes de la grille).
$disciplines = [];
foreach ($grille as $r) {
    $code = (int) $r['discipline_code'];
    if (!isset($disciplines[$code])) {
        $disciplines[$code] = ['fr' => $r['discipline_fr'], 'en' => $r['discipline_en']];
    }
}
ksort($disciplines);

// Grille des matchs planifies : [jour][code][heure_debut] = ligne.
// Pool des tireurs en attente d'horaire, par discipline (tous jours confondus).
$matchsParJour          = [];
$enAttenteParDiscipline = array_fill_keys(array_keys($disciplines), []);

foreach ($grille as $r) {
    $code = (int) $r['discipline_code'];
    if ($r['date_match'] && $r['heure_debut']) {
        $heure = substr($r['heure_debut'], 0, 5);
        $matchsParJour[$r['date_match']][$code][$heure] = $r;
    } else {
        $enAttenteParDiscipline[$code][] = $r;
    }
}

// Blocs horaires libres, par jour.
$blocsParJour = [];
foreach ($blocsHoraires as $b) {
    $blocsParJour[$b['jour']][] = $b;
}

// Creneaux fixes de 10 minutes, 09h00 -> 18h30.
$creneaux = [];
$curseur  = strtotime('09:00');
$limite   = strtotime('18:30');
while ($curseur <= $limite) {
    $creneaux[] = date('H:i', $curseur);
    $curseur += 600;
}

// Compteurs : matchs programmes vs tireurs en attente d'horaire.
$nbProgrammes = 0;
$nbEnAttente  = 0;
foreach ($grille as $r) {
    if ($r['date_match'] && $r['heure_debut']) {
        $nbProgrammes++;
    } else {
        $nbEnAttente++;
    }
}

// Jours de la semaine abreges pour les onglets (libelle court + date).
$joursCourtsFr = [0 => 'Dimanche', 1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'];

// Donnees pour le JS : popovers d'assignation et verification live des conflits de proximite.
$grilleJs = array_map(static function (array $r): array {
    return [
        'inscriptionId'  => (int) $r['inscription_id'],
        'tireurType'     => $r['tireur_type'],
        'tireurId'       => (int) $r['tireur_id'],
        'disciplineCode' => (int) $r['discipline_code'],
        'disciplineFr'   => $r['discipline_fr'],
        'nom'            => $r['nom'],
        'prenom'         => $r['prenom'],
        'coach'          => $r['coach'] ?: ($r['tireur_type'] === 'membre' ? 'Javeline' : '???'),
        'dateMatch'      => $r['date_match'],
        'heureDebut'     => $r['heure_debut'] ? substr($r['heure_debut'], 0, 5) : null,
        'heureFin'       => $r['heure_fin'] ? substr($r['heure_fin'], 0, 5) : null,
        'scoreId'        => $r['score_id'] !== null ? (int) $r['score_id'] : null,
    ];
}, $grille);
?>

<div id="plan-alerte" class="membres-alerte" role="alert" aria-live="polite" hidden></div>

<div class="page-header mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-0">Plan de tir — <?= htmlspecialchars($challenge['libelle']) ?></h1>
        <p class="text-muted mb-0 small">
            <?= $debut === $fin ? $debut : 'Du ' . $debut . ' au ' . $fin ?>
            <?php if ($archive): ?>
                <span class="badge bg-secondary ms-2">Archivé</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="plan-stats d-flex">
            <div class="plan-stat">
                <span class="plan-stat-valeur" id="plan-stat-programmes"><?= $nbProgrammes ?></span>
                <span class="plan-stat-label">Programmés</span>
            </div>
            <div class="plan-stat">
                <span class="plan-stat-valeur" id="plan-stat-attente"><?= $nbEnAttente ?></span>
                <span class="plan-stat-label">En attente</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= APP_URL ?>/challenges/<?= (int) $challenge['id'] ?>/resume"
               class="btn btn-outline-secondary btn-sm"
               aria-label="Retour au résumé du challenge">
                ← Résumé
            </a>
            <a href="<?= APP_URL ?>/challenges/<?= (int) $challenge['id'] ?>/planning"
               target="_blank"
               class="btn btn-sm btn-outline-primary"
               aria-label="Imprimer le plan de tir">
                Imprimer le plan de tir
            </a>
        </div>
    </div>
</div>

<?php if ($archive): ?>
    <p class="text-muted small mb-3">Ce challenge est archivé, le plan de tir n'est plus modifiable.</p>
<?php elseif (empty($disciplines)): ?>
    <p class="text-muted small mb-3">Aucun tireur inscrit pour le moment : rien à planifier.</p>
<?php else: ?>
    <p class="text-muted small mb-3">
        Cliquez sur une case libre pour assigner un tireur en attente, ou sur une case remplie pour la retirer.
    </p>

    <!-- Barre de contrôles : jours, légende, ajout de bloc — sur une seule ligne -->
    <div class="plan-controles mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3">

        <div class="plan-jours-tabs" role="tablist" aria-label="Choix du jour">
            <?php $premierJour = true; ?>
            <?php foreach ($jours as $jour): ?>
            <button class="plan-jour-tab <?= $premierJour ? 'active' : '' ?>"
                    type="button"
                    role="tab"
                    data-jour="<?= htmlspecialchars($jour) ?>"
                    aria-selected="<?= $premierJour ? 'true' : 'false' ?>">
                <?= htmlspecialchars($joursCourtsFr[(int) date('w', strtotime($jour))]) ?>
                <span class="plan-jour-tab-date"><?= date('d/m', strtotime($jour)) ?></span>
            </button>
            <?php $premierJour = false; ?>
            <?php endforeach; ?>
        </div>

        <div class="plan-legende d-flex align-items-center gap-3 flex-wrap">
            <span><i class="plan-legende-puce plan-puce-libre" aria-hidden="true"></i>Créneau libre</span>
            <span><i class="plan-legende-puce plan-puce-programme" aria-hidden="true"></i>Tireur programmé</span>
            <span><i class="plan-legende-puce plan-puce-conflit" aria-hidden="true"></i>Créneaux proches</span>
            <span><i class="plan-legende-puce plan-puce-bloc" aria-hidden="true"></i>Bloc du challenge</span>
        </div>

        <?php if (!$archive): ?>
        <button type="button" class="btn btn-sm btn-outline-secondary btn-ajouter-bloc">
            + Ajouter un bloc horaire
        </button>
        <?php endif; ?>

    </div>

    <?php $premierJour = true; ?>
    <?php foreach ($jours as $jour): ?>
    <div class="plan-jour-panneau <?= $premierJour ? '' : 'd-none' ?>" data-jour-panneau="<?= htmlspecialchars($jour) ?>">

        <div class="card plan-grille-card mb-4">
            <div class="table-responsive plan-grille-scroll">
                <table class="table table-sm table-bordered plan-grille-table mb-0">
                    <thead>
                        <tr>
                            <th class="plan-col-horaire">Horaires</th>
                            <?php foreach ($disciplines as $code => $lib): ?>
                            <th>
                                <span class="plan-discipline-code"><?= $code ?></span> — <?= htmlspecialchars($lib['fr']) ?>
                                <span class="plan-discipline-en"><?= htmlspecialchars($lib['en']) ?></span>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $blocsJour = $blocsParJour[$jour] ?? [];
                        // Index heure -> bloc couvrant ce creneau, pour fusionner les lignes (rowspan).
                        $blocParHeure = [];
                        foreach ($blocsJour as $bloc) {
                            $db = substr($bloc['heure_debut'], 0, 5);
                            $fb = substr($bloc['heure_fin'], 0, 5);
                            foreach ($creneaux as $h) {
                                if ($h >= $db && $h <= $fb) {
                                    $blocParHeure[$h] = $bloc;
                                }
                            }
                        }
                        $estPremierJourChallenge = $jour === $challenge['date_debut'];
                        ?>
                        <?php foreach ($creneaux as $heure):
                            $estOuvertureAuto = $estPremierJourChallenge && $heure >= '09:00' && $heure <= '09:50';
                            $bloc             = $estOuvertureAuto ? null : ($blocParHeure[$heure] ?? null);
                            $debutBloc        = $bloc ? substr($bloc['heure_debut'], 0, 5) : null;
                            $finBloc          = $bloc ? substr($bloc['heure_fin'], 0, 5) : null;
                            $spanBloc         = $bloc
                                ? count(array_filter($creneaux, fn($h) => $h >= $debutBloc && $h <= $finBloc))
                                : 0;
                        ?>
                        <tr>
                            <td class="plan-col-horaire"><?= str_replace(':', ' h ', $heure) ?></td>
                            <?php if ($estOuvertureAuto): ?>
                                <?php if ($heure === '09:00'): ?>
                                <td class="plan-case-bloc plan-case-auto" colspan="<?= count($disciplines) ?>" rowspan="6">
                                    Ouverture et préparation du stand
                                </td>
                                <?php endif; ?>
                            <?php elseif ($bloc): ?>
                                <?php if ($heure === $debutBloc): ?>
                                <td class="plan-case-bloc" colspan="<?= count($disciplines) ?>" rowspan="<?= $spanBloc ?>">
                                    <div class="plan-bloc-contenu">
                                        <span><?= htmlspecialchars($bloc['libelle']) ?>
                                            <span class="plan-bloc-heures"><?= str_replace(':', 'h', $debutBloc) ?><?= $finBloc !== $debutBloc ? ' → ' . str_replace(':', 'h', $finBloc) : '' ?></span>
                                        </span>
                                        <?php if (!$archive): ?>
                                        <button type="button" class="btn-retirer-bloc" data-bloc-id="<?= (int) $bloc['id'] ?>">Retirer</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php foreach ($disciplines as $code => $lib):
                                    $match = $matchsParJour[$jour][$code][$heure] ?? null;
                                ?>
                                <td class="plan-case">
                                    <button type="button"
                                            class="plan-case-btn <?= $match ? 'plan-case-remplie' : '' ?>"
                                            data-jour="<?= htmlspecialchars($jour) ?>"
                                            data-discipline="<?= $code ?>"
                                            data-heure="<?= $heure ?>"
                                            <?= $archive ? 'disabled' : '' ?>>
                                        <?php if ($match):
                                            $coach = $match['coach'] ?: ($match['tireur_type'] === 'membre' ? 'Javeline' : '???');
                                        ?>
                                        <span class="plan-case-ligne">
                                            <span class="plan-case-nom"><?= htmlspecialchars($match['nom']) ?></span>
                                            / <span class="plan-case-coach"><?= htmlspecialchars($coach) ?></span>
                                        </span>
                                        <?php endif; ?>
                                    </button>
                                </td>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <?php $premierJour = false; ?>
    <?php endforeach; ?>

    <!-- Popover d'assignation / suppression -->
    <div class="plan-backdrop" id="plan-backdrop" hidden></div>
    <div class="plan-popover" id="plan-popover" role="dialog" aria-modal="true" hidden></div>

    <!-- Formulaire d'ajout de bloc horaire -->
    <div class="modal fade" id="modal-bloc" tabindex="-1" aria-labelledby="modal-bloc-titre" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="modal-bloc-titre">Ajouter un bloc horaire</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer la fenêtre"></button>
                </div>
                <div class="modal-body">
                    <div id="bloc-erreur" class="alert alert-danger py-2 mb-3" role="alert" hidden></div>
                    <div class="mb-3">
                        <label for="bloc-libelle" class="form-label">Libellé <span aria-hidden="true">*</span></label>
                        <input type="text" class="form-control" id="bloc-libelle" maxlength="150" placeholder="ex : Pause déjeuner" aria-required="true">
                    </div>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <label for="bloc-debut" class="form-label">Heure de début <span aria-hidden="true">*</span></label>
                            <input type="time" class="form-control" id="bloc-debut" step="600" aria-required="true">
                        </div>
                        <div class="col-sm-6">
                            <label for="bloc-fin" class="form-label">Heure de fin <span aria-hidden="true">*</span></label>
                            <input type="time" class="form-control" id="bloc-fin" step="600" aria-required="true">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="btn-bloc-enregistrer">Ajouter</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const CHALLENGE_ID      = <?= (int) $challenge['id'] ?>;
        const DUREE_CONFLIT_MIN = <?= (int) $dureeConflit ?>;
        const GRILLE_INITIALE   = <?= json_encode($grilleJs, JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="<?= APP_URL ?>/public/js/challenge-plan-de-tir.js?v=<?= filemtime(APP_ROOT . '/public/js/challenge-plan-de-tir.js') ?>"></script>
<?php endif; ?>
