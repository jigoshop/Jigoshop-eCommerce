<?php

namespace Jigoshop;

use Jigoshop\Helper\Styles;

/**
 * Class Frontend
 * @package Jigoshop;
 * @author Krzysztof Kasowski
 */
class Frontend
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', function () {
           Styles::add('jigoshop', \JigoshopInit::getUrl().'/assets/css/jigoshop.css');
        });
    }
}