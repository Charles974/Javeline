/* challenge-plan-de-tir.js — Grille interactive d'attribution des horaires */

$(document).ready(function () {

    let grille = GRILLE_INITIALE.slice();

    const $backdrop = $('#plan-backdrop');
    const $popover  = $('#plan-popover');

    // ----------------------------------------------------------------
    // Onglets par jour
    // ----------------------------------------------------------------
    $('.plan-jour-tab').on('click', function () {
        activerJour($(this).data('jour'));
    });

    function activerJour(jour) {
        const $tab = $('.plan-jour-tab[data-jour="' + jour + '"]');
        if (!$tab.length) return;
        $('.plan-jour-tab').removeClass('active').attr('aria-selected', 'false');
        $tab.addClass('active').attr('aria-selected', 'true');
        $('.plan-jour-panneau').addClass('d-none');
        $('.plan-jour-panneau[data-jour-panneau="' + jour + '"]').removeClass('d-none');
        fermerPopover();
    }

    function jourActif() {
        return $('.plan-jour-tab.active').data('jour');
    }

    // Recharge la page en conservant le jour affiché (restauré via l'ancre d'URL).
    function rechargerSurJour() {
        window.location.hash = 'jour=' + jourActif();
        window.location.reload();
    }

    // Au chargement : réactive le jour mémorisé dans l'ancre, le cas échéant.
    const hashJour = window.location.hash.match(/^#jour=(\d{4}-\d{2}-\d{2})$/);
    if (hashJour) {
        activerJour(hashJour[1]);
    }

    // ----------------------------------------------------------------
    // Clic sur une case de la grille
    // ----------------------------------------------------------------
    $(document).on('click', '.plan-case-btn', function () {
        const $btn        = this;
        const jour         = $(this).data('jour');
        const discipline   = parseInt($(this).data('discipline'), 10);
        const heure        = String($(this).data('heure'));

        const match = trouverMatch(jour, discipline, heure);
        if (match) {
            ouvrirPopoverRetrait($btn, match);
        } else {
            ouvrirPopoverAssignation($btn, jour, discipline, heure);
        }
    });

    function trouverMatch(jour, discipline, heure) {
        return grille.find(function (r) {
            return r.dateMatch === jour && r.disciplineCode === discipline && r.heureDebut === heure;
        }) || null;
    }

    function tireursEnAttente(discipline) {
        return grille.filter(function (r) {
            return r.disciplineCode === discipline && !r.dateMatch;
        });
    }

    // ----------------------------------------------------------------
    // Détection de proximité (même règle que côté serveur) : un tireur ne
    // peut pas être proposé sur un créneau à moins de DUREE_CONFLIT_MIN
    // minutes d'un autre de ses créneaux déjà planifiés, le même jour.
    // ----------------------------------------------------------------
    function conflitsPourTireur(tireurType, tireurId, jour, heure, excluInscriptionId) {
        const t = heureEnMinutes(heure);
        return grille.filter(function (r) {
            if (r.inscriptionId === excluInscriptionId) return false;
            if (r.tireurType !== tireurType || r.tireurId !== tireurId) return false;
            if (r.dateMatch !== jour || !r.heureDebut) return false;
            return Math.abs(heureEnMinutes(r.heureDebut) - t) < DUREE_CONFLIT_MIN;
        });
    }

    function heureEnMinutes(h) {
        const [hh, mm] = h.split(':').map(Number);
        return hh * 60 + mm;
    }

    function heurePlus(h, minutes) {
        const total = heureEnMinutes(h) + minutes;
        const hh = Math.floor(total / 60) % 24;
        const mm = total % 60;
        return (hh < 10 ? '0' : '') + hh + ':' + (mm < 10 ? '0' : '') + mm;
    }

    // ----------------------------------------------------------------
    // Popover : assignation d'un tireur en attente
    // ----------------------------------------------------------------
    function ouvrirPopoverAssignation(anchor, jour, discipline, heure) {
        const candidats = tireursEnAttente(discipline);

        let html = '<div class="plan-popover-head">'
            + '<h3>' + discipline + ' — ' + escapeHtml(labelDiscipline(discipline)) + '</h3>'
            + '<button type="button" class="plan-popover-close" aria-label="Fermer">&times;</button>'
            + '</div>'
            + '<p class="plan-popover-sub">' + heure.replace(':', ' h ') + ' · Créneau libre</p>';

        if (candidats.length === 0) {
            html += '<p class="plan-popover-vide">Tous les tireurs inscrits à cette discipline sont déjà programmés.</p>';
        } else {
            html += '<div class="plan-candidats">';
            candidats.forEach(function (c, i) {
                const conflits = conflitsPourTireur(c.tireurType, c.tireurId, jour, heure, c.inscriptionId);
                const dispo    = conflits.length === 0;
                html += '<button type="button" class="plan-candidat' + (dispo ? '' : ' plan-candidat-conflit') + '" data-candidat="' + i + '">'
                    + '<span class="plan-candidat-nom">' + escapeHtml(c.nom) + ' <span class="plan-candidat-coach">/ ' + escapeHtml(c.coach) + '</span></span>'
                    + '<span class="plan-candidat-meta">' + (dispo
                        ? 'Disponible'
                        : 'Proche de ' + conflits[0].heureDebut + ' — ' + conflits[0].disciplineCode + ' ' + escapeHtml(conflits[0].disciplineFr)) + '</span>'
                    + '</button>';
            });
            html += '</div>';
        }

        afficherPopover(anchor, html);

        $popover.find('.plan-candidat').on('click', function () {
            const c = candidats[parseInt($(this).data('candidat'), 10)];
            assigner(c, jour, heure);
        });
    }

    function assigner(candidat, jour, heure) {
        const heureFin = heurePlus(heure, 60);

        $.ajax({
            url: APP_URL + '/challenges/' + CHALLENGE_ID + '/modifier-horaire',
            method: 'POST',
            data: {
                inscription_id: candidat.inscriptionId,
                date_match: jour,
                heure_debut: heure,
                heure_fin: heureFin,
            },
            dataType: 'json',
        }).done(function (rep) {
            if (!rep.success) {
                afficherAlerte((rep.erreurs && rep.erreurs.join(' ')) || rep.message || 'Erreur lors de l\'assignation.', 'erreur');
                return;
            }
            candidat.dateMatch  = jour;
            candidat.heureDebut = heure;
            candidat.heureFin   = heureFin;

            mettreAJourCase(jour, candidat.disciplineCode, heure, candidat);
            mettreAJourCompteurs();
            fermerPopover();

            afficherAlerte(
                rep.chevauchement ? rep.avertissement : 'Tireur assigné.',
                rep.chevauchement ? 'avertissement' : 'succes'
            );
        }).fail(function (xhr) {
            afficherAlerte(messageErreur(xhr), 'erreur');
        });
    }

    // ----------------------------------------------------------------
    // Popover : retrait d'un tireur déjà programmé
    // ----------------------------------------------------------------
    function ouvrirPopoverRetrait(anchor, match) {
        let html = '<div class="plan-popover-head">'
            + '<h3>' + match.disciplineCode + ' — ' + escapeHtml(match.disciplineFr) + '</h3>'
            + '<button type="button" class="plan-popover-close" aria-label="Fermer">&times;</button>'
            + '</div>'
            + '<p class="plan-popover-sub">' + match.heureDebut.replace(':', ' h ') + ' · Créneau occupé</p>'
            + '<div class="plan-candidat" style="cursor:default;">'
            + '<span class="plan-candidat-nom">' + escapeHtml(match.nom) + ' ' + escapeHtml(match.prenom) + '</span>'
            + '<span class="plan-candidat-meta">' + escapeHtml(match.coach) + '</span>'
            + '</div>';

        if (match.scoreId) {
            html += '<p class="plan-popover-vide">Un score a déjà été saisi : impossible de retirer cet horaire.</p>';
        } else {
            html += '<button type="button" class="plan-retirer-action">Retirer ce tireur</button>';
        }

        afficherPopover(anchor, html);

        $popover.find('.plan-retirer-action').on('click', function () {
            retirer(match);
        });
    }

    function retirer(match) {
        $.ajax({
            url: APP_URL + '/challenges/' + CHALLENGE_ID + '/retirer-horaire',
            method: 'POST',
            data: { inscription_id: match.inscriptionId },
            dataType: 'json',
        }).done(function (rep) {
            if (!rep.success) {
                afficherAlerte(rep.message || 'Erreur lors du retrait.', 'erreur');
                return;
            }
            const jour = match.dateMatch, discipline = match.disciplineCode, heure = match.heureDebut;
            match.dateMatch = null;
            match.heureDebut = null;
            match.heureFin = null;

            viderCase(jour, discipline, heure);
            mettreAJourCompteurs();
            fermerPopover();
            afficherAlerte('Horaire retiré.', 'succes');
        }).fail(function (xhr) {
            afficherAlerte(messageErreur(xhr), 'erreur');
        });
    }

    // ----------------------------------------------------------------
    // Mise à jour du DOM après assignation / retrait
    // ----------------------------------------------------------------
    function selecteurCase(jour, discipline, heure) {
        return '.plan-case-btn[data-jour="' + jour + '"][data-discipline="' + discipline + '"][data-heure="' + heure + '"]';
    }

    function mettreAJourCase(jour, discipline, heure, candidat) {
        const $btn = $(selecteurCase(jour, discipline, heure));
        $btn.addClass('plan-case-remplie').html(
            '<span class="plan-case-ligne"><span class="plan-case-nom">' + escapeHtml(candidat.nom)
            + '</span> / <span class="plan-case-coach">' + escapeHtml(candidat.coach) + '</span></span>'
        );
    }

    function viderCase(jour, discipline, heure) {
        $(selecteurCase(jour, discipline, heure)).removeClass('plan-case-remplie').empty();
    }

    // Compteurs "Programmés / En attente" de l'en-tête, recalculés depuis l'état local.
    function mettreAJourCompteurs() {
        const programmes = grille.filter(function (r) { return r.dateMatch && r.heureDebut; }).length;
        $('#plan-stat-programmes').text(programmes);
        $('#plan-stat-attente').text(grille.length - programmes);
    }

    function labelDiscipline(code) {
        const r = grille.find(function (g) { return g.disciplineCode === code; });
        return r ? r.disciplineFr : '';
    }

    // ----------------------------------------------------------------
    // Popover : affichage / positionnement / fermeture
    // ----------------------------------------------------------------
    function afficherPopover(anchor, html) {
        $popover.html(html);
        $popover.removeAttr('hidden');
        $backdrop.removeAttr('hidden');

        const r  = anchor.getBoundingClientRect();
        const pw = $popover.outerWidth();
        let left = r.right + 10;
        if (left + pw > window.innerWidth - 8) left = r.left - pw - 10;
        if (left < 8) left = Math.min(Math.max(8, r.left), window.innerWidth - pw - 8);
        const top = Math.min(r.top, window.innerHeight - $popover.outerHeight() - 16);

        $popover.css({ left: left + 'px', top: Math.max(8, top) + 'px' });

        $popover.find('.plan-popover-close').on('click', fermerPopover);
    }

    function fermerPopover() {
        $popover.attr('hidden', true).empty();
        $backdrop.attr('hidden', true);
    }

    $backdrop.on('click', fermerPopover);
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') fermerPopover();
    });

    // ----------------------------------------------------------------
    // Ajout / retrait de blocs horaires libres
    // ----------------------------------------------------------------
    const $modalBloc  = $('#modal-bloc');
    const modalBlocBS = new bootstrap.Modal($modalBloc[0]);
    let jourBlocCourant = null;

    $(document).on('click', '.btn-ajouter-bloc', function () {
        jourBlocCourant = jourActif();
        $('#bloc-libelle').val('');
        $('#bloc-debut').val('');
        $('#bloc-fin').val('');
        $('#bloc-erreur').attr('hidden', true).empty();
        modalBlocBS.show();
    });

    $('#btn-bloc-enregistrer').on('click', function () {
        const libelle = $('#bloc-libelle').val().trim();
        const debut   = $('#bloc-debut').val();
        const fin     = $('#bloc-fin').val();

        if (!libelle || !debut || !fin) {
            afficherErreurBloc('Merci de renseigner le libellé et les heures.');
            return;
        }
        if (fin < debut) {
            afficherErreurBloc('L\'heure de fin doit être égale ou postérieure à l\'heure de début.');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).text('Ajout…');

        $.ajax({
            url: APP_URL + '/challenges/' + CHALLENGE_ID + '/plan-de-tir/blocs',
            method: 'POST',
            data: { jour: jourBlocCourant, libelle: libelle, heure_debut: debut, heure_fin: fin },
            dataType: 'json',
        }).done(function (rep) {
            if (!rep.success) {
                afficherErreurBloc((rep.erreurs && rep.erreurs.join(' ')) || rep.message || 'Erreur lors de l\'ajout.');
                return;
            }
            // La fusion des lignes (rowspan) est calculée côté serveur : on recharge la grille.
            rechargerSurJour();
        }).fail(function (xhr) {
            afficherErreurBloc(messageErreur(xhr));
        }).always(function () {
            $btn.prop('disabled', false).text('Ajouter');
        });
    });

    function afficherErreurBloc(message) {
        $('#bloc-erreur').text(message).removeAttr('hidden');
    }

    $(document).on('click', '.btn-retirer-bloc', function () {
        const blocId = $(this).data('bloc-id');
        if (!confirm('Retirer ce bloc horaire ?')) return;

        $.ajax({
            url: APP_URL + '/challenges/' + CHALLENGE_ID + '/plan-de-tir/blocs/supprimer',
            method: 'POST',
            data: { bloc_id: blocId },
            dataType: 'json',
        }).done(function (rep) {
            if (!rep.success) {
                afficherAlerte(rep.message || 'Erreur lors de la suppression.', 'erreur');
                return;
            }
            rechargerSurJour();
        }).fail(function (xhr) {
            afficherAlerte(messageErreur(xhr), 'erreur');
        });
    });

    // ----------------------------------------------------------------
    // Utilitaires
    // ----------------------------------------------------------------
    function escapeHtml(s) {
        return $('<div>').text(s == null ? '' : s).html();
    }

    function messageErreur(xhr) {
        try {
            const data = JSON.parse(xhr.responseText);
            if (data.erreurs) return data.erreurs.join(' ');
            if (data.message) return data.message;
        } catch (e) { /* réponse non JSON */ }
        return 'Une erreur est survenue. Veuillez réessayer.';
    }

    function afficherAlerte(message, type) {
        const $alerte = $('#plan-alerte');
        $alerte
            .removeClass('alerte-succes alerte-erreur alerte-avertissement')
            .addClass('alerte-' + type)
            .text(message)
            .removeAttr('hidden');
        setTimeout(function () { $alerte.attr('hidden', true); }, type === 'succes' ? 4000 : 6000);
    }

});
