/* challenge-resume.js — Filtres et tri du tableau de résumé */

$(document).ready(function () {

    // ----------------------------------------------------------------
    // Filtre combiné : statut de tir + discipline
    // ----------------------------------------------------------------
    function appliquerFiltres() {
        const statutFiltre    = $('input[name="filtre-tir"]:checked').val();
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
    $('#filtre-discipline').on('change', appliquerFiltres);

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

});
