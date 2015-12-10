<?php


class ConferenceManagerDB
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();
    }

}