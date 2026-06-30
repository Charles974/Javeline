<section class="hero text-center py-5" aria-labelledby="titre-accueil">
    <h1 id="titre-accueil">Association Javeline</h1>
    <p class="lead">Gestion des scores — Silhouette métallique</p>
</section>

<!-- Zone challenge : mise à jour via AJAX après création -->
<section class="challenge-actif mb-4" id="zone-challenge" aria-labelledby="titre-challenge">
    <h2 id="titre-challenge" class="visually-hidden">Challenge en cours ou à venir</h2>
    <?php require APP_ROOT . '/app/views/partials/challenge_card.php'; ?>
</section>

<!-- Message de succès (masqué par défaut) -->
<div id="alerte-succes" class="alerte-succes mx-auto mb-4" role="alert" aria-live="polite" hidden>
    <span id="alerte-succes-texte"></span>
</div>

<!-- Boutons de navigation principale -->
<section class="navigation-principale text-center" aria-label="Navigation principale">
    <div class="nav-boutons">
        <a href="<?= APP_URL ?>/membres"
           class="btn btn-nav"
           aria-label="Gérer les tireurs membres">
            Tireurs membres
        </a>
        <a href="<?= APP_URL ?>/externes"
           class="btn btn-nav"
           aria-label="Gérer les tireurs non membres">
            Tireurs non membres
        </a>
        <button type="button"
                class="btn btn-nav btn-nav-accent"
                data-bs-toggle="modal"
                data-bs-target="#modal-challenge"
                aria-label="Créer un nouveau challenge">
            Créer un challenge
        </button>
        <a href="<?= APP_URL ?>/challenges/historique"
           class="btn btn-nav"
           aria-label="Voir l'historique des challenges">
            Historique des challenges
        </a>
    </div>
</section>

<!-- Modal : création d'un challenge -->
<div class="modal fade"
     id="modal-challenge"
     tabindex="-1"
     aria-labelledby="modal-challenge-titre"
     aria-modal="true"
     role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-challenge-titre">Créer un challenge</h3>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fermer la fenêtre"></button>
            </div>

            <form id="form-challenge" novalidate aria-label="Formulaire de création d'un challenge">
                <div class="modal-body">

                    <!-- Message d'erreur inline -->
                    <div id="modal-erreur" class="modal-erreur" role="alert" aria-live="assertive" hidden></div>

                    <div class="mb-3">
                        <label for="challenge-libelle" class="form-label">Nom du challenge <span aria-hidden="true">*</span></label>
                        <input type="text"
                               class="form-control"
                               id="challenge-libelle"
                               name="libelle"
                               required
                               maxlength="200"
                               autocomplete="off"
                               aria-required="true">
                    </div>

                    <div class="mb-3">
                        <label for="challenge-date-debut" class="form-label">Date de début <span aria-hidden="true">*</span></label>
                        <input type="date"
                               class="form-control"
                               id="challenge-date-debut"
                               name="date_debut"
                               required
                               aria-required="true">
                    </div>

                    <div class="mb-3">
                        <label for="challenge-date-fin" class="form-label">Date de fin <span aria-hidden="true">*</span></label>
                        <input type="date"
                               class="form-control"
                               id="challenge-date-fin"
                               name="date_fin"
                               required
                               aria-required="true">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit"
                            class="btn btn-primary"
                            id="btn-valider-challenge">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
