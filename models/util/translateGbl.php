<?php

function dps2tops($dps, $reverse = false)
{
    $gbls = array(
        "AGFM" => "AG",
        "BGNC" => "BF",
        "BGAC" => "BG",
        "BKML" => "BK",
        "BKMT" => "BM",
        "BKAS" => "BS",
        "CAAT" => "CA",
        "CGAT" => "CG",
        "CFAT" => "CI",
        "CNNQ" => "CN",
        "CLPK" => "CP",
        "CHAT" => "CS",
        "DBAT" => "DA",
        "DBAQ" => "DB",
        "DMAT" => "DT",
        "EBAK" => "EB",
        "FAAM" => "FA",
        "FAAT" => "FC",
        "FDNT" => "FD",
        "FHAT" => "FH",
        "FRNQ" => "FR",
        "FSAT" => "FS",
        "GSAT" => "GS",
        "HAFC" => "HA",
        "HAAE" => "HE",
        "HBAT" => "HH",
        "HOAT" => "HS",
        "JENQ" => "JE",
        "JEAT" => "JL",
        "KDAK" => "KD",
        "KKFA" => "KK",
        "KOAT" => "KO",
        "LHNQ" => "LA",
        "LFMT" => "LF",
        "LGNL" => "LG",
        "LKAT" => "LK",
        "LKNQ" => "LS",
        "MAYF" => "MF",
    );

    if ($reverse) {
// tops2dps
        if (preg_match('/^([a-z]{4}).*?([0-9]{6})$/i', $dps, $match)) {
            return $gbls[$match[1]] . '-' . $match[2];
        }
    } else {
// dps2tops
        if (preg_match('/^([a-z]{2}).*?([0-9]{6})$/i', $dps, $match)) {
            foreach ($gbls as $dps => $tops) {
                if ($tops == $match[1]) {
                    return $dps . '0' . $match[2];
                }
            }
        }
    }
}

function tops2dps($tops)
{
    return dps2tops($tops, true);
}

