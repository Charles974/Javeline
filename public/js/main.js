/* main.js — Script principal Javeline */

$(document).ready(function () {

    // ----------------------------------------------------------------
    // Session expirée : toute réponse AJAX en 401 renvoie vers la
    // page de connexion (le serveur ne peut pas rediriger un appel AJAX).
    // ----------------------------------------------------------------
    $(document).ajaxError(function (event, xhr) {
        if (xhr.status === 401) {
            window.location.href = APP_URL + '/connexion';
        }
    });

    // ----------------------------------------------------------------
    // Barre de progression AJAX : indicateur de chargement fin en haut
    // de page, non bloquant. Pilotee par les evenements AJAX globaux de
    // jQuery (ajaxStart/ajaxStop), elle couvre automatiquement tous les
    // appels ($.ajax, $.getJSON...) sans modification des autres scripts.
    // ----------------------------------------------------------------
    const $barreChargement = $('<div class="chargement-barre" aria-hidden="true"></div>').appendTo('body');
    let chargementTimer      = null;
    let chargementProgression = 0;

    $(document).ajaxStart(demarrerChargement);
    $(document).ajaxStop(terminerChargement);

    function demarrerChargement() {
        // Petit delai avant affichage : evite un clignotement sur les
        // requetes tres rapides (donnees servies quasi instantanement).
        clearTimeout(chargementTimer);
        chargementTimer = setTimeout(function () {
            chargementProgression = 0;
            $barreChargement.addClass('actif').css('width', '0%');
            avancerChargement();
        }, 150);
    }

    function avancerChargement() {
        // Progression simulee qui ralentit en approchant de 90 % ;
        // les 100 % restants sont atteints a la reponse du serveur.
        if (chargementProgression < 90) {
            chargementProgression += (90 - chargementProgression) * 0.25;
            $barreChargement.css('width', chargementProgression + '%');
            chargementTimer = setTimeout(avancerChargement, 200);
        }
    }

    function terminerChargement() {
        clearTimeout(chargementTimer);
        $barreChargement.css('width', '100%');
        // Laisse la barre atteindre 100 % avant de la faire disparaitre.
        setTimeout(function () {
            $barreChargement.removeClass('actif');
            setTimeout(function () {
                $barreChargement.css('width', '0%');
            }, 300);
        }, 200);
    }

    // ----------------------------------------------------------------
    // Changement de son propre mot de passe (modal du menu compte)
    // ----------------------------------------------------------------
    const $formMdp    = $('#form-mot-de-passe');
    const $erreursMdp = $('#mdp-erreurs');
    const modalMdpEl  = document.getElementById('modal-mot-de-passe');
    const modalMdp    = modalMdpEl ? bootstrap.Modal.getOrCreateInstance(modalMdpEl) : null;

    // Réinitialise le formulaire et les messages à l'ouverture de la modal
    $('#modal-mot-de-passe').on('show.bs.modal', function () {
        $formMdp[0].reset();
        $erreursMdp.attr('hidden', true).empty();
    });

    $formMdp.on('submit', function (e) {
        e.preventDefault();

        const $btn = $('#btn-valider-mdp');
        $erreursMdp.attr('hidden', true).empty();
        $btn.prop('disabled', true).text('Enregistrement…');

        $.ajax({
            url     : APP_URL + '/mot-de-passe',
            method  : 'POST',
            data    : $formMdp.serialize(),
            dataType: 'json',
        })
        .done(function (rep) {
            if (rep.success) {
                modalMdp.hide();
                afficherSucces(rep.message);
            } else {
                afficherErreursMdp(rep.erreurs || [rep.message]);
            }
        })
        .fail(function (xhr) {
            let msg = 'Une erreur est survenue. Veuillez réessayer.';
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.erreurs) {
                    afficherErreursMdp(data.erreurs);
                    return;
                }
                if (data.message) msg = data.message;
            } catch (e) { /* réponse non JSON */ }
            afficherErreursMdp([msg]);
        })
        .always(function () {
            $btn.prop('disabled', false).text('Enregistrer');
        });
    });

    function afficherErreursMdp(erreurs) {
        const liste = erreurs.map(function (e) {
            return '<li>' + $('<span>').text(e).html() + '</li>';
        }).join('');
        $erreursMdp.html('<ul class="mb-0">' + liste + '</ul>').removeAttr('hidden');
    }

    // --- Tooltips Bootstrap ---
    $('[data-bs-toggle="tooltip"]').each(function () {
        new bootstrap.Tooltip(this);
    });

    // --- Création d'un challenge (modal + AJAX) ---
    const $form       = $('#form-challenge');
    const $btnValider = $('#btn-valider-challenge');
    const $erreur     = $('#modal-erreur');
    const modalEl     = document.getElementById('modal-challenge');
    const modal       = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;

    // Réinitialise le formulaire et les messages à l'ouverture de la modal
    $('#modal-challenge').on('show.bs.modal', function () {
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
        // Crée la zone d'alerte si la page ne la fournit pas (le message de
        // succès du changement de mot de passe peut s'afficher partout).
        if ($('#alerte-succes').length === 0) {
            $('#contenu-principal').prepend(
                '<div id="alerte-succes" class="alerte-succes mx-auto mb-4" role="alert" aria-live="polite" hidden>'
                + '<span id="alerte-succes-texte"></span></div>'
            );
        }
        const $alerte = $('#alerte-succes');
        $('#alerte-succes-texte').text(message);
        $alerte.removeAttr('hidden');
        // Masque automatiquement après 4 secondes
        setTimeout(function () {
            $alerte.attr('hidden', true);
        }, 4000);
    }

    // ----------------------------------------------------------------
    // Tri générique des tableaux ".table-triable" (colonnes data-tri)
    // Délégué sur le document car les tableaux sont rechargés en AJAX.
    // ----------------------------------------------------------------
    $(document).on('click keypress', '.table-triable thead th[data-tri]', function (e) {
        if (e.type === 'keypress' && e.which !== 13) return;

        const $th      = $(this);
        const $table   = $th.closest('.table-triable');
        const $tbody   = $table.find('tbody');
        const index    = $th.index();
        const type     = $th.data('tri');
        const sensActuel = $th.data('sens-tri') === 'asc' ? 'asc' : null;
        const sens     = sensActuel === 'asc' ? 'desc' : 'asc';

        $table.find('thead th[data-tri]').removeData('sens-tri').removeClass('tri-asc tri-desc');
        $th.data('sens-tri', sens).addClass(sens === 'asc' ? 'tri-asc' : 'tri-desc');

        const lignes = $tbody.find('tr').get();

        lignes.sort(function (ligneA, ligneB) {
            const valeurA = valeurCellule(ligneA, index, type);
            const valeurB = valeurCellule(ligneB, index, type);
            let comparaison;
            if (type === 'date') {
                comparaison = new Date(valeurA) - new Date(valeurB);
            } else {
                comparaison = valeurA.localeCompare(valeurB, 'fr', { sensitivity: 'base' });
            }
            return sens === 'asc' ? comparaison : -comparaison;
        });

        $.each(lignes, function (i, ligne) {
            $tbody.append(ligne);
        });
    });

    function valeurCellule(ligne, index, type) {
        const $cellule = $(ligne).find('td').eq(index);
        const valeurAttribut = $cellule.data('valeur');
        if (valeurAttribut !== undefined) return String(valeurAttribut);
        return $cellule.text().trim();
    }

    // ----------------------------------------------------------------
    // Masque de saisie des numéros de téléphone : "07 77 01 01 01"
    // Délégué sur le document car certains champs sont injectés en AJAX.
    // ----------------------------------------------------------------
    $(document).on('input', 'input[type="tel"]', function () {
        const position = this.selectionStart === this.value.length;
        const chiffres  = this.value.replace(/\D/g, '');
        this.value = chiffres.replace(/(\d{2})(?=\d)/g, '$1 ');
        if (position) {
            this.selectionStart = this.selectionEnd = this.value.length;
        }
    });

});
