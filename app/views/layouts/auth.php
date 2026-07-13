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
<body class="page-auth">

    <main class="auth-conteneur" role="main">
        <?= $contenu ?? '' ?>
    </main>

</body>
</html>
