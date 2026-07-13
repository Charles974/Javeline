<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titrePage ?? APP_NAME) ?></title>

    <!-- Bootstrap 5 (fichier local, pas de dependance a un CDN) -->
    <link
        rel="stylesheet"
        href="<?= APP_URL ?>/public/vendor/bootstrap/css/bootstrap.min.css"
    >

    <!-- Styles personnalisés -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css?v=<?= filemtime(APP_ROOT . '/public/css/style.css') ?>">
</head>
<body>

    <?php require APP_ROOT . '/app/views/partials/navbar.php'; ?>

    <!-- jQuery, Bootstrap JS et APP_URL chargés avant le contenu
         pour que les scripts inline des vues y aient accès
         (fichiers locaux, pas de dependance a un CDN) -->
    <script src="<?= APP_URL ?>/public/vendor/jquery/jquery.min.js"></script>
    <script src="<?= APP_URL ?>/public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const APP_URL = <?= json_encode(APP_URL, JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="<?= APP_URL ?>/public/js/main.js"></script>

    <main class="container my-4" id="contenu-principal" role="main">
        <?= $contenu ?? '' ?>
    </main>

    <?php require APP_ROOT . '/app/views/partials/footer.php'; ?>
    <?php if (Auth::estAdmin()): ?>
        <?php require APP_ROOT . '/app/views/partials/modal_challenge.php'; ?>
    <?php endif; ?>
    <?php if (Auth::estConnecte()): ?>
        <?php require APP_ROOT . '/app/views/partials/modal_mot_de_passe.php'; ?>
    <?php endif; ?>
</body>
</html>
