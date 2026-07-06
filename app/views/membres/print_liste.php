<?php
// Vue imprimable : liste des tireurs membres.
// Attendu : $membres (array)
?>
<div class="liste-impression">
    <div class="liste-impression-entete">
        <h1>Association Javeline</h1>
        <h2>Liste des tireurs membres</h2>
        <span class="liste-impression-date"><?= date('d/m/Y') ?></span>
    </div>

    <?php if (empty($membres)): ?>
        <p class="liste-impression-vide">Aucun membre enregistré.</p>
    <?php else: ?>
        <table class="liste-impression-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Date de naissance</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($membres as $membre): ?>
                <tr>
                    <td><?= htmlspecialchars($membre['nom']) ?></td>
                    <td><?= htmlspecialchars($membre['prenom']) ?></td>
                    <td><?= date('d/m/Y', strtotime($membre['date_naissance'])) ?></td>
                    <td><?= htmlspecialchars($membre['telephone']) ?></td>
                    <td><?= htmlspecialchars($membre['email']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="liste-impression-pied">
            Nombre de membres : <strong><?= count($membres) ?></strong> —
            Édité le <?= date('d/m/Y à H:i') ?>
        </p>
    <?php endif; ?>
</div>
