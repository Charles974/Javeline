<div class="page-header mb-4">
    <h1>Tireurs non membres</h1>
</div>

<!-- Message retour AJAX -->
<div id="externes-alerte" class="membres-alerte" role="alert" aria-live="polite" hidden></div>

<div class="membres-row">

    <!-- ====== Formulaire ====== -->
    <div class="mb-4">
        <div class="card form-card">
            <div class="card-header">
                <h2 class="card-titre" id="form-titre">Nouveau tireur</h2>
            </div>
            <div class="card-body">

                <div id="form-erreurs" class="form-erreurs" role="alert" aria-live="assertive" hidden></div>

                <form id="form-externe" novalidate aria-labelledby="form-titre">
                    <input type="hidden" id="externe-id" name="id" value="">

                    <div class="row g-2">
                        <div class="col-6 col-md-3 mb-2">
                            <label for="externe-nom" class="form-label visually-hidden">Nom <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="externe-nom" name="nom" placeholder="Nom *"
                                   required maxlength="100" autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label for="externe-prenom" class="form-label visually-hidden">Prénom <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="externe-prenom" name="prenom" placeholder="Prénom *"
                                   required maxlength="100" autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="externe-club" class="form-label visually-hidden">Club <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="externe-club" name="club" placeholder="Club *"
                                   required maxlength="150" autocomplete="off" aria-required="true">
                        </div>
                    </div>

                    <div class="row g-2 align-items-center">
                        <div class="col-sm-3 mb-2">
                            <label for="externe-tel" class="form-label visually-hidden">Téléphone</label>
                            <input type="tel" class="form-control" id="externe-tel" name="telephone" placeholder="Téléphone"
                                   maxlength="20" autocomplete="off">
                        </div>
                        <div class="col-sm-3 mb-2">
                            <label for="externe-email" class="form-label visually-hidden">Email</label>
                            <input type="email" class="form-control" id="externe-email" name="email" placeholder="Email"
                                   maxlength="150" autocomplete="off">
                        </div>
                        <div class="col-sm-3 mb-2">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="externe-etranger"
                                       name="etranger" value="1">
                                <label class="form-check-label" for="externe-etranger">
                                    Tireur étranger
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-3 mb-2">
                            <label for="externe-coach" class="form-label visually-hidden">Coach</label>
                            <input type="text" class="form-control" id="externe-coach" name="coach" placeholder="Coach"
                                   maxlength="150" autocomplete="off">
                        </div>
                    </div>

                    <?php $typeFiche = 'externe'; require APP_ROOT . '/app/views/partials/mention_rgpd.php'; ?>

                    <div class="form-actions">
                        <button type="submit" id="btn-ajouter" class="btn btn-primary" aria-label="Ajouter le tireur">
                            Ajouter
                        </button>
                        <button type="button" id="btn-modifier" class="btn btn-warning" disabled aria-label="Modifier le tireur sélectionné">
                            Modifier
                        </button>
                        <button type="button" id="btn-imprimer" class="btn btn-secondary" disabled aria-label="Imprimer la fiche du tireur sélectionné">
                            Imprimer
                        </button>
                        <button type="button" id="btn-supprimer" class="btn btn-danger" disabled aria-label="Supprimer le tireur sélectionné">
                            Supprimer
                        </button>
                        <button type="button" id="btn-reset" class="btn btn-outline-secondary" aria-label="Réinitialiser le formulaire">
                            Effacer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ====== Liste des tireurs externes ====== -->
    <div class="card liste-card membres-liste-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-titre">Liste des tireurs non membres</h2>
            <span id="externes-compteur" class="badge bg-secondary">
                <?= count($externes) ?> tireur<?= count($externes) > 1 ? 's' : '' ?>
            </span>
        </div>
        <div class="card-body p-0" id="zone-externes-liste">
            <?php require APP_ROOT . '/app/views/partials/externes_liste.php'; ?>
        </div>
    </div>

</div>

<script src="<?= APP_URL ?>/public/js/externes.js"></script>
