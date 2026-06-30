<?php
// Partial : liste des tireurs inscrits au challenge (une ligne par discipline).
// Attendu : $inscrits (array), $challengeId (int)

// Calcule la famille de discipline à partir du code
function familleDiscipline(int $code): string
{
    if ($code >= 400 && $code <= 403) return 'gros-calibre';
    if ($code >= 404 && $code <= 407) return 'petit-calibre';
    if ($code >= 408 && $code <= 409) return 'field';
    if ($code >= 410 && $code <= 411) return 'carabine-pc';
    if ($code >= 412 && $code <= 413) return 'carabine-gc';
    return '';
}
?>
<?php if (empty($inscrits)): ?>
    <p class="liste-vide-sm">Aucun tireur inscrit pour le moment.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-hover inscrits-table mb-0" id="table-inscrits" aria-label="Tireurs inscrits au challenge">
            <thead>
                <tr>
                    <th scope="col" class="col-sortable" data-col="0" aria-sort="none">
                        Nom <span class="sort-icone" aria-hidden="true"></span>
                    </th>
                    <th scope="col" class="col-sortable" data-col="1" aria-sort="none">
                        Prénom <span class="sort-icone" aria-hidden="true"></span>
                    </th>
                    <th scope="col" class="col-sortable" data-col="2" aria-sort="none">
                        Type <span class="sort-icone" aria-hidden="true"></span>
                    </th>
                    <th scope="col" class="col-sortable" data-col="3" aria-sort="none">
                        Discipline <span class="sort-icone" aria-hidden="true"></span>
                    </th>
                    <th scope="col" class="col-actions">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $tireurPrecedent = null;
                foreach ($inscrits as $inscrit):
                    $cleUnique    = $inscrit['tireur_type'] . '-' . $inscrit['tireur_id'];
                    $premiereLigne = ($cleUnique !== $tireurPrecedent);
                    $tireurPrecedent = $cleUnique;
                    $famille = familleDiscipline((int)$inscrit['discipline_code']);
                ?>
                <tr class="ligne-inscrit <?= $premiereLigne ? 'premiere-ligne-tireur' : '' ?>"
                    data-id="<?= (int)$inscrit['id'] ?>"
                    data-tireur-type="<?= htmlspecialchars($inscrit['tireur_type']) ?>"
                    data-tireur-id="<?= (int)$inscrit['tireur_id'] ?>"
                    data-nom="<?= htmlspecialchars($inscrit['nom']) ?>"
                    data-prenom="<?= htmlspecialchars($inscrit['prenom']) ?>"
                    data-info="<?= htmlspecialchars($inscrit['club'] ?? '') ?>"
                    data-discipline-code="<?= (int)$inscrit['discipline_code'] ?>"
                    data-famille="<?= $famille ?>"
                    role="button"
                    tabindex="0"
                    aria-label="<?= htmlspecialchars($inscrit['nom'] . ' ' . $inscrit['prenom']) ?> — <?= htmlspecialchars($inscrit['discipline_fr']) ?>">
                    <td><?= htmlspecialchars($inscrit['nom']) ?></td>
                    <td><?= htmlspecialchars($inscrit['prenom']) ?></td>
                    <td>
                        <span class="badge-type badge-type-<?= $inscrit['tireur_type'] ?>">
                            <?= $inscrit['tireur_type'] === 'membre' ? 'M' : 'E' ?>
                        </span>
                    </td>
                    <td>
                        <span class="discipline-code"><?= (int)$inscrit['discipline_code'] ?></span>
                        <?= htmlspecialchars($inscrit['discipline_fr']) ?>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger btn-supprimer-inscription"
                                data-id="<?= (int)$inscrit['id'] ?>"
                                aria-label="Supprimer l'inscription de <?= htmlspecialchars($inscrit['nom']) ?> pour <?= htmlspecialchars($inscrit['discipline_fr']) ?>"
                                onclick="event.stopPropagation()">
                            &times;
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
