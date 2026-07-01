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
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/challenges/<?= (int)$challenge['id'] ?>/resume"
           class="btn btn-outline-primary btn-sm"
           aria-label="Voir le résumé du challenge">
            Résumé
        </a>
        <a href="<?= APP_URL ?>/" class="btn btn-outline-secondary btn-sm" aria-label="Retour à l'accueil">
            ← Accueil
        </a>
    </div>
</div>

<div id="insc-alerte" class="membres-alerte mb-3" role="alert" aria-live="polite" hidden></div>

<?php if ($archive): ?>
<div class="alert alert-warning">Ce challenge est archivé. Les inscriptions sont en lecture seule.</div>
<?php endif; ?>

<div class="row g-3 inscriptions-row">

    <!-- ===================================================
         COLONNE GAUCHE : tireurs disponibles
         =================================================== -->
    <div class="col-lg-5">
        <div class="card form-card dispo-card">

            <!-- Membres disponibles -->
            <div class="dispo-section">
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
            </div>

            <div class="dispo-section">
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
            </div>

            <?php if (!$archive): ?>
            <div class="card-footer text-center">
                <p class="dispo-hint mb-0">Double-clic sur un tireur pour accéder directement à sa fiche</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================================================
         COLONNE CENTRALE : bouton de transfert
         =================================================== -->
    <?php if (!$archive): ?>
    <div class="col-lg-auto d-flex flex-column align-items-center justify-content-center transfer-col">
        <button type="button"
                id="btn-vers-inscription"
                class="btn btn-primary btn-transfer"
                disabled
                aria-label="Inscrire ce tireur au challenge">
            <span aria-hidden="true">&rarr;</span>
        </button>
    </div>
    <?php endif; ?>

    <!-- ===================================================
         COLONNE DROITE : fiche tireur + liste inscrits
         =================================================== -->
    <div class="col-lg d-flex flex-column gap-3 inscript-col">

        <!-- Fiche tireur (toujours visible) -->
        <div class="card form-card fiche-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-titre mb-0">Fiche tireur</h2>
                <span class="badge bg-info" id="fiche-type-badge" hidden></span>
            </div>
            <div class="card-body fiche-body">

                <p class="fiche-vide" id="fiche-vide">
                    Sélectionnez un tireur dans la liste de gauche pour afficher sa fiche,
                    modifier ses informations ou l'inscrire au challenge.
                </p>

                <div id="fiche-contenu" hidden>

                    <div id="insc-erreurs" class="form-erreurs" role="alert" aria-live="assertive" hidden></div>

                    <!-- Profil du tireur (éditable) -->
                    <form id="form-profil" class="fiche-form" novalidate>
                        <input type="hidden" id="profil-id" name="id" value="">

                        <p class="fiche-section-titre">Informations du tireur</p>

                        <!-- Champs membre -->
                        <div id="champs-membre" class="fiche-grille mb-2" hidden>
                            <div>
                                <label for="pm-nom" class="form-label">Nom <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="pm-nom" name="nom" maxlength="100" autocomplete="off">
                            </div>
                            <div>
                                <label for="pm-prenom" class="form-label">Prénom <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="pm-prenom" name="prenom" maxlength="100" autocomplete="off">
                            </div>
                            <div>
                                <label for="pm-naissance" class="form-label">Date de naissance <span aria-hidden="true">*</span></label>
                                <input type="date" class="form-control" id="pm-naissance" name="date_naissance">
                            </div>
                            <div>
                                <label for="pm-lieu" class="form-label">Lieu de naissance <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="pm-lieu" name="lieu_naissance" maxlength="150" autocomplete="off">
                            </div>
                            <div class="col-span-2">
                                <label for="pm-licence" class="form-label">N° de licence <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="pm-licence" name="numero_licence" maxlength="50" autocomplete="off">
                            </div>
                            <div class="col-span-2">
                                <label for="pm-adresse1" class="form-label">Adresse <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="pm-adresse1" name="adresse1" maxlength="200" autocomplete="off">
                            </div>
                            <div class="col-span-2">
                                <label for="pm-adresse2" class="form-label">Adresse (complément)</label>
                                <input type="text" class="form-control" id="pm-adresse2" name="adresse2" maxlength="200" autocomplete="off">
                            </div>
                            <div>
                                <label for="pm-cp" class="form-label">Code postal <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="pm-cp" name="code_postal" maxlength="5" inputmode="numeric" pattern="[0-9]{5}" autocomplete="off">
                            </div>
                            <div>
                                <label for="pm-ville" class="form-label">Ville <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="pm-ville" name="ville" maxlength="100" autocomplete="off">
                            </div>
                            <div>
                                <label for="pm-tel" class="form-label">Téléphone <span aria-hidden="true">*</span></label>
                                <input type="tel" class="form-control" id="pm-tel" name="telephone" maxlength="15" inputmode="numeric" pattern="[0-9]+" autocomplete="off">
                            </div>
                            <div>
                                <label for="pm-email" class="form-label">Email <span aria-hidden="true">*</span></label>
                                <input type="email" class="form-control" id="pm-email" name="email" maxlength="150" autocomplete="off">
                            </div>
                            <div class="col-span-2 form-check">
                                <input type="checkbox" class="form-check-input" id="pm-certificat" name="certificat_medical" value="1">
                                <label class="form-check-label" for="pm-certificat">Certificat médical valide</label>
                            </div>
                        </div>

                        <!-- Champs non membre -->
                        <div id="champs-externe" class="fiche-grille mb-2" hidden>
                            <div>
                                <label for="pe-nom" class="form-label">Nom <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="pe-nom" name="nom" maxlength="100" autocomplete="off">
                            </div>
                            <div>
                                <label for="pe-prenom" class="form-label">Prénom <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="pe-prenom" name="prenom" maxlength="100" autocomplete="off">
                            </div>
                            <div class="col-span-2">
                                <label for="pe-club" class="form-label">Club <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="pe-club" name="club" maxlength="150" autocomplete="off">
                            </div>
                            <div>
                                <label for="pe-tel" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="pe-tel" name="telephone" maxlength="20" autocomplete="off">
                            </div>
                            <div>
                                <label for="pe-email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="pe-email" name="email" maxlength="150" autocomplete="off">
                            </div>
                            <div class="col-span-2 form-check">
                                <input type="checkbox" class="form-check-input" id="pe-etranger" name="etranger" value="1">
                                <label class="form-check-label" for="pe-etranger">Tireur étranger (libellés en anglais)</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" id="btn-enregistrer-profil" class="btn btn-success btn-sm">
                                Enregistrer les modifications
                            </button>
                            <button type="button" id="btn-annuler-profil" class="btn btn-outline-secondary btn-sm">
                                Annuler
                            </button>
                        </div>
                    </form>

                    <hr class="fiche-separateur">

                    <!-- Inscription aux disciplines du challenge -->
                    <form id="form-inscription" class="fiche-form" novalidate>
                        <input type="hidden" id="insc-challenge-id" value="<?= (int)$challenge['id'] ?>">
                        <input type="hidden" id="insc-tireur-type" name="tireur_type" value="">
                        <input type="hidden" id="insc-tireur-id"   name="tireur_id"   value="">

                        <p class="fiche-section-titre">Disciplines pour ce challenge</p>

                        <div class="dropdown mb-2">
                            <button type="button"
                                    class="btn btn-outline-secondary disc-dropdown-toggle dropdown-toggle"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside"
                                    aria-expanded="false"
                                    aria-label="Choisir les disciplines">
                                <span id="disc-compteur" class="disc-compteur-badge disc-compteur-vide">0 sélectionnée</span>
                            </button>
                            <ul class="dropdown-menu disc-dropdown-menu p-2" aria-label="Disciplines disponibles">
                                <?php foreach ($disciplinesParFamille as $famille => $items): ?>
                                <li class="filtre-groupe-titre"><?= htmlspecialchars($famille) ?></li>
                                <?php foreach ($items as $d): ?>
                                <li>
                                    <label class="filtre-option">
                                        <input type="checkbox"
                                               class="disc-checkbox"
                                               id="disc-<?= (int)$d['code'] ?>"
                                               name="discipline_ids[]"
                                               value="<?= (int)$d['id'] ?>">
                                        <span class="discipline-code"><?= (int)$d['code'] ?></span>
                                        <?= htmlspecialchars($d['libelle_fr']) ?>
                                    </label>
                                </li>
                                <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="form-actions">
                            <button type="submit" id="btn-ajouter-insc" class="btn btn-primary btn-sm">
                                Ajouter au challenge
                            </button>
                            <button type="button" id="btn-modifier-insc" class="btn btn-warning btn-sm" hidden>
                                Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Liste des inscrits -->
        <div class="card liste-card inscrits-card">
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
<script src="<?= APP_URL ?>/public/js/challenge-inscriptions.js"></script>
