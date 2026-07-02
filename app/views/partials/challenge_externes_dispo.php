<?php
// Partial : tireurs externes disponibles (non inscrits au challenge).
// Attendu : $externes (array), $challengeId (int)
?>
<?php if (empty($externes)): ?>
    <p class="liste-vide-sm">Tous les tireurs non membres sont inscrits.</p>
<?php else: ?>
    <table class="table table-sm table-hover dispo-table table-triable mb-0" aria-label="Tireurs non membres disponibles">
        <thead>
            <tr>
                <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par nom">Nom</th>
                <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par prénom">Prénom</th>
                <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par club">Club</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($externes as $e): ?>
            <tr class="ligne-dispo"
                data-id="<?= (int)$e['id'] ?>"
                data-type="externe"
                data-nom="<?= htmlspecialchars($e['nom']) ?>"
                data-prenom="<?= htmlspecialchars($e['prenom']) ?>"
                data-info="<?= htmlspecialchars($e['club']) ?>"
                role="button"
                tabindex="0"
                aria-label="Sélectionner <?= htmlspecialchars($e['nom'] . ' ' . $e['prenom']) ?>">
                <td><?= htmlspecialchars($e['nom']) ?></td>
                <td><?= htmlspecialchars($e['prenom']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($e['club']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
