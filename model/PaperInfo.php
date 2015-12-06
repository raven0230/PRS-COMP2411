<?php


namespace model;


class PaperInfo
{
    private $title;
    private $authorArr;
    private $reviewStatusArr;
    private $keywordArr;
private
    const MAX_KEYWORD = 5;

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getAuthorArr()
    {
        return $this->authorArr;
    }

    /**
     * @param mixed $authorArr
     */
    public function setAuthorArr($authorArr)
    {

        $this->authorArr = $authorArr;
    }

    public function addAuthor($authorName)
    {
        array_push($this->authorArr, $authorName);
    }

    /**
     * @return mixed
     */
    public function getKeywordArr()
    {
        return $this->keywordArr;
    }

    public function addKeyword($keyword)
    {
        if (sizeof($this->keywordArr) < 5) {
            array_push($keyword);
        }
    }

    /**
     * @param mixed $keywordArr
     */
    public function setKeywordArr($keywordArr)
    {
        if (sizeof($keywordArr) > 5) {
            array_splice($keywordArr, self::MAX_KEYWORD);
        }
        $this->keywordArr = $keywordArr;
    }


}