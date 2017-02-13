<?php

namespace Jigoshop\Entity\Product;

/**
 * Class Review
 * @package Jigoshop\Entity\Product;
 * @author Krzysztof Kasowski
 */
class Review
{
    /** @var  int */
    private $rating;
    /** @var  \WP_Comment */
    private $comment;

    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return \WP_Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param \WP_Comment $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
}