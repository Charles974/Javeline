<?php
// Partial réutilisable : tableau des membres.
// Attendu : $membres (array)
?>
<?php if (empty($membres)): ?>
    <p class="liste-vide">Aucun membre enregistré.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover membres-table table-triable" aria-label="Liste des tireurs membres">
            <thead>
                <tr>
                    <th scope="col" class="col-nom" data-tri="texte" tabindex="0" role="button" aria-label="Trier par nom">Nom</th>
                    <th scope="col" class="col-prenom" data-tri="texte" tabindex="0" role="button" aria-label="Trier par prénom">Prénom</th>
                    <th scope="col" class="col-naissance" data-tri="date" tabindex="0" role="button" aria-label="Trier par date de naissance">Date de naissance</th>
                    <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par téléphone">Téléphone</th>
                    <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par email">Email</th>
                    <th scope="col" class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($membres as $membre): ?>
                <tr class="ligne-membre"
                    data-id="<?= (int)$membre['id'] ?>"
                    data-nom="<?= htmlspecialchars($membre['nom']) ?>"
                    data-prenom="<?= htmlspecialchars($membre['prenom']) ?>"
                    data-naissance="<?= htmlspecialchars($membre['date_naissance']) ?>"
                    data-telephone="<?= htmlspecialchars($membre['telephone']) ?>"
                    data-email="<?= htmlspecialchars($membre['email']) ?>"
                    role="button"
                    tabindex="0"
                    aria-label="Sélectionner <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>">
                    <td><?= htmlspecialchars($membre['nom']) ?></td>
                    <td><?= htmlspecialchars($membre['prenom']) ?></td>
                    <td data-valeur="<?= htmlspecialchars($membre['date_naissance']) ?>"><?= date('d/m/Y', strtotime($membre['date_naissance'])) ?></td>
                    <td><?= htmlspecialchars($membre['telephone']) ?></td>
                    <td><?= htmlspecialchars($membre['email']) ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/membres/historique/<?= (int)$membre['id'] ?>"
                           class="btn btn-sm btn-outline-secondary"
                           aria-label="Historique de <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>"
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
