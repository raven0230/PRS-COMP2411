<?php

include_once '../util/autoload.php';

class ReviewerDB
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();
    }

    /*
     * $reviewer:
     * array(
     *      "title" => string,
     *      "firstName" => string,
     *      "lastName" => string,
     *      "phone" => string,
     *      "fax" => string,
     *      "department" => string,
     *      "organisations" => array(int, ...)
     *      "gender" => int,
     *      "address" => string,
     *      "city" => string,
     *      "country" => string,
     *      "password" => string,
     *      "email" => string
     *      "areas" => array(int, ...)
     * )
     */
    function addReviewer($reviewer)
    {
        //Reviewer
        $reviewerSql = "INSERT INTO Reviewer (title, first_name, last_name, phone, fax, department, gender, address,
                city, country, password, deleted, email)
                VALUES (:title, :firstName, :lastName, :phone, :fax, :department, :gender, :address,
                :city, :country, :passwordd, FALSE, :email)";

        //Reviewer_Organisation
        $orgSql = "INSERT INTO Reviewer_Organisation (organisation_id, reviewer_id) VALUES";
        $orgaSqlChanged = false;
        $orgCount = 0;
        foreach ($reviewer["organisations"] as $organisation) {
            if ($orgCount != 0) {
                $orgSql .= ",";
            }
            $orgSql .= " (:orgId" . $orgCount++ . ", last_insert_id())";
            $orgaSqlChanged = true;
        }

        //Reviewer_Area
        $areaSql = "INSERT INTO Reviewer_Area (area_id, reviewer_id) VALUES";
        $areaSqlChanged = false;
        $areaCount = 0;
        foreach ($reviewer["areas"] as $area) {
            if ($areaCount != 0) {
                $areaSql .= ",";
            }
            $areaSql .= " (:areaId" . $areaCount++ . ", last_insert_id())";
            $areaSqlChanged = true;
        }

        try {
            $this->conn->beginTransaction();

            //Prepare statements
            $reviewerStmt = $this->conn->prepare($reviewerSql);
            if ($orgaSqlChanged) {
                $orgStmt = $this->conn->prepare($orgSql);
            }
            if ($areaSqlChanged) {
                $areaStmt = $this->conn->prepare($areaSql);
            }

            $reviewerKeys = array_keys($reviewer);
            for ($i = 0; $i < sizeof($reviewer); $i++) {
                if ($reviewerKeys[$i] != "organisations" and $reviewerKeys[$i] != "areas") {
                    if (gettype($reviewer[$reviewerKeys[$i]]) != "integer") {
                        $reviewerStmt->bindValue(":" . $reviewerKeys[$i], $reviewer[$reviewerKeys[$i]]);
                    } else {
                        $reviewerStmt->bindValue(":" . $reviewerKeys[$i], $reviewer[$reviewerKeys[$i]], PDO::PARAM_INT);
                    }
                }
            }
            $reviewerStmt->execute();

            if ($orgaSqlChanged) {
                $iterator = 0;
                foreach ($reviewer["organisations"] as $organisation) {
                    $orgStmt->bindValue(":orgId" . $iterator++, $organisation);
                }
                $orgStmt->execute();
            }

            if ($areaSqlChanged) {
                $iterator = 0;
                foreach ($reviewer["areas"] as $area) {
                    $areaStmt->bindValue(":areaId" . $iterator, $area);
                }
                $areaStmt->execute();
            }

            $this->conn->commit();

            return 1;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return -1;
        }

        /*
         * $searchParameter:
         * array(
         *      "title" -> string,
         *      "first_name" -> string,
         *      "last_name" -> string,
         *      "gender" -> int,
         *      "country" -> string,
         *      "city" -> string,
         *      "deleted" -> boolean,
         *      "email" -> string
         *      "organisation" -> string
         * )
         */
        function searchReviewer($searchParameter)
        {

        }

    }
}