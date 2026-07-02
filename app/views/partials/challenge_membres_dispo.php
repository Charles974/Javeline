<?php
// Partial : membres disponibles (non inscrits au challenge).
// Attendu : $membres (array), $challengeId (int)
?>
<?php if (empty($membres)): ?>
    <p class="liste-vide-sm">Tous les membres sont inscrits.</p>
<?php else: ?>
    <table class="table table-sm table-hover dispo-table table-triable mb-0" aria-label="Membres disponibles">
        <thead>
            <tr>
                <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par nom">Nom</th>
                <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par prénom">Prénom</th>
                <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par licence">Licence</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($membres as $m): ?>
            <tr class="ligne-dispo"
                data-id="<?= (int)$m['id'] ?>"
                data-type="membre"
                data-nom="<?= htmlspecialchars($m['nom']) ?>"
                data-prenom="<?= htmlspecialchars($m['prenom']) ?>"
                data-info="<?= htmlspecialchars($m['numero_licence']) ?>"
                role="button"
                tabindex="0"
                aria-label="Sélectionner <?= htmlspecialchars($m['nom'] . ' ' . $m['prenom']) ?>">
                <td><?= htmlspecialchars($m['nom']) ?></td>
                <td><?= htmlspecialchars($m['prenom']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($m['numero_licence']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
