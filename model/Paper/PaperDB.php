<?php

include_once "../model/util/autoload.php";

class PaperDB
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();
    }

    public function getSubmission($submissionId)
    {
        $fileSQL = "SELECT file, file_mime, time FROM Submission
            WHERE Submission.id = :submissionId";
        try {
            $stmt = $this->conn->prepare($fileSQL);
            $stmt->bindValue(":submissionId", $submissionId, PDO::PARAM_INT);
            $file = null;
            $file_mime = "";
            if ($stmt->execute()) {
                return $stmt->fetchAll();
            } else {
                return -1;
            }
        } catch (PDOException $e) {
            return -1;
        }
    }

    public function getSubmissionType($submissionId)
    {
        try {
            $stmt = $this->conn->prepare("SELECT type FROM Submission WHERE id = :submissionId");
            $stmt->bindValue(":submissionId", $submissionId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $stmt->bindColumn("type", $type);
                if ($stmt->fetch(PDO::FETCH_BOUND)) {
                    return $type;
                } else {
                    return 0;
                }
            } else {
                return -1;
            }
        } catch (PDOException $e) {
            return -1;
        }
    }

//    public function getPaperTitle($submissionId) {
//        $sql = "SELECT Paper.title AS 'title' FROM Paper, Submission, Author, Author_Paper"
//    }

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
        //Join needed tables
        $sql = "SELECT Paper.id AS 'paper_id', Paper.title AS 'title', group_concat(Author.name) AS 'authors',
                Paper.status AS 'status'
                FROM Paper, Paper_Keyword, Keyword, Author, Author_Paper
                WHERE Paper_Keyword.paper_id = Paper.id AND Paper_Keyword.keyword_id = Keyword.id
                AND Author.id = Author_Paper.author_id AND Author_Paper.paper_id = Paper.id
                GROUP BY Paper.id";

        $valueArr = array( //Store values to bind into prepared statement later
            "paperId" => array(),
            "author" => array(),
            "title" => array(),
            "keyword" => array(),
            "status" => array()
        );

        //Append suitable SQL words according to parameter values
        $sqlToAppend = "";
        foreach ($searchParameter as $parameter) {
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

    public function getPaperTitle($paperId)
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT title FROM Paper
            WHERE id = ?;"
            );
            $stmt->bindValue(1, $paperId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                if ($result = $stmt->fetchAll()) {
                    if (!empty($result)) {
                        return $result[0];
                    } else {
                        return 0;
                    }
                } else {
                    return -1;
                }
            } else {
                return -1;
            }
        } catch (PDOException $e) {
            return -1;
        }
    }

    public function getPaperAuthors($paperId)
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT name FROM Author, Author_Paper
            WHERE author_id = id AND paper_id = ?;"
            );
            $stmt->bindValue(1, $paperId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                if ($result = $stmt->fetchAll()) {
                    if (!empty($result)) {
                        return $result;
                    } else {
                        return 0;
                    }
                } else {
                    return -1;
                }
            } else {
                return -1;
            }
        } catch (PDOException $e) {
            return -1;
        }
    }

    public function getPaperKeywords($paperId)
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT keyword FROM Keyword, Paper_Keyword
            WHERE id = keyword_id AND paper_id = ?;"
            );
            $stmt->bindValue(1, $paperId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                if ($result = $stmt->fetchAll()) {
                    if (!empty($result)) {
                        return $result;
                    } else {
                        return 0;
                    }
                } else {
                    return -1;
                }
            } else {
                return -1;
            }
        } catch (PDOException $e) {
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

    public function getPaperSubmitInfo($paperId)
    {

        if (!is_int($paperId)) {
            return -1;
        }

        $sql = "SELECT Submission.type AS 'submitType', Submission.time AS 'submitTime',
                Submission.reviewStatus AS 'reviewStatus', Submission.id AS 'submitId'
                FROM Paper, Author, Submission, Author_Paper
                WHERE Paper.id = Submission.paper_id AND Author_Paper.paper_id = Paper.id
                AND Author_Paper.author_id = Author.id AND Paper.id = :paperId";

        try {
            $stmt = $this->conn->prepare($sql);

            $stmt->bindValue(":paperId", $paperId, PDO::PARAM_INT);

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
     * $file:
     * array( //simple array
     *      pointer returned by fopen(),
     *      mime type string from POST file upload
     * )
     */
    function addPaper($title, $authors, $keywords, $file)
    {
        $fileEmpty = empty($file);

        //Start Transaction
        try {
            $this->conn->beginTransaction();
            //Title
            $titleSql = "INSERT INTO Paper (title, status, responsible_chair)
                         VALUES (:title, 10, (SELECT id FROM Track_Chair ORDER BY rand() LIMIT 1));";
            $titleStmt = $this->conn->prepare($titleSql);
            $titleStmt->bindValue(":title", $title);
            $titleStmt->execute();
            $paperId = $this->conn->lastInsertId();

            //Keywords
            $keywordStmts = array();
            for ($i = 0; $i < sizeof($keywords); $i++) {
                $keywordSql = "INSERT INTO Keyword (keyword) VALUES (:keyword)
                           ON DUPLICATE KEY UPDATE id = last_insert_id(id);";
                $paperKeywordsql = "INSERT INTO Paper_Keyword (paper_id, keyword_id)
                                VALUES (:paperId, last_insert_id());";
                $keywordStmt = $this->conn->prepare($keywordSql);
                $paperKeywordStmt = $this->conn->prepare($paperKeywordsql);
                array_push($keywordStmts, array(
                    $keywordStmt,
                    $paperKeywordStmt
                ));
            }

            for ($i = 0; $i < sizeof($keywordStmts); $i++) {
                $keywordStmts[$i][0]->bindValue(":keyword", $keywords[$i]);
                $keywordStmts[$i][0]->execute();
                $keywordStmts[$i][1]->bindValue(":paperId", $paperId, PDO::PARAM_INT);
                $keywordStmts[$i][1]->execute();
            }

            //Authors
            $authorStmts = array();
            for ($a = 0; $a < sizeof($authors); $a++) {
                $authorSql = "INSERT INTO Author (name, address, city, country) VALUES (:namee, :address, :city, :country)
                    ON DUPLICATE KEY UPDATE id = last_insert_id(id);";
                $authorPaperSql = "INSERT INTO Author_Paper (paper_id, author_id)
                    VALUES (:paperId, last_insert_id());";
                array_push($authorStmts, array(
                    $this->conn->prepare($authorSql),
                    $this->conn->prepare($authorPaperSql)
                ));
            }

            for ($i = 0; $i < sizeof($keywordStmts); $i++) {
                $authorStmts[$i][0]->bindValue(":namee", $authors[$i]["name"]);
                $authorStmts[$i][0]->bindValue(":address", $authors[$i]["address"]);
                $authorStmts[$i][0]->bindValue(":city", $authors[$i]["city"]);
                $authorStmts[$i][0]->bindValue(":country", $authors[$i]["country"]);
                $authorStmts[$i][0]->execute();
                $keywordStmts[$i][1]->bindValue(":paperId", $paperId, PDO::PARAM_INT);
                $keywordStmts[$i][1]->execute();
            }

            //Insert file if provided
            if (!$fileEmpty) {
                $fileSql = "INSERT INTO Submission (reviewStatus, type, file, file_mime, paper_id)
                    VALUES (0, 1, :filePointer, :fileMime, (SELECT MAX(id) FROM Paper));";
                $fileStmt = $this->conn->prepare($fileSql);
                $fileStmt->bindValue(":filePointer", $file[0], PDO::PARAM_LOB);
                $fileStmt->bindValue(":fileMime", $file[1]);
                $fileStmt->execute();
            }
            return 1;
        } catch (PDOException $e) {
            return -1;
        }
    }

    /*
     * $filePointer: pointer returned by fopen()
     * $fileMime: mime type string retrieved from POST upload
     */
    function addSubmission($paperId, $submissionType, $filePointer, $fileMime)
    {
        try {
            if (is_int($paperId) or Util::validateSubmitType($submissionType)) {
                $submissionSql = "INSERT INTO Submission (reviewStatus, type, file, file_mime, paper_id)
                          VALUES (0, :submissionType, :filee, :fileMime, :paperId);";
                $paperSql = "UPDATE Paper SET status = :submissionType * 10";

                $this->conn->beginTransaction();
                $submissionStmt = $this->conn->prepare($submissionSql);
                $submissionStmt->bindValue(":submissionType", $submissionType, PDO::PARAM_INT);
                $submissionStmt->bindValue(":filee", $filePointer, PDO::PARAM_LOB);
                $submissionStmt->bindValue(":fileMime", $fileMime);
                $submissionStmt->bindValue(":paperId", $paperId);
                $submissionStmt->execute();
                $submissionId = $this->conn->lastInsertId();

                $paperStmt = $this->conn->prepare($paperSql);
                $paperStmt->bindValue(":submissionType", $submissionType, PDO::PARAM_INT);
                $paperStmt->execute();

                $this->conn->commit();

                $assignReviewJob = new ReviewDB();
                $assignReviewJob->assignReviewJob($submissionId);
            } else {
                return -1;
            }
        } catch (PDOException $e) {
            return -1;
        }
    }

}


