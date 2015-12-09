<?php

include_once "../util/autoload.php";

class PaperDB
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();
    }

    public function getSubmission($paperId, $submitType)
    {
        $fileSQL = "SELECT file, file_mime, time FROM Submission
            WHERE paper_id = :paperID AND type = :type;";
        $stmt = $this->conn->prepare($fileSQL);
        $stmt->bindValue(":paperID", $paperId, PDO::PARAM_INT);
        $stmt->bindValue(":type", $submitType, PDO::PARAM_INT);
        $file = null;
        $file_mime = "";
        if ($stmt->execute()) {
            $stmt->bindColumn(1, $file, PDO::PARAM_LOB);
            $stmt->bindColumn(2, $file_mime, PDO::PARAM_STR);
            $stmt->bindColumn(3, $time, PDO::PARAM_STR);
            if ($stmt->fetch(PDO::FETCH_BOUND)) {
                return array($file, $file_mime, $time);
            } else {
                return -1;
            }
        } else {
            return -1;
        }
    }

    public function getPaperTitle($paperId)
    {
        $stmt = $this->conn->prepare(
            "SELECT title FROM Paper
            WHERE id = ?;"
        );
        $stmt->bindValue(1, $paperId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $stmt->bindColumn(1, $title, PDO::PARAM_STR);
            if ($stmt->fetch(PDO::FETCH_BOUND)) {
                return $title;
            } else {
                return 0;
            }
        } else {
            return -1;
        }
    }

    public function getPaperAuthors($paperId)
    {
        $authorArr = array();
        $stmt = $this->conn->prepare(
            "SELECT name FROM Author, Author_Paper
            WHERE author_id = id AND paper_id = ?;"
        );
        $stmt->bindValue(1, $paperId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $stmt->bindColumn(1, $authorName, PDO::PARAM_STR);
            while ($stmt->fetch(PDO::FETCH_BOUND)) {
                array_push($authorArr, $authorName);
            }
            return $authorArr;
        } else {
            return -1;
        }
    }

    public function getPaperKeywords($paperId)
    {
        $keywordArr = array();
        $stmt = $this->conn->prepare(
            "SELECT name FROM Keyword, Paper_Keyword
            WHERE id = keyword_id AND paper_id = ?;"
        );
        $stmt->bindValue(1, $paperId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $stmt->bindColumn(1, $keyword, PDO::PARAM_STR);
            while ($stmt->fetch(PDO::FETCH_BOUND)) {
                array_push($keywordArr, $keyword);
            }
            return $keywordArr;
        } else {
            return -1;
        }
    }

    public function getPaperStatus($paperId)
    {
        $stmt = $this->conn->prepare(
            "SELECT type, reviewStatus FROM Submission
            WHERE paper_id = ? IN (
            SELECT max(type) FROM Submission
            WHERE paper_id = ?
            );"
        );
        $stmt->bindValue(1, $paperId, PDO::PARAM_INT);
        $stmt->bindValue(2, $paperId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $stmt->bindColumn(1, $type, PDO::PARAM_INT);
            $stmt->bindColumn(2, $status, PDO::PARAM_INT);
            if ($stmt->fetch(PDO::FETCH_BOUND)) {
                return array($type, $status);
            } else {
                return 0;
            }
        } else {
            return -1;
        }
    }

    /*
     * $searchParameter format:
     * multiple associative arrays in simple array, each associative array as parameter.
     * parameter structure:
     * "name" - valid names: author, title, keyword, status:int, paperId
     * "value"
     * "isAnd" - true for AND, false for OR
     * "isExact" - true or false
     */
    public function getPaperList($searchParameter)
    {
        //Array to hold PaperInfo objects
        $result = array();

        //Join needed tables
        $sql = "SELECT Paper.id AS 'paper_id', group_concat(Author.name) AS 'authors',
                group_concat(Keyword.keyword) AS 'keywords', Paper.status AS 'status'
                FROM Paper, Paper_Keyword, Keyword, Author, Author_Paper
                WHERE Paper_Keyword.paper_id = Paper.id AND Paper_Keyword.keyword_id = Keyword.id
                AND Author.id = Author_Paper.author_id AND Author_Paper.paper_id = Paper.id
                GROUP BY Paper.id";

        //Inspect $searchParameter

        $valueArr = array( //Store values to bind into prepared statement later
            "paperId" => array(),
            "author" => array(),
            "title" => array(),
            "keyword" => array(),
            "status" => array()
        );

        //Append suitable SQL words according to parameter values
        foreach ($searchParameter as $parameter) {
            $sqlToAppend = "";
            if ($parameter["isAnd"]) {
                $sqlToAppend .= " AND";
            } else {
                $sqlToAppend .= " OR";
            }
            switch ($name = $parameter["name"]) {
                case "paperId":
                    if (is_int($parameter["value"])) {
                        $sqlToAppend .= " Paper.id = :paperId" . sizeof($valueArr["paperId"]);
                        array_push($valueArr["paperId"], $parameter["value"]);
                    }
                    break;
                case "author":
                    $sqlToAppend .= " Author.name";
                    if ($parameter["isExact"]) {
                        $sqlToAppend .= " = :author" . sizeof($valueArr["author"]);
                    } else {
                        $sqlToAppend .= " LIKE :author" . sizeof($valueArr["author"]);
                    }
                    array_push($valueArr["author"], "" . $parameter["value"]);
                    break;
                case "title":
                    $sqlToAppend .= " Paper.title";
                    if ($parameter["isExact"]) {
                        $sqlToAppend .= " = :title" . sizeof($valueArr["title"]);
                    } else {
                        $sqlToAppend .= " LIKE :title" . sizeof($valueArr["title"]);
                    }
                    array_push($valueArr["title"], "" . $parameter["value"]);
                    break;
                case "keyword":
                    $sqlToAppend .= " Keyword.keyword";
                    if ($parameter["isExact"]) {
                        $sqlToAppend .= " = :keyword" . sizeof($valueArr["keyword"]);
                    } else {
                        $sqlToAppend .= " LIKE :keyword" . sizeof($valueArr["keyword"]);
                    }
                    array_push($valueArr["keyword"], "" . $parameter["value"]);
                    break;
                case "status":
                    if (Util::validatePaperStatus($parameter["value"])) {
                        $sqlToAppend .= " Paper.status = :status" . sizeof($valueArr["status"]);
                        array_push($valueArr["status"], $parameter["value"]);
                    }
                    break;
                default:
                    break;
            }
        }

        //Prepare statment
        $stmt = $this->conn->prepare($sql . $sqlToAppend . " ;");

        //Bind values
        if (!empty($valueArr["author"])) {
            foreach ($valueArr["author"] as $value) {
                for ($a = 0; $a < sizeof($value); $a++) {
                    $stmt->bindValue(":author" . $a, $value[$a], PDO::PARAM_STR);
                }
            }
        }

        if (!empty($valueArr["title"])) {
            foreach ($valueArr["title"] as $value) {
                for ($a = 0; $a < sizeof($value); $a++) {
                    $stmt->bindValue(":title" . $a, $value[$a], PDO::PARAM_STR);
                }
            }
        }

        if (!empty($valueArr["keyword"])) {
            foreach ($valueArr["keyword"] as $value) {
                for ($a = 0; $a < sizeof($value); $a++) {
                    $stmt->bindValue(":keyword" . $a, $value[$a], PDO::PARAM_STR);
                }
            }
        }

        if (!empty($valueArr["paperId"])) {
            foreach ($valueArr["paperId"] as $value) {
                for ($a = 0; $a < sizeof($value); $a++) {
                    $stmt->bindValue(":paperId" . $a, $value[$a], PDO::PARAM_INT);
                }
            }
        }

        if (!empty($valueArr["status"])) {
            foreach ($valueArr["status"] as $value) {
                for ($a = 0; $a < sizeof($value); $a++) {
                    $stmt->bindValue(":status" . $a, $value[$a], PDO::PARAM_INT);
                }
            }
        }

        //Execute and get result
        if ($stmt->execute()) {
            if (!$result = $stmt->fetchAll()) {
                return $result;
            } else {
                return -1;
            }
        } else {
            return -1;
        }
    }

    function addPaper($title, $authors, $keywords, $file = array())
    {
        $fileEmpty = empty($file);

        //Start Transaction
        $sql = "BEGIN;";
        //Insert title
        $sql .= "INSERT INTO Paper (title, progress) VALUES (:title, 10);";

        //Insert keywords
        for ($i = 0; $i < sizeof($keywords); $i++) {
            $sql .= "INSERT INTO Keyword (keyword) VALUES (:keyword" . $i . ")
                    ON DUPLICATE KEY UPDATE id = last_insert_id(id);";
            $sql .= "INSERT INTO Paper_Keyword (paper_id, keyword_id)
                    VALUES ((SELECT MAX(id) FROM Paper), last_insert_id());";
        }
        //Insert authors
        for ($a = 0; $a < sizeof($authors); $a++) {
            $sql .= "INSERT INTO Author (name, address, city, country)
                    VALUES (:name" . $a . ", :address" . $a . ", :city" . $a . ", :country" . $a . ")
                    ON DUPLICATE KEY UPDATE id = last_insert_id(id);";
            $sql .= "INSERT INTO Author_Paper (paper_id, author_id)
                    VALUES ((SELECT MAX(id) FROM Paper), last_insert_id());";
        }
        //Insert file if provided
        if (!$fileEmpty) {
            $sql .= "INSERT INTO Submission (reviewStatus, type, file, file_mime, paper_id)
                    VALUES (0, 1, :filePointer, :fileMime, (SELECT MAX(id) FROM Paper));";
        }
        //End Transaction
        $sql .= "COMMIT;";

        //Prepare and bind values
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":title", $title);
        for ($n = 0; $n < sizeof($keywords); $n++) {
            $stmt->bindValue(":keyword" . $n, $keywords[$n]);
        }
        for ($h = 0; $h < sizeof($authors); $h++) {
            $stmt->bindValue(":name" . $h, $authors[$h]["name"]);
            $stmt->bindValue(":address" . $h, $authors[$h]["address"]);
            $stmt->bindValue(":city" . $h, $authors[$h]["city"]);
            $stmt->bindValue(":country" . $h, $authors[$h]["country"]);
        }
        $stmt->bindValue(":filePointer", $file[0], PDO::PARAM_LOB);
        $stmt->bindValue(":fileMime", $file[1]);

        //Execute
        if ($stmt->execute()) {
            return $stmt->rowCount();
        } else {
            return -1;
        }
    }

}


