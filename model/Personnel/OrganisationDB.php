<?php


class OrganisationDB
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();
    }

}