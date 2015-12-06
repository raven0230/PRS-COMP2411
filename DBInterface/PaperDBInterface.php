<?php


namespace PRS\DBInterface;


interface PaperDBInterface
{

    /**
     * Get file from particular submission.
     * @param $paperId
     * @param $submitType - 0 for abs, 1 for paper, 2 for revised
     * @return array
     * array is simple array
     * int indicates problems - 0 for empty result, 1 for error
     */
    function getSubmissionFile($paperId, $submitType);


    /**
     * Get paper info - NOT submission.
     * @param $paperId
     * @return \model\PaperInfo $paperObj
     */
    function getPaperInfo($paperId);

    function getPaperList();
}