<?php
    $debut  = date('d/m/Y', strtotime($challenge['date_debut']));
    $fin    = date('d/m/Y', strtotime($challenge['date_fin']));
    $label  = $debut === $fin ? $debut : 'Du ' . $debut . ' au ' . $fin;
    $genere = date('d/m/Y à H:i');

    // Couleur d'en-tete approximative par discipline (a affiner si besoin).
    $couleursDiscipline = [
        400 => ['bg' => '#E2231A', 'texte' => '#FFFFFF'],
        401 => ['bg' => '#F4801F', 'texte' => '#FFFFFF'],
        402 => ['bg' => '#F7A81B', 'texte' => '#FFFFFF'],
        403 => ['bg' => '#FFF200', 'texte' => '#000000'],
        404 => ['bg' => '#7B2048', 'texte' => '#FFFFFF'],
        405 => ['bg' => '#E619E6', 'texte' => '#FFFFFF'],
        406 => ['bg' => '#F3A6D6', 'texte' => '#000000'],
        407 => ['bg' => '#F2C48D', 'texte' => '#000000'],
        408 => ['bg' => '#A6A6A6', 'texte' => '#FFFFFF'],
        409 => ['bg' => '#1E8A73', 'texte' => '#FFFFFF'],
        410 => ['bg' => '#9ACD32', 'texte' => '#000000'],
        411 => ['bg' => '#7CB342', 'texte' => '#FFFFFF'],
        412 => ['bg' => '#6B8E23', 'texte' => '#FFFFFF'],
        413 => ['bg' => '#556B2F', 'texte' => '#FFFFFF'],
    ];

    // Initiales du prenom (ex: "Hans Peter" -> "HP", "Marc" -> "M").
    $initiales = function (string $prenom): string {
        $mots = preg_split('/[\s\-]+/', trim($prenom));
        $init = '';
        foreach ($mots as $mot) {
            if ($mot !== '') {
                $init .= mb_strtoupper(mb_substr($mot, 0, 1));
            }
        }
        return $init;
    };

    // "NOM Initiales (Club)" — club = JAV pour les membres.
    $nomAffiche = function (array $t) use ($initiales): string {
        $nom  = mb_strtoupper($t['nom']);
        $init = $initiales($t['prenom']);
        return trim($nom . ' ' . $init) . ' (' . $t['club'] . ')';
    };
?>
<div class="cld-page">

    <?php if ($disciplineCode): ?>
        <p class="classements-filtre">
            Filtré sur la discipline <?= (int)$disciplineCode ?>
            <?php if (!empty($groupes)): ?>
                — <?= htmlspecialchars(array_values($groupes)[0]['libelle_fr']) ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <?php if (empty($groupes)): ?>
        <p class="classements-vide">Aucun score enregistré pour cette sélection.</p>
    <?php else: ?>

        <?php foreach ($groupes as $code => $groupe):
            $couleur    = $couleursDiscipline[$code] ?? ['bg' => '#888888', 'texte' => '#FFFFFF'];
            $nbTireurs  = count($groupe['tireurs']);
            $nbDefects  = count($groupe['defects']);
        ?>
        <div class="cld-groupe">
            <span class="cld-code-badge"><?= (int)$code ?></span>
            <table class="cld-table">
                <thead>
                    <tr>
                        <th rowspan="3" class="cld-col-classement"><span>CLASSEMENT</span></th>
                        <th rowspan="2" class="cld-col-club-dates">
                            <span class="cld-titre-club"><?= htmlspecialchars($challenge['libelle']) ?></span><br>
                            <span class="cld-titre-dates"><?= htmlspecialchars($label) ?></span>
                        </th>
                        <th colspan="5" rowspan="2" class="cld-discipline-titre"
                            style="background-color: <?= $couleur['bg'] ?>; color: <?= $couleur['texte'] ?>;">
                            <span class="cld-discipline-fr"><?= htmlspecialchars($groupe['libelle_fr']) ?></span><br>
                            <span class="cld-discipline-en"><?= htmlspecialchars($groupe['libelle_en']) ?></span>
                        </th>
                    </tr>
                    <tr></tr>
                    <tr>
                        <th class="cld-col-nom-header">NOM / NAME / CLUB</th>
                        <th class="cld-col-animal"><span class="cld-animal-icone" aria-hidden="true"></span>CHICKENS</th>
                        <th class="cld-col-animal"><span class="cld-animal-icone" aria-hidden="true"></span>PIGS</th>
                        <th class="cld-col-animal"><span class="cld-animal-icone" aria-hidden="true"></span>TURKEYS</th>
                        <th class="cld-col-animal"><span class="cld-animal-icone" aria-hidden="true"></span>RAMS</th>
                        <th class="cld-col-total-header">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupe['tireurs'] as $t): ?>
                    <tr class="<?= $t['exaequo'] ? 'cld-ligne-exaequo' : '' ?>">
                        <td class="cld-col-rang">
                            <?php if ($t['medaille']): ?>
                                <span class="medaille medaille-<?= $t['medaille'] ?>" title="<?= ucfirst($t['medaille']) ?>">
                                    <?= match($t['medaille']) { 'or' => 'Or', 'argent' => 'Ar', 'bronze' => 'Br' } ?>
                                </span>
                            <?php else: ?>
                                <?= (int)$t['rang'] ?>
                            <?php endif; ?>
                            <?php if ($t['exaequo']): ?>
                                <span class="exaequo-label">Ex-æquo</span>
                            <?php endif; ?>
                        </td>
                        <td class="cld-col-nom"><?= htmlspecialchars($nomAffiche($t)) ?></td>
                        <td class="cld-col-animal-val"><?= (int)$t['poulets'] ?></td>
                        <td class="cld-col-animal-val"><?= (int)$t['cochons'] ?></td>
                        <td class="cld-col-animal-val"><?= (int)$t['dindons'] ?></td>
                        <td class="cld-col-animal-val"><?= (int)$t['mouflons'] ?></td>
                        <td class="cld-col-total"><?= (int)$t['total'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php foreach ($groupe['defects'] as $d): ?>
                    <tr class="cld-ligne-defect">
                        <td class="cld-col-rang"><?= (int)$d['rang'] ?></td>
                        <td colspan="6" class="cld-defect-cell">DEFECT</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ($nbTireurs === 0 && $nbDefects === 0): ?>
                    <tr>
                        <td colspan="7" class="cld-vide">Aucun inscrit.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <div class="classements-pied">
        Généré le <?= $genere ?>
    </div>

</div>
