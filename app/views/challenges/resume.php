<?php
    $debut   = date('d/m/Y', strtotime($challenge['date_debut']));
    $fin     = date('d/m/Y', strtotime($challenge['date_fin']));
    $archive = ($challenge['statut'] === 'archive');

    $nbTotal  = count($participants);
    $nbScores = count(array_filter($participants, fn($p) => $p['score_id'] !== null));
?>

<div class="membres-row">

<div id="resume-alerte" class="membres-alerte" role="alert" aria-live="polite" hidden></div>

<div class="page-header mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><?= htmlspecialchars($challenge['libelle']) ?></h1>
        <p class="text-muted mb-0 small">
            <?= $debut === $fin ? $debut : 'Du ' . $debut . ' au ' . $fin ?>
            <?php if ($archive): ?>
                <span class="badge bg-secondary ms-2">Archivé</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= APP_URL ?>/challenges/<?= (int)$challenge['id'] ?>"
           class="btn btn-outline-secondary btn-sm"
           aria-label="Aller à la gestion des inscriptions">
            ← Inscriptions
        </a>
        <a href="<?= APP_URL ?>/"
           class="btn btn-outline-secondary btn-sm"
           aria-label="Retour à l'accueil">
            Accueil
        </a>
    </div>
</div>

<?php if (!$archive): ?>
<p class="text-muted small mb-3">Cliquez sur un participant pour saisir ou modifier son score.</p>
<?php endif; ?>

<!-- Compteurs -->
<div class="resume-compteurs mb-3 d-flex gap-3 flex-wrap">
    <div class="resume-compteur-bloc">
        <span class="resume-compteur-valeur" id="cpt-affiches"><?= $nbTotal ?></span>
        <span class="resume-compteur-label">affichés</span>
    </div>
    <div class="resume-compteur-bloc resume-compteur-score">
        <span class="resume-compteur-valeur" id="cpt-scores"><?= $nbScores ?></span>
        <span class="resume-compteur-label">avec score</span>
    </div>
    <div class="resume-compteur-bloc resume-compteur-attente">
        <span class="resume-compteur-valeur" id="cpt-attente"><?= $nbTotal - $nbScores ?></span>
        <span class="resume-compteur-label">sans score</span>
    </div>
</div>

<!-- Barre de filtres -->
<div class="card resume-filtres-card mb-3">
    <div class="card-body py-2 d-flex flex-wrap align-items-center gap-3">

        <div class="d-flex align-items-center gap-2">
            <span class="resume-filtre-label">Afficher :</span>
            <div class="btn-group btn-group-sm" role="group" aria-label="Filtrer par statut de tir">
                <input type="radio" class="btn-check" name="filtre-tir" id="filtre-tous"    value="tous"    checked>
                <label class="btn btn-outline-secondary" for="filtre-tous">Tous</label>

                <input type="radio" class="btn-check" name="filtre-tir" id="filtre-tires"   value="tires">
                <label class="btn btn-outline-success"   for="filtre-tires">A tiré</label>

                <input type="radio" class="btn-check" name="filtre-tir" id="filtre-attente" value="attente">
                <label class="btn btn-outline-warning"  for="filtre-attente">N'a pas tiré</label>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <label for="filtre-discipline" class="resume-filtre-label">Discipline :</label>
            <select class="form-select form-select-sm resume-select-discipline"
                    id="filtre-discipline"
                    aria-label="Filtrer par discipline">
                <option value="">Toutes</option>
                <?php foreach ($disciplinesFiltres as $code => $libelle): ?>
                <option value="<?= $code ?>">
                    <?= $code ?> — <?= htmlspecialchars($libelle) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Légende -->
        <div class="d-flex align-items-center gap-2">
            <span class="resume-legende-puce resume-puce-score"></span>
            <span class="resume-legende-texte">Score enregistré</span>
            <span class="resume-legende-puce resume-puce-attente ms-2"></span>
            <span class="resume-legende-texte">En attente</span>
        </div>

        <!-- Boutons classements -->
        <div class="d-flex align-items-center gap-2 ms-auto">
            <a href="<?= APP_URL ?>/challenges/<?= (int)$challenge['id'] ?>/plan-de-tir"
               class="btn btn-sm btn-outline-primary"
               aria-label="Attribuer les horaires du plan de tir">
                Attribuer les horaires
            </a>
            <a href="<?= APP_URL ?>/challenges/<?= (int)$challenge['id'] ?>/classements"
               target="_blank"
               class="btn btn-sm btn-outline-primary"
               aria-label="Générer le PDF de tous les classements">
                Classement
            </a>
            <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    id="btn-classement-filtre"
                    disabled
                    hidden
                    title="Sélectionnez d'abord une discipline dans le filtre"
                    aria-label="Générer le PDF des classements selon le filtre discipline actif">
                Classement avec filtre
            </button>
        </div>

    </div>
