<?php
// Vue imprimable : liste des catégories (disciplines).
// Attendu : $disciplines (array)
?>
<div class="liste-impression">
    <div class="liste-impression-entete">
        <h1>Association Javeline</h1>
        <h2>Catégories</h2>
        <span class="liste-impression-date"><?= date('d/m/Y') ?></span>
    </div>

    <?php if (empty($disciplines)): ?>
        <p class="liste-impression-vide">Aucune catégorie enregistrée.</p>
    <?php else: ?>
        <table class="liste-impression-table">
            <thead>
                <tr>
                    <th class="liste-impression-col-num">Numéro de catégorie</th>
                    <th>Désignation catégorie</th>
                    <th>Désignation anglaise</th>
                    <th class="liste-impression-col-qualif">Qualif F1</th>
                    <th class="liste-impression-col-qualif">Qualif F2</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($disciplines as $d): ?>
                <tr>
                    <td><?= (int)$d['code'] ?></td>
                    <td><?= htmlspecialchars($d['libelle_fr']) ?></td>
                    <td><?= htmlspecialchars($d['libelle_en']) ?></td>
                    <td class="liste-impression-col-qualif"><?= (int)$d['qualif_f1'] ?></td>
                    <td class="liste-impression-col-qualif"><?= (int)$d['qualif_f2'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="liste-impression-pied">
            Nombre de catégories : <strong><?= count($disciplines) ?></strong> —
            Édité le <?= date('d/m/Y à H:i') ?>
        </p>
    <?php endif; ?>
</div>
