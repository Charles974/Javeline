<div class="fiche-sheet">
    <div class="fiche-tireur-entete">
        <img class="fiche-tireur-logo" src="<?= APP_URL ?>/public/img/images.png" alt="Logo Javeline">
        <div class="fiche-tireur-entete-texte">
            <div class="fiche-tireur-association">Association Javeline</div>
            <div class="fiche-tireur-titre">Fiche tireur non membre</div>
        </div>
    </div>

    <div class="fiche-tireur-corps">
        <table class="fiche-tireur-table">
            <tbody>
                <tr>
                    <th>Nom</th>
                    <td><?= htmlspecialchars($externe['nom']) ?></td>
                </tr>
                <tr>
                    <th>Prénom</th>
                    <td><?= htmlspecialchars($externe['prenom']) ?></td>
                </tr>
                <tr>
                    <th>Club</th>
                    <td><?= htmlspecialchars($externe['club']) ?></td>
                </tr>
                <tr>
                    <th>Tireur étranger</th>
                    <td>
                        <?php if ($externe['etranger']): ?>
                            <span class="fiche-tireur-badge fiche-tireur-badge-ok">Oui</span>
                        <?php else: ?>
                            <span class="fiche-tireur-badge fiche-tireur-badge-off">Non</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Téléphone</th>
                    <td><?= htmlspecialchars($externe['telephone'] ?? '') ?: '—' ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= htmlspecialchars($externe['email'] ?? '') ?: '—' ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="fiche-tireur-pied">
        <span>Édité le <?= date('d/m/Y à H:i') ?></span>
        <span>© <?= date('Y') ?> Javeline — Tous droits réservés</span>
    </div>
</div>
