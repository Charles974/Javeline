/* challenge-inscriptions.js — Gestion des inscriptions à un challenge */

$(document).ready(function () {

    // ----------------------------------------------------------------
    // Impression — disponible aussi sur un challenge archivé (lecture seule),
    // donc branchée avant la neutralisation du mode archive ci-dessous.
    // ----------------------------------------------------------------
    $('#btn-imprimer-inscrits').on('click', function () {
        window.open(APP_URL + '/challenges/' + CHALLENGE_ID + '/imprimer', '_blank');
    });

    if (CHALLENGE_ARCHIVE) return;

    const $ficheVide    = $('#fiche-vide');
    const $ficheContenu = $('#fiche-contenu');
    const $ficheBadge   = $('#fiche-type-badge');
    const $btnVers      = $('#btn-vers-inscription');
    const $btnAjouter   = $('#btn-ajouter-insc');
    const $btnModifier  = $('#btn-modifier-insc');
    const $btnEnregistrerProfil = $('#btn-enregistrer-profil');
    const $btnAnnulerProfil     = $('#btn-annuler-profil');
    const $erreurs      = $('#insc-erreurs');
    const $alerte       = $('#insc-alerte');
    const $formProfil   = $('#form-profil');
    const $form         = $('#form-inscription');
    const $champsMembre  = $('#champs-membre');
    const $champsExterne = $('#champs-externe');

    let tireurSelectionneGauche = null;
    let tireurActuel   = null;   // { type, id } du tireur affiché dans la fiche
    let profilOriginal = null;   // valeurs d'origine, pour le bouton Annuler
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
    // Double-clic : ouvre directement la fiche (point 2)
    // ----------------------------------------------------------------
    $(document).on('dblclick', '.ligne-dispo', function () {
        tireurSelectionneGauche = extraireTireurDispo($(this));
        chargerFiche(tireurSelectionneGauche, false, []);
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
    // Bouton central "→" : ouvre la fiche du tireur sélectionné à gauche
    // ----------------------------------------------------------------
    $btnVers.on('click', function () {
        if (tireurSelectionneGauche) {
            chargerFiche(tireurSelectionneGauche, false, []);
        }
    });

    // ----------------------------------------------------------------
    // Clic sur un inscrit → ouvre la fiche en mode modification
    // ----------------------------------------------------------------
    $(document).on('click', '.ligne-inscrit', function (e) {
        if ($(e.target).closest('.btn-supprimer-inscription').length) return;
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
                chargerFiche(tireur, true, rep.discipline_ids || []);
            }
        );
    });

    // ----------------------------------------------------------------
    // Compteur de disciplines cochées
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
    // Soumission : Ajouter / Mettre à jour l'inscription aux disciplines
    // ----------------------------------------------------------------
    $form.on('submit', function (e) {
        e.preventDefault();
        soumettreInscription('inscrire', 'Ajouter au challenge', $btnAjouter);
    });

    $btnModifier.on('click', function () {
        soumettreInscription('modifier-inscriptions', 'Mettre à jour', $btnModifier);
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
                reinitialiserFiche();
            } else {
                afficherAlerte(rep.message || 'Erreur lors de la suppression.', 'erreur');
            }
        })
        .fail(function () {
            afficherAlerte('Une erreur est survenue.', 'erreur');
        });
    });

    // ----------------------------------------------------------------
    // Enregistrement silencieux du profil (membre ou non membre)
    // ----------------------------------------------------------------
    $formProfil.on('submit', function (e) {
        e.preventDefault();
        if (!tireurActuel) return;

        cacherErreur();
        $btnEnregistrerProfil.prop('disabled', true).text('Enregistrement…');

        const endpoint = tireurActuel.type === 'membre' ? '/membres/modifier' : '/externes/modifier';

        $.ajax({
            url     : APP_URL + endpoint,
            method  : 'POST',
            data    : $formProfil.serialize(),
            dataType: 'json',
        })
        .done(function (rep) {
            if (rep.success) {
                profilOriginal = collecterValeursProfil(tireurActuel.type);
                afficherAlerte('Fiche mise à jour.', 'succes');
                rafraichirListesChallenge();
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
            $btnEnregistrerProfil.prop('disabled', false).text('Enregistrer les modifications');
        });
    });

    $btnAnnulerProfil.on('click', function () {
        if (!tireurActuel || !profilOriginal) return;
        appliquerValeursProfil(tireurActuel.type, profilOriginal);
        cacherErreur();
    });

    // ----------------------------------------------------------------
    // Fonctions internes
    // ----------------------------------------------------------------

    function chargerFiche(tireur, modif, disciplineIds) {
        modeModification = modif;
        tireurActuel = { type: tireur.type, id: tireur.id };

        $ficheVide.attr('hidden', true);
        $ficheContenu.removeAttr('hidden');

        $ficheBadge.text(tireur.type === 'membre' ? 'Membre' : 'Non membre').removeAttr('hidden');

        $('#insc-tireur-type').val(tireur.type);
        $('#insc-tireur-id').val(tireur.id);

        $('.disc-checkbox').prop('checked', false);
        $.each(disciplineIds, function (_, did) {
            $('.disc-checkbox[value="' + did + '"]').prop('checked', true);
        });
        mettreAJourCompteurDisc();

        if (modif) {
            $btnAjouter.attr('hidden', true);
            $btnModifier.removeAttr('hidden');
        } else {
            $btnAjouter.removeAttr('hidden');
            $btnModifier.attr('hidden', true);
        }

        cacherErreur();

        // Charge le profil complet du tireur (pour l'édition à la volée)
        const endpoint = tireur.type === 'membre' ? '/membres/get/' : '/externes/get/';
        $.getJSON(APP_URL + endpoint + tireur.id)
            .done(function (rep) {
                if (!rep.success) return;
                const data = tireur.type === 'membre' ? rep.membre : rep.externe;
                appliquerValeursProfil(tireur.type, data);
                profilOriginal = collecterValeursProfil(tireur.type);
            })
            .fail(function () {
                afficherErreur(['Impossible de charger la fiche du tireur.']);
            });
    }

    function activerChampsFiche(type) {
        const membre = type === 'membre';
        $champsMembre.prop('hidden', !membre).find('input').prop('disabled', !membre);
        $champsExterne.prop('hidden', membre).find('input').prop('disabled', membre);
    }

    function appliquerValeursProfil(type, data) {
        activerChampsFiche(type);
        $('#profil-id').val(data.id);

        if (type === 'membre') {
            $('#pm-nom').val(data.nom);
            $('#pm-prenom').val(data.prenom);
            $('#pm-naissance').val(data.date_naissance);
            $('#pm-lieu').val(data.lieu_naissance);
            $('#pm-licence').val(data.numero_licence);
            $('#pm-adresse1').val(data.adresse1);
            $('#pm-adresse2').val(data.adresse2 || '');
            $('#pm-cp').val(data.code_postal);
            $('#pm-ville').val(data.ville);
            $('#pm-tel').val(data.telephone).trigger('input');
            $('#pm-email').val(data.email);
            $('#pm-certificat').prop('checked', data.certificat_medical == 1);
            $('#pm-coach').val(data.coach || '');
        } else {
            $('#pe-nom').val(data.nom);
            $('#pe-prenom').val(data.prenom);
            $('#pe-club').val(data.club);
            $('#pe-tel').val(data.telephone || '').trigger('input');
            $('#pe-email').val(data.email || '');
            $('#pe-etranger').prop('checked', data.etranger == 1);
            $('#pe-coach').val(data.coach || '');
        }
    }

    function collecterValeursProfil(type) {
        if (type === 'membre') {
            return {
                id                : $('#profil-id').val(),
                nom               : $('#pm-nom').val(),
                prenom            : $('#pm-prenom').val(),
                date_naissance    : $('#pm-naissance').val(),
                lieu_naissance    : $('#pm-lieu').val(),
                numero_licence    : $('#pm-licence').val(),
                adresse1          : $('#pm-adresse1').val(),
                adresse2          : $('#pm-adresse2').val(),
                code_postal       : $('#pm-cp').val(),
                ville             : $('#pm-ville').val(),
                telephone         : $('#pm-tel').val(),
                email             : $('#pm-email').val(),
                certificat_medical: $('#pm-certificat').is(':checked') ? 1 : 0,
                coach             : $('#pm-coach').val(),
            };
        }
        return {
            id       : $('#profil-id').val(),
            nom      : $('#pe-nom').val(),
            prenom   : $('#pe-prenom').val(),
            club     : $('#pe-club').val(),
            telephone: $('#pe-tel').val(),
            email    : $('#pe-email').val(),
            etranger : $('#pe-etranger').is(':checked') ? 1 : 0,
            coach    : $('#pe-coach').val(),
        };
    }

    function reinitialiserFiche() {
        $ficheContenu.attr('hidden', true);
        $ficheVide.removeAttr('hidden');
        $ficheBadge.attr('hidden', true);
        $formProfil[0].reset();
        $form[0].reset();
        modeModification = false;
        tireurActuel = null;
        profilOriginal = null;
        tireurSelectionneGauche = null;
        $('.ligne-dispo').removeClass('ligne-selectionnee');
        $btnVers.prop('disabled', true);
        mettreAJourCompteurDisc();
        cacherErreur();
    }

    function soumettreInscription(endpoint, btnLabel, $btn) {
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
                reinitialiserFiche();
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

    function rafraichirListesChallenge() {
        $.getJSON(APP_URL + '/challenges/' + CHALLENGE_ID + '/panneaux', function (rep) {
            if (rep.success) rafraichirPanneaux(rep.panneaux);
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

});
