<!-- Modal : création d'un challenge (disponible sur toutes les pages via la barre de navigation) -->
<div class="modal fade"
     id="modal-challenge"
     tabindex="-1"
     aria-labelledby="modal-challenge-titre"
     aria-modal="true"
     role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-challenge-titre">Créer un challenge</h3>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fermer la fenêtre"></button>
            </div>

            <form id="form-challenge" novalidate aria-label="Formulaire de création d'un challenge">
                <div class="modal-body">

                    <!-- Message d'erreur inline -->
                    <div id="modal-erreur" class="modal-erreur" role="alert" aria-live="assertive" hidden></div>

                    <div class="mb-3">
                        <label for="challenge-libelle" class="form-label">Nom du challenge <span aria-hidden="true">*</span></label>
                        <input type="text"
                               class="form-control"
                               id="challenge-libelle"
                               name="libelle"
                               required
                               maxlength="200"
                               autocomplete="off"
                               aria-required="true">
                    </div>

                    <div class="mb-3">
                        <label for="challenge-date-debut" class="form-label">Date de début <span aria-hidden="true">*</span></label>
                        <input type="date"
                               class="form-control"
                               id="challenge-date-debut"
                               name="date_debut"
                               required
                               aria-required="true">
                    </div>

                    <div class="mb-3">
                        <label for="challenge-date-fin" class="form-label">Date de fin <span aria-hidden="true">*</span></label>
                        <input type="date"
                               class="form-control"
                               id="challenge-date-fin"
                               name="date_fin"
                               required
                               aria-required="true">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit"
                            class="btn btn-primary"
                            id="btn-valider-challenge">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
