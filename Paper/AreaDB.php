<?php


class AreaDB
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();
    }

    /*
     * $areas: array(string, ...)
     */
    function addArea($areas)
    {
        $sql = "INSERT INTO Area (name) VALUES";
        for ($i = 0; $i < sizeof($areas); $i++) {
            if ($i != 0) {
                $sql .= ",";
            }
            $sql .= " (:name" . $i . ")";
        }
        $sql .= ";";

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare($sql);
            for ($i = 0; $i < sizeof($areas); $i++) {
                $stmt->bindValue(":name" . $i, $areas[$i]);
            }
            $stmt->execute();
            $affectedRow = $stmt->rowCount();
            $this->conn->commit();
            return $affectedRow;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return -1;
        }
    }

    function getAllAreas()
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM Area;");
            if ($stmt->execute()) {
                return $stmt->fetchAll();
            } else {
                return -1;
            }
        } catch (PDOException $e) {
            return -1;
        }
    }
}