<?php

include_once '../model/util/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$reviewDB = new ReviewDB();
var_dump($reviewDB->getReviewJobs(1));
