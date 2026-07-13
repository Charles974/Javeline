<?php
// Partial réutilisable : tableau des comptes utilisateurs.
// Attendu : $utilisateurs (array, sans les hash de mots de passe)
?>
<?php if (empty($utilisateurs)): ?>
    <p class="liste-vide">Aucun compte enregistré.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-triable" aria-label="Liste des comptes utilisateurs">
            <thead>
                <tr>
                    <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par identifiant">Identifiant</th>
                    <th scope="col" data-tri="texte" tabindex="0" role="button" aria-label="Trier par profil">Profil</th>
                    <th scope="col" data-tri="date" tabindex="0" role="button" aria-label="Trier par date de création">Créé le</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $utilisateur): ?>
                <tr class="ligne-utilisateur"
                    data-id="<?= (int)$utilisateur['id'] ?>"
                    data-identifiant="<?= htmlspecialchars($utilisateur['identifiant']) ?>"
                    data-role="<?= htmlspecialchars($utilisateur['role']) ?>"
                    role="button"
                    tabindex="0"
                    aria-label="Sélectionner le compte <?= htmlspecialchars($utilisateur['identifiant']) ?>">
                    <td>
                        <?= htmlspecialchars($utilisateur['identifiant']) ?>
                        <?php if ((int)$utilisateur['id'] === Auth::id()): ?>
                            <span class="badge bg-secondary badge-compte-courant">vous</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(Auth::libelleRole($utilisateur['role'])) ?></td>
                    <td data-valeur="<?= htmlspecialchars($utilisateur['created_at']) ?>">
                        <?= date('d/m/Y', strtotime($utilisateur['created_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
