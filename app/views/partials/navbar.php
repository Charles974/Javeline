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

                <?php if (Auth::estAdmin()): ?>
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
                <?php endif; ?>

                <?php if (Auth::role() === Auth::ROLE_TOUR): ?>
                    <li class="nav-item">
                        <?php if ($challengeNavbar): ?>
                            <a class="nav-link" href="<?= APP_URL ?>/challenges/<?= (int)$challengeNavbar['id'] ?>/resume" aria-label="Saisir les scores du challenge en cours">Saisie des scores</a>
                        <?php else: ?>
                            <a class="nav-link disabled" href="#" aria-disabled="true" aria-label="Aucun challenge en cours">Saisie des scores</a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <?php if ($challengeNavbar): ?>
                            <a class="nav-link" href="<?= APP_URL ?>/challenges/<?= (int)$challengeNavbar['id'] ?>/planning" aria-label="Consulter le planning du challenge en cours">Planning</a>
                        <?php else: ?>
                            <a class="nav-link disabled" href="#" aria-disabled="true" aria-label="Aucun challenge en cours">Planning</a>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>

                <?php if (Auth::estAdmin() || Auth::role() === Auth::ROLE_UTILISATEUR): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/challenges/historique" aria-label="Voir l'historique des challenges">Historique des challenges</a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::estAdmin()): ?>
                    <li class="nav-item">
                        <button type="button"
                                class="nav-link nav-cta"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-challenge"
                                aria-label="Créer un nouveau challenge">
                            + Créer un challenge
                        </button>
                    </li>
                <?php endif; ?>

                <!-- Menu du compte connecté -->
                <li class="nav-item dropdown">
                    <button type="button"
                            class="nav-link dropdown-toggle nav-compte"
                            id="menu-compte"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            aria-label="Menu du compte <?= htmlspecialchars(Auth::identifiant()) ?>">
                        <?= htmlspecialchars(Auth::identifiant()) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menu-compte">
                        <li>
                            <span class="dropdown-item-text nav-compte-role">
                                Profil : <?= htmlspecialchars(Auth::libelleRole(Auth::role())) ?>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if (Auth::estAdmin()): ?>
                            <li>
                                <a class="dropdown-item" href="<?= APP_URL ?>/utilisateurs" aria-label="Gérer les comptes utilisateurs">
                                    Gestion des comptes
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <button type="button"
                                    class="dropdown-item"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modal-mot-de-passe"
                                    aria-label="Changer mon mot de passe">
                                Changer mon mot de passe
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item dropdown-item-danger" href="<?= APP_URL ?>/deconnexion" aria-label="Se déconnecter">
                                Se déconnecter
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
