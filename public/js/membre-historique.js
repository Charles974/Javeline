$(function () {
    'use strict';

    $(document).on('click keydown', '.ligne-membre[data-href]', function (e) {
        if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
        e.preventDefault();
        window.location.href = $(this).data('href');
    });
});
