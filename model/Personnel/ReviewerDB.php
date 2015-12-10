<?php

include_once '../model/util/autoload.php';

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
         *      array(
         *          "key" => string, // only allow search by email, gender, country, name, area, organisation
         *          "value" => various,
         *          "isExact" => boolean,
         *          "isAnd" => boolean,
         *      ), ...
         * )
         */
        function searchReviewer($searchParameter)
        {
            $sql = "SELECT id, title, first_name, last_name FROM Reviewer WHERE";
            $parameterUsageCount = array(
                "name" => 0,
                "email" => 0,
                "gender" => 0,
                "country" => 0,
                "area" => 0,
                "organisation" => 0
            );

            $valuesToBind = array(
                "names" => array(),
                "email" => array(),
                "gender" => array(),
                "country" => array(),
                "area" => array(),
                "organisation" => array()
            );
            $firstInsert = true;
            foreach ($searchParameter as $parameter) {
                switch ($parameter["key"]) {
                    case "name":
                        $names = explode(" ", $parameter["value"]);
                        foreach ($names as $name) {
                            if (!$firstInsert) {
                                if ($parameter["isAnd"]) {
                                    $sql .= " AND";
                                } else {
                                    $sql .= " OR";
                                }
                            } else {
                                $firstInsert = false;
                            }
                            $sql .= " Reviewer.name";
                            if ($parameter["isExact"]) {
                                $sql .= " =";
                            } else {
                                $sql .= " LIKE";
                            }
                            $sql .= " :name" . $parameterUsageCount["name"]++;
                            array_push($valuesToBind["names"], $parameter["value"]);
                        }
                        break;

                    case "email":
                        if ($parameterUsageCount["email"] == 0) {
                            if (!$firstInsert) {
                                if ($parameter["isAnd"]) {
                                    $sql .= " AND";
                                } else {
                                    $sql .= " OR";
                                }
                            } else {
                                $firstInsert = false;
                            }
                            $sql .= " Reviewer.email";
                            if ($parameter["isExact"]) {
                                $sql .= " =";
                            } else {
                                $sql .= " LIKE";
                            }
                            $sql .= " :email" . $parameterUsageCount["email"]++;
                            array_push($valuesToBind["email"], $parameter["email"]);
                        }
                        break;

                    case "country":
                        if (!$firstInsert) {
                            if ($parameter["isAnd"]) {
                                $sql .= " AND";
                            } else {
                                $sql .= " OR";
                            }
                        } else {
                            $firstInsert = false;
                        }
                        $sql .= " Reviewer.country";
                        if ($parameter["isExact"]) {
                            $sql .= " =";
                        } else {
                            $sql .= " LIKE";
                        }
                        $sql .= " :country" . $parameterUsageCount["country"]++;
                        array_push($valuesToBind["country"], $parameter["country"]);
                        break;

                    case "gender":
                        if (!$firstInsert) {
                            if ($parameter["isAnd"]) {
                                $sql .= " AND";
                            } else {
                                $sql .= " OR";
                            }
                        } else {
                            $firstInsert = false;
                        }
                        $sql .= " Reviewer.gender = :gender" . $parameterUsageCount["gender"]++;
                        array_push($valuesToBind["gender"], $parameter["gender"]);
                        break;

                    case "area":
                        if (!$firstInsert) {
                            if ($parameter["isAnd"]) {
                                $sql .= " AND";
                            } else {
                                $sql .= " OR";
                            }
                        } else {
                            $firstInsert = false;
                        }
                        if ($parameterUsageCount["area"] == 0) {
                            $sql .= " id IN";
                        }
                        $sql .= " id IN";
                        if ($parameter["isExact"]) {
                            $sql .= " =";
                        } else {
                            $sql .= " LIKE";
                        }
                        $sql .= " :email" . $parameterUsageCount["email"]++;
                        array_push($valuesToBind["email"], $parameter["email"]);

                        break;
                }
            }

        }

        /*
         * Returns:
         * array(
         *      array(
         *          "title" => string,
         *          "first_name" => string,
         *          "last_name" => string,
         *          "department" => string
         *      )
         * )
         */
        function getAllReviewer()
        {
            try {
                $stmt = $this->conn->prepare("SELECT id, title, first_name, last_name, department FROM Reviewer;");
                if ($stmt->execute()) {
                    return $stmt->fetchAll();
                } else {
                    return -1;
                }
            } catch (PDOException $e) {
                return -1;
            }
        }

        function getReviewer($reviewerId)
        {
            $sql = "SELECT title, first_name, last_name, phone, fax, department, gender, address, city,
                    country, email, Area.name, organisation.name FROM Reviewer, Area, organisation
                    WHERE Reviewer.id = :reviewerId AND Reviewer.id = Reviewer_Area.reviewer_id
                    AND Reviewer.id = Reviewer_Organisation.reviewer_id";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(":reviewerId", $reviewerId, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    return $stmt->fetchAll();
                } else {
                    return -1;
                }
            } catch (PDOException $e) {
                return -1;
            }
        }

        /*
         * $infoToUpdate:
         *      array(
         *          [various attributes]
         * )
         */
        function updateReviewerInfo($reviewerId, $infoToUpdate)
        {

        }

    }
}