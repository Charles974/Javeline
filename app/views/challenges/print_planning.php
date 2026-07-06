<?php
// Jours de la semaine et mois en francais pour l'entete de chaque tableau.
$joursFr = [0 => 'Dimanche', 1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'];
$moisFr  = [1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
            7 => 'Juillet', 8 => 'Aout', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'];

// Regroupement des matchs planifies par date.
$parJour = [];
foreach ($planning as $m) {
    $parJour[$m['date_match']][] = $m;
}
ksort($parJour);

// Colonnes du tableau : une par discipline inscrite au challenge (ordre croissant de code), libelle FR/EN.
$disciplines = [];
foreach ($planning as $m) {
    $code = (int) $m['discipline_code'];
    if (!isset($disciplines[$code])) {
        $disciplines[$code] = ['fr' => $m['discipline_fr'], 'en' => $m['discipline_en']];
    }
}
ksort($disciplines);
$nbCols = count($disciplines);

// Le tableau commence toujours a 9h00 ; la zone grise d'ouverture couvre 9h00 -> 9h50 (6 creneaux de 10 min).
$planningDebut  = '09:00';
$planningFinMin = '18:10';

$dateDebutChallenge = date('d/m/Y', strtotime($challenge['date_debut']));
$dateFinChallenge2   = date('d/m/Y', strtotime($challenge['date_fin']));
?>

<div class="fiche fiche-planning">
    <div class="fiche-entete">
        <h1>Association Javeline</h1>
        <h2>Plan de tir — <?= htmlspecialchars($challenge['libelle']) ?></h2>
        <p class="planning-dates-challenge">
            <?= $dateDebutChallenge === $dateFinChallenge2
                ? 'Le ' . $dateDebutChallenge
                : 'Du ' . $dateDebutChallenge . ' au ' . $dateFinChallenge2 ?>
        </p>
    </div>

    <?php if (empty($planning)): ?>
        <p>Aucun match planifié pour le moment.</p>
    <?php else: ?>
        <?php
        $dateDebutChallengeIso = date('Y-m-d', strtotime($challenge['date_debut']));
        $dateFinChallenge      = date('Y-m-d', strtotime($challenge['date_fin']));
        foreach ($parJour as $date => $matchsJour):
            // Index [heure][code_discipline] = liste de noms pour cette date.
            $index      = [];
            $dernierFin = $planningFinMin;
            foreach ($matchsJour as $m) {
                $slot = substr($m['heure_debut'], 0, 5);
                $code = (int) $m['discipline_code'];
                $index[$slot][$code][] = $m['nom'] . '/' . $m['prenom'];
                $finMatch = substr($m['heure_fin'], 0, 5);
                if ($finMatch > $dernierFin) {
                    $dernierFin = $finMatch;
                }
            }

            // Creneaux de 10 minutes de 9h00 jusqu'a la fin du dernier match (18h10 au minimum).
            $creneaux = [];
            $curseur  = strtotime($planningDebut);
            $limite   = max(strtotime($planningFinMin), strtotime($dernierFin));
            while ($curseur <= $limite) {
                $creneaux[] = date('H:i', $curseur);
                $curseur += 600;
            }

            // Zone grise d'ouverture : uniquement le premier jour du challenge.
            $estPremierJour = $date === $dateDebutChallengeIso;

            // Zone grise de fin de journee : demarre juste apres le dernier creneau utilise.
            $dernierCreneauUtilise = !empty($index) ? max(array_keys($index)) : '09:50';
            $debutCloture   = date('H:i', strtotime($dernierCreneauUtilise) + 600);
            $estDernierJour = $date === $dateFinChallenge;
            $derniereLigne  = $creneaux[count($creneaux) - 1];

            $jourSemaine = $joursFr[(int) date('w', strtotime($date))];
            $libelleJour = $jourSemaine . ' ' . date('d', strtotime($date)) . ' ' . $moisFr[(int) date('n', strtotime($date))] . ' ' . date('Y', strtotime($date));
        ?>
        <div class="planning-jour">
            <h3 class="planning-date"><?= htmlspecialchars($libelleJour) ?></h3>
            <table class="fiche-table planning-table">
                <thead>
                    <tr>
                        <th class="planning-col-horaire">Horaires</th>
                        <?php foreach ($disciplines as $code => $libelle): ?>
                        <th><?= (int) $code ?> — <?= htmlspecialchars($libelle['fr']) ?>/<br><?= htmlspecialchars($libelle['en']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($creneaux as $heure):
                        $estOuverture = $estPremierJour && $heure >= '09:00' && $heure <= '09:50';
                        $estCloture   = !$estOuverture && $heure >= $debutCloture;

                        $libelleCloture = '';
                        if ($estCloture) {
                            if ($estDernierJour) {
                                $libelleCloture = $heure === $derniereLigne
                                    ? 'Remise des médailles'
                                    : ($heure === $debutCloture ? 'Rangement des bêtes' : '');
                            } else {
                                $libelleCloture = $heure === $debutCloture ? 'Requillage Ensemble des Silhouettes' : '';
                            }
                        }
                    ?>
                    <tr>
                        <td class="planning-col-horaire"><?= str_replace(':', ' h ', $heure) ?></td>
                        <?php if ($estOuverture): ?>
                            <?php if ($heure === '09:00'): ?>
                            <td class="planning-case-grise planning-case-ouverture" colspan="<?= $nbCols ?>" rowspan="6">
                                Ouverture et préparation du stand
                            </td>
                            <?php endif; ?>
                        <?php elseif ($estCloture): ?>
                            <td class="planning-case-grise" colspan="<?= $nbCols ?>">
                                <?= htmlspecialchars($libelleCloture) ?>
                            </td>
                        <?php else: ?>
                            <?php foreach ($disciplines as $code => $libelle): ?>
                            <td class="planning-case">
                                <?= isset($index[$heure][$code])
                                    ? implode('<br>', array_map('htmlspecialchars', $index[$heure][$code]))
                                    : '' ?>
                            </td>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
        <p class="fiche-pied">
            Édité le <?= date('d/m/Y à H:i') ?>
        </p>
    <?php endif; ?>
</div>
