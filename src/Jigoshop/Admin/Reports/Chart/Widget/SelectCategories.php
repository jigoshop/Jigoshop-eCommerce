<?php

namespace Jigoshop\Admin\Reports\Chart\Widget;


use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Admin\Reports\Chart\WidgetInterface;

class SelectCategories implements WidgetInterface
{
    const SLUG = 'select_categories';
    private $selectedCategories = array();
    private $allCategories = array();

    public function __construct($selectedCategories, $allCategories)
    {
        $this->selectedCategories = $selectedCategories;
        $this->allCategories = $allCategories;
    }

    public function getSlug()
    {
        return self::SLUG;
    }

    public function getTitle()
    {
        return __('Select Categories', 'jigoshop');
    }

    public function getArgs()
    {
        return array(
            'id' => 'select_categories',
            'name' => 'show_categories',
            'value' => $this->selectedCategories,
            'multiple' => true,
            'classes' => array(),
            'options' => $this->allCategories,
            'size' => 14,
        );
    }

    public function isVisible()
    {
        return false;
    }
    
    public function display()
    {
        Forms::select($this->getArgs());
    }
}