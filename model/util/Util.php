<?php

include_once "../model/util/autoload.php";

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

    static function validateSubmitType($submitType)
    {
        return (is_int($submitType) or $submitType == 1 or $submitType == 2 or $submitType == 3);
    }

    static function printPrettyAuthorNames($authors)
    {
        $authorString = "";
        if (sizeof($authors) >= 3) {
            return $authors[0] . ' et al.';
        } else {
            $iterator = 0;
            foreach ($authors as $author) {
                if ($iterator++ != 0) {
                    $authorString .= ', ';
                }
                $authorString .= $author;
            }
            return $authorString;
        }
    }

    static function submitTypeToString($submitType)
    {
        $submitTypee = intval($submitType);
        switch ($submitTypee) {
            case 1:
                return "Abstract";
            case 2:
                return "Main Paper";
            case 3:
                return "Revised Paper";
            default:
                return "Type ID Error";
        }
    }

    static function boolToYesNo($bool)
    {
        $booll = boolval($bool);
        return ($booll == true ? 'Yes' : 'No');
    }

}




