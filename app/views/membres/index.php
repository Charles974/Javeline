<div class="page-header mb-4">
    <h1>Tireurs membres</h1>
</div>

<!-- Message retour AJAX -->
<div id="membres-alerte" class="membres-alerte" role="alert" aria-live="polite" hidden></div>

<div class="membres-row">

    <!-- ====== Formulaire ====== -->
    <div class="mb-4">
        <div class="card form-card">
            <div class="card-header">
                <h2 class="card-titre" id="form-titre">Nouveau membre</h2>
            </div>
            <div class="card-body">

                <!-- Erreurs inline -->
                <div id="form-erreurs" class="form-erreurs" role="alert" aria-live="assertive" hidden></div>

                <form id="form-membre" novalidate aria-labelledby="form-titre">
                    <input type="hidden" id="membre-id" name="id" value="">

                    <div class="row g-2">
                        <div class="col-6 col-md-3 mb-2">
                            <label for="membre-nom" class="form-label visually-hidden">Nom <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-nom" name="nom" placeholder="Nom *"
                                   required maxlength="100" autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label for="membre-prenom" class="form-label visually-hidden">Prénom <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-prenom" name="prenom" placeholder="Prénom *"
                                   required maxlength="100" autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label for="membre-naissance" class="form-label visually-hidden">Date de naissance <span aria-hidden="true">*</span></label>
                            <input type="date" class="form-control" id="membre-naissance"
                                   name="date_naissance" placeholder="Date de naissance *" required aria-required="true">
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label for="membre-lieu" class="form-label visually-hidden">Lieu de naissance <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-lieu" name="lieu_naissance" placeholder="Lieu de naissance *"
                                   required maxlength="150" autocomplete="off" aria-required="true">
                        </div>
                    </div>

                    <div class="row g-2 align-items-center">
                        <div class="col-md-4 mb-2">
                            <label for="membre-licence" class="form-label visually-hidden">N° de licence <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-licence" name="numero_licence" placeholder="N° de licence *"
                                   required maxlength="50" autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="membre-coach" class="form-label visually-hidden">Coach</label>
                            <input type="text" class="form-control" id="membre-coach" name="coach" placeholder="Coach"
                                   maxlength="150" autocomplete="off">
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="membre-certificat"
                                       name="certificat_medical" value="1">
                                <label class="form-check-label" for="membre-certificat">
                                    Certificat médical valide
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label for="membre-tel" class="form-label visually-hidden">Téléphone <span aria-hidden="true">*</span></label>
                            <input type="tel" class="form-control" id="membre-tel" name="telephone" placeholder="Téléphone *"
                                   required maxlength="20" inputmode="numeric" pattern="[0-9 ]+"
                                   autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="membre-email" class="form-label visually-hidden">Email <span aria-hidden="true">*</span></label>
                            <input type="email" class="form-control" id="membre-email" name="email" placeholder="Email *"
                                   required maxlength="150" autocomplete="off" aria-required="true">
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4 mb-2">
                            <label for="membre-adresse1" class="form-label visually-hidden">Adresse <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-adresse1" name="adresse1" placeholder="Adresse *"
                                   required maxlength="200" autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="membre-adresse2" class="form-label visually-hidden">Adresse (complément)</label>
                            <input type="text" class="form-control" id="membre-adresse2" name="adresse2" placeholder="Adresse (complément)"
                                   maxlength="200" autocomplete="off">
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <label for="membre-cp" class="form-label visually-hidden">Code postal <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-cp" name="code_postal" placeholder="Code postal *"
                                   required maxlength="5" inputmode="numeric" pattern="[0-9]{5}"
                                   autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <label for="membre-ville" class="form-label visually-hidden">Ville <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="membre-ville" name="ville" placeholder="Ville *"
                                   required maxlength="100" autocomplete="off" aria-required="true">
                        </div>
                    </div>

                    <?php $typeFiche = 'membre'; require APP_ROOT . '/app/views/partials/mention_rgpd.php'; ?>

                    <!-- Boutons d'action -->
                    <div class="form-actions">
                        <button type="submit" id="btn-ajouter" class="btn btn-success" aria-label="Ajouter le membre">
                            Ajouter
                        </button>
                        <button type="button" id="btn-modifier" class="btn btn-warning" disabled aria-label="Modifier le membre sélectionné">
                            Modifier
                        </button>
                        <button type="button" id="btn-imprimer" class="btn btn-secondary" disabled aria-label="Imprimer la fiche du membre sélectionné">
                            Imprimer
                        </button>
                        <button type="button" id="btn-supprimer" class="btn btn-danger" disabled aria-label="Supprimer le membre sélectionné">
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

    <!-- ====== Liste des membres ====== -->
    <div class="card liste-card membres-liste-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-titre">Liste des membres</h2>
            <div class="d-flex align-items-center gap-2">
                <span id="membres-compteur" class="badge bg-secondary">
                    <?= count($membres) ?> membre<?= count($membres) > 1 ? 's' : '' ?>
                </span>
                <a href="<?= APP_URL ?>/membres/imprimer"
                   target="_blank"
                   class="btn btn-sm btn-outline-light"
                   aria-label="Imprimer la liste des tireurs membres">
                    Imprimer la liste
                </a>
            </div>
        </div>
        <div class="card-body p-0" id="zone-membres-liste">
            <?php require APP_ROOT . '/app/views/partials/membres_liste.php'; ?>
        </div>
    </div>

</div>

<script src="<?= APP_URL ?>/public/js/membres.js"></script>
