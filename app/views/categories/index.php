<div class="page-header mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-0">Catégories de tir</h1>
        <p class="text-muted mb-0 small">Table de référence — lecture seule</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/categories/imprimer"
           target="_blank"
           class="btn btn-outline-secondary btn-sm"
           aria-label="Imprimer la liste des catégories">
            Imprimer
        </a>
        <a href="<?= APP_URL ?>/" class="btn btn-outline-secondary btn-sm" aria-label="Retour à l'accueil">
            ← Accueil
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card liste-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-titre mb-0">Catégories</h2>
                <span class="badge bg-secondary"><?= count($categories) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($categories)): ?>
                    <p class="liste-vide-sm">Aucune catégorie enregistrée.</p>
                <?php else: ?>
                    <table class="table table-sm table-hover mb-0" aria-label="Liste des catégories de tir">
                        <thead>
                            <tr>
                                <th scope="col" class="ps-3">#</th>
                                <th scope="col">Libellé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $i => $cat): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($cat['libelle']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
