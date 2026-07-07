<section class="hero-accueil" aria-labelledby="titre-accueil">
    <div class="hero-presentation">
        <div class="hero-identite">
            <img src="<?= APP_URL ?>/public/img/images.png" alt="Logo Javeline Nancéienne" class="hero-logo">
            <div>
                <p class="hero-eyebrow">Javeline Nancéienne</p>
                <h1 id="titre-accueil">Association Javeline</h1>
            </div>
        </div>
        <p class="lead hero-texte">
            Gestion des scores — tir sur <strong>silhouette métallique</strong>.
            Suivez le challenge en cours et gérez vos tireurs.
        </p>
    </div>

    <!-- Zone challenge : mise à jour via AJAX après création -->
    <div class="hero-challenge" id="zone-challenge" aria-labelledby="titre-challenge">
        <h2 id="titre-challenge" class="visually-hidden">Challenge en cours ou à venir</h2>
        <?php require APP_ROOT . '/app/views/partials/challenge_card.php'; ?>
    </div>
</section>

<!-- Message de succès (masqué par défaut) -->
<div id="alerte-succes" class="alerte-succes mx-auto mb-4" role="alert" aria-live="polite" hidden>
    <span id="alerte-succes-texte"></span>
</div>

<!-- Accès rapide -->
<section class="acces-rapide" aria-label="Accès rapide">
    <p class="acces-rapide-titre">Accès rapide</p>
    <div class="acces-rapide-grille">
        <a href="<?= APP_URL ?>/membres"
           class="acces-rapide-lien"
           aria-label="Gérer les tireurs membres">
            Tireurs membres
        </a>
        <a href="<?= APP_URL ?>/externes"
           class="acces-rapide-lien"
           aria-label="Gérer les tireurs non membres">
            Tireurs non membres
        </a>
        <a href="<?= APP_URL ?>/disciplines"
           class="acces-rapide-lien"
           aria-label="Consulter les disciplines disponibles">
            Disciplines
        </a>
        <a href="<?= APP_URL ?>/challenges/historique"
           class="acces-rapide-lien"
           aria-label="Voir l'historique des challenges">
            Historique des challenges
        </a>
        <button type="button"
                class="acces-rapide-lien acces-rapide-accent"
                data-bs-toggle="modal"
                data-bs-target="#modal-challenge"
                aria-label="Créer un nouveau challenge">
            + Créer un challenge
        </button>
    </div>
</section>
