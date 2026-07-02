<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche tireur — <?= htmlspecialchars($externe['nom'] . ' ' . $externe['prenom']) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/fiche-print.css">
</head>
<body onload="window.print()">

<div class="fiche">
    <div class="fiche-entete">
        <h1>Association Javeline</h1>
        <h2>Fiche tireur non membre</h2>
    </div>

    <table class="fiche-table">
        <tr>
            <th>Nom</th>
            <td><?= htmlspecialchars($externe['nom']) ?></td>
            <th>Prénom</th>
            <td><?= htmlspecialchars($externe['prenom']) ?></td>
        </tr>
        <tr>
            <th>Club</th>
            <td colspan="3"><?= htmlspecialchars($externe['club']) ?></td>
        </tr>
        <tr>
            <th>Téléphone</th>
            <td><?= htmlspecialchars($externe['telephone'] ?? '—') ?></td>
            <th>Email</th>
            <td><?= htmlspecialchars($externe['email'] ?? '—') ?></td>
        </tr>
        <tr>
            <th>Tireur étranger</th>
            <td colspan="3"><?= $externe['etranger'] ? 'Oui' : 'Non' ?></td>
        </tr>
    </table>

    <div class="fiche-pied">
        Édité le <?= date('d/m/Y à H:i') ?>
    </div>
</div>

</body>
</html>
