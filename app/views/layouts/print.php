<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titrePage ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/fiche-print.css?v=<?= filemtime(APP_ROOT . '/public/css/fiche-print.css') ?>">
    <style>
        /* Texte du pied de page repete sur chaque page (voir @page dans fiche-print.css) */
        :root {
            --footer-edite: "Édité le <?= date('d/m/Y à H:i') ?>";
        }
    </style>
    <!-- Paged.js : calcule la pagination reelle pour numeroter "X sur Y" en pied de page -->
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
