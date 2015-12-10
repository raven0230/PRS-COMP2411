<?php


class MiscDB
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();
    }

    /*
     * $name: array(string, ...)
     */
    function addOrganisation($names)
    {
        $sql = "INSERT INTO organisation (name) VALUES";
        for ($i = 0; $i < sizeof($names); $i++) {
            if ($i != 0) {
                $sql .= ",";
            }
            $sql .= " (:name" . $i . ")";
        }
        $sql .= ";";

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare($sql);
            for ($i = 0; $i < sizeof($names); $i++) {
                $stmt->bindValue(":name" . $i, $names[$i]);
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

    function getAllOrganisations()
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM organisation;");
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