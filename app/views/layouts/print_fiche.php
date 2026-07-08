<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titrePage ?? APP_NAME) ?></title>
    <?php
    // Feuille de style de la fiche : par defaut la fiche tireur (portrait).
    // Une vue peut fournir $ficheCss pour reprendre le meme style dans une
    // autre orientation (ex : liste des disciplines en paysage).
    $ficheCss = $ficheCss ?? 'fiche-tireur';
    ?>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/<?= htmlspecialchars($ficheCss) ?>.css?v=<?= filemtime(APP_ROOT . '/public/css/' . $ficheCss . '.css') ?>">
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
