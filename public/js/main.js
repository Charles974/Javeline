/* main.js — Script principal JavelinePHP */

$(document).ready(function () {
    // Initialisation des composants Bootstrap via jQuery si besoin
    // Exemple : activation des tooltips Bootstrap
    $('[data-bs-toggle="tooltip"]').each(function () {
        new bootstrap.Tooltip(this);
    });
});
