/* externes.js — Gestion des tireurs non membres */

$(document).ready(function () {

    const $form        = $('#form-externe');
    const $titreForm   = $('#form-titre');
    const $idChamp     = $('#externe-id');
    const $btnAjouter  = $('#btn-ajouter');
    const $btnModifier = $('#btn-modifier');
    const $btnImprimer = $('#btn-imprimer');
    const $btnReset    = $('#btn-reset');
    const $erreurs     = $('#form-erreurs');
    const $alerte      = $('#externes-alerte');

    // ----------------------------------------------------------------
    // Clic sur une ligne → charge et remplit le formulaire
    // ----------------------------------------------------------------
    $(document).on('click keypress', '.ligne-externe', function (e) {
        if (e.type === 'keypress' && e.which !== 13) return;

        const $ligne = $(this);
        const id = $ligne.data('id');

        $.getJSON(APP_URL + '/externes/get/' + id, function (data) {
            if (!data.success) return;

            const ex = data.externe;
            $idChamp.val(ex.id);
            $('#externe-nom').val(ex.nom);
            $('#externe-prenom').val(ex.prenom);
            $('#externe-club').val(ex.club);
            $('#externe-tel').val(ex.telephone || '');
            $('#externe-email').val(ex.email || '');
            $('#externe-etranger').prop('checked', ex.etranger == 1);

            $titreForm.text('Modifier le tireur');
            $btnAjouter.prop('disabled', true);
            $btnModifier.prop('disabled', false);
            $btnImprimer.prop('disabled', false);

            $('.ligne-externe').removeClass('ligne-selectionnee');
            $ligne.addClass('ligne-selectionnee');

            cacherErreur();
            cacherAlerte();

            $('html, body').animate({ scrollTop: $form.offset().top - 20 }, 300);
        });
    });

    // ----------------------------------------------------------------
    // Bouton Effacer
    // ----------------------------------------------------------------
    $btnReset.on('click', function () {
        reinitialiserFormulaire();
    });

    // ----------------------------------------------------------------
    // Soumission : Ajouter
    // ----------------------------------------------------------------
    $form.on('submit', function (e) {
        e.preventDefault();
        cacherErreur();

        $btnAjouter.prop('disabled', true).text('Enregistrement…');

        $.ajax({
            url     : APP_URL + '/externes/ajouter',
            method  : 'POST',
            data    : $form.serialize(),
            dataType: 'json',
        })
        .done(function (rep) {
            if (rep.success) {
                mettreAJourListe(rep.html);
                afficherAlerte(rep.message, 'succes');
                reinitialiserFormulaire();
            } else {
                afficherErreur(rep.erreurs || [rep.message]);
            }
        })
        .fail(gererEchec)
        .always(function () {
            $btnAjouter.prop('disabled', false).text('Ajouter');
        });
    });

    // ----------------------------------------------------------------
    // Bouton Modifier
    // ----------------------------------------------------------------
    $btnModifier.on('click', function () {
        cacherErreur();
        $btnModifier.prop('disabled', true).text('Enregistrement…');

        $.ajax({
            url     : APP_URL + '/externes/modifier',
            method  : 'POST',
            data    : $form.serialize(),
            dataType: 'json',
        })
        .done(function (rep) {
            if (rep.success) {
                mettreAJourListe(rep.html);
                afficherAlerte(rep.message, 'succes');
                reinitialiserFormulaire();
            } else {
                afficherErreur(rep.erreurs || [rep.message]);
            }
        })
        .fail(gererEchec)
        .always(function () {
            $btnModifier.prop('disabled', false).text('Modifier');
        });
    });

    // ----------------------------------------------------------------
    // Bouton Imprimer
    // ----------------------------------------------------------------
    $btnImprimer.on('click', function () {
        const id = $idChamp.val();
        if (id) {
            window.open(APP_URL + '/externes/fiche/' + id, '_blank');
        }
    });

    // ----------------------------------------------------------------
    // Fonctions utilitaires
    // ----------------------------------------------------------------

    function mettreAJourListe(html) {
        $('#zone-externes-liste').html(html);
        const nb = $('.ligne-externe').length;
        $('#externes-compteur').text(nb + ' tireur' + (nb > 1 ? 's' : ''));
    }

    function reinitialiserFormulaire() {
        $form[0].reset();
        $idChamp.val('');
        $titreForm.text('Nouveau tireur');
        $btnAjouter.prop('disabled', false);
        $btnModifier.prop('disabled', true);
        $btnImprimer.prop('disabled', true);
        $('.ligne-externe').removeClass('ligne-selectionnee');
        cacherErreur();
    }

    function afficherErreur(erreurs) {
        const items = erreurs.map(function (e) {
            return '<li>' + $('<span>').text(e).html() + '</li>';
        }).join('');
        $erreurs.html('<ul class="mb-0">' + items + '</ul>').removeAttr('hidden');
    }

    function cacherErreur() {
        $erreurs.attr('hidden', true).empty();
    }

    function afficherAlerte(message, type) {
        $alerte
            .removeClass('alerte-succes alerte-erreur')
            .addClass(type === 'succes' ? 'alerte-succes' : 'alerte-erreur')
            .text(message)
            .removeAttr('hidden');
        setTimeout(function () { $alerte.attr('hidden', true); }, 4000);
    }

    function cacherAlerte() {
        $alerte.attr('hidden', true);
    }

    function gererEchec(xhr) {
        let msg = 'Une erreur est survenue. Veuillez réessayer.';
        try {
            const data = JSON.parse(xhr.responseText);
            if (data.erreurs) { afficherErreur(data.erreurs); return; }
            if (data.message) msg = data.message;
        } catch (e) { /* réponse non JSON */ }
        afficherErreur([msg]);
    }

});
