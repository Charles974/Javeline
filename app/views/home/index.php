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
        <a href="<?= APP_URL ?>/disciplines"
           class="btn btn-nav"
           aria-label="Consulter les disciplines disponibles">
            Disciplines
        </a>
        <a href="<?= APP_URL ?>/challenges/historique"
           class="btn btn-nav"
           aria-label="Voir l'historique des challenges">
            Historique des challenges
        </a>
    </div>
</section>
