<?php

include_once "../util/autoload.php";

class Util
{

    /*
     * $status format:
     * int
     * Current Paper [1: abstract, 2: paper, 3: revision] * 10 +
     * Paper Status [0: pending, 1: accepted, 2: rejected] * 1
     */
    static function validatePaperStatus($status)
    {
        $stat = intval($status);
        if (floor($stat / 10) != 1 or floor($stat / 10) != 2 or floor($stat / 10) != 3) {
            return false;
        } else {
            if ($stat % 10 != 0 or $stat % 10 != 1 or $stat % 10 != 2) {
                return false;
            } else {
                return true;
            }
        }
    }

    static function genPaperStatus($submitType, $reviewStatus)
    {
        return $submitType * 10 + $reviewStatus;
    }

}




