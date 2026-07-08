<?php
    require_once APP_ROOT . '/app/models/ChallengeModel.php';
    $challengeNavbar = (new ChallengeModel())->findActif();
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-javeline" aria-label="Navigation principale">
    <div class="container">
        <a class="navbar-brand" href="<?= APP_URL ?>/">
            <img src="<?= APP_URL ?>/public/img/images.png" alt="" class="navbar-logo">
            <?= htmlspecialchars(APP_NAME) ?>
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#menu-principal"
            aria-controls="menu-principal"
            aria-expanded="false"
            aria-label="Ouvrir le menu de navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu-principal">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <?php if ($challengeNavbar): ?>
                        <a class="nav-link" href="<?= APP_URL ?>/challenges/<?= (int)$challengeNavbar['id'] ?>" aria-label="Gérer les inscriptions du challenge en cours">Gestion des inscriptions</a>
                    <?php else: ?>
                        <a class="nav-link disabled" href="#" aria-disabled="true" aria-label="Aucun challenge en cours">Gestion des inscriptions</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/membres" aria-label="Gérer les tireurs membres">Tireurs membres</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/externes" aria-label="Gérer les tireurs non membres">Tireurs non membres</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/disciplines" aria-label="Consulter les disciplines disponibles">Disciplines</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/challenges/historique" aria-label="Voir l'historique des challenges">Historique des challenges</a>
                </li>
                <li class="nav-item">
                    <button type="button"
                            class="nav-link nav-cta"
                            data-bs-toggle="modal"
                            data-bs-target="#modal-challenge"
                            aria-label="Créer un nouveau challenge">
                        + Créer un challenge
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>
