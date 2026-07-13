/* challenge-resume.js — Filtres, tri et saisie des scores */

$(document).ready(function () {

    // ----------------------------------------------------------------
    // Variables d'état
    // ----------------------------------------------------------------
    let inscriptionCourante = null; // données de la ligne ouverte dans la modale
    let valeurOriginales    = {};   // valeurs au moment de l'ouverture
    const $modal            = $('#modal-score');
    const modalBS           = new bootstrap.Modal($modal[0]);

    let horaireCourant   = null; // données de la ligne dont l'horaire est en cours d'édition
    const $modalHoraire  = $('#modal-horaire');
    const modalHoraireBS = new bootstrap.Modal($modalHoraire[0]);

    // ----------------------------------------------------------------
    // Filtre combiné : statut de tir + discipline
    // ----------------------------------------------------------------
    function appliquerFiltres() {
        const statutFiltre     = $('input[name="filtre-tir"]:checked').val();
        const disciplineFiltre = $('#filtre-discipline').val();

        let nbAffiches = 0;

        $('#table-resume tbody .ligne-participant').each(function () {
            const aScore     = $(this).data('score') === 1;
            const discipline = String($(this).data('discipline-code'));

            const statutOk = statutFiltre === 'tous'
                || (statutFiltre === 'tires'   &&  aScore)
                || (statutFiltre === 'attente' && !aScore);

            const disciplineOk = disciplineFiltre === '' || discipline === disciplineFiltre;

            const visible = statutOk && disciplineOk;
            $(this).toggle(visible);
            if (visible) nbAffiches++;
        });

        $('#cpt-affiches').text(nbAffiches);
    }

    $('input[name="filtre-tir"]').on('change', appliquerFiltres);

    $('#filtre-discipline').on('change', function () {
        appliquerFiltres();
        // Active / désactive le bouton classement-filtre selon qu'une discipline est sélectionnée
        const actif = $(this).val() !== '';
        $('#btn-classement-filtre')
            .prop('disabled', !actif)
            .toggleClass('btn-outline-secondary', !actif)
            .toggleClass('btn-outline-primary',    actif);
    });

    // ----------------------------------------------------------------
    // Bouton "Classement avec filtre" — ouvre la vue print filtrée
    // ----------------------------------------------------------------
    $('#btn-classement-filtre').on('click', function () {
        const discipline = $('#filtre-discipline').val();
        if (!discipline) return;
        const url = APP_URL + '/challenges/' + CHALLENGE_ID + '/classements?discipline=' + encodeURIComponent(discipline);
        window.open(url, '_blank');
    });

    // ----------------------------------------------------------------
    // Tri des colonnes
    // ----------------------------------------------------------------
    $(document).on('click', '.col-sortable', function () {
        const $th    = $(this);
        const col    = parseInt($th.data('col'), 10);
        const actuel = $th.attr('aria-sort');
        const asc    = actuel !== 'ascending';

        $('.col-sortable').attr('aria-sort', 'none').find('.sort-icone').text('');
        $th.attr('aria-sort', asc ? 'ascending' : 'descending');
        $th.find('.sort-icone').text(asc ? ' ▲' : ' ▼');

        const $tbody = $('#table-resume tbody');
        const lignes = $tbody.find('tr.ligne-participant').toArray();

        lignes.sort(function (a, b) {
            const valA = $(a).find('td').eq(col).text().trim().toLowerCase();
            const valB = $(b).find('td').eq(col).text().trim().toLowerCase();
            return asc ? valA.localeCompare(valB, 'fr') : valB.localeCompare(valA, 'fr');
        });

        $.each(lignes, function (_, ligne) { $tbody.append(ligne); });
    });

    // ----------------------------------------------------------------
    // Clic sur une ligne → ouvre la modale
    // ----------------------------------------------------------------
    if (!CHALLENGE_ARCHIVE) {
        $(document).on('click keydown', '.ligne-cliquable', function (e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
            e.preventDefault();
            ouvrirModal($(this));
        });
    }

    function ouvrirModal($ligne) {
        inscriptionCourante = {
            inscriptionId  : $ligne.data('inscription-id'),
            nom            : $ligne.data('nom'),
            prenom         : $ligne.data('prenom'),
            club           : $ligne.data('club'),
            tireurType     : $ligne.data('tireur-type'),
            disciplineCode : $ligne.data('discipline-code'),
            disciplineFr   : $ligne.data('discipline-fr'),
        };

        // Valeurs actuelles (ou 0 si pas encore de score)
        const poulets  = $ligne.data('poulets')  !== '' ? parseInt($ligne.data('poulets'),  10) : 0;
        const cochons  = $ligne.data('cochons')  !== '' ? parseInt($ligne.data('cochons'),  10) : 0;
        const dindons  = $ligne.data('dindons')  !== '' ? parseInt($ligne.data('dindons'),  10) : 0;
        const mouflons = $ligne.data('mouflons') !== '' ? parseInt($ligne.data('mouflons'), 10) : 0;

        valeurOriginales = { poulets, cochons, dindons, mouflons };

        // Remplir les informations du tireur
        const estMembre = inscriptionCourante.tireurType === 'membre';
        const typeLabel = estMembre ? 'Membre' : 'Non membre';
        const club      = estMembre ? 'Javeline' : (inscriptionCourante.club || '');
        $('#score-nom').text(inscriptionCourante.nom + ' ' + inscriptionCourante.prenom);

        const badgeClasse = estMembre ? 'score-tireur-badge-membre' : 'score-tireur-badge-externe';
        const $badge = $('<span>')
            .addClass('score-tireur-badge ' + badgeClasse)
            .text(typeLabel);
        $('#score-detail').empty().append($badge);
        if (club) {
            $('#score-detail').append($('<span>').addClass('score-tireur-club').text(club));
        }
        $('#score-discipline').text(
            'Discipline : ' + inscriptionCourante.disciplineCode + ' — ' + inscriptionCourante.disciplineFr
        );

        // Remplir les inputs
        $('#score-poulets').val(poulets || '');
        $('#score-cochons').val(cochons || '');
        $('#score-dindons').val(dindons || '');
        $('#score-mouflons').val(mouflons || '');

        mettreAJourTotal();

        modalBS.show();
    }

    // ----------------------------------------------------------------
    // Édition de l'horaire d'un match (uniquement si challenge non archivé)
    // ----------------------------------------------------------------
    if (!CHALLENGE_ARCHIVE) {
        $(document).on('click', '.btn-modifier-horaire', function (e) {
            e.stopPropagation();
            ouvrirModalHoraire($(this).closest('.ligne-participant'));
        });
    }

    function ouvrirModalHoraire($ligne) {
        horaireCourant = {
            inscriptionId : $ligne.data('inscription-id'),
            tireurType    : $ligne.data('tireur-type'),
            tireurId      : $ligne.data('tireur-id'),
            nom           : $ligne.data('nom'),
            prenom        : $ligne.data('prenom'),
            disciplineCode: $ligne.data('discipline-code'),
            disciplineFr  : $ligne.data('discipline-fr'),
        };

        $('#horaire-nom').text(horaireCourant.nom + ' ' + horaireCourant.prenom);
        $('#horaire-discipline').text(
            'Discipline : ' + horaireCourant.disciplineCode + ' — ' + horaireCourant.disciplineFr
        );

        $('#horaire-date').val(convertirDateEnISO($ligne.data('date-match')));
        $('#horaire-debut').val($ligne.data('heure-debut') || '');
        $('#horaire-fin').val($ligne.data('heure-fin') || '');

        cacherAvertissementChevauchement();
        verifierChevauchementLive();

        modalHoraireBS.show();
    }

    function convertirDateEnISO(dateMatch) {
        // date_match est déjà au format Y-m-d (issu de la base)
        return dateMatch || '';
    }

    // ----------------------------------------------------------------
    // Avertissement de chevauchement en temps réel (non bloquant),
    // basé sur les autres lignes du même tireur déjà affichées dans le tableau.
    // ----------------------------------------------------------------
    $modalHoraire.on('input', '#horaire-date, #horaire-debut, #horaire-fin', verifierChevauchementLive);

    function verifierChevauchementLive() {
        if (!horaireCourant) return;

        const date  = $('#horaire-date').val();
        const debut = $('#horaire-debut').val();
        const fin   = $('#horaire-fin').val();

        if (!date || !debut || !fin || fin <= debut) {
            cacherAvertissementChevauchement();
            return;
        }

        const conflits = [];
        $('#table-resume tbody .ligne-participant').each(function () {
            const $autre = $(this);
            if (String($autre.data('inscription-id')) === String(horaireCourant.inscriptionId)) return;
            if ($autre.data('tireur-type') !== horaireCourant.tireurType) return;
            if (String($autre.data('tireur-id')) !== String(horaireCourant.tireurId)) return;

            const autreDate  = $autre.data('date-match');
            const autreDebut = $autre.data('heure-debut');
            const autreFin   = $autre.data('heure-fin');
            if (!autreDate || !autreDebut || !autreFin) return;

            if (String(autreDate) === date && debut < autreFin && autreDebut < fin) {
                conflits.push(
                    $autre.data('discipline-code') + ' — ' + $autre.data('discipline-fr')
                    + ' (' + autreDebut + '–' + autreFin + ')'
                );
            }
        });

        if (conflits.length) {
            $('#horaire-avertissement')
                .text('Chevauchement d\'horaire avec : ' + conflits.join(', '))
                .removeAttr('hidden');
        } else {
            cacherAvertissementChevauchement();
        }
    }

    function cacherAvertissementChevauchement() {
        $('#horaire-avertissement').attr('hidden', true).empty();
    }

    // ----------------------------------------------------------------
    // Bouton Enregistrer (horaire) → AJAX. Le chevauchement n'empêche
    // jamais l'enregistrement, il déclenche seulement un avertissement.
    // ----------------------------------------------------------------
    $('#btn-horaire-enregistrer').on('click', function () {
        if (!horaireCourant) return;

        const date  = $('#horaire-date').val();
        const debut = $('#horaire-debut').val();
        const fin   = $('#horaire-fin').val();

        if (!date || !debut || !fin) {
            alert('Merci de renseigner la date et les heures de début et de fin.');
            return;
        }
        if (fin <= debut) {
            alert('L\'heure de fin doit être postérieure à l\'heure de début.');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).text('Enregistrement…');

        $.ajax({
            url     : APP_URL + '/challenges/' + CHALLENGE_ID + '/modifier-horaire',
            method  : 'POST',
            data    : {
                inscription_id : horaireCourant.inscriptionId,
                date_match     : date,
                heure_debut    : debut,
                heure_fin      : fin,
            },
            dataType: 'json',
        })
        .done(function (rep) {
            if (rep.success) {
                mettreAJourHoraireLigne(rep);
                modalHoraireBS.hide();
                if (rep.chevauchement) {
                    afficherAlerteResume(rep.avertissement, 'avertissement');
                } else {
                    afficherAlerteResume('Horaire mis à jour.', 'succes');
                }
            } else {
                alert((rep.erreurs && rep.erreurs.join(' ')) || rep.message || 'Erreur lors de l\'enregistrement.');
            }
        })
        .fail(function (xhr) {
            let msg = 'Une erreur est survenue. Veuillez réessayer.';
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.erreurs) msg = data.erreurs.join(' ');
                else if (data.message) msg = data.message;
            } catch (e) { /* réponse non JSON */ }
            alert(msg);
        })
        .always(function () {
            $btn.prop('disabled', false).text('Enregistrer');
        });
    });

    function mettreAJourHoraireLigne(rep) {
        const $ligne = $('#table-resume tbody .ligne-participant[data-inscription-id="' + rep.inscription_id + '"]');
        if (!$ligne.length) return;

        $ligne
            .data('date-match', rep.date_match).attr('data-date-match', rep.date_match)
            .data('heure-debut', rep.heure_debut).attr('data-heure-debut', rep.heure_debut)
            .data('heure-fin', rep.heure_fin).attr('data-heure-fin', rep.heure_fin);

        const [annee, mois, jour] = rep.date_match.split('-');
        $ligne.find('.resume-horaire-texte').text(jour + '/' + mois + ' ' + rep.heure_debut);
    }

    function afficherAlerteResume(message, type) {
        const $alerte = $('#resume-alerte');
        $alerte
            .removeClass('alerte-succes alerte-erreur alerte-avertissement')
            .addClass('alerte-' + type)
            .text(message)
            .removeAttr('hidden');
        setTimeout(function () { $alerte.attr('hidden', true); }, type === 'avertissement' ? 6000 : 4000);
    }

    // ----------------------------------------------------------------
    // Calcul du total
    // ----------------------------------------------------------------
    $modal.on('input', '.score-input', mettreAJourTotal);

    function mettreAJourTotal() {
        let total = 0;
        $('.score-input').each(function () {
            const val = parseInt($(this).val(), 10);
            if (!isNaN(val) && val > 0) total += val;
        });
        $('#score-total').text(total);
    }

    // ----------------------------------------------------------------
    // Lecture des valeurs saisies
    // ----------------------------------------------------------------
    function lireValeurs() {
        return {
            poulets  : Math.max(0, parseInt($('#score-poulets').val(),  10) || 0),
            cochons  : Math.max(0, parseInt($('#score-cochons').val(),  10) || 0),
            dindons  : Math.max(0, parseInt($('#score-dindons').val(),  10) || 0),
            mouflons : Math.max(0, parseInt($('#score-mouflons').val(), 10) || 0),
        };
    }

    function estModifie() {
        const v = lireValeurs();
        return v.poulets  !== valeurOriginales.poulets
            || v.cochons  !== valeurOriginales.cochons
            || v.dindons  !== valeurOriginales.dindons
            || v.mouflons !== valeurOriginales.mouflons;
    }

    // ----------------------------------------------------------------
    // Bouton Annuler la saisie → remet les valeurs d'origine
    // ----------------------------------------------------------------
    $('#btn-score-annuler').on('click', function () {
        if (estModifie()) {
            if (!confirm('Annuler les modifications et remettre les valeurs précédentes ?')) return;
        }
        $('#score-poulets').val(valeurOriginales.poulets  || '');
        $('#score-cochons').val(valeurOriginales.cochons  || '');
        $('#score-dindons').val(valeurOriginales.dindons  || '');
        $('#score-mouflons').val(valeurOriginales.mouflons || '');
        mettreAJourTotal();
    });

    // ----------------------------------------------------------------
    // Bouton Fermer (footer) — et bouton X de la modale
    // ----------------------------------------------------------------
    $('#btn-score-fermer, #btn-modal-fermer').on('click', function () {
        if (estModifie()) {
            if (!confirm('Des modifications non enregistrées seront perdues. Fermer quand même ?')) return;
        }
        fermerModal();
    });

    // Empêche la fermeture du modal par backdrop ou Échap sans confirmation
    $modal[0].addEventListener('hide.bs.modal', function (e) {
        // Autorisée si la fermeture vient de fermerModal() (flag interne)
        if ($modal.data('fermeture-autorisee')) {
            $modal.removeData('fermeture-autorisee');
            return;
        }
        // Sinon bloquer
        e.preventDefault();
    });

    function fermerModal() {
        $modal.data('fermeture-autorisee', true);
        modalBS.hide();
        inscriptionCourante = null;
        valeurOriginales    = {};
    }

    // ----------------------------------------------------------------
    // Bouton Enregistrer → AJAX
    // ----------------------------------------------------------------
    $('#btn-score-enregistrer').on('click', function () {
        const valeurs = lireValeurs();
        const $btn    = $(this);

        $btn.prop('disabled', true).text('Enregistrement…');

        $.ajax({
            url     : APP_URL + '/challenges/' + CHALLENGE_ID + '/saisir-score',
            method  : 'POST',
            data    : {
                inscription_id : inscriptionCourante.inscriptionId,
                poulets        : valeurs.poulets,
                cochons        : valeurs.cochons,
                dindons        : valeurs.dindons,
                mouflons       : valeurs.mouflons,
            },
            dataType: 'json',
        })
        .done(function (rep) {
            if (rep.success) {
                mettreAJourLigne(rep);
                fermerModal();
            } else {
                alert(rep.message || 'Erreur lors de l\'enregistrement.');
            }
        })
        .fail(function (xhr) {
            let msg = 'Une erreur est survenue. Veuillez réessayer.';
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.message) msg = data.message;
            } catch (e) { /* réponse non JSON */ }
            alert(msg);
        })
        .always(function () {
            $btn.prop('disabled', false).text('Enregistrer');
        });
    });

    // ----------------------------------------------------------------
    // Mise à jour de la ligne dans le tableau (sans rechargement)
    // Préserve le tri et les filtres actifs
    // ----------------------------------------------------------------
    function mettreAJourLigne(rep) {
        const $ligne = $('#table-resume tbody .ligne-participant[data-inscription-id="' + rep.inscription_id + '"]');
        if (!$ligne.length) return;

        const detScore = rep.poulets + '/' + rep.cochons + '/' + rep.dindons + '/' + rep.mouflons;

        // Met à jour les data-attributes
        $ligne
            .data('score',    1)
            .attr('data-score', 1)
            .data('poulets',  rep.poulets).attr('data-poulets',  rep.poulets)
            .data('cochons',  rep.cochons).attr('data-cochons',  rep.cochons)
            .data('dindons',  rep.dindons).attr('data-dindons',  rep.dindons)
            .data('mouflons', rep.mouflons).attr('data-mouflons', rep.mouflons);

        // Met à jour le style de la ligne
        $ligne.removeClass('ligne-attente').addClass('ligne-score');

        // Met à jour la cellule score (colonne 5)
        $ligne.find('td').eq(5).html(
            '<span class="resume-total fw-bold">' + rep.total + '</span>'
            + ' <span class="resume-detail text-muted">(' + detScore + ')</span>'
        );

        // Met à jour les compteurs
        const nbScores = $('#table-resume tbody .ligne-participant[data-score="1"]').length;
        const nbTotal  = $('#table-resume tbody .ligne-participant').length;
        $('#cpt-scores').text(nbScores);
        $('#cpt-attente').text(nbTotal - nbScores);

        // Réapplique les filtres actifs (sans changer le tri courant)
        appliquerFiltres();
    }

});
