<?php
// Vue imprimable : liste des matchs (inscriptions) d'un challenge.
// Reprend le style graphique de la liste des categories (fiche paysage).
// Attendu : $challenge (array), $inscrits (array)
$debut = date('d/m/Y', strtotime($challenge['date_debut']));
$fin   = date('d/m/Y', strtotime($challenge['date_fin']));
?>
<div class="fiche-sheet">
    <div class="fiche-tireur-entete">
        <img class="fiche-tireur-logo" src="<?= APP_URL ?>/public/img/images.png" alt="Logo Javeline">
        <div class="fiche-tireur-entete-texte">
            <div class="fiche-tireur-association">Association Javeline</div>
            <div class="fiche-tireur-titre">Liste des matchs — <?= htmlspecialchars($challenge['libelle']) ?></div>
            <div class="fiche-tireur-sous-titre">
                <?= $debut === $fin ? $debut : 'Du ' . $debut . ' au ' . $fin ?>
            </div>
        </div>
    </div>

    <div class="fiche-tireur-corps">
        <?php if (empty($inscrits)): ?>
            <p class="fiche-liste-vide">Aucun tireur inscrit.</p>
        <?php else: ?>
            <table class="fiche-liste-table" aria-label="Liste des matchs">
                <thead>
                    <tr>
                        <th scope="col">Nom</th>
                        <th scope="col">Prénom</th>
                        <th scope="col">Club</th>
                        <th class="fiche-liste-col-cat" scope="col">Cat</th>
                        <th scope="col">Catégorie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inscrits as $inscrit): ?>
                    <?php
                        $club = $inscrit['tireur_type'] === 'membre' ? 'Javeline' : $inscrit['club'];
                        $libelleDiscipline = $inscrit['etranger']
                            ? $inscrit['discipline_en']
                            : $inscrit['discipline_fr'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($inscrit['nom']) ?></td>
                        <td><?= htmlspecialchars($inscrit['prenom']) ?></td>
                        <td class="fiche-liste-col-club"><?= htmlspecialchars($club) ?></td>
                        <td class="fiche-liste-col-cat"><?= (int)$inscrit['discipline_code'] ?></td>
                        <td><?= htmlspecialchars($libelleDiscipline) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="fiche-tireur-pied">
        <span>Nombre de lignes : <?= count($inscrits) ?> — Édité le <?= date('d/m/Y à H:i') ?></span>
        <span>© <?= date('Y') ?> Javeline — Tous droits réservés</span>
    </div>
</div>
