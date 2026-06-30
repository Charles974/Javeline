/* challenge-inscriptions.js — Gestion des inscriptions à un challenge */

$(document).ready(function () {

    if (CHALLENGE_ARCHIVE) return; // lecture seule si archivé

    const $panneau      = $('#panneau-inscription');
    const $titreP       = $('#panneau-titre');
    const $btnVers      = $('#btn-vers-inscription');
    const $btnAjouter   = $('#btn-ajouter-insc');
    const $btnModifier  = $('#btn-modifier-insc');
    const $btnAnnuler   = $('#btn-annuler-inscription');
    const $erreurs      = $('#insc-erreurs');
    const $alerte       = $('#insc-alerte');
    const $form         = $('#form-inscription');

    let tireurSelectionneGauche = null; // tireur sélectionné dans la liste gauche
    let modeModification = false;

    // ----------------------------------------------------------------
    // Sélection d'un tireur dans les tables de gauche
    // ----------------------------------------------------------------
    $(document).on('click keypress', '.ligne-dispo', function (e) {
        if (e.type === 'keypress' && e.which !== 13) return;

        $('.ligne-dispo').removeClass('ligne-selectionnee');
        $(this).addClass('ligne-selectionnee');

        tireurSelectionneGauche = {
            type   : $(this).data('type'),
            id     : $(this).data('id'),
            nom    : $(this).data('nom'),
            prenom : $(this).data('prenom'),
            info   : $(this).data('info'),
        };

        $btnVers.prop('disabled', false);
    });

    // ----------------------------------------------------------------
    // Bouton "Inscrire ce tireur →" : ouvre le panneau en mode ajout
    // ----------------------------------------------------------------
    $btnVers.on('click', function () {
        if (!tireurSelectionneGauche) return;

        chargerPanneau(tireurSelectionneGauche, false, []);
    });

    // ----------------------------------------------------------------
    // Clic sur une ligne de la liste des inscrits : ouvre en mode modification
    // ----------------------------------------------------------------
    $(document).on('click keypress', '.ligne-inscrit', function (e) {
        if (e.type === 'keypress' && e.which !== 13) return;

        const tireur = {
            type   : $(this).data('tireur-type'),
            id     : $(this).data('tireur-id'),
            nom    : $(this).data('nom'),
            prenom : $(this).data('prenom'),
            info   : $(this).data('info'),
        };

        // Récupère les disciplines déjà assignées
        $.getJSON(
            APP_URL + '/challenges/' + CHALLENGE_ID + '/disciplines-tireur',
            { type: tireur.type, tid: tireur.id },
            function (rep) {
                chargerPanneau(tireur, true, rep.discipline_ids || []);
            }
        );
    });

    // ----------------------------------------------------------------
    // Bouton Annuler : ferme le panneau
    // ----------------------------------------------------------------
    $btnAnnuler.on('click', function () {
        fermerPanneau();
    });

    // ----------------------------------------------------------------
    // Soumission : Ajouter
    // ----------------------------------------------------------------
    $form.on('submit', function (e) {
        e.preventDefault();
        soumettre('inscrire', 'Inscrire ce tireur');
    });

    // ----------------------------------------------------------------
    // Bouton : Mettre à jour (modifier)
    // ----------------------------------------------------------------
    $btnModifier.on('click', function () {
        soumettre('modifier-inscriptions', 'Mettre à jour');
    });

    // ----------------------------------------------------------------
    // Suppression d'une inscription
    // ----------------------------------------------------------------
    $(document).on('click', '.btn-supprimer-inscription', function () {
        const inscriptionId = $(this).data('id');

        if (!confirm('Supprimer cette inscription ?')) return;

        $.ajax({
            url     : APP_URL + '/challenges/' + CHALLENGE_ID + '/supprimer-inscription',
            method  : 'POST',
            data    : { inscription_id: inscriptionId },
            dataType: 'json',
        })
        .done(function (rep) {
            if (rep.success) {
                rafraichirPanneaux(rep.panneaux);
                afficherAlerte(rep.message, 'succes');
                fermerPanneau();
            } else {
                afficherAlerte(rep.message || 'Erreur lors de la suppression.', 'erreur');
            }
        })
        .fail(function () {
            afficherAlerte('Une erreur est survenue.', 'erreur');
        });
    });

    // ----------------------------------------------------------------
    // Bouton Imprimer
    // ----------------------------------------------------------------
    $('#btn-imprimer-inscrits').on('click', function () {
        window.open(APP_URL + '/challenges/' + CHALLENGE_ID + '/imprimer', '_blank');
    });

    // ----------------------------------------------------------------
    // Fonctions internes
    // ----------------------------------------------------------------

    function chargerPanneau(tireur, modif, disciplineIds) {
        modeModification = modif;

        $('#insc-tireur-type').val(tireur.type);
        $('#insc-tireur-id').val(tireur.id);
        $('#insc-nom-affiche').text(tireur.nom + ' ' + tireur.prenom);

        const typeLabel = tireur.type === 'membre' ? 'Membre' : 'Non membre';
        const detail    = tireur.info ? typeLabel + ' — ' + tireur.info : typeLabel;
        $('#insc-detail-affiche').text(detail);

        // Coche les disciplines déjà assignées
        $('input[name="discipline_ids[]"]').prop('checked', false);
        $.each(disciplineIds, function (_, did) {
            $('input[name="discipline_ids[]"][value="' + did + '"]').prop('checked', true);
        });

        if (modif) {
            $titreP.text('Modifier les disciplines');
            $btnAjouter.hide();
            $btnModifier.show();
        } else {
            $titreP.text('Inscription au challenge');
            $btnAjouter.show();
            $btnModifier.hide();
        }

        cacherErreur();
        $panneau.removeAttr('hidden');
        $('html, body').animate({ scrollTop: $panneau.offset().top - 20 }, 250);
    }

    function fermerPanneau() {
        $panneau.attr('hidden', true);
        $form[0].reset();
        modeModification = false;
        tireurSelectionneGauche = null;
        $('.ligne-dispo').removeClass('ligne-selectionnee');
        $btnVers.prop('disabled', true);
        cacherErreur();
    }

    function soumettre(endpoint, btnLabel) {
        cacherErreur();

        const $btn = modeModification ? $btnModifier : $btnAjouter;
        $btn.prop('disabled', true).text('Enregistrement…');

        $.ajax({
            url     : APP_URL + '/challenges/' + CHALLENGE_ID + '/' + endpoint,
            method  : 'POST',
            data    : $form.serialize(),
            dataType: 'json',
        })
        .done(function (rep) {
            if (rep.success) {
                rafraichirPanneaux(rep.panneaux);
                afficherAlerte(rep.message, 'succes');
                fermerPanneau();
            } else {
                afficherErreur(rep.erreurs || [rep.message]);
            }
        })
        .fail(function (xhr) {
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.erreurs) { afficherErreur(data.erreurs); return; }
            } catch (e) { /* non JSON */ }
            afficherErreur(['Une erreur est survenue. Veuillez réessayer.']);
        })
        .always(function () {
            $btn.prop('disabled', false).text(btnLabel);
        });
    }

    function rafraichirPanneaux(panneaux) {
        $('#zone-membres-dispo').html(panneaux.membres);
        $('#zone-externes-dispo').html(panneaux.externes);
        $('#zone-inscrits').html(panneaux.inscrits);

        // Met à jour les compteurs
        $('#cpt-membres').text($('#zone-membres-dispo .ligne-dispo').length);
        $('#cpt-externes').text($('#zone-externes-dispo .ligne-dispo').length);
        $('#cpt-inscrits').text($('#zone-inscrits .ligne-inscrit').length);
    }

    function afficherErreur(erreurs) {
        const items = erreurs.map(function (msg) {
            return '<li>' + $('<span>').text(msg).html() + '</li>';
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

});
