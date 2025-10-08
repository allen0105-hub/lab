<?php
// includes/functions.php

if (!function_exists('ordinal')) {
    /**
     * Converts a number to its ordinal form (1 → 1st, 2 → 2nd, etc.)
     *
     * @param int $num The number to convert
     * @return string Ordinal number with suffix
     */
    function ordinal($num) {
        $num = (int)$num;
        $ends = ['th','st','nd','rd','th','th','th','th','th','th'];
        if (($num % 100) >= 11 && ($num % 100) <= 13) return $num . 'th';
        return $num . $ends[$num % 10];
    }
}
