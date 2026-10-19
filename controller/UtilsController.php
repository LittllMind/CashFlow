<?php

function accentToUpperCase($str) {
    $str = mb_strtoupper($str);
    $str = str_replace(
        array('à', 'â', 'ä', 'é', 'è', 'ê', 'ë', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ÿ'),
        array('À', 'Â', 'Ä', 'É', 'È', 'Ê', 'Ë', 'Î', 'Ï', 'Ô', 'Ö', 'Ù', 'Û', 'Ü', 'Ÿ'),
        $str
    );
    return $str;
}