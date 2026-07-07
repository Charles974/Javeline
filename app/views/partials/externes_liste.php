<?php
// Partial réutilisable : tableau des tireurs externes.
// Attendu : $externes (array)
?>
<?php if (empty($externes)): ?>
    <p class="liste-vide">Aucun tireur non membre enregistré.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover membres-table table-triable" aria-label="Liste des tireurs non membres">
            <thead>
                <tr>
                    <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par nom">Nom</th>
                    <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par prénom">Prénom</th>
                    <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par club">Club</th>
                    <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par téléphone">Téléphone</th>
                    <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par email">Email</th>
                    <th scope="col" class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($externes as $externe): ?>
                <tr class="ligne-externe"
                    data-id="<?= (int)$externe['id'] ?>"
                    role="button"
                    tabindex="0"
                    aria-label="Sélectionner <?= htmlspecialchars($externe['nom'] . ' ' . $externe['prenom']) ?>">
                    <td><?= htmlspecialchars($externe['nom']) ?></td>
                    <td><?= htmlspecialchars($externe['prenom']) ?></td>
                    <td><?= htmlspecialchars($externe['club']) ?></td>
                    <td><?= $externe['telephone'] ? htmlspecialchars(format_telephone_fr($externe['telephone'])) : '—' ?></td>
                    <td><?= htmlspecialchars($externe['email'] ?? '—') ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/externes/historique/<?= (int)$externe['id'] ?>"
                           class="btn btn-sm btn-outline-secondary"
                           aria-label="Historique de <?= htmlspecialchars($externe['nom'] . ' ' . $externe['prenom']) ?>"
                           onclick="event.stopPropagation()">
                            Historique
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
