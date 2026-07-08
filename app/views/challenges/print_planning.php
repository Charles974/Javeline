<?php
// Reprend exactement la logique de construction de la grille de l'ecran
// "Plan de tir" (app/views/challenges/plan_de_tir.php), pour que l'export
// PDF reflete fidelement l'interface. Meme source de donnees ($grille).
//
// IMPORTANT — pagination :
// La grille d'un jour compte pres de 58 creneaux (09h00 -> 18h30, pas de
// 10 min). Une seule <table> aussi haute doit se fragmenter sur plusieurs
// pages. La fragmentation automatique d'un grand tableau par Paged.js, quand
// elle est combinee a un saut de page force entre les jours, provoque des
// pages blanches en cascade (le tableau ne demarrait plus en premiere page
// et des dizaines de pages vides s'ajoutaient a la fin).
//
// Pour fiabiliser l'export, on decoupe nous-memes chaque jour en blocs d'un
// nombre fixe de lignes. Chaque bloc est un tableau autonome (avec son propre
// en-tete de colonnes), plus court qu'une page, et marque comme insecable :
// Paged.js n'a alors que des blocs simples a placer, ce qui supprime la
// cascade de pages blanches.

$dateDebut = date('d/m/Y', strtotime($challenge['date_debut']));
$dateFin   = date('d/m/Y', strtotime($challenge['date_fin']));

// Nombre maximal de creneaux par bloc imprime. Choisi pour qu'un bloc
// (en-tete de colonnes + lignes) tienne largement dans une page A4 paysage.
$lignesParBloc = 18;

// Disciplines effectivement inscrites au challenge (colonnes de la grille).
$disciplines = [];
foreach ($grille as $r) {
    $code = (int) $r['discipline_code'];
    if (!isset($disciplines[$code])) {
        $disciplines[$code] = ['fr' => $r['discipline_fr'], 'en' => $r['discipline_en']];
    }
}
ksort($disciplines);
$nbCols = count($disciplines);

// Grille des matchs planifies : [jour][code][heure_debut] = ligne.
$matchsParJour = [];
foreach ($grille as $r) {
    if ($r['date_match'] && $r['heure_debut']) {
        $code  = (int) $r['discipline_code'];
        $heure = substr($r['heure_debut'], 0, 5);
        $matchsParJour[$r['date_match']][$code][$heure] = $r;
    }
}

// Blocs horaires libres, par jour.
$blocsParJour = [];
foreach ($blocsHoraires as $b) {
    $blocsParJour[$b['jour']][] = $b;
}

// Creneaux fixes de 10 minutes, 09h00 -> 18h30 (memes bornes que l'ecran).
$creneaux = [];
$curseur  = strtotime('09:00');
$limite   = strtotime('18:30');
while ($curseur <= $limite) {
    $creneaux[] = date('H:i', $curseur);
    $curseur += 600;
}
?>

<div class="fiche fiche-planning">
    <div class="fiche-entete">
        <h1>Association Javeline</h1>
        <h2>Plan de tir — <?= htmlspecialchars($challenge['libelle']) ?></h2>
        <p class="planning-dates-challenge">
            <?= $dateDebut === $dateFin ? 'Le ' . $dateDebut : 'Du ' . $dateDebut . ' au ' . $dateFin ?>
        </p>
    </div>

    <?php if (empty($disciplines)): ?>
        <p>Aucun tireur inscrit pour le moment.</p>
    <?php else: ?>
        <?php
        $premierBloc = true;
        foreach ($jours as $jour):
            $estPremierJour = $jour === $challenge['date_debut'];

            // Blocs horaires (pauses, etc.) etendus a chaque creneau du jour.
            $blocsJour    = $blocsParJour[$jour] ?? [];
            $blocParHeure = [];
            foreach ($blocsJour as $bloc) {
                $db = substr($bloc['heure_debut'], 0, 5);
                $fb = substr($bloc['heure_fin'], 0, 5);
                foreach ($creneaux as $h) {
                    if ($h >= $db && $h <= $fb) {
                        $blocParHeure[$h] = $bloc;
                    }
                }
            }

            // Decoupage des creneaux du jour en blocs de $lignesParBloc lignes.
            $blocsCreneaux = array_chunk($creneaux, $lignesParBloc);
            $nbBlocs       = count($blocsCreneaux);

            foreach ($blocsCreneaux as $indexBloc => $creneauxBloc):
                // Chaque jour demarre sur une nouvelle page (sauf le tout
                // premier bloc, qui suit directement l'en-tete de la fiche).
                $sautPage      = !$premierBloc && $indexBloc === 0;
                $premierBloc   = false;
                $classesBloc   = 'planning-bloc' . ($sautPage ? ' planning-bloc-saut' : '');
        ?>
        <div class="<?= $classesBloc ?>">
            <h3 class="planning-date">
                <?= htmlspecialchars(format_date_fr_complete($jour)) ?><?php if ($nbBlocs > 1): ?> <span class="planning-suite">(<?= $indexBloc + 1 ?>/<?= $nbBlocs ?>)</span><?php endif; ?>
            </h3>
            <table class="planning-table">
                <thead>
                    <tr>
                        <th class="planning-col-horaire">Horaires</th>
                        <?php foreach ($disciplines as $code => $lib): ?>
                        <th><?= (int) $code ?> — <?= htmlspecialchars($lib['fr']) ?> / <?= htmlspecialchars($lib['en']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($creneauxBloc as $heure):
                        $estOuverture = $estPremierJour && $heure >= '09:00' && $heure <= '09:50';
                        $bloc         = $estOuverture ? null : ($blocParHeure[$heure] ?? null);
                        $debutBloc    = $bloc ? substr($bloc['heure_debut'], 0, 5) : null;
                    ?>
                    <tr>
                        <td class="planning-col-horaire"><?= str_replace(':', ' h ', $heure) ?></td>
                        <?php if ($estOuverture): ?>
                            <td class="planning-case-grise" colspan="<?= $nbCols ?>">
                                <?= $heure === '09:00' ? 'Ouverture et préparation du stand' : '' ?>
                            </td>
                        <?php elseif ($bloc): ?>
                            <td class="planning-case-grise" colspan="<?= $nbCols ?>">
                                <?= $heure === $debutBloc ? htmlspecialchars($bloc['libelle']) : '' ?>
                            </td>
                        <?php else: ?>
                            <?php foreach ($disciplines as $code => $lib):
                                $match = $matchsParJour[$jour][$code][$heure] ?? null;
                                $coach = $match ? ($match['coach'] ?: ($match['tireur_type'] === 'membre' ? 'Javeline' : '???')) : '';
                            ?>
                            <td class="planning-case">
                                <?= $match ? htmlspecialchars($match['nom'] . ' / ' . $coach) : '' ?>
                            </td>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; // blocs de creneaux ?>
        <?php endforeach; // jours ?>
        <p class="fiche-pied">
            Édité le <?= date('d/m/Y à H:i') ?>
        </p>
    <?php endif; ?>
</div>
