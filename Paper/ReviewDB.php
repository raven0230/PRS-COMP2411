<?php

include_once "../util/autoload.php";

class ReviewDB
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();

    }

    public function getReviewFile($paperId, $submitType)
    {
        $stmt = $this->conn->prepare("SELECT Review_Record.file AS 'file', Review_Record.file_mime AS 'mime'
                           FROM Review_Record, Submission WHERE
                           Review_Record.submission_id = Submission.id AND Submission.paper_id = :paperId
                           AND Submission.type = :submitType");

        $stmt->bindValue(":paperId", $paperId);
        $stmt->bindValue(":submitType", $submitType);

        if ($stmt->execute()) {
            $stmt->bindColumn("file", $file);
            $stmt->bindColumn("mime", $file_mime);
            if ($stmt->fetch()) {
                return array($file, $file_mime);
            } else {
                return -1;
            }
        } else {
            return -1;
        }
    }

    public function getReviewDetails($paperId, $submitType)
    {
        //get reviewer's name, rating, comment, time
        $stmt = $this->conn->prepare("SELECT concat(first_name, ' ', last_name) AS 'name',
                                      Review_Record.rating AS 'rating', Review_Record.file AS 'comment',
                                      Review_Record.time AS 'time' FROM Reviewer, Review_Record, Submission
                                      WHERE Review_Record.reviewer_id = Reviewer.email AND
                                      Submission.id = Review_Record.submission_id AND
                                      Submission.type = :submitType AND Submission.paper_id = :paperId");
        $stmt->bindValue(":submitType", $submitType, PDO::PARAM_INT);
        $stmt->bindValue(":paperId", $paperId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $stmt->bindColumn("name", $name, PDO::PARAM_STR);
            $stmt->bindColumn("rating", $rating, PDO::PARAM_INT);
            $stmt->bindColumn("comment", $comment, PDO::PARAM_LOB);
            $stmt->bindColumn("time", $time, PDO::PARAM_STR);
            if ($stmt->fetch(PDO::FETCH_BOUND)) {
                return array(
                    "name" => $name,
                    "rating" => $rating,
                    "comment" => $comment,
                    "time" => $time
                );
            } else {
                return -1;
            }
        } else {
            return -1;
        }

    }

    function assignReviewJob($submissionId, $reviewerIds)
    {
        $sql = "INSERT INTO Review_Record (submission_id, reviewer_id) VALUES";
        $reviewerIdsSize = sizeof($reviewerIds);
        if (empty($reviewerIds)) {
            return -1;
        }
        for ($i = 0; $i < $reviewerIdsSize; $i++) {
            $sql .= " (:submissionId" . $i . ", :reviewerId" . $i . ")";
            if ($i < $reviewerIdsSize - 1) {
                $sql .= ",";
            }
        }
        $stmt = $this->conn->prepare($sql);
        for ($a = 0; $a < $reviewerIds; $a++) {
            $stmt->bindValue(":submissionId" . $a, $submissionId, PDO::PARAM_INT);
            $stmt->bindValue(":reviewerId" . $a, $reviewerIds[$a], PDO::PARAM_INT);
        }

        if ($stmt->execute()) {
            return $stmt->rowCount();
        } else {
            return -1;
        }
    }

    function doReview($reviewRecordId, $filePointer, $fileMime, $rating)
    {
        $reviewSql = "UPDATE Review_Record
                 SET file = :filePointer, file_mime = :fileMime, rating = :rating,
                 completed = TRUE
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