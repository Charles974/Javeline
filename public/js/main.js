/* main.js — Script principal Javeline */

$(document).ready(function () {

    // --- Tooltips Bootstrap ---
    $('[data-bs-toggle="tooltip"]').each(function () {
        new bootstrap.Tooltip(this);
    });

    // --- Création d'un challenge (modal + AJAX) ---
    const $form       = $('#form-challenge');
    const $btnValider = $('#btn-valider-challenge');
    const $erreur     = $('#modal-erreur');
    const modal       = bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-challenge'));

    // Réinitialise le formulaire et les messages à l'ouverture de la modal
    document.getElementById('modal-challenge').addEventListener('show.bs.modal', function () {
        $form[0].reset();
        cacherErreur();
    });

    $form.on('submit', function (e) {
        e.preventDefault();

        cacherErreur();
        $btnValider.prop('disabled', true).text('Enregistrement…');

        $.ajax({
            url    : APP_URL + '/challenges/creer',
            method : 'POST',
            data   : $form.serialize(),
            dataType: 'json',
        })
        .done(function (reponse) {
            if (reponse.success) {
                modal.hide();
                // Met à jour uniquement la zone challenge sans rechargement de page
                $('#zone-challenge').html(reponse.html);
                afficherSucces('Challenge créé avec succès.');
            } else {
                afficherErreur(reponse.erreurs || [reponse.message]);
            }
        })
        .fail(function (xhr) {
            let msg = 'Une erreur est survenue. Veuillez réessayer.';
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.erreurs) {
                    afficherErreur(data.erreurs);
                    return;
                }
                if (data.message) msg = data.message;
            } catch (e) { /* réponse non JSON */ }
            afficherErreur([msg]);
        })
        .always(function () {
            $btnValider.prop('disabled', false).text('Créer');
        });
    });

    function afficherErreur(erreurs) {
        const liste = erreurs.map(function (e) {
            return '<li>' + $('<span>').text(e).html() + '</li>';
        }).join('');
        $erreur.html('<ul class="mb-0">' + liste + '</ul>').removeAttr('hidden');
    }

    function cacherErreur() {
        $erreur.attr('hidden', true).empty();
    }

    function afficherSucces(message) {
        const $alerte = $('#alerte-succes');
        $('#alerte-succes-texte').text(message);
        $alerte.removeAttr('hidden');
        // Masque automatiquement après 4 secondes
        setTimeout(function () {
            $alerte.attr('hidden', true);
        }, 4000);
    }

});
