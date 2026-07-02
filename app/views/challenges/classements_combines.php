<?php
    $debut  = date('d/m/Y', strtotime($challenge['date_debut']));
    $fin    = date('d/m/Y', strtotime($challenge['date_fin']));
    $label  = $debut === $fin ? $debut : 'Du ' . $debut . ' au ' . $fin;
    $genere = date('d/m/Y à H:i');
?>
<div class="classements-page">

    <div class="classements-entete">
        <h1><?= htmlspecialchars($challenge['libelle']) ?></h1>
        <p class="classements-dates"><?= $label ?></p>
        <p class="classements-filtre">Classements combinés (aggregates)</p>
    </div>

    <?php if (empty($groupes)): ?>
        <p class="classements-vide">Aucun score enregistré pour les combinés de ce challenge.</p>
    <?php else: ?>

        <?php foreach ($groupes as $groupe): ?>
        <div class="classements-discipline">

            <h2 class="classements-discipline-titre">
                <?= htmlspecialchars($groupe['libelle_fr']) ?>
                <span class="classements-libelle-en">(<?= htmlspecialchars($groupe['libelle_en']) ?>)</span>
                <span class="classements-code">— <?= implode('+', $groupe['codes']) ?></span>
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
                        <th class="col-epreuves">Épreuves</th>
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
                        <td class="col-epreuves"><?= (int)$t['nb_disciplines'] ?>/<?= $groupe['nb_epreuves'] ?></td>
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
