<?php


class KeywordDB
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();
    }

}