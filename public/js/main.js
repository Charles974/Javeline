/* main.js — Script principal Javeline */

$(document).ready(function () {
    // Initialisation des composants Bootstrap via jQuery si besoin
    // Exemple : activation des tooltips Bootstrap
    $('[data-bs-toggle="tooltip"]').each(function () {
        new bootstrap.Tooltip(this);
    });
});
