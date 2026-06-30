<?php
    $debut   = date('d/m/Y', strtotime($challenge['date_debut']));
    $fin     = date('d/m/Y', strtotime($challenge['date_fin']));
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
            <div class="dispo-recherche px-2 pt-2">
                <input type="search"
                       id="recherche-membres"
                       class="form-control form-control-sm"
                       placeholder="Rechercher un membre…"
                       aria-label="Rechercher dans la liste des membres disponibles">
            </div>
            <div class="dispo-zone" id="zone-membres-dispo">
                <?php require APP_ROOT . '/app/views/partials/challenge_membres_dispo.php'; ?>
            </div>

            <div class="card-header d-flex justify-content-between align-items-center mt-1">
                <h2 class="card-titre mb-0">Non membres disponibles</h2>
                <span class="badge bg-secondary" id="cpt-externes"><?= count($externes) ?></span>
            </div>
            <div class="dispo-recherche px-2 pt-2">
                <input type="search"
                       id="recherche-externes"
                       class="form-control form-control-sm"
                       placeholder="Rechercher un non membre…"
                       aria-label="Rechercher dans la liste des non membres disponibles">
            </div>
            <div class="dispo-zone" id="zone-externes-dispo">
                <?php require APP_ROOT . '/app/views/partials/challenge_externes_dispo.php'; ?>
            </div>

            <?php if (!$archive): ?>
            <div class="card-footer text-center">
                <button type="button"
                        id="btn-vers-inscription"
                        class="btn btn-primary"
                        disabled
                        aria-label="Ouvrir le panneau d'inscription pour ce tireur">
                    Inscrire ce tireur →
                </button>
                <p class="dispo-hint mt-1 mb-0">Double-clic sur un tireur pour accéder directement au formulaire</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================================================
         COLONNE DROITE : formulaire + liste inscrits
         =================================================== -->
    <div class="col-lg-7 d-flex flex-column gap-3">

        <!-- Panneau d'inscription -->
        <div class="card form-card" id="panneau-inscription" hidden>
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-titre mb-0" id="panneau-titre">Inscription</h2>
                <button type="button" class="btn-close btn-close-white" id="btn-annuler-inscription"
                        aria-label="Annuler et fermer le panneau"></button>
            </div>
            <div class="card-body">

                <div id="insc-erreurs" class="form-erreurs" role="alert" aria-live="assertive" hidden></div>

                <form id="form-inscription" novalidate>
                    <input type="hidden" id="insc-challenge-id" value="<?= (int)$challenge['id'] ?>">
                    <input type="hidden" id="insc-tireur-type" name="tireur_type" value="">
                    <input type="hidden" id="insc-tireur-id"   name="tireur_id"   value="">

                    <div class="tireur-info-bloc mb-3">
                        <div class="tireur-info-nom" id="insc-nom-affiche"></div>
                        <div class="tireur-info-detail" id="insc-detail-affiche"></div>
                    </div>

                    <!-- Disciplines -->
                    <fieldset>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <legend class="form-label fw-semibold mb-0">
                                Disciplines <span aria-hidden="true">*</span>
                            </legend>
                            <span id="disc-compteur" class="disc-compteur-badge">0 sélectionnée</span>
                        </div>
                        <div class="disciplines-grille">
                            <?php foreach ($disciplines as $d): ?>
                            <div class="form-check discipline-item">
                                <input type="checkbox"
                                       class="form-check-input disc-checkbox"
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
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="card-titre mb-0">Inscrits au challenge</h2>
                <div class="d-flex align-items-center gap-2 flex-wrap">

                    <!-- Filtre multi-catégories -->
                    <div class="dropdown" id="filtre-categories-wrapper">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                data-bs-auto-close="outside"
                                aria-expanded="false"
                                aria-label="Filtrer par catégorie">
                            Filtrer
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end filtre-menu p-2" aria-label="Filtres disponibles">
                            <li class="filtre-groupe-titre">Type</li>
                            <li>
                                <label class="filtre-option">
                                    <input type="checkbox" class="filtre-check" data-filtre-type="type" value="membre"> Membres
                                </label>
                            </li>
                            <li>
                                <label class="filtre-option">
                                    <input type="checkbox" class="filtre-check" data-filtre-type="type" value="externe"> Non membres
                                </label>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="filtre-groupe-titre">Famille de disciplines</li>
                            <li>
                                <label class="filtre-option">
                                    <input type="checkbox" class="filtre-check" data-filtre-type="famille" value="gros-calibre"> Gros Calibre (400-403)
                                </label>
                            </li>
                            <li>
                                <label class="filtre-option">
                                    <input type="checkbox" class="filtre-check" data-filtre-type="famille" value="petit-calibre"> Petit Calibre (404-407)
                                </label>
                            </li>
                            <li>
                                <label class="filtre-option">
                                    <input type="checkbox" class="filtre-check" data-filtre-type="famille" value="field"> Field (408-409)
                                </label>
                            </li>
                            <li>
                                <label class="filtre-option">
                                    <input type="checkbox" class="filtre-check" data-filtre-type="famille" value="carabine-pc"> Carabine PC (410-411)
                                </label>
                            </li>
                            <li>
                                <label class="filtre-option">
                                    <input type="checkbox" class="filtre-check" data-filtre-type="famille" value="carabine-gc"> Carabine GC (412-413)
                                </label>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button" class="btn btn-sm btn-link p-0 text-danger" id="btn-reset-filtres">
                                    Réinitialiser les filtres
                                </button>
                            </li>
                        </ul>
                    </div>

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
    const CHALLENGE_ID      = <?= (int)$challenge['id'] ?>;
    const CHALLENGE_ARCHIVE = <?= $archive ? 'true' : 'false' ?>;
</script>
<script src="<?= APP_URL ?>/js/challenge-inscriptions.js"></script>