</div>

<!-- Tableau -->
<div class="card liste-card membres-liste-card">
    <div class="card-body p-0">
        <?php if (empty($participants)): ?>
            <p class="liste-vide-sm">Aucun participant inscrit.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover membres-table resume-table mb-0"
                   id="table-resume"
                   aria-label="Participants au challenge">
                <thead>
                    <tr>
                        <th scope="col" class="col-sortable" data-col="0" aria-sort="none">
                            Nom <span class="sort-icone" aria-hidden="true"></span>
                        </th>
                        <th scope="col" class="col-sortable" data-col="1" aria-sort="none">
                            Prénom <span class="sort-icone" aria-hidden="true"></span>
                        </th>
                        <th scope="col" class="col-sortable" data-col="2" aria-sort="none">
                            Club <span class="sort-icone" aria-hidden="true"></span>
                        </th>
                        <th scope="col" class="col-sortable" data-col="3" aria-sort="none">
                            Discipline <span class="sort-icone" aria-hidden="true"></span>
                        </th>
                        <th scope="col" class="col-sortable" data-col="4" aria-sort="none">
                            Horaire <span class="sort-icone" aria-hidden="true"></span>
                        </th>
                        <th scope="col" class="col-sortable" data-col="5" aria-sort="none">
                            Score <span class="sort-icone" aria-hidden="true"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $p):
                        $aScore  = $p['score_id'] !== null;
                        $horaire = '';
                        if ($p['date_match'] && $p['heure_debut']) {
                            $horaire = date('d/m', strtotime($p['date_match']))
                                     . ' ' . substr($p['heure_debut'], 0, 5);
                        }
                        $total   = $aScore ? (int)$p['total'] : '';
                        $detScore = $aScore
                            ? $p['poulets'] . '/' . $p['cochons'] . '/' . $p['dindons'] . '/' . $p['mouflons']
                            : '';
                    ?>
                    <tr class="ligne-participant <?= $aScore ? 'ligne-score' : 'ligne-attente' ?> <?= $archive ? '' : 'ligne-cliquable' ?>"
                        data-inscription-id="<?= (int)$p['inscription_id'] ?>"
                        data-match-id="<?= $p['date_match'] ? (int)($p['score_id'] ?? 0) : '' ?>"
                        data-score="<?= $aScore ? '1' : '0' ?>"
                        data-discipline-code="<?= (int)$p['discipline_code'] ?>"
                        data-discipline-fr="<?= htmlspecialchars($p['discipline_fr']) ?>"
                        data-nom="<?= htmlspecialchars($p['nom']) ?>"
                        data-prenom="<?= htmlspecialchars($p['prenom']) ?>"
                        data-club="<?= htmlspecialchars($p['club'] ?? '') ?>"
                        data-tireur-type="<?= htmlspecialchars($p['tireur_type']) ?>"
                        data-tireur-id="<?= (int)$p['tireur_id'] ?>"
                        data-date-match="<?= htmlspecialchars($p['date_match'] ?? '') ?>"
                        data-heure-debut="<?= htmlspecialchars($p['heure_debut'] ? substr($p['heure_debut'], 0, 5) : '') ?>"
                        data-heure-fin="<?= htmlspecialchars($p['heure_fin'] ? substr($p['heure_fin'], 0, 5) : '') ?>"
                        data-poulets="<?= $aScore ? (int)$p['poulets'] : '' ?>"
                        data-cochons="<?= $aScore ? (int)$p['cochons'] : '' ?>"
                        data-dindons="<?= $aScore ? (int)$p['dindons'] : '' ?>"
                        data-mouflons="<?= $aScore ? (int)$p['mouflons'] : '' ?>"
                        data-abandon="<?= $aScore ? (int)$p['abandon'] : 0 ?>"
                        <?= $archive ? '' : 'role="button" tabindex="0"' ?>
                        aria-label="<?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?> — <?= htmlspecialchars($p['discipline_fr']) ?>">
                        <td><?= htmlspecialchars($p['nom']) ?></td>
                        <td><?= htmlspecialchars($p['prenom']) ?></td>
                        <td><?= $p['tireur_type'] === 'membre' ? 'Javeline' : ($p['club'] ? htmlspecialchars($p['club']) : '<span class="text-muted">—</span>') ?></td>
                        <td>
                            <span class="discipline-code"><?= (int)$p['discipline_code'] ?></span>
                            <?= htmlspecialchars($p['discipline_fr']) ?>
                        </td>
                        <td class="resume-col-horaire">
                            <span class="resume-horaire-texte"><?= htmlspecialchars($horaire) ?: '<span class="text-muted">—</span>' ?></span>
                        </td>
                        <td class="resume-col-score">
                            <?php if ($aScore && (int)$p['abandon'] === 1): ?>
                                <span class="resume-abandon">Abandon</span>
                            <?php elseif ($aScore): ?>
                                <span class="resume-total fw-bold"><?= $total ?></span>
                                <span class="resume-detail text-muted">(<?= $detScore ?>)</span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

