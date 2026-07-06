<div class="page-header mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-0">Disciplines</h1>
        <p class="text-muted mb-0 small">Table de référence — lecture seule</p>
    </div>
    <a href="<?= APP_URL ?>/" class="btn btn-outline-secondary btn-sm" aria-label="Retour à l'accueil">
        ← Accueil
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card liste-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-titre mb-0">Disciplines disponibles</h2>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary"><?= count($disciplines) ?></span>
                    <a href="<?= APP_URL ?>/disciplines/imprimer"
                       target="_blank"
                       class="btn btn-sm btn-outline-primary"
                       aria-label="Imprimer la liste des catégories">
                        Imprimer la liste
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($disciplines)): ?>
                    <p class="liste-vide-sm">Aucune discipline enregistrée.</p>
                <?php else: ?>
                    <table class="table table-sm table-hover mb-0" aria-label="Liste des disciplines">
                        <thead>
                            <tr>
                                <th scope="col" class="ps-3">Code</th>
                                <th scope="col">Libellé (FR)</th>
                                <th scope="col">Libellé (EN)</th>
                                <th scope="col">Qualif F1</th>
                                <th scope="col">Qualif F2</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($disciplines as $d): ?>
                            <tr>
                                <td class="ps-3 fw-semibold"><?= (int)$d['code'] ?></td>
                                <td><?= htmlspecialchars($d['libelle_fr']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($d['libelle_en']) ?></td>
                                <td><?= (int)$d['qualif_f1'] ?></td>
                                <td><?= (int)$d['qualif_f2'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
