<?php

namespace Jigoshop\Entity\Product\Attachment;

use Jigoshop\Entity\Product\Attachment;

/**
 * Class Datafile
 * @package Jigoshop\Entity\Product\Attachment;
 * @author Krzysztof Kasowski
 */
class Datafile extends Attachment
{
    const TYPE = 'datafile';
    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }
}