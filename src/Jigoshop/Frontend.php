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
           Styles::add('fontawesome', 'https://use.fontawesome.com/releases/v5.0.12/css/all.css');
           Styles::add('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css');
        });
    }
}