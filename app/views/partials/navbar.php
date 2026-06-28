<nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Navigation principale">
    <div class="container">
        <a class="navbar-brand" href="<?= APP_URL ?>/">
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
                    <a class="nav-link" href="<?= APP_URL ?>/" aria-label="Accueil">Accueil</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
