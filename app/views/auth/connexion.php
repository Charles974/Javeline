<div class="card auth-card" aria-labelledby="titre-connexion">
    <div class="card-body">
        <div class="auth-entete">
            <img src="<?= APP_URL ?>/public/img/images.png" alt="Logo Javeline" class="auth-logo">
            <h1 id="titre-connexion" class="auth-titre"><?= htmlspecialchars(APP_NAME) ?></h1>
            <p class="auth-sous-titre">Connectez-vous pour accéder au site</p>
        </div>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger auth-erreur" role="alert">
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= APP_URL ?>/connexion" novalidate>
            <div class="mb-3">
                <label for="identifiant" class="form-label">Identifiant</label>
                <input type="text"
                       class="form-control"
                       id="identifiant"
                       name="identifiant"
                       value="<?= htmlspecialchars($identifiant ?? '') ?>"
                       required
                       maxlength="50"
                       autocomplete="username"
                       autofocus
                       aria-required="true">
            </div>
            <div class="mb-4">
                <label for="mot_de_passe" class="form-label">Mot de passe</label>
                <input type="password"
                       class="form-control"
                       id="mot_de_passe"
                       name="mot_de_passe"
                       required
                       autocomplete="current-password"
                       aria-required="true">
            </div>
            <button type="submit" class="btn btn-primary w-100" aria-label="Se connecter">
                Se connecter
            </button>
        </form>
    </div>
</div>
