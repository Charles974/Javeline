/* membres.js — Gestion des tireurs membres */

$(document).ready(function () {

    const $form        = $('#form-membre');
    const $titreForm   = $('#form-titre');
    const $idChamp     = $('#membre-id');
    const $btnAjouter   = $('#btn-ajouter');
    const $btnModifier  = $('#btn-modifier');
    const $btnImprimer  = $('#btn-imprimer');
    const $btnSupprimer = $('#btn-supprimer');
    const $btnReset     = $('#btn-reset');
    const $erreurs     = $('#form-erreurs');
    const $alerte      = $('#membres-alerte');

    // ----------------------------------------------------------------
    // Clic sur une ligne du tableau → remplit le formulaire
    // ----------------------------------------------------------------
    $(document).on('click keypress', '.ligne-membre', function (e) {
        // Supporte aussi la touche Entrée pour l'accessibilité
        if (e.type === 'keypress' && e.which !== 13) return;

        const $ligne = $(this);
        const id = $ligne.data('id');

        // Active les boutons et surligne immédiatement (l'id est déjà dans data-id)
        $idChamp.val(id);
        $titreForm.text('Modifier le membre');
        $btnAjouter.prop('disabled', true);
        $btnModifier.prop('disabled', false);
        $btnImprimer.prop('disabled', false);
        $btnSupprimer.prop('disabled', false);
        $('.ligne-membre').removeClass('ligne-selectionnee');
        $ligne.addClass('ligne-selectionnee');
        cacherErreur();
        cacherAlerte();

        // Charge les données complètes du membre via AJAX pour remplir le formulaire
        $.getJSON(APP_URL + '/membres/get/' + id)
            .done(function (data) {
                if (!data.success) return;
                const m = data.membre;
                $('#membre-nom').val(m.nom);
                $('#membre-prenom').val(m.prenom);
                $('#membre-naissance').val(m.date_naissance);
                $('#membre-lieu').val(m.lieu_naissance);
                $('#membre-licence').val(m.numero_licence);
                $('#membre-adresse1').val(m.adresse1);
                $('#membre-adresse2').val(m.adresse2 || '');
                $('#membre-cp').val(m.code_postal);
                $('#membre-ville').val(m.ville);
                $('#membre-tel').val(m.telephone);
                $('#membre-email').val(m.email);
                $('#membre-certificat').prop('checked', m.certificat_medical == 1);
                // Scroll vers le formulaire sur mobile
                $('html, body').animate({ scrollTop: $form.offset().top - 20 }, 300);
            })
            .fail(function () {
                afficherErreur(['Impossible de charger les données du membre.']);
                reinitialiserFormulaire();
            });
    });

    // ----------------------------------------------------------------
    // Bouton Effacer → réinitialise le formulaire
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
            url     : APP_URL + '/membres/ajouter',
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
            url     : APP_URL + '/membres/modifier',
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
    // Bouton Supprimer
    // ----------------------------------------------------------------
    $btnSupprimer.on('click', function () {
        const id  = $idChamp.val();
        const nom = $('#membre-nom').val() + ' ' + $('#membre-prenom').val();
        if (!id) return;

        if (!confirm('Supprimer le membre « ' + nom + ' » ?\nCette action est irréversible.')) return;

        $btnSupprimer.prop('disabled', true).text('Suppression…');

        $.ajax({
            url     : APP_URL + '/membres/supprimer',
            method  : 'POST',
            data    : { id: id },
            dataType: 'json',
        })
        .done(function (rep) {
            if (rep.success) {
                mettreAJourListe(rep.html);
                afficherAlerte(rep.message, 'succes');
                reinitialiserFormulaire();
            } else {
                afficherErreur([rep.message]);
            }
        })
        .fail(gererEchec)
        .always(function () {
            $btnSupprimer.prop('disabled', true).text('Supprimer');
        });
    });

    // ----------------------------------------------------------------
    // Bouton Imprimer → ouvre la fiche dans un nouvel onglet
    // ----------------------------------------------------------------
    $btnImprimer.on('click', function () {
        const id = $idChamp.val();
        if (id) {
            window.open(APP_URL + '/membres/fiche/' + id, '_blank');
        }
    });

    // ----------------------------------------------------------------
    // Fonctions utilitaires
    // ----------------------------------------------------------------

    function mettreAJourListe(html) {
        $('#zone-membres-liste').html(html);
        const nb = $('.ligne-membre').length;
        $('#membres-compteur').text(nb + ' membre' + (nb > 1 ? 's' : ''));
    }

    function reinitialiserFormulaire() {
        $form[0].reset();
        $idChamp.val('');
        $titreForm.text('Nouveau membre');
        $btnAjouter.prop('disabled', false);
        $btnModifier.prop('disabled', true);
        $btnImprimer.prop('disabled', true);
        $btnSupprimer.prop('disabled', true);
        $('.ligne-membre').removeClass('ligne-selectionnee');
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
