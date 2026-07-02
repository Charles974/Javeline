<div class="fiche">
    <div class="fiche-entete">
        <h1>Association Javeline</h1>
        <h2>Planning des matchs — <?= htmlspecialchars($challenge['libelle']) ?></h2>
        <p>
            <?php
                $debut = date('d/m/Y', strtotime($challenge['date_debut']));
                $fin   = date('d/m/Y', strtotime($challenge['date_fin']));
                echo $debut === $fin ? $debut : 'Du ' . $debut . ' au ' . $fin;
            ?>
        </p>
    </div>

    <?php if (empty($planning)): ?>
        <p>Aucun match planifié pour le moment.</p>
    <?php else: ?>
        <table class="fiche-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Horaire</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Club</th>
                    <th>Discipline</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($planning as $m): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($m['date_match'])) ?></td>
                    <td><?= substr($m['heure_debut'], 0, 5) ?> – <?= substr($m['heure_fin'], 0, 5) ?></td>
                    <td><?= htmlspecialchars($m['nom']) ?></td>
                    <td><?= htmlspecialchars($m['prenom']) ?></td>
                    <td><?= htmlspecialchars($m['club']) ?></td>
                    <td><?= (int)$m['discipline_code'] ?> — <?= htmlspecialchars($m['discipline_fr']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="fiche-pied">
            <?= count($planning) ?> match<?= count($planning) > 1 ? 's' : '' ?> planifié<?= count($planning) > 1 ? 's' : '' ?> —
            Édité le <?= date('d/m/Y à H:i') ?>
        </p>
    <?php endif; ?>
</div>
