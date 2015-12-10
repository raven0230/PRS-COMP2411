<?php

include_once "../model/util/autoload.php";

class Work
{
    static function getWorkRows($reviewerId)
    {
        $reviewDB = new ReviewDB();
        $rows = $reviewDB->getReviewJobs($reviewerId);
        if ($rows == -1) {
            return "Error retrieving files.";
        }

        $htmlToReturn = "";
        if (empty($rows)) {
            return "You have currently done all your review works. Come back later";
        }
        foreach ($rows as $row) {
            $authors = explode(",", $row['authorsName']);
            $htmlToReturn .= "<tr>";
            $htmlToReturn .= "<td>" . $row["reviewId"] . "</a></td>";
            $htmlToReturn .= "<td><a href='review.php?reviewId=" . $row['reviewId'] . "'>" . $row["paperTitle"] . "</a></td>";
            $htmlToReturn .= "<td>" . Util::printPrettyAuthorNames($authors) . "</td>";
            $htmlToReturn .= "<td>" . $row["assignedTime"] . "</td>";
            $htmlToReturn .= "<td>" . Util::submitTypeToString($row["type"]) . "</td>";
            $htmlToReturn .= "<td>" . Util::boolToYesNo($row["completed"]) . "</td>";
            $htmlToReturn .= "</tr>";
        }
        return $htmlToReturn;
    }
}