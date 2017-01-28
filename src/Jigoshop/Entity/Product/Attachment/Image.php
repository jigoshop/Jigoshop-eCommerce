<?php

namespace Jigoshop\Entity\Product\Attachment;

use Jigoshop\Entity\Product\Attachment;

/**
 * Class Image
 * @package Jigoshop\Entity\Product\Attachment;
 * @author Krzysztof Kasowski
 */
class Image extends Attachment
{
    const TYPE = 'image';

    /** @var  string  */
    private $thumbnail;

    /** @var  image  */
    private $image;

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param string $thumbnail
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param Image $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return array
     */
    public function getStateToSave()
    {
        $state = parent::getStateToSave();
        $state['thumbnail'] =  $this->thumbnail;
        $state['image'] = $this->image;

        return $state;
    }

    /**
     * @param array $state
     */
    public function restoreState(array $state)
    {
        parent::restoreState($state);
        $this->thumbnail = isset($state['thumbnail']) ? $state['thumbnail'] : null;
        $this->image = isset($state['image']) ? $state['image'] : null;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $state = parent::getStateToSave();
        $state['thumbnail'] =  $this->thumbnail;
        $state['image'] = $this->image;

        return $state;
    }
}