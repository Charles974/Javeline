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
// Pour fiabiliser l'export, on decoupe nous-memes chaque jour en blocs de
// lignes. Chaque bloc est un tableau autonome (avec son propre en-tete de
// colonnes), plus court qu'une page, et marque comme insecable : Paged.js
// n'a alors que des blocs simples a placer, ce qui supprime la cascade de
// pages blanches.
//
// Decoupage : la hauteur de chaque creneau depend du nombre de disciplines
// (colonnes plus etroites -> noms des tireurs sur 2 lignes) ; on estime donc
// la hauteur reelle de chaque ligne, puis chaque page est remplie au maximum
// de sa hauteur disponible. Les creneaux restants d'un jour sont repartis a
// hauteurs egales sur le nombre minimal de pages (pas de page presque vide).

$dateDebut = date('d/m/Y', strtotime($challenge['date_debut']));
$dateFin   = date('d/m/Y', strtotime($challenge['date_fin']));

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

// ------------------------------------------------------------------
// Estimation des hauteurs (en points PDF, 1 cm = 28.35 pt) pour le
// decoupage en pages. Valeurs deduites de la geometrie A4 paysage et
// des styles .planning-* / .fiche-planning de fiche-print.css, puis
// verifiees sur le rendu reel de Paged.js.
// ------------------------------------------------------------------
$hauteurDisponible = 490.0; // zone imprimable verticale (21 cm - marges)
$hauteurEntete     = 61.0;  // en-tete de la fiche (premiere page seulement)
$hauteurTitreJour  = 21.0;  // titre h3 du jour + sa marge
$margeSecurite     = 12.0;  // tolerance sur les arrondis d'estimation

// Largeur d'une colonne discipline : grille (29.7 cm - marges) moins la
// colonne Horaires (2.2 cm), partagee entre les disciplines.
$largeurColonne = 694.5 / max(1, $nbCols);

// Nombre de caracteres par ligne dans une cellule (noms a 8 pt) et dans
// l'en-tete de colonnes (libelles a 6.5 pt gras), padding deduit.
$charsCellule   = max(4, (int) floor(($largeurColonne - 7) / 4.0));
$charsEnTeteCol = max(4, (int) floor(($largeurColonne - 4.5) / 3.6));

/*
 * Nombre de lignes occupees par un texte dans une cellule : simulation du
 * retour a la ligne mot par mot (un mot trop long est coupe, comme avec
 * overflow-wrap: anywhere).
 */
$lignesPourTexte = static function (string $texte, int $maxChars): int {
    $lignes  = 1;
    $courant = 0;
    foreach (preg_split('/\s+/', trim($texte)) as $mot) {
        $long = mb_strlen($mot);
        if ($courant > 0 && $courant + 1 + $long > $maxChars) {
            $lignes++;
            $courant = $long;
        } else {
            $courant += ($courant > 0 ? 1 : 0) + $long;
        }
        while ($courant > $maxChars) {
            $lignes++;
            $courant -= $maxChars;
        }
    }
    return $lignes;
};

// Hauteur de l'en-tete de colonnes : dictee par le libelle le plus long.
$lignesEnTeteCol = 1;
foreach ($disciplines as $code => $lib) {
    $libelleCol      = $code . ' — ' . $lib['fr'] . ' / ' . $lib['en'];
    $lignesEnTeteCol = max($lignesEnTeteCol, $lignesPourTexte($libelleCol, $charsEnTeteCol));
}
$hauteurEnTeteCol = $lignesEnTeteCol * 7.8 + 5;

/*
 * Hauteur estimee d'un creneau : 12.7 pt pour une ligne simple, davantage
 * si le texte "Nom / Coach" d'une cellule passe sur plusieurs lignes.
 */
$hauteurCreneau = static function (string $jour, string $heure) use ($matchsParJour, $disciplines, $charsCellule, $lignesPourTexte): float {
    $lignes = 1;
    foreach ($disciplines as $code => $lib) {
        $match = $matchsParJour[$jour][$code][$heure] ?? null;
        if ($match) {
            $coach  = $match['coach'] ?: ($match['tireur_type'] === 'membre' ? 'Javeline' : '???');
            $lignes = max($lignes, $lignesPourTexte($match['nom'] . ' / ' . $coach, $charsCellule));
        }
    }
    return $lignes === 1 ? 12.7 : $lignes * 9.2 + 3.8;
};
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

            // Hauteur estimee de chaque creneau du jour, et budgets de page.
            // Le premier bloc du document partage la premiere page avec
            // l'en-tete de la fiche : son budget est reduit d'autant.
            $hauteurs = [];
            foreach ($creneaux as $heure) {
                $hauteurs[$heure] = $hauteurCreneau($jour, $heure);
            }
            $budgetPage    = $hauteurDisponible - $hauteurTitreJour - $hauteurEnTeteCol - $margeSecurite;
            $budgetPremier = $budgetPage - ($premierBloc ? $hauteurEntete : 0);

            // Premiere page du jour : remplie au maximum de son budget.
            $blocsCreneaux = [[]];
            $cumul         = 0.0;
            $indexCreneau  = 0;
            foreach ($creneaux as $heure) {
                if ($cumul + $hauteurs[$heure] > $budgetPremier && $blocsCreneaux[0] !== []) {
                    break;
                }
                $blocsCreneaux[0][] = $heure;
                $cumul += $hauteurs[$heure];
                $indexCreneau++;
            }

            // Creneaux restants : repartis a hauteurs egales sur le nombre
            // minimal de pages, pour ne pas finir par une page presque vide.
            $restants = array_slice($creneaux, $indexCreneau);
            if ($restants !== []) {
                $hauteurRestante = 0.0;
                foreach ($restants as $heure) {
                    $hauteurRestante += $hauteurs[$heure];
                }
                $nbPagesSuite = (int) ceil($hauteurRestante / $budgetPage);
                $cible        = $hauteurRestante / $nbPagesSuite;

                $blocCourant = [];
                $cumul       = 0.0;
                foreach ($restants as $heure) {
                    if ($cumul + $hauteurs[$heure] > $cible + 0.01 && $blocCourant !== [] && count($blocsCreneaux) < $nbPagesSuite) {
                        $blocsCreneaux[] = $blocCourant;
                        $blocCourant     = [];
                        $cumul           = 0.0;
                    }
                    $blocCourant[] = $heure;
                    $cumul += $hauteurs[$heure];
                }
                $blocsCreneaux[] = $blocCourant;
            }
            $nbBlocs = count($blocsCreneaux);

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
                                <?php // Libelle repete en tete de bloc imprime si le bloc horaire est coupe par un saut de page. ?>
                                <?= ($heure === $debutBloc || $heure === $creneauxBloc[0]) ? htmlspecialchars($bloc['libelle']) : '' ?>
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
