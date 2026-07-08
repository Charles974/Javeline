<div class="fiche-sheet">
    <div class="fiche-tireur-entete">
        <img class="fiche-tireur-logo" src="<?= APP_URL ?>/public/img/images.png" alt="Logo Javeline">
        <div class="fiche-tireur-entete-texte">
            <div class="fiche-tireur-association">Association Javeline</div>
            <div class="fiche-tireur-titre">Fiche membre</div>
        </div>
    </div>

    <div class="fiche-tireur-corps">
        <table class="fiche-tireur-table">
            <tbody>
                <tr>
                    <th>Nom</th>
                    <td><?= htmlspecialchars($membre['nom']) ?></td>
                </tr>
                <tr>
                    <th>Prénom</th>
                    <td><?= htmlspecialchars($membre['prenom']) ?></td>
                </tr>
                <tr>
                    <th>Date de naissance</th>
                    <td><?= date('d/m/Y', strtotime($membre['date_naissance'])) ?></td>
                </tr>
                <tr>
                    <th>Lieu de naissance</th>
                    <td><?= htmlspecialchars($membre['lieu_naissance']) ?></td>
                </tr>
                <tr>
                    <th>N° de licence</th>
                    <td><?= htmlspecialchars($membre['numero_licence']) ?></td>
                </tr>
                <tr>
                    <th>Certificat médical</th>
                    <td>
                        <?php if ($membre['certificat_medical']): ?>
                            <span class="fiche-tireur-badge fiche-tireur-badge-ok">Valide</span>
                        <?php else: ?>
                            <span class="fiche-tireur-badge fiche-tireur-badge-off">Non fourni</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Téléphone</th>
                    <td><?= htmlspecialchars($membre['telephone']) ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= htmlspecialchars($membre['email']) ?></td>
                </tr>
                <tr>
                    <th>Adresse</th>
                    <td>
                        <?= htmlspecialchars($membre['adresse1']) ?>
                        <?php if ($membre['adresse2']): ?>
                            <br><?= htmlspecialchars($membre['adresse2']) ?>
                        <?php endif; ?>
                        <br><?= htmlspecialchars($membre['code_postal']) ?> <?= htmlspecialchars($membre['ville']) ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="fiche-tireur-pied">
        <span>Édité le <?= date('d/m/Y à H:i') ?></span>
        <span>© <?= date('Y') ?> Javeline — Tous droits réservés</span>
    </div>
</div>
