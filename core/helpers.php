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
