<?php


$Directory = new RecursiveDirectoryIterator('../');
$Iterator = new RecursiveIteratorIterator($Directory);
$Regex = new RegexIterator($Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
$inner = $Regex->getInnerIterator();
$files = array_keys(iterator_to_array($inner));
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) == "php"
        and in_array("model", explode("/", $file)) === true
    ) {
        include_once $file;
    }

//    if (pathinfo($file, PATHINFO_EXTENSION) == "php"
//        and in_array("model", explode("/", $file)) === true) {
//        echo $file;
//    }
}
