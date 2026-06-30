<div class="fiche-impression">
    <h1>Catégories de tir — <?= htmlspecialchars(APP_NAME) ?></h1>

    <?php if (empty($categories)): ?>
        <p>Aucune catégorie enregistrée.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Libellé</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $i => $cat): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($cat['libelle']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
