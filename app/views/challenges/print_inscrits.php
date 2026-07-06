<div class="liste-impression">
    <div class="liste-impression-entete">
        <h1>Association Javeline</h1>
        <h2>Liste des matchs — <?= htmlspecialchars($challenge['libelle']) ?></h2>
        <?php
            $debut = date('d/m/Y', strtotime($challenge['date_debut']));
            $fin   = date('d/m/Y', strtotime($challenge['date_fin']));
        ?>
        <p class="liste-impression-sous-titre">
            <?= $debut === $fin ? $debut : 'Du ' . $debut . ' au ' . $fin ?>
        </p>
        <span class="liste-impression-date"><?= date('d/m/Y') ?></span>
    </div>

    <?php if (empty($inscrits)): ?>
        <p class="liste-impression-vide">Aucun tireur inscrit.</p>
    <?php else: ?>
        <table class="liste-impression-table liste-impression-table-matchs">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Club</th>
                    <th class="liste-impression-col-cat">Cat</th>
                    <th>Catégorie</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inscrits as $inscrit): ?>
                <?php
                    $club = $inscrit['tireur_type'] === 'membre' ? 'Javeline' : $inscrit['club'];
                    $libelleDiscipline = $inscrit['etranger']
                        ? $inscrit['discipline_en']
                        : $inscrit['discipline_fr'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($inscrit['nom']) ?></td>
                    <td><?= htmlspecialchars($inscrit['prenom']) ?></td>
                    <td class="liste-impression-col-club"><?= htmlspecialchars($club) ?></td>
                    <td class="liste-impression-col-cat"><?= (int)$inscrit['discipline_code'] ?></td>
                    <td><?= htmlspecialchars($libelleDiscipline) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="liste-impression-pied">
            Nombre de lignes : <strong><?= count($inscrits) ?></strong> —
            Édité le <?= date('d/m/Y à H:i') ?>
        </p>
    <?php endif; ?>
</div>
