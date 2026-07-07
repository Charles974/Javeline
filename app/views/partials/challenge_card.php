<?php
// Partial réutilisable : carte du challenge actif ou à venir.
// Attendu : $challengeActif (array|null)
?>
<?php if ($challengeActif): ?>
    <?php
        $debut   = date('d/m/Y', strtotime($challengeActif['date_debut']));
        $fin     = date('d/m/Y', strtotime($challengeActif['date_fin']));
        $today   = date('Y-m-d');
        $enCours = ($today >= $challengeActif['date_debut'] && $today <= $challengeActif['date_fin']);
    ?>
    <div class="card challenge-card mx-auto">
        <div class="card-body">
            <div class="challenge-card-entete">
                <span class="badge-statut <?= $enCours ? 'badge-en-cours' : 'badge-a-venir' ?>">
                    <?= $enCours ? 'En cours' : 'À venir' ?>
                </span>
                <span class="challenge-card-souscription">Prochain rendez-vous</span>
            </div>
            <h2 class="card-title mt-2">
                <?= htmlspecialchars($challengeActif['libelle']) ?>
            </h2>
            <p class="card-text dates-challenge">
                <?= $debut === $fin ? $debut : 'Du ' . $debut . ' au ' . $fin ?>
            </p>
            <div class="challenge-card-stats">
                <ul class="liste-inscrits" aria-label="Tireurs inscrits">
                    <li>
                        <span class="inscrits-label">Matchs</span>
                        <span class="inscrits-compte"><?= (int)$challengeActif['nb_matchs'] ?></span>
                    </li>
                </ul>
                <div class="challenge-card-separateur" aria-hidden="true"></div>
                <div class="challenge-card-animaux">
                    <img src="<?= APP_URL ?>/public/img/poulet.png" alt="Poulet" title="Poulet">
                    <img src="<?= APP_URL ?>/public/img/cochon.png" alt="Cochon" title="Cochon">
                    <img src="<?= APP_URL ?>/public/img/dindon.png" alt="Dindon" title="Dindon">
                    <img src="<?= APP_URL ?>/public/img/mouflon.png" alt="Mouflon" title="Mouflon">
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="<?= APP_URL ?>/challenges/<?= (int)$challengeActif['id'] ?>"
                   class="btn btn-primary"
                   aria-label="Gérer les inscriptions du challenge <?= htmlspecialchars($challengeActif['libelle']) ?>">
                    Inscriptions
                </a>
                <a href="<?= APP_URL ?>/challenges/<?= (int)$challengeActif['id'] ?>/resume"
                   class="btn btn-outline-primary"
                   aria-label="Voir le résumé du challenge <?= htmlspecialchars($challengeActif['libelle']) ?>">
                    Résumé
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card challenge-vide mx-auto">
        <div class="card-body text-center">
            <p class="mb-0">Aucun challenge en cours ou à venir.</p>
        </div>
    </div>
<?php endif; ?>
