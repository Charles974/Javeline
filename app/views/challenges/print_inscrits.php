<div class="fiche">
    <div class="fiche-entete">
        <h1>Association Javeline</h1>
        <h2>Liste des inscrits — <?= htmlspecialchars($challenge['libelle']) ?></h2>
        <p>
            <?php
                $debut = date('d/m/Y', strtotime($challenge['date_debut']));
                $fin   = date('d/m/Y', strtotime($challenge['date_fin']));
                echo $debut === $fin ? $debut : 'Du ' . $debut . ' au ' . $fin;
            ?>
        </p>
    </div>

    <?php if (empty($inscrits)): ?>
        <p>Aucun tireur inscrit.</p>
    <?php else: ?>
        <table class="fiche-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Type</th>
                    <th>Discipline</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inscrits as $inscrit): ?>
                <tr>
                    <td><?= htmlspecialchars($inscrit['nom']) ?></td>
                    <td><?= htmlspecialchars($inscrit['prenom']) ?></td>
                    <td><?= $inscrit['tireur_type'] === 'membre' ? 'Membre' : 'Externe' ?></td>
                    <?php
                        $libelleDiscipline = $inscrit['etranger']
                            ? $inscrit['discipline_en']
                            : $inscrit['discipline_fr'];
                    ?>
                    <td><?= (int)$inscrit['discipline_code'] ?> — <?= htmlspecialchars($libelleDiscipline) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="fiche-pied">
            <?= count($inscrits) ?> inscription<?= count($inscrits) > 1 ? 's' : '' ?> —
            Édité le <?= date('d/m/Y à H:i') ?>
        </p>
    <?php endif; ?>
</div>
