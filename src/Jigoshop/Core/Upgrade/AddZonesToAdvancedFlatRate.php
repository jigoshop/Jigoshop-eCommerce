<?php

namespace Jigoshop\Core\Upgrade;
use Jigoshop\Container;
use Jigoshop\Core\Options;
use WPAL\Wordpress;

/**
 * Class AddZonesToAdvancedFlatRate
 * @package Jigoshop\Core\Upgrade;
 * @author Krzysztof Kasowski
 */
class AddZonesToAdvancedFlatRate implements Upgrader
{

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function up(Wordpress $wp, Container $di)
    {
        /** @var Options $options */
        $options = $di->get('jigoshop.options');
        $rates = $options->get('shipping.advanced_flat_rate.rates', []);
        for($i = 0; $i < count($rates); $i++) {
            if(isset($rates[$i]['country']) && $rates[$i]['country'] && (!isset($rates[$i]['countries']) || empty($rates[$i]['countries']))) {
                $country = $rates[$i]['country'];
                if(!empty($rates[$i]['states'])) {
                    $rates[$i]['states'] = array_map(function($state) use ($country) {
                        return $country.':'.$state;
                    }, $rates[$i]['states']);
                } else {
                    $rates[$i]['countries'] = [$country];
                }
            };
            $rates[$i]['rest_of_the_world'] = isset($rates[$i]['rest_of_the_world']) ? $rates[$i]['rest_of_the_world'] : false;
            $rates[$i] = array_merge([
                'label' => __('New rate', 'jigoshop-ecommerce'),
                'cost' => '0',
                'continents' => [],
                'countries' => [],
                'states' => [],
                'postcode' => '',
                'rest-of-the-world' => false
            ], $rates[$i]);
        }

        $options->update('shipping.advanced_flat_rate.rates', $rates);
        $options->saveOptions();
    }

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function down(Wordpress $wp, Container $di)
    {
    }
}