<?php

if (!function_exists('numberToWords')) {
    function numberToWords($number) {
        $units = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
        $teens = ['dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
        $tens = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];
        $thousands = ['', 'mille', 'million', 'milliard'];

        if ($number == 0) {
            return 'zéro';
        }

        $words = '';
        $num = intval($number);
        $chunks = array_reverse(str_split(str_pad($num, 12, '0', STR_PAD_LEFT), 3));

        foreach ($chunks as $i => $chunk) {
            if ($chunk == '000') {
                continue;
            }

            $chunkWords = '';
            $hundreds = intval($chunk[0]);
            $tensUnits = intval(substr($chunk, 1));

            if ($hundreds > 0) {
                $chunkWords .= $units[$hundreds] . ' cent ';
            }

            if ($tensUnits < 10) {
                $chunkWords .= $units[$tensUnits];
            } elseif ($tensUnits < 20) {
                $chunkWords .= $teens[$tensUnits - 10];
            } else {
                $chunkWords .= $tens[intval($tensUnits / 10)];
                if ($tensUnits % 10 > 0) {
                    $chunkWords .= '-' . $units[$tensUnits % 10];
                }
            }

            if ($i > 0) {
                $chunkWords .= ' ' . $thousands[$i];
            }

            $words = $chunkWords . ' ' . $words;
        }

        return trim($words);
    }
}