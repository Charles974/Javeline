<div class="page-header mb-4">
    <h1>Tireurs membres</h1>
</div>

<!-- Message retour AJAX -->
<div id="membres-alerte" class="membres-alerte" role="alert" aria-live="polite" hidden></div>

<div class="row g-4">

    <!-- ====== Colonne gauche : formulaire ====== -->
    <div class="col-lg-5">
        <div class="card form-card h-100">
            <div class="card-header">
                <h2 class="card-titre" id="form-titre">Nouveau membre</h2>
            </div>
            <div class="card-body">

                <!-- Erreurs inline -->
                <div id="form-erreurs" class="form-erreurs" role="alert" aria-live="assertive" hidden></div>

                <form id="form-membre" novalidate aria-labelledby="form-titre">
                    <input type="hidden" id="membre-id" name="id" value="">

                    <div class="row g-2">
                        <div class="col-sm-6 mb-2">
                            <label for="membre-nom" class="form-label">Nom <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-nom" name="nom"
                                   required maxlength="100" autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label for="membre-prenom" class="form-label">Prénom <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-prenom" name="prenom"
                                   required maxlength="100" autocomplete="off" aria-required="true">
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-sm-6 mb-2">
                            <label for="membre-naissance" class="form-label">Date de naissance <span aria-hidden="true">*</span></label>
                            <input type="date" class="form-control" id="membre-naissance"
                                   name="date_naissance" required aria-required="true">
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label for="membre-lieu" class="form-label">Lieu de naissance <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-lieu" name="lieu_naissance"
                                   required maxlength="150" autocomplete="off" aria-required="true">
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-sm-6 mb-2">
                            <label for="membre-categorie" class="form-label">Catégorie d'âge <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-categorie" name="categorie_age"
                                   required maxlength="50" autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label for="membre-licence" class="form-label">N° de licence <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-licence" name="numero_licence"
                                   required maxlength="50" autocomplete="off" aria-required="true">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="membre-adresse1" class="form-label">Adresse <span aria-hidden="true">*</span></label>
                        <input type="text" class="form-control" id="membre-adresse1" name="adresse1"
                               required maxlength="200" autocomplete="off" aria-required="true">
                    </div>

                    <div class="mb-2">
                        <label for="membre-adresse2" class="form-label">Adresse (complément)</label>
                        <input type="text" class="form-control" id="membre-adresse2" name="adresse2"
                               maxlength="200" autocomplete="off">
                    </div>

                    <div class="row g-2">
                        <div class="col-sm-4 mb-2">
                            <label for="membre-cp" class="form-label">Code postal <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-cp" name="code_postal"
                                   required maxlength="10" autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-sm-8 mb-2">
                            <label for="membre-ville" class="form-label">Ville <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-ville" name="ville"
                                   required maxlength="100" autocomplete="off" aria-required="true">
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-sm-6 mb-2">
                            <label for="membre-tel" class="form-label">Téléphone <span aria-hidden="true">*</span></label>
                            <input type="tel" class="form-control" id="membre-tel" name="telephone"
                                   required maxlength="20" autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label for="membre-email" class="form-label">Email <span aria-hidden="true">*</span></label>
                            <input type="email" class="form-control" id="membre-email" name="email"
                                   required maxlength="150" autocomplete="off" aria-required="true">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="membre-certificat"
                                   name="certificat_medical" value="1">
                            <label class="form-check-label" for="membre-certificat">
                                Certificat médical valide
                            </label>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="form-actions">
                        <button type="submit" id="btn-ajouter" class="btn btn-primary" aria-label="Ajouter le membre">
                            Ajouter
                        </button>
                        <button type="button" id="btn-modifier" class="btn btn-warning" disabled aria-label="Modifier le membre sélectionné">
                            Modifier
                        </button>
                        <button type="button" id="btn-imprimer" class="btn btn-secondary" disabled aria-label="Imprimer la fiche du membre sélectionné">
                            Imprimer
                        </button>
                        <button type="button" id="btn-reset" class="btn btn-outline-secondary" aria-label="Réinitialiser le formulaire">
                            Effacer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ====== Colonne droite : liste des membres ====== -->
    <div class="col-lg-7">
        <div class="card liste-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-titre">Liste des membres</h2>
                <span id="membres-compteur" class="badge bg-secondary">
                    <?= count($membres) ?> membre<?= count($membres) > 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="card-body p-0" id="zone-membres-liste">
                <?php require APP_ROOT . '/app/views/partials/membres_liste.php'; ?>
            </div>
        </div>
    </div>

</div>

<script src="<?= APP_URL ?>/js/membres.js"></script>
