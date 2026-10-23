<?php

namespace App\Support;

/**
 * Conversion d'un montant numérique en toutes lettres (français), pour les
 * documents officiels (« arrêté à la somme de … »). Utilise l'extension intl
 * (NumberFormatter SPELLOUT) lorsqu'elle est disponible, avec un repli interne.
 */
class MontantEnLettres
{
    /**
     * Retourne le montant en lettres, majuscules, suivi de la devise.
     */
    public static function convertir(int|float|string $montant, string $devise = 'FRANCS CFA'): string
    {
        $entier = (int) round((float) $montant);
        $lettres = self::enLettres($entier);

        return mb_strtoupper(trim($lettres.' '.$devise));
    }

    private static function enLettres(int $nombre): string
    {
        if ($nombre === 0) {
            return 'zéro';
        }

        if (class_exists(\NumberFormatter::class)) {
            $formatter = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
            $valeur = $formatter->format($nombre);
            if (is_string($valeur) && $valeur !== '') {
                return $valeur;
            }
        }

        return self::repli($nombre);
    }

    /** Repli minimal si intl est indisponible. */
    private static function repli(int $nombre): string
    {
        $unites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix',
            'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
        $dizaines = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'];

        $moins1000 = function (int $n) use (&$moins1000, $unites, $dizaines): string {
            if ($n < 20) {
                return $unites[$n];
            }
            if ($n < 100) {
                $d = intdiv($n, 10);
                $u = $n % 10;
                if (in_array($d, [7, 9], true)) {
                    $d -= 1;
                    $u += 10;
                }
                $sep = ($u === 1 && $d !== 8) ? ' et ' : ($u ? '-' : '');

                return trim($dizaines[$d].($u ? $sep.$unites[$u] : ''));
            }
            $c = intdiv($n, 100);
            $r = $n % 100;

            return trim(($c > 1 ? $unites[$c].' ' : '').'cent'.($r ? ' '.$moins1000($r) : ''));
        };

        $parts = [];
        foreach ([1_000_000_000 => 'milliard', 1_000_000 => 'million', 1_000 => 'mille'] as $palier => $mot) {
            if ($nombre >= $palier) {
                $q = intdiv($nombre, $palier);
                $nombre %= $palier;
                $prefixe = ($palier === 1_000 && $q === 1) ? '' : $moins1000($q).' ';
                $parts[] = trim($prefixe.$mot.($q > 1 && $palier !== 1_000 ? 's' : ''));
            }
        }
        if ($nombre > 0) {
            $parts[] = $moins1000($nombre);
        }

        return implode(' ', $parts);
    }
}
