<?php

include_once "../util/autoload.php";

class DBHelper
{

    private static $connection = array(
        "type" => "mysql",
        "host" => "mysql.comp.polyu.edu.hk",
        "port" => "3306",
        "dbname" => "14073582d",
        "user" => "14073582d",
        "password" => "lamer_1234",
    );

    static function getConnection()
    {
        try {
            $connection = self::$connection;
            $conn = new PDO($connection["type"] .
                ":host=" . $connection["host"] .
                ";port=" . $connection["port"] .
                ";dbname=" . $connection["dbname"],
                $connection["user"],
                $connection["password"],
                array(
                    PDO::ATTR_PERSISTENT
                )
            );
            return $conn;
        } catch (PDOException $e) {
            return -1;
        }

    }

}




