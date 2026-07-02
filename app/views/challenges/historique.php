<div class="page-header mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-0">Historique des challenges</h1>
        <p class="text-muted mb-0 small"><?= count($challenges) ?> challenge<?= count($challenges) > 1 ? 's' : '' ?> enregistré<?= count($challenges) > 1 ? 's' : '' ?></p>
    </div>
    <a href="<?= APP_URL ?>/" class="btn btn-outline-secondary btn-sm" aria-label="Retour à l'accueil">
        ← Accueil
    </a>
</div>

<?php if (empty($challenges)): ?>
    <p class="liste-vide-sm">Aucun challenge enregistré.</p>
<?php else: ?>
    <div class="card liste-card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" aria-label="Historique des challenges">
                <thead>
                    <tr>
                        <th scope="col" class="ps-3">Challenge</th>
                        <th scope="col">Dates</th>
                        <th scope="col" class="text-center">Membres</th>
                        <th scope="col" class="text-center">Non membres</th>
                        <th scope="col" class="text-center">Total matchs</th>
                        <th scope="col" class="text-center">Statut</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($challenges as $c): ?>
                    <?php
                        $debut  = date('d/m/Y', strtotime($c['date_debut']));
                        $fin    = date('d/m/Y', strtotime($c['date_fin']));
                        $dates  = $debut === $fin ? $debut : $debut . ' → ' . $fin;
                        $archive = $c['statut'] === 'archive';
                    ?>
                    <tr>
                        <td class="ps-3 fw-semibold"><?= htmlspecialchars($c['libelle']) ?></td>
                        <td class="text-muted small"><?= $dates ?></td>
                        <td class="text-center"><?= (int)$c['nb_membres'] ?></td>
                        <td class="text-center"><?= (int)$c['nb_externes'] ?></td>
                        <td class="text-center fw-semibold"><?= (int)$c['nb_matchs'] ?></td>
                        <td class="text-center">
                            <?php if ($archive): ?>
                                <span class="badge bg-secondary">Archivé</span>
                            <?php else: ?>
                                <span class="badge bg-success">Ouvert</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-3">
                            <a href="<?= APP_URL ?>/challenges/<?= (int)$c['id'] ?>/resume"
                               class="btn btn-sm btn-outline-primary"
                               aria-label="Voir le résumé de <?= htmlspecialchars($c['libelle']) ?>">
                                Résumé
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
