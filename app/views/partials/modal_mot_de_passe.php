<?php
// Modal de changement de son propre mot de passe (tous profils connectés).
// Inclus dans le layout principal ; soumis en AJAX vers POST /mot-de-passe.
?>
<div class="modal fade" id="modal-mot-de-passe" tabindex="-1" aria-labelledby="modal-mdp-titre" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form-mot-de-passe" novalidate>
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="modal-mdp-titre">Changer mon mot de passe</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <!-- Erreurs inline -->
                    <div id="mdp-erreurs" class="form-erreurs" role="alert" aria-live="assertive" hidden></div>

                    <div class="mb-3">
                        <label for="mdp-actuel" class="form-label">Mot de passe actuel</label>
                        <input type="password" class="form-control" id="mdp-actuel"
                               name="mot_de_passe_actuel" required
                               autocomplete="current-password" aria-required="true">
                    </div>
                    <div class="mb-3">
                        <label for="mdp-nouveau" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="mdp-nouveau"
                               name="nouveau_mot_de_passe" required minlength="8"
                               autocomplete="new-password" aria-required="true"
                               aria-describedby="mdp-nouveau-aide">
                        <div id="mdp-nouveau-aide" class="form-text">8 caractères minimum.</div>
                    </div>
                    <div class="mb-1">
                        <label for="mdp-confirmation" class="form-label">Confirmation du nouveau mot de passe</label>
                        <input type="password" class="form-control" id="mdp-confirmation"
                               name="confirmation_mot_de_passe" required minlength="8"
                               autocomplete="new-password" aria-required="true">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" id="btn-valider-mdp" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
