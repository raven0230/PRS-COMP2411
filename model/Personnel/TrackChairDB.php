<?php


class TrackChairDB
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();
    }

}