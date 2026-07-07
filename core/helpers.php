<?php
// Fonctions utilitaires globales de l'application

/**
 * Formate un numéro de téléphone français (10 chiffres commençant par 0)
 * en groupes de 2 chiffres, ex : 0707808080 -> 07 07 80 80 80.
 * Si le numéro n'est pas au format français, il est retourné tel quel.
 */
function format_telephone_fr(?string $telephone): string
{
    if ($telephone === null || trim($telephone) === '') {
        return '';
    }

    $chiffres = preg_replace('/[^0-9]/', '', $telephone);

    if (preg_match('/^0[1-9][0-9]{8}$/', $chiffres)) {
        return implode(' ', str_split($chiffres, 2));
    }

    return $telephone;
}

/**
 * Formate une date ISO (Y-m-d) en libellé français complet,
 * ex : 2026-04-18 -> "Samedi 18 Avril 2026".
 */
function format_date_fr_complete(string $dateIso): string
{
    $joursFr = [0 => 'Dimanche', 1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'];
    $moisFr  = [1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
                7 => 'Juillet', 8 => 'Aout', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'];

    $temps = strtotime($dateIso);

    return $joursFr[(int) date('w', $temps)] . ' ' . date('d', $temps)
        . ' ' . $moisFr[(int) date('n', $temps)] . ' ' . date('Y', $temps);
}
