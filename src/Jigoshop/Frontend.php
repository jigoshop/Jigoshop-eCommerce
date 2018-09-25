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
            Styles::add('fontawesome', 'https://use.fontawesome.com/releases/v5.0.12/css/all.css');
            Styles::add('bootstrap', \JigoshopInit::getUrl().'/assets/css/vendors/bootstrap.css');
            Styles::add('jigoshop', \JigoshopInit::getUrl().'/assets/css/jigoshop.css', ['bootstrap', 'fontawesome']);
        }, 0);
    }
}