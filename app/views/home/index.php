<section class="hero text-center py-5" aria-labelledby="titre-accueil">
    <h1 id="titre-accueil">Association Javeline</h1>
    <p class="lead">Gestion des scores — Silhouette métallique</p>
</section>

<!-- Résumé du challenge en cours ou à venir -->
<section class="challenge-actif mb-5" aria-labelledby="titre-challenge">
    <?php if ($challengeActif): ?>
        <?php
            $debut = date('d/m/Y', strtotime($challengeActif['date_debut']));
            $fin   = date('d/m/Y', strtotime($challengeActif['date_fin']));
            $today = date('Y-m-d');
            $enCours = ($today >= $challengeActif['date_debut'] && $today <= $challengeActif['date_fin']);
        ?>
        <div class="card challenge-card mx-auto">
            <div class="card-body">
                <span class="badge-statut <?= $enCours ? 'badge-en-cours' : 'badge-a-venir' ?>">
                    <?= $enCours ? 'En cours' : 'À venir' ?>
                </span>
                <h2 id="titre-challenge" class="card-title mt-2">
                    <?= htmlspecialchars($challengeActif['libelle']) ?>
                </h2>
                <p class="card-text dates-challenge">
                    <?= $debut === $fin ? $debut : 'Du ' . $debut . ' au ' . $fin ?>
                </p>
                <ul class="liste-inscrits" aria-label="Tireurs inscrits">
                    <li>
                        <span class="inscrits-label">Membres</span>
                        <span class="inscrits-compte"><?= (int)$challengeActif['nb_membres'] ?></span>
                    </li>
                    <li>
                        <span class="inscrits-label">Non membres</span>
                        <span class="inscrits-compte"><?= (int)$challengeActif['nb_externes'] ?></span>
                    </li>
                </ul>
                <a href="<?= APP_URL ?>/challenges/<?= (int)$challengeActif['id'] ?>"
                   class="btn btn-primary"
                   aria-label="Voir le détail du challenge <?= htmlspecialchars($challengeActif['libelle']) ?>">
                    Voir le challenge
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card challenge-vide mx-auto">
            <div class="card-body text-center">
                <p class="mb-0">Aucun challenge en cours ou à venir.</p>
            </div>
        </div>
    <?php endif; ?>
</section>

<!-- Boutons de navigation principale -->
<section class="navigation-principale text-center" aria-label="Navigation principale">
    <div class="nav-boutons">
        <a href="<?= APP_URL ?>/membres"
           class="btn btn-nav"
           aria-label="Gérer les tireurs membres">
            Tireurs membres
        </a>
        <a href="<?= APP_URL ?>/externes"
           class="btn btn-nav"
           aria-label="Gérer les tireurs non membres">
            Tireurs non membres
        </a>
        <a href="<?= APP_URL ?>/challenges/creer"
           class="btn btn-nav btn-nav-accent"
           aria-label="Créer un nouveau challenge">
            Créer un challenge
        </a>
        <a href="<?= APP_URL ?>/challenges/historique"
           class="btn btn-nav"
           aria-label="Voir l'historique des challenges">
            Historique des challenges
        </a>
    </div>
</section>
