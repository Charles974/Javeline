/* challenge-resume.js — Filtres, tri et saisie des scores */

$(document).ready(function () {

    // ----------------------------------------------------------------
    // Variables d'état
    // ----------------------------------------------------------------
    let inscriptionCourante = null; // données de la ligne ouverte dans la modale
    let valeurOriginales    = {};   // valeurs au moment de l'ouverture
    const $modal            = $('#modal-score');
    const modalBS           = new bootstrap.Modal($modal[0]);

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
        const typeLabel = inscriptionCourante.tireurType === 'membre' ? 'Membre' : 'Non membre';
        const club      = inscriptionCourante.club || '';
        $('#score-nom').text(inscriptionCourante.nom + ' ' + inscriptionCourante.prenom);
        $('#score-detail').text(club ? typeLabel + ' — ' + club : typeLabel);
        $('#score-discipline').text(
            'Discipline : ' + inscriptionCourante.disciplineCode + ' — ' + inscriptionCourante.disciplineFr
        );

        // Remplir les inputs
        $('#score-poulets').val(poulets || '');
        $('#score-cochons').val(cochons || '');
        $('#score-dindons').val(dindons || '');
        $('#score-mouflons').val(mouflons || '');

        cacherAlerteMax();
        mettreAJourTotal();

        modalBS.show();
    }

    // ----------------------------------------------------------------
    // Calcul du total et avertissement si > 10
    // ----------------------------------------------------------------
    $modal.on('input', '.score-input', function () {
        mettreAJourTotal();
        verifierDepassement();
    });

    function mettreAJourTotal() {
        let total = 0;
        $('.score-input').each(function () {
            const val = parseInt($(this).val(), 10);
            if (!isNaN(val) && val > 0) total += val;
        });
        $('#score-total').text(total);
    }

    function verifierDepassement() {
        let depassement = false;
        $('.score-input').each(function () {
            const val = parseInt($(this).val(), 10);
            const tropElevee = !isNaN(val) && val > 10;
            $(this).toggleClass('score-input-invalide', tropElevee);
            if (tropElevee) depassement = true;
        });
        $('#score-alerte-max').attr('hidden', !depassement || null);
    }

    function cacherAlerteMax() {
        $('.score-input').removeClass('score-input-invalide');
        $('#score-alerte-max').attr('hidden', true);
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
        cacherAlerteMax();
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
