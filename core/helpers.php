<?php
// Fonctions utilitaires globales de l'application

// ---------------------------------------------------------------------------
// Fonctions de secours si l'extension PHP "mbstring" n'est pas activée.
// Elles reproduisent le comportement de mb_strtoupper / mb_strlen / mb_substr
// pour les chaînes UTF-8 (accents français inclus). Si mbstring est présente,
// les fonctions natives sont utilisées automatiquement.
// ---------------------------------------------------------------------------

if (!function_exists('mb_strtoupper')) {
    /**
     * Version de secours de mb_strtoupper (UTF-8, accents français gérés).
     */
    function mb_strtoupper(string $chaine, ?string $encodage = null): string
    {
        $minusculesAccentuees = [
            'à' => 'À', 'â' => 'Â', 'ä' => 'Ä', 'á' => 'Á', 'ã' => 'Ã',
            'é' => 'É', 'è' => 'È', 'ê' => 'Ê', 'ë' => 'Ë',
            'î' => 'Î', 'ï' => 'Ï', 'í' => 'Í',
            'ô' => 'Ô', 'ö' => 'Ö', 'ó' => 'Ó', 'õ' => 'Õ',
            'ù' => 'Ù', 'û' => 'Û', 'ü' => 'Ü', 'ú' => 'Ú',
            'ç' => 'Ç', 'ñ' => 'Ñ', 'ÿ' => 'Ÿ',
            'æ' => 'Æ', 'œ' => 'Œ',
        ];

        return strtoupper(strtr($chaine, $minusculesAccentuees));
    }
}

if (!function_exists('mb_strlen')) {
    /**
     * Version de secours de mb_strlen : compte les caractères UTF-8,
     * pas les octets (un accent = 1 caractère).
     */
    function mb_strlen(string $chaine, ?string $encodage = null): int
    {
        $caracteres = preg_split('//u', $chaine, -1, PREG_SPLIT_NO_EMPTY);

        // Si la chaîne n'est pas de l'UTF-8 valide, repli sur strlen.
        return $caracteres === false ? strlen($chaine) : count($caracteres);
    }
}

if (!function_exists('mb_substr')) {
    /**
     * Version de secours de mb_substr : extrait une portion de chaîne UTF-8
     * sans couper un caractère accentué en deux.
     */
    function mb_substr(string $chaine, int $debut, ?int $longueur = null, ?string $encodage = null): string
    {
        $caracteres = preg_split('//u', $chaine, -1, PREG_SPLIT_NO_EMPTY);

        // Si la chaîne n'est pas de l'UTF-8 valide, repli sur substr.
        if ($caracteres === false) {
            return $longueur === null ? substr($chaine, $debut) : substr($chaine, $debut, $longueur);
        }

        return implode('', array_slice($caracteres, $debut, $longueur));
    }
}

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
