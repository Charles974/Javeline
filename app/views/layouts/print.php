<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titrePage ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/fiche-print.css">
</head>
<body onload="window.print()">
    <?= $contenu ?? '' ?>
</body>
</html>
