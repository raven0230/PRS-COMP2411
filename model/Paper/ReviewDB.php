<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once "../model/util/autoload.php";



class ReviewDB
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();

    }

    function checkConn()
    {
        if (gettype($this->conn) != 'integer') {
            return "Connection Succeeded.";
        } else {
            return "Connection Failed.";
        }
    }

    public function getReviewFile($reviewId)
    {
        try {
            $stmt = $this->conn->prepare("SELECT file, file_mime FROM Review_Record
                                      WHERE id = :reviewId");
            $stmt->bindValue(":reviewId", $reviewId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $stmt->bindColumn("file", $file);
                $stmt->bindColumn("mime", $file_mime);
                if ($stmt->fetch(PDO::FETCH_BOUND)) {
                    return array($file, $file_mime);
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

    public function getSubmissionReviews($submissionId)
    {
        $sql = "SELECT concat(first_name, ' ', last_name) AS 'reviewerName',
                Review_Record.rating AS 'rating', Review_Record.completed AS 'completed',
                (if (Review_Record.completed = TRUE, Review_Record.completed_time, 'N/A')) AS 'time',
                Review_Record.id AS 'reviewId'
                FROM Reviewer, Review_Record
                WHERE Review_Record.reviewer_id = Reviewer.id AND Review_Record.submission_id = :submissionId;";

        try {
            $stmt = $this->conn->prepare($sql);

            if ($stmt->execute()) {
                if ($result = $stmt->fetchAll()) {
                    return $result;
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

    function assignReviewJob($submissionId)
    {
        $sql = "INSERT INTO Review_Record (submission_id, reviewer_id)
                VALUES (:submissionId, (SELECT Reviewer.id FROM Reviewer, Submission, Review_Record
                WHERE Submission.id = :submissionId AND Review_Record.submission_id = Submission.id
                AND Review_Record.reviewer_id != Reviewer.id
                ORDER BY RAND() LIMIT 1))";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(":submissionId", $submissionId, PDO::PARAM_INT);
            for ($i = 0; $i < 3; $i++) {
                if (!$stmt->execute()) {
                    return -1;
                }
            }
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return -1;
        }
    }

    function getReviewJobs($reviewerId)
    {
        $sql = "SELECT Review_Record.id AS 'reviewId', Paper.title AS 'paperTitle',
                group_concat(Author.name) AS 'authorsName', assigned_time AS 'assignedTime',
                submission_id AS 'submissionId', Review_Record.completed AS 'completed',
                Submission.type AS 'type'
                FROM Review_Record, Paper, Submission, Author, Author_Paper
                WHERE Review_Record.submission_id = Submission.id AND Submission.paper_id = Paper.id
                AND Review_Record.reviewer_id = :reviewerId AND Paper.id = Author_Paper.paper_id
                AND Author_Paper.author_id = Author.id";
        try {
            $allResult = array();
            $result = array();
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(":reviewerId", $reviewerId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($allResult, $result);
                }
                return $allResult;
            } else {
                return -1;
            }
        } catch (PDOException $e) {
            return -1;
        }

    }

    function doReview($reviewRecordId, $filePointer, $fileMime, $rating)
    {
        $reviewSql = "UPDATE Review_Record
                 SET file = :filePointer, file_mime = :fileMime, rating = :rating, completed = TRUE
                 WHERE id = :reviewRecordId;";

        $subIdSql = "SELECT Submission.id FROM Submission WHERE Submission.id = Review_Record.submission_id
                     AND Review_Record.id = :reviewRecordId";
        try {
            $this->conn->beginTransaction();

            //Upload review
            $reviewStmt = $this->conn->prepare($reviewSql);
            $reviewStmt->bindValue(":filePointer", $filePointer, PDO::PARAM_LOB);
            $reviewStmt->bindValue(":fileMime", $fileMime, PDO::PARAM_STR);
            $reviewStmt->bindValue(":rating", $rating, PDO::PARAM_INT);
            $reviewStmt->bindValue(":reviewRecordId", $reviewRecordId, PDO::PARAM_INT);
            $reviewStmt->execute();

            //Get submission id
            $id = $reviewRecordId;
            $subIdStmt = $this->conn->prepare($subIdSql);
            $subIdStmt->bindParam(":reviewRecordId", $id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
            $subIdStmt->execute();

            $this->conn->commit();

            if ($this->decideReviewStatus($id) != -1) {
                return 1;
            } else {
                return -1;
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return -1;
        }
    }

    private function decideReviewStatus($submissionId)
    {
        //Update Submission's review status if all 3 reviews has been done.
        $submissionSql = "UPDATE Submission
                SET reviewStatus = if((SELECT avg(rating) FROM Review_Record
                                       WHERE submission_id = :submissionId) >= 5, 1, 2)
                WHERE id IN (SELECT submission_id FROM Review_Record
                             WHERE submission_id = :submissionId AND completed = TRUE AND COUNT(*) = 3);";

        //Update Paper's status if reviewStatus is 1 or 2
        $acceptSql = "UPDATE Paper SET status = (if((SELECT reviewStatus FROM Submission AS SUB
                                             WHERE reviewStatus = 1),
                                             Submission.reviewStatus + Submission.type * 10, Paper.status))
                 WHERE Paper.id = Submission.paper_id AND Submission.id = :submissionId;";

        $rejectSql = "UPDATE Paper SET status = (if((SELECT reviewStatus FROM Submission AS SUB
                                             WHERE reviewStatus = 2),
                                             Submission.reviewStatus + Submission.type * 10, Paper.status))
                 WHERE Paper.id = Submission.paper_id AND Submission.id = :submissionId;";
        try {
            $this->conn->beginTransaction();

            $submissionStmt = $this->conn->prepare($submissionSql);
            $acceptStmt = $this->conn->prepare($acceptSql);
            $rejectStmt = $this->conn->prepare($rejectSql);

            $submissionStmt->bindValue(":submissionID", $submissionId, PDO::PARAM_INT);
            $submissionStmt->execute();
            $acceptStmt->bindValue(":submissionID", $submissionId, PDO::PARAM_INT);
            $acceptStmt->execute();
            $rejectStmt->bindValue(":submissionID", $submissionId, PDO::PARAM_INT);
            $rejectStmt->execute();

            $this->conn->commit();

            return 1;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            return -1;
        }

    }

}