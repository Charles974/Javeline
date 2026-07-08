<?php
// Vue imprimable : liste des categories (disciplines).
// Style repris des fiches membres, en format paysage.
// Attendu : $disciplines (array)
?>
<div class="fiche-sheet">
    <div class="fiche-tireur-entete">
        <img class="fiche-tireur-logo" src="<?= APP_URL ?>/public/img/images.png" alt="Logo Javeline">
        <div class="fiche-tireur-entete-texte">
            <div class="fiche-tireur-association">Association Javeline</div>
            <div class="fiche-tireur-titre">Liste des catégories</div>
            <div class="fiche-tireur-sous-titre">Table de référence des disciplines</div>
        </div>
    </div>

    <div class="fiche-tireur-corps">
        <?php if (empty($disciplines)): ?>
            <p class="fiche-liste-vide">Aucune catégorie enregistrée.</p>
        <?php else: ?>
            <table class="fiche-liste-table" aria-label="Liste des catégories">
                <thead>
                    <tr>
                        <th class="fiche-liste-col-code" scope="col">Numéro de catégorie</th>
                        <th scope="col">Désignation catégorie</th>
                        <th scope="col">Désignation anglaise</th>
                        <th class="fiche-liste-col-qualif" scope="col">Qualif F1</th>
                        <th class="fiche-liste-col-qualif" scope="col">Qualif F2</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($disciplines as $d): ?>
                    <tr>
                        <td class="fiche-liste-col-code"><?= (int)$d['code'] ?></td>
                        <td><?= htmlspecialchars($d['libelle_fr']) ?></td>
                        <td class="fiche-liste-col-en"><?= htmlspecialchars($d['libelle_en']) ?></td>
                        <td class="fiche-liste-col-qualif"><?= (int)$d['qualif_f1'] ?></td>
                        <td class="fiche-liste-col-qualif"><?= (int)$d['qualif_f2'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="fiche-tireur-pied">
        <span>Nombre de catégories : <?= count($disciplines) ?> — Édité le <?= date('d/m/Y à H:i') ?></span>
        <span>© <?= date('Y') ?> Javeline — Tous droits réservés</span>
    </div>
</div>
