<?php
// Vue imprimable : liste des tireurs non membres.
// Attendu : $externes (array)
?>
<div class="liste-impression">
    <div class="liste-impression-entete">
        <h1>Association Javeline</h1>
        <h2>Liste des tireurs non membres</h2>
        <span class="liste-impression-date"><?= date('d/m/Y') ?></span>
    </div>

    <?php if (empty($externes)): ?>
        <p class="liste-impression-vide">Aucun tireur non membre enregistré.</p>
    <?php else: ?>
        <table class="liste-impression-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Club</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th class="liste-impression-col-etr">Étranger</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($externes as $externe): ?>
                <tr>
                    <td><?= htmlspecialchars($externe['nom']) ?></td>
                    <td><?= htmlspecialchars($externe['prenom']) ?></td>
                    <td><?= htmlspecialchars($externe['club']) ?></td>
                    <td><?= htmlspecialchars($externe['telephone'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($externe['email'] ?? '—') ?></td>
                    <td class="liste-impression-col-etr"><?= $externe['etranger'] ? '☑' : '' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="liste-impression-pied">
            Nombre de tireurs : <strong><?= count($externes) ?></strong> —
            Édité le <?= date('d/m/Y à H:i') ?>
        </p>
    <?php endif; ?>
</div>
