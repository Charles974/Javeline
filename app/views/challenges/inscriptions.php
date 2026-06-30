<?php
    $debut = date('d/m/Y', strtotime($challenge['date_debut']));
    $fin   = date('d/m/Y', strtotime($challenge['date_fin']));
    $today = date('Y-m-d');
    $archive = ($challenge['statut'] === 'archive');
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
    <a href="<?= APP_URL ?>/" class="btn btn-outline-secondary btn-sm" aria-label="Retour à l'accueil">
        ← Accueil
    </a>
</div>

<!-- Alerte retour AJAX -->
<div id="insc-alerte" class="membres-alerte mb-3" role="alert" aria-live="polite" hidden></div>

<?php if ($archive): ?>
<div class="alert alert-warning">Ce challenge est archivé. Les inscriptions sont en lecture seule.</div>
<?php endif; ?>

<div class="row g-3">

    <!-- ===================================================
         COLONNE GAUCHE : tireurs disponibles
         =================================================== -->
    <div class="col-lg-5">
        <div class="card form-card">

            <!-- Membres disponibles -->
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-titre mb-0">Membres disponibles</h2>
                <span class="badge bg-secondary" id="cpt-membres"><?= count($membres) ?></span>
            </div>
            <div class="dispo-zone" id="zone-membres-dispo">
                <?php require APP_ROOT . '/app/views/partials/challenge_membres_dispo.php'; ?>
            </div>

            <!-- Séparateur -->
            <div class="card-header d-flex justify-content-between align-items-center mt-1">
                <h2 class="card-titre mb-0">Non membres disponibles</h2>
                <span class="badge bg-secondary" id="cpt-externes"><?= count($externes) ?></span>
            </div>
            <div class="dispo-zone" id="zone-externes-dispo">
                <?php require APP_ROOT . '/app/views/partials/challenge_externes_dispo.php'; ?>
            </div>

            <!-- Bouton de déplacement -->
            <?php if (!$archive): ?>
            <div class="card-footer text-center">
                <button type="button"
                        id="btn-vers-inscription"
                        class="btn btn-primary"
                        disabled
                        aria-label="Déplacer le tireur sélectionné vers le formulaire d'inscription">
                    Inscrire ce tireur →
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================================================
         COLONNE DROITE : formulaire + liste inscrits
         =================================================== -->
    <div class="col-lg-7 d-flex flex-column gap-3">

        <!-- Panneau d'inscription (affiché uniquement quand un tireur est sélectionné) -->
        <div class="card form-card" id="panneau-inscription" hidden>
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-titre mb-0" id="panneau-titre">Inscription</h2>
                <button type="button" class="btn-close btn-close-white" id="btn-annuler-inscription"
                        aria-label="Annuler et fermer le panneau"></button>
            </div>
            <div class="card-body">

                <!-- Erreurs -->
                <div id="insc-erreurs" class="form-erreurs" role="alert" aria-live="assertive" hidden></div>

                <form id="form-inscription" novalidate>
                    <input type="hidden" id="insc-challenge-id" value="<?= (int)$challenge['id'] ?>">
                    <input type="hidden" id="insc-tireur-type" name="tireur_type" value="">
                    <input type="hidden" id="insc-tireur-id"   name="tireur_id"   value="">

                    <!-- Infos tireur -->
                    <div class="tireur-info-bloc mb-3">
                        <div class="tireur-info-nom" id="insc-nom-affiche"></div>
                        <div class="tireur-info-detail" id="insc-detail-affiche"></div>
                    </div>

                    <!-- Disciplines (checkboxes en 2 colonnes) -->
                    <fieldset>
                        <legend class="form-label fw-semibold">
                            Disciplines <span aria-hidden="true">*</span>
                        </legend>
                        <div class="disciplines-grille">
                            <?php foreach ($disciplines as $d): ?>
                            <div class="form-check discipline-item">
                                <input type="checkbox"
                                       class="form-check-input"
                                       id="disc-<?= (int)$d['code'] ?>"
                                       name="discipline_ids[]"
                                       value="<?= (int)$d['id'] ?>"
                                       aria-label="<?= htmlspecialchars($d['libelle_fr']) ?>">
                                <label class="form-check-label" for="disc-<?= (int)$d['code'] ?>">
                                    <span class="discipline-code"><?= (int)$d['code'] ?></span>
                                    <?= htmlspecialchars($d['libelle_fr']) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>

                    <div class="form-actions mt-3">
                        <button type="submit" id="btn-ajouter-insc" class="btn btn-primary">
                            Ajouter au challenge
                        </button>
                        <button type="button" id="btn-modifier-insc" class="btn btn-warning" hidden>
                            Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des inscrits -->
        <div class="card liste-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-titre mb-0">Inscrits au challenge</h2>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary" id="cpt-inscrits"><?= count($inscrits) ?></span>
                    <button type="button"
                            id="btn-imprimer-inscrits"
                            class="btn btn-sm btn-outline-light"
                            aria-label="Imprimer la liste des inscrits">
                        Imprimer
                    </button>
                </div>
            </div>
            <div class="card-body p-0" id="zone-inscrits">
                <?php require APP_ROOT . '/app/views/partials/challenge_inscrits_liste.php'; ?>
            </div>
        </div>

    </div>
</div>

<script>
    const CHALLENGE_ID = <?= (int)$challenge['id'] ?>;
    const CHALLENGE_ARCHIVE = <?= $archive ? 'true' : 'false' ?>;
</script>
<script src="<?= APP_URL ?>/js/challenge-inscriptions.js"></script>
