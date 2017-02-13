<?php

namespace Jigoshop\Core\Upgrade;

use Jigoshop\Container;
use Jigoshop\Core\Options;
use Jigoshop\Shipping\AdvancedFlatRate;
use WPAL\Wordpress;

/**
 * Class GetOptionsFromAddFlatRatePlugin
 * @package Jigoshop\Core\Upgrade;
 * @author Krzysztof Kasowski
 */
class GetOptionsFromAddFlatRatePlugin implements Upgrader
{

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function up(Wordpress $wp, Container $di)
    {
        /** @var Options $options */
        $options = $di->get('jigoshop.core.options');
        $settings = $options->get('shipping.add_flat_rate', [
            'enabled' => false,
            'title' => '',
            'taxable' => false,
            'fee' => 0,
            'available_for' => 'all',
            'countries' => [],
            'rates' => []
        ]);

        $options->update('shipping.'.AdvancedFlatRate::ID, $settings);
        $options->saveOptions();
    }

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function down(Wordpress $wp, Container $di)
    {
        /** @var Options $options */
        $options = $di->get('jigoshop.core.options');
        $settings = $options->get('shipping.'.AdvancedFlatRate::ID, [
            'enabled' => false,
            'title' => '',
            'taxable' => false,
            'fee' => 0,
            'available_for' => 'all',
            'countries' => [],
            'rates' => []
        ]);

        $options->update('shipping.add_flat_rate', $settings);
        $options->saveOptions();
    }
}