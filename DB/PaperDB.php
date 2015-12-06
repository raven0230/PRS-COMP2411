<?php

namespace DB;

use model\PaperInfo;

class PaperDB
{
    private $conn;
    private $fileSQL =
        "SELECT file, file_mime FROM Submission
            WHERE paper_id = :paperID AND
            type = :type;";

    function __construct()
    {
        $this->conn = DBHelper::getConnection();
    }

    public function getSubmissionFile($paperId, $submitType)
    {
        $stmt = $this->conn->prepare($this->fileSQL);
        $stmt->bindValue(":paperID", $paperId, \PDO::PARAM_INT);
        $stmt->bindValue(":type", $submitType, \PDO::PARAM_INT);
        $file = null;
        $file_mime = "";
        if ($stmt->execute()) {
            $stmt->bindColumn(1, $file, \PDO::PARAM_LOB);
            $stmt->bindColumn(2, $file_mime, \PDO::PARAM_STR);
            if ($stmt->fetch(\PDO::FETCH_BOUND)) {
                return array($file, $file_mime);
            } else {
                return 0;
            }
        } else {
            return -1;
        }
    }

    public function getPaperInfo($paperId)
    {
        //Prepare a Paper object to hold information;
        $paper = new PaperInfo();

        //Get paper Title
        $stmt = $this->conn->prepare(
            "SELECT title FROM Paper
            WHERE id = ?;"
        );
        $stmt->bindValue(1, $paperId, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            $stmt->bindColumn(1, $title, \PDO::PARAM_STR);
            if ($stmt->fetch(\PDO::FETCH_BOUND)) {
                $paper->setTitle($title);
            } else {
                return 0;
            }
        } else {
            return -1;
        }

        //Get paper authors
        $stmt = $this->conn->prepare(
            "SELECT name FROM Author, Author_Paper
            WHERE author_id = id AND paper_id = ?;"
        );
        $stmt->bindValue(1, $paperId, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            $stmt->bindColumn(1, $author_name, \PDO::PARAM_STR);
            while ($stmt->fetch(\PDO::FETCH_BOUND)) {
                $paper->addAuthor($author_name);
            }
        } else {
            return -1;
        }

        //Get paper keywords
        $stmt = $this->conn->prepare(
            "SELECT name FROM keyword, Paper_Keyword
            WHERE id = keyword_id AND paper_id = ?;"
        );
        $stmt->bindValue(1, $paperId, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            $stmt->bindColumn(1, $keyword, \PDO::PARAM_STR);
            while ($stmt->fetch(\PDO::FETCH_BOUND)) {
                $paper->addKeyword($keyword);
            }
        } else {
            return -1;
        }

        //Get paper review status
        $stmt = $this->conn->prepare(
            "SELECT "
        );
    }

    public function getPaperList($searchParameter)
    {

    }
}