</div>

<!-- ===================================================
     Modale saisie des scores
     =================================================== -->
<div class="modal fade"
     id="modal-score"
     tabindex="-1"
     aria-labelledby="modal-score-titre"
     aria-modal="true"
     role="dialog"
     data-bs-backdrop="static"
     data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="modal-title fs-5" id="modal-score-titre">Saisie des scores</h3>
                <button type="button"
                        class="btn-close"
                        id="btn-modal-fermer"
                        aria-label="Fermer la fenêtre"></button>
            </div>

            <div class="modal-body">

                <!-- Infos tireur -->
                <div class="score-tireur-bloc mb-4">
                    <div class="score-tireur-nom" id="score-nom"></div>
                    <div class="score-tireur-detail" id="score-detail"></div>
                    <div class="score-tireur-discipline mt-1" id="score-discipline"></div>
                </div>

                <!-- État abandon -->
                <p id="score-abandon-info" class="score-abandon-info mb-3" hidden>
                    Tireur déclaré en abandon : les scores sont remis à zéro et la saisie est désactivée.
                </p>

                <!-- Grille des 4 silhouettes -->
                <div class="score-saisie-grille mb-4">
                    <?php
                    $animaux = [
                        'poulets'  => ['Poulets', 'poulet.png'],
                        'cochons'  => ['Cochons', 'cochon.png'],
                        'dindons'  => ['Dindons', 'dindon.png'],
                        'mouflons' => ['Mouflons', 'mouflon.png'],
                    ];
                    foreach ($animaux as $champ => [$libelle, $image]):
                    ?>
                    <div class="score-saisie-animal">
                        <div class="score-animal-nom"><?= $libelle ?></div>
                        <img src="<?= APP_URL ?>/public/img/<?= $image ?>"
                             alt="Silhouette <?= $libelle ?>"
                             class="score-animal-picto">
                        <input type="number"
                               class="form-control score-input"
                               id="score-<?= $champ ?>"
                               name="<?= $champ ?>"
                               min="0"
                               step="1"
                               placeholder="0"
                               aria-label="Score <?= $libelle ?>">
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Total -->
                <div class="score-total-bloc">
                    <span class="score-total-label">Total :</span>
                    <span class="score-total-valeur" id="score-total">0</span>
                </div>

            </div>

            <div class="modal-footer d-flex justify-content-between">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-danger" id="btn-score-annuler">
                        Annuler la saisie
                    </button>
                    <button type="button"
                            class="btn btn-outline-warning"
                            id="btn-score-abandon"
                            aria-label="Déclarer ou annuler l'abandon du tireur">
                        Abandon
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" id="btn-score-fermer">
                        Fermer
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-score-enregistrer">
                        Enregistrer
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ===================================================
     Modale édition de l'horaire du match
     =================================================== -->
<div class="modal fade"
     id="modal-horaire"
     tabindex="-1"
     aria-labelledby="modal-horaire-titre"
     aria-modal="true"
     role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="modal-title fs-5" id="modal-horaire-titre">Modifier l'horaire</h3>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fermer la fenêtre"></button>
            </div>

            <div class="modal-body">

                <div class="score-tireur-bloc mb-3">
                    <div class="score-tireur-nom" id="horaire-nom"></div>
                    <div class="score-tireur-discipline mt-1" id="horaire-discipline"></div>
                </div>

                <!-- Avertissement chevauchement (non bloquant) -->
                <div id="horaire-avertissement" class="alert alert-warning py-2 mb-3" role="alert" hidden></div>

                <div class="row g-2">
                    <div class="col-sm-4 mb-2">
                        <label for="horaire-date" class="form-label">Date <span aria-hidden="true">*</span></label>
                        <input type="date" class="form-control" id="horaire-date" aria-required="true">
                    </div>
                    <div class="col-sm-4 mb-2">
                        <label for="horaire-debut" class="form-label">Heure de début <span aria-hidden="true">*</span></label>
                        <input type="time" class="form-control" id="horaire-debut" aria-required="true">
                    </div>
                    <div class="col-sm-4 mb-2">
                        <label for="horaire-fin" class="form-label">Heure de fin <span aria-hidden="true">*</span></label>
                        <input type="time" class="form-control" id="horaire-fin" aria-required="true">
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <button type="button" class="btn btn-primary" id="btn-horaire-enregistrer">
                    Enregistrer
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    const CHALLENGE_ID      = <?= (int)$challenge['id'] ?>;
    const CHALLENGE_ARCHIVE = <?= $archive ? 'true' : 'false' ?>;
</script>
<script src="<?= APP_URL ?>/public/js/challenge-resume.js"></script>
