<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titrePage ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/fiche-tireur.css?v=<?= filemtime(APP_ROOT . '/public/css/fiche-tireur.css') ?>">
    <!-- Paged.js : numerote les pages ("Page X sur Y") en pied de page et supprime
         les en-tetes/pieds automatiques du navigateur -->
    <script>
        window.PagedConfig = {
            after: () => window.print()
        };
    </script>
    <script src="<?= APP_URL ?>/public/vendor/pagedjs/paged.polyfill.js" onerror="window.print()"></script>
</head>
<body>
    <?= $contenu ?? '' ?>
</body>
</html>
