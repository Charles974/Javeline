<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche membre — <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/css/fiche-print.css">
</head>
<body onload="window.print()">

<div class="fiche">
    <div class="fiche-entete">
        <h1>Association Javeline</h1>
        <h2>Fiche membre</h2>
    </div>

    <table class="fiche-table">
        <tr>
            <th>Nom</th>
            <td><?= htmlspecialchars($membre['nom']) ?></td>
            <th>Prénom</th>
            <td><?= htmlspecialchars($membre['prenom']) ?></td>
        </tr>
        <tr>
            <th>Date de naissance</th>
            <td><?= date('d/m/Y', strtotime($membre['date_naissance'])) ?></td>
            <th>Lieu de naissance</th>
            <td><?= htmlspecialchars($membre['lieu_naissance']) ?></td>
        </tr>
        <tr>
            <th>Catégorie d'âge</th>
            <td><?= htmlspecialchars($membre['categorie_age']) ?></td>
            <th>N° de licence</th>
            <td><?= htmlspecialchars($membre['numero_licence']) ?></td>
        </tr>
        <tr>
            <th>Adresse</th>
            <td colspan="3">
                <?= htmlspecialchars($membre['adresse1']) ?>
                <?php if ($membre['adresse2']): ?>
                    <br><?= htmlspecialchars($membre['adresse2']) ?>
                <?php endif; ?>
                <br><?= htmlspecialchars($membre['code_postal']) ?> <?= htmlspecialchars($membre['ville']) ?>
            </td>
        </tr>
        <tr>
            <th>Téléphone</th>
            <td><?= htmlspecialchars($membre['telephone']) ?></td>
            <th>Email</th>
            <td><?= htmlspecialchars($membre['email']) ?></td>
        </tr>
        <tr>
            <th>Certificat médical</th>
            <td colspan="3"><?= $membre['certificat_medical'] ? 'Oui' : 'Non' ?></td>
        </tr>
    </table>

    <div class="fiche-pied">
        Édité le <?= date('d/m/Y à H:i') ?>
    </div>
</div>

</body>
</html>
