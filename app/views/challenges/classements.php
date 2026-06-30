<?php
    $debut = date('d/m/Y', strtotime($challenge['date_debut']));
    $fin   = date('d/m/Y', strtotime($challenge['date_fin']));
    $label = $debut === $fin ? $debut : 'Du ' . $debut . ' au ' . $fin;
    $genere = date('d/m/Y à H:i');
?>
<div class="classements-page">

    <div class="classements-entete">
        <h1><?= htmlspecialchars($challenge['libelle']) ?></h1>
        <p class="classements-dates"><?= $label ?></p>
        <?php if ($disciplineCode): ?>
            <p class="classements-filtre">
                Filtré sur la discipline <?= (int)$disciplineCode ?>
                <?php if (!empty($groupes)): ?>
                    — <?= htmlspecialchars(array_values($groupes)[0]['libelle']) ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if (empty($groupes)): ?>
        <p class="classements-vide">Aucun score enregistré pour cette sélection.</p>
    <?php else: ?>

        <?php foreach ($groupes as $code => $groupe): ?>
        <div class="classements-discipline">

            <h2 class="classements-discipline-titre">
                <span class="classements-code"><?= (int)$code ?></span>
                <?= htmlspecialchars($groupe['libelle']) ?>
            </h2>

            <table class="classements-table">
                <thead>
                    <tr>
                        <th class="col-rang">Cl.</th>
                        <th class="col-nom">Nom</th>
                        <th class="col-prenom">Prénom</th>
                        <th class="col-club">Club</th>
                        <th class="col-cible">P</th>
                        <th class="col-cible">C</th>
                        <th class="col-cible">D</th>
                        <th class="col-cible">M</th>
                        <th class="col-total">Total</th>
                        <th class="col-medaille">Médaille</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupe['tireurs'] as $t): ?>
                    <tr class="<?= $t['exaequo'] ? 'ligne-exaequo' : '' ?>">
                        <td class="col-rang"><?= (int)$t['rang'] ?></td>
                        <td class="col-nom"><?= htmlspecialchars($t['nom']) ?></td>
                        <td class="col-prenom"><?= htmlspecialchars($t['prenom']) ?></td>
                        <td class="col-club"><?= $t['club'] ? htmlspecialchars($t['club']) : '—' ?></td>
                        <td class="col-cible"><?= (int)$t['poulets'] ?></td>
                        <td class="col-cible"><?= (int)$t['cochons'] ?></td>
                        <td class="col-cible"><?= (int)$t['dindons'] ?></td>
                        <td class="col-cible"><?= (int)$t['mouflons'] ?></td>
                        <td class="col-total"><?= (int)$t['total'] ?></td>
                        <td class="col-medaille">
                            <?php if ($t['medaille']): ?>
                                <span class="medaille medaille-<?= $t['medaille'] ?>"
                                      title="<?= ucfirst($t['medaille']) ?>">
                                    <?= match($t['medaille']) {
                                        'or'     => 'Or',
                                        'argent' => 'Ar',
                                        'bronze' => 'Br',
                                    } ?>
                                </span>
                                <?php if ($t['exaequo']): ?>
                                    <span class="exaequo-label">Ex-æquo</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <div class="classements-pied">
        Généré le <?= $genere ?>
    </div>

</div>
