<div class="page-header mb-4">
    <h1>Gestion des comptes</h1>
</div>

<!-- Message retour AJAX -->
<div id="utilisateurs-alerte" class="membres-alerte" role="alert" aria-live="polite" hidden></div>

<div class="utilisateurs-row">

    <!-- ====== Formulaire ====== -->
    <div class="mb-4">
        <div class="card form-card">
            <div class="card-header">
                <h2 class="card-titre" id="form-titre">Nouveau compte</h2>
            </div>
            <div class="card-body">

                <!-- Erreurs inline -->
                <div id="form-erreurs" class="form-erreurs" role="alert" aria-live="assertive" hidden></div>

                <form id="form-utilisateur" novalidate aria-labelledby="form-titre">
                    <input type="hidden" id="utilisateur-id" name="id" value="">

                    <div class="row g-2">
                        <div class="col-md-4 mb-2">
                            <label for="utilisateur-identifiant" class="form-label">Identifiant <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control" id="utilisateur-identifiant" name="identifiant"
                                   required minlength="3" maxlength="50" pattern="[A-Za-z0-9._-]+"
                                   autocomplete="off" aria-required="true">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="utilisateur-mdp" class="form-label">Mot de passe <span aria-hidden="true">*</span></label>
                            <input type="password" class="form-control" id="utilisateur-mdp" name="mot_de_passe"
                                   minlength="8" autocomplete="new-password" aria-required="true"
                                   aria-describedby="utilisateur-mdp-aide">
                            <div id="utilisateur-mdp-aide" class="form-text">
                                8 caractères minimum. En modification : laisser vide pour conserver le mot de passe actuel.
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="utilisateur-role" class="form-label">Profil <span aria-hidden="true">*</span></label>
                            <select class="form-select" id="utilisateur-role" name="role" required aria-required="true">
                                <?php foreach (Auth::ROLES as $code => $libelle): ?>
                                    <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($libelle) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="form-actions">
                        <button type="submit" id="btn-ajouter" class="btn btn-success" aria-label="Créer le compte">
                            Ajouter
                        </button>
                        <button type="button" id="btn-modifier" class="btn btn-warning" disabled aria-label="Modifier le compte sélectionné">
                            Modifier
                        </button>
                        <button type="button" id="btn-supprimer" class="btn btn-danger" disabled aria-label="Supprimer le compte sélectionné">
                            Supprimer
                        </button>
                        <button type="button" id="btn-reset" class="btn btn-outline-secondary" aria-label="Réinitialiser le formulaire">
                            Effacer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ====== Liste des comptes ====== -->
    <div class="card liste-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-titre">Liste des comptes</h2>
            <span id="utilisateurs-compteur" class="badge bg-secondary">
                <?= count($utilisateurs) ?> compte<?= count($utilisateurs) > 1 ? 's' : '' ?>
            </span>
        </div>
        <div class="card-body p-0" id="zone-utilisateurs-liste">
            <?php require APP_ROOT . '/app/views/partials/utilisateurs_liste.php'; ?>
        </div>
    </div>

</div>

<script src="<?= APP_URL ?>/public/js/utilisateurs.js"></script>
