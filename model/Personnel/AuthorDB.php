<?php


class AuthorDB
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();
    }

    /*
     * $authors:
     * array(
     *  array(
     *      "name" => string,
     *      "address" => string,
     *      "city" => string,
     *      "country" => string
     *  ),
     *  ...
     * )
     */
    function addAuthor($authors)
    {
        $sql = "INSERT INTO Author (name, address, city, country) VALUES";
        $iterator = 0;
        for ($i = 0; $i < sizeof($authors); $i++) {
            if ($iterator != 0) {
                $sql .= ",";
            }
            $sql .= " (:name" . $i . ", :address" . $i . ", :city" . $i . ", :country" . $i . ")";
        }
        $sql .= ";";

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare($sql);
            for ($i = 0; $i < sizeof($areas); $i++) {
                $stmt->bindValue(":name" . $i, $authors["name"]);
                $stmt->bindValue(":address" . $i, $authors["address"]);
                $stmt->bindValue(":city" . $i, $authors["city"]);
                $stmt->bindValue(":country" . $i, $authors["country"]);
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

}