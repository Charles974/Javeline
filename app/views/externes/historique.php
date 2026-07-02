<div class="page-header mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-0">Historique — <?= htmlspecialchars($externe['nom'] . ' ' . $externe['prenom']) ?></h1>
        <p class="text-muted mb-0 small">Club : <?= htmlspecialchars($externe['club']) ?></p>
    </div>
    <a href="<?= APP_URL ?>/externes" class="btn btn-outline-secondary btn-sm" aria-label="Retour à la liste des tireurs non membres">
        ← Tireurs non membres
    </a>
</div>

<div class="card liste-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-titre">Challenges disputés</h2>
        <span class="badge bg-secondary">
            <?= count($challenges) ?> challenge<?= count($challenges) > 1 ? 's' : '' ?>
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($challenges)): ?>
            <p class="liste-vide">Ce tireur n'a encore participé à aucun challenge.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover membres-table" aria-label="Historique des challenges du tireur">
                    <thead>
                        <tr>
                            <th scope="col">Challenge</th>
                            <th scope="col">Dates</th>
                            <th scope="col">Disciplines</th>
                            <th scope="col">Score</th>
                            <th scope="col">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($challenges as $c): ?>
                        <?php
                            $debut = date('d/m/Y', strtotime($c['date_debut']));
                            $fin   = date('d/m/Y', strtotime($c['date_fin']));
                        ?>
                        <tr class="ligne-membre"
                            role="button"
                            tabindex="0"
                            data-href="<?= APP_URL ?>/challenges/<?= (int)$c['id'] ?>/resume"
                            aria-label="Voir le challenge <?= htmlspecialchars($c['libelle']) ?>">
                            <td><?= htmlspecialchars($c['libelle']) ?></td>
                            <td><?= $debut === $fin ? $debut : $debut . ' au ' . $fin ?></td>
                            <td><?= (int)$c['nb_disciplines'] ?></td>
                            <td><?= (int)$c['total_score'] ?></td>
                            <td>
                                <?php if ($c['statut'] === 'archive'): ?>
                                    <span class="badge bg-secondary">Archivé</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Ouvert</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= APP_URL ?>/public/js/membre-historique.js"></script>
