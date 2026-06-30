<?php
    $debut   = date('d/m/Y', strtotime($challenge['date_debut']));
    $fin     = date('d/m/Y', strtotime($challenge['date_fin']));
    $archive = ($challenge['statut'] === 'archive');

    $nbTotal  = count($participants);
    $nbScores = count(array_filter($participants, fn($p) => $p['score_id'] !== null));
?>

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

<!-- Compteurs -->
<div class="resume-compteurs mb-3 d-flex gap-3 flex-wrap">
    <div class="resume-compteur-bloc">
        <span class="resume-compteur-valeur" id="cpt-affiches"><?= $nbTotal ?></span>
        <span class="resume-compteur-label">affichés</span>
    </div>
    <div class="resume-compteur-bloc resume-compteur-score">
        <span class="resume-compteur-valeur"><?= $nbScores ?></span>
        <span class="resume-compteur-label">avec score</span>
    </div>
    <div class="resume-compteur-bloc resume-compteur-attente">
        <span class="resume-compteur-valeur"><?= $nbTotal - $nbScores ?></span>
        <span class="resume-compteur-label">sans score</span>
    </div>
</div>

<!-- Barre de filtres -->
<div class="card resume-filtres-card mb-3">
    <div class="card-body py-2 d-flex flex-wrap align-items-center gap-3">

        <!-- Filtre tiré / non tiré -->
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

        <!-- Filtre discipline -->
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

        <!-- Légende couleurs -->
        <div class="d-flex align-items-center gap-2 ms-auto">
            <span class="resume-legende-puce resume-puce-score"></span>
            <span class="resume-legende-texte">Score enregistré</span>
            <span class="resume-legende-puce resume-puce-attente ms-2"></span>
            <span class="resume-legende-texte">En attente</span>
        </div>

    </div>
</div>

<!-- Tableau -->
<div class="card liste-card">
    <div class="card-body p-0">
        <?php if (empty($participants)): ?>
            <p class="liste-vide-sm">Aucun participant inscrit.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover resume-table mb-0"
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
                        $aScore   = $p['score_id'] !== null;
                        $horaire  = '';
                        if ($p['date_match'] && $p['heure_debut']) {
                            $horaire = date('d/m', strtotime($p['date_match']))
                                     . ' ' . substr($p['heure_debut'], 0, 5);
                        }
                        $total    = $aScore ? (int)$p['total'] : '';
                        $detScore = $aScore
                            ? $p['poulets'] . '/' . $p['cochons'] . '/' . $p['dindons'] . '/' . $p['mouflons']
                            : '';
                    ?>
                    <tr class="ligne-participant <?= $aScore ? 'ligne-score' : 'ligne-attente' ?>"
                        data-score="<?= $aScore ? '1' : '0' ?>"
                        data-discipline-code="<?= (int)$p['discipline_code'] ?>">
                        <td><?= htmlspecialchars($p['nom']) ?></td>
                        <td><?= htmlspecialchars($p['prenom']) ?></td>
                        <td><?= $p['club'] ? htmlspecialchars($p['club']) : '<span class="text-muted">—</span>' ?></td>
                        <td>
                            <span class="discipline-code"><?= (int)$p['discipline_code'] ?></span>
                            <?= htmlspecialchars($p['discipline_fr']) ?>
                        </td>
                        <td class="resume-col-horaire">
                            <?= htmlspecialchars($horaire) ?: '<span class="text-muted">—</span>' ?>
                        </td>
                        <td class="resume-col-score">
                            <?php if ($aScore): ?>
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

<script>
    const CHALLENGE_ID = <?= (int)$challenge['id'] ?>;
</script>
<script src="<?= APP_URL ?>/js/challenge-resume.js"></script>
