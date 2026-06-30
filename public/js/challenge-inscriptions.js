/* challenge-inscriptions.js — Gestion des inscriptions à un challenge */

$(document).ready(function () {

    if (CHALLENGE_ARCHIVE) return;

    const $panneau     = $('#panneau-inscription');
    const $titreP      = $('#panneau-titre');
    const $btnVers     = $('#btn-vers-inscription');
    const $btnAjouter  = $('#btn-ajouter-insc');
    const $btnModifier = $('#btn-modifier-insc');
    const $btnAnnuler  = $('#btn-annuler-inscription');
    const $erreurs     = $('#insc-erreurs');
    const $alerte      = $('#insc-alerte');
    const $form        = $('#form-inscription');

    let tireurSelectionneGauche = null;
    let modeModification = false;

    // ----------------------------------------------------------------
    // Recherche en temps réel dans les tables de gauche
    // ----------------------------------------------------------------
    $('#recherche-membres').on('input', function () {
        filtrerTableDispo('#zone-membres-dispo', $(this).val());
    });

    $('#recherche-externes').on('input', function () {
        filtrerTableDispo('#zone-externes-dispo', $(this).val());
    });

    function filtrerTableDispo(zone, terme) {
        const termeLower = terme.toLowerCase().trim();
        $(zone + ' .ligne-dispo').each(function () {
            const texte = $(this).text().toLowerCase();
            $(this).toggle(termeLower === '' || texte.includes(termeLower));
        });
    }

    // ----------------------------------------------------------------
    // Sélection d'un tireur (clic simple)
    // ----------------------------------------------------------------
    $(document).on('click', '.ligne-dispo', function () {
        $('.ligne-dispo').removeClass('ligne-selectionnee');
        $(this).addClass('ligne-selectionnee');
        tireurSelectionneGauche = extraireTireurDispo($(this));
        $btnVers.prop('disabled', false);
    });

    // ----------------------------------------------------------------
    // Double-clic : ouvre directement le panneau (point 2)
    // ----------------------------------------------------------------
    $(document).on('dblclick', '.ligne-dispo', function () {
        tireurSelectionneGauche = extraireTireurDispo($(this));
        chargerPanneau(tireurSelectionneGauche, false, []);
    });

    function extraireTireurDispo($ligne) {
        return {
            type   : $ligne.data('type'),
            id     : $ligne.data('id'),
            nom    : $ligne.data('nom'),
            prenom : $ligne.data('prenom'),
            info   : $ligne.data('info'),
        };
    }

    // ----------------------------------------------------------------
    // Bouton "Inscrire ce tireur →"
    // ----------------------------------------------------------------
    $btnVers.on('click', function () {
        if (tireurSelectionneGauche) {
            chargerPanneau(tireurSelectionneGauche, false, []);
        }
    });

    // ----------------------------------------------------------------
    // Clic sur un inscrit → ouvre en mode modification
    // ----------------------------------------------------------------
    $(document).on('click', '.ligne-inscrit', function () {
        const tireur = {
            type   : $(this).data('tireur-type'),
            id     : $(this).data('tireur-id'),
            nom    : $(this).data('nom'),
            prenom : $(this).data('prenom'),
            info   : $(this).data('info'),
        };

        $.getJSON(
            APP_URL + '/challenges/' + CHALLENGE_ID + '/disciplines-tireur',
            { type: tireur.type, tid: tireur.id },
            function (rep) {
                chargerPanneau(tireur, true, rep.discipline_ids || []);
            }
        );
    });

    // ----------------------------------------------------------------
    // Compteur de disciplines cochées (point 5)
    // ----------------------------------------------------------------
    $(document).on('change', '.disc-checkbox', function () {
        mettreAJourCompteurDisc();
    });

    function mettreAJourCompteurDisc() {
        const nb = $('.disc-checkbox:checked').length;
        $('#disc-compteur').text(nb + ' sélectionnée' + (nb > 1 ? 's' : ''));
        $('#disc-compteur').toggleClass('disc-compteur-vide', nb === 0);
    }

    // ----------------------------------------------------------------
    // Filtre multi-catégories sur la liste des inscrits (point 1)
    // ----------------------------------------------------------------
    $(document).on('change', '.filtre-check', function () {
        appliquerFiltresInscrits();
    });

    $('#btn-reset-filtres').on('click', function () {
        $('.filtre-check').prop('checked', false);
        appliquerFiltresInscrits();
    });

    function appliquerFiltresInscrits() {
        const typesActifs    = $('.filtre-check[data-filtre-type="type"]:checked').map(function () { return $(this).val(); }).get();
        const famillesActives = $('.filtre-check[data-filtre-type="famille"]:checked').map(function () { return $(this).val(); }).get();

        let nbVisibles = 0;

        $('#zone-inscrits .ligne-inscrit').each(function () {
            const type    = $(this).data('tireur-type');
            const famille = $(this).data('famille');

            const typeOk    = typesActifs.length === 0    || typesActifs.includes(type);
            const familleOk = famillesActives.length === 0 || famillesActives.includes(famille);

            const visible = typeOk && familleOk;
            $(this).toggle(visible);
            if (visible) nbVisibles++;
        });

        const total = $('#zone-inscrits .ligne-inscrit').length;
        const badge = nbVisibles === total
            ? total
            : nbVisibles + ' / ' + total;
        $('#cpt-inscrits').text(badge);

        // Indique visuellement si un filtre est actif
        const filtresActifs = typesActifs.length > 0 || famillesActives.length > 0;
        $('#filtre-categories-wrapper .dropdown-toggle').toggleClass('btn-warning btn-outline-light', filtresActifs);
    }

    // ----------------------------------------------------------------
    // Tri des colonnes de la liste des inscrits (point 6)
    // ----------------------------------------------------------------
    $(document).on('click', '.col-sortable', function () {
        const $th    = $(this);
        const col    = parseInt($th.data('col'), 10);
        const actuel = $th.attr('aria-sort');
        const asc    = actuel !== 'ascending';

        // Réinitialise les autres colonnes
        $('.col-sortable').attr('aria-sort', 'none').find('.sort-icone').text('');

        $th.attr('aria-sort', asc ? 'ascending' : 'descending');
        $th.find('.sort-icone').text(asc ? ' ▲' : ' ▼');

        const $tbody = $('#table-inscrits tbody');
        const lignes = $tbody.find('tr.ligne-inscrit').toArray();

        lignes.sort(function (a, b) {
            const valA = $(a).find('td').eq(col).text().trim().toLowerCase();
            const valB = $(b).find('td').eq(col).text().trim().toLowerCase();
            return asc ? valA.localeCompare(valB, 'fr') : valB.localeCompare(valA, 'fr');
        });

        $.each(lignes, function (_, ligne) { $tbody.append(ligne); });
    });

    // ----------------------------------------------------------------
    // Bouton Annuler
    // ----------------------------------------------------------------
    $btnAnnuler.on('click', function () {
        fermerPanneau();
    });

    // ----------------------------------------------------------------
    // Soumission : Ajouter
    // ----------------------------------------------------------------
    $form.on('submit', function (e) {
        e.preventDefault();
        soumettre('inscrire', 'Ajouter au challenge', $btnAjouter);
    });

    // ----------------------------------------------------------------
    // Bouton Mettre à jour
    // ----------------------------------------------------------------
    $btnModifier.on('click', function () {
        soumettre('modifier-inscriptions', 'Mettre à jour', $btnModifier);
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
    // Impression
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

        $('.disc-checkbox').prop('checked', false);
        $.each(disciplineIds, function (_, did) {
            $('.disc-checkbox[value="' + did + '"]').prop('checked', true);
        });

        mettreAJourCompteurDisc();

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
        mettreAJourCompteurDisc();
        cacherErreur();
    }

    function soumettre(endpoint, btnLabel, $btn) {
        cacherErreur();
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

        $('#cpt-membres').text($('#zone-membres-dispo .ligne-dispo').length);
        $('#cpt-externes').text($('#zone-externes-dispo .ligne-dispo').length);

        // Réapplique les filtres actifs après rafraîchissement
        appliquerFiltresInscrits();

        // Réapplique les recherches actives
        filtrerTableDispo('#zone-membres-dispo', $('#recherche-membres').val());
        filtrerTableDispo('#zone-externes-dispo', $('#recherche-externes').val());
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
