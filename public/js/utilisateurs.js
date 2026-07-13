/* utilisateurs.js — Gestion des comptes utilisateurs (administrateur) */

$(document).ready(function () {

    const $form         = $('#form-utilisateur');
    const $titreForm    = $('#form-titre');
    const $idChamp      = $('#utilisateur-id');
    const $identifiant  = $('#utilisateur-identifiant');
    const $motDePasse   = $('#utilisateur-mdp');
    const $role         = $('#utilisateur-role');
    const $btnAjouter   = $('#btn-ajouter');
    const $btnModifier  = $('#btn-modifier');
    const $btnSupprimer = $('#btn-supprimer');
    const $btnReset     = $('#btn-reset');
    const $erreurs      = $('#form-erreurs');
    const $alerte       = $('#utilisateurs-alerte');

    // ----------------------------------------------------------------
    // Clic sur une ligne du tableau → remplit le formulaire
    // ----------------------------------------------------------------
    $(document).on('click keypress', '.ligne-utilisateur', function (e) {
        // Supporte aussi la touche Entrée pour l'accessibilité
        if (e.type === 'keypress' && e.which !== 13) return;

        const $ligne = $(this);

        $idChamp.val($ligne.data('id'));
        $identifiant.val($ligne.data('identifiant')).prop('readonly', true);
        $motDePasse.val('');
        $role.val($ligne.data('role'));

        $titreForm.text('Modifier le compte');
        $btnAjouter.prop('disabled', true);
        $btnModifier.prop('disabled', false);
        $btnSupprimer.prop('disabled', false);
        $('.ligne-utilisateur').removeClass('ligne-selectionnee');
        $ligne.addClass('ligne-selectionnee');
        cacherErreur();
        cacherAlerte();
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
            url     : APP_URL + '/utilisateurs/ajouter',
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
            $btnAjouter.prop('disabled', $idChamp.val() !== '').text('Ajouter');
        });
    });

    // ----------------------------------------------------------------
    // Bouton Modifier (profil et/ou nouveau mot de passe)
    // ----------------------------------------------------------------
    $btnModifier.on('click', function () {
        cacherErreur();

        $btnModifier.prop('disabled', true).text('Enregistrement…');

        $.ajax({
            url     : APP_URL + '/utilisateurs/modifier',
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
                $btnModifier.prop('disabled', false);
            }
        })
        .fail(function (xhr) {
            gererEchec(xhr);
            $btnModifier.prop('disabled', false);
        })
        .always(function () {
            $btnModifier.text('Modifier');
        });
    });

    // ----------------------------------------------------------------
    // Bouton Supprimer
    // ----------------------------------------------------------------
    $btnSupprimer.on('click', function () {
        const id = $idChamp.val();
        if (!id) return;

        if (!confirm('Supprimer le compte « ' + $identifiant.val() + ' » ?\nCette action est irréversible.')) return;

        $btnSupprimer.prop('disabled', true).text('Suppression…');

        $.ajax({
            url     : APP_URL + '/utilisateurs/supprimer',
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
                $btnSupprimer.prop('disabled', false);
            }
        })
        .fail(function (xhr) {
            gererEchec(xhr);
            $btnSupprimer.prop('disabled', false);
        })
        .always(function () {
            $btnSupprimer.text('Supprimer');
        });
    });

    // ----------------------------------------------------------------
    // Fonctions utilitaires
    // ----------------------------------------------------------------

    function mettreAJourListe(html) {
        $('#zone-utilisateurs-liste').html(html);
        const nb = $('.ligne-utilisateur').length;
        $('#utilisateurs-compteur').text(nb + ' compte' + (nb > 1 ? 's' : ''));
    }

    function reinitialiserFormulaire() {
        $form[0].reset();
        $idChamp.val('');
        $identifiant.prop('readonly', false);
        $titreForm.text('Nouveau compte');
        $btnAjouter.prop('disabled', false);
        $btnModifier.prop('disabled', true);
        $btnSupprimer.prop('disabled', true);
        $('.ligne-utilisateur').removeClass('ligne-selectionnee');
        cacherErreur();
    }

    function afficherErreur(erreurs) {
        const items = erreurs.map(function (e) {
            return '<li>' + $('<span>').text(e).html() + '</li>';
        }).join('');
        $erreurs.html('<ul class="mb-0">' + items + '</ul>').removeAttr('hidden');
        setTimeout(cacherErreur, 4000);
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
