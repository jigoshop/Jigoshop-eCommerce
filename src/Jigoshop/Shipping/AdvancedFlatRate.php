<?php

namespace Jigoshop\Shipping;

use Jigoshop\Core\Types;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Options;
use Jigoshop\Helper\Render;
use Jigoshop\Integration;
use Jigoshop\Exception;
use Jigoshop\Service\CartServiceInterface;
use WPAL\Wordpress;

/**
 * Class Method
 * @package Jigoshop\Extension\AddFlatRate\Common;
 * @author Krzysztof Kasowski
 */
class AdvancedFlatRate implements MultipleMethod
{
    const ID = 'advanced_flat_rate';
    /** @var  array */
    private $settings;
    /** @var  Rate[] */
    private $rates;
    /** @var  int */
    private $rate;
    /** @var  CartServiceInterface */
    private $cartService;

    /**
     * Method constructor.
     */
    public function __construct(Wordpress $wp, CartServiceInterface $cartService)
    {
        Options::setDefaults('shipping.' . self::ID, array(
            'enabled' => false,
            'title' => '',
            'taxable' => false,
            'fee' => 0,
            'available_for' => 'all',
            'countries' => array(),
            'rates' => array()
        ));
        $this->settings = Options::getOptions('shipping.' . self::ID);
        $this->cartService = $cartService;
    }

    /**
     * @return string ID of shipping method.
     */
    public function getId()
    {
        return self::ID;
    }

    /**
     * @return string Name of method.
     */
    public function getName()
    {
        return is_admin() ? __('Advanced flat rate', 'jigoshop') : $this->settings['title'];
    }

    /**
     * @return string Customizable title of method.
     */
    public function getTitle()
    {
        return $this->settings['title'];
    }

    /**
     * @return bool Whether current method is enabled and able to work.
     */
    public function isEnabled()
    {
        $post = get_post();
        if ($post === null || $post->post_type != Types::ORDER) {
            $cart = $this->cartService->getCurrent();
            $customer = $cart->getCustomer();
        } else {
            $customer = unserialize(get_post_meta($post->ID, 'customer', true));
        }

        return $this->settings['enabled'] && ($this->settings['available_for'] === 'all' || in_array($customer->getShippingAddress()->getCountry(),
                    $this->settins['countries']));
    }

    /**
     * @return bool Whether current method is taxable.
     */
    public function isTaxable()
    {
        return $this->settings['taxable'];
    }

    /**
     * @return array List of options to display on Shipping settings page.
     */
    public function getOptions()
    {
        return array(
            array(
                'name' => sprintf('[%s][enabled]', self::ID),
                'type' => 'checkbox',
                'title' => __('Enable', 'jigoshop'),
                'checked' => $this->settings['enabled'],
                'classes' => array('switch-medium'),
            ),
            array(
                'name' => sprintf('[%s][title]', self::ID),
                'type' => 'text',
                'title' => __('Title', 'jigoshop'),
                'value' => $this->settings['title'],
            ),
            array(
                'name' => sprintf('[%s][taxable]', self::ID),
                'type' => 'checkbox',
                'title' => __('Is taxable?', 'jigoshop'),
                'checked' => $this->settings['taxable'],
                'classes' => array('switch-medium'),
            ),
            array(
                'name' => sprintf('[%s][fee]', self::ID),
                'type' => 'number',
                'title' => __('Fee', 'jigoshop'),
                'value' => $this->settings['fee'],
            ),
            array(
                'name' => sprintf('[%s][available_for]', self::ID),
                'id' => 'advanced_flat_rate_available_for',
                'title' => __('Available for', 'jigoshop'),
                'type' => 'select',
                'value' => $this->settings['available_for'],
                'options' => array(
                    'all' => __('All allowed countries', 'jigoshop'),
                    'specific' => __('Selected countries', 'jigoshop'),
                ),
            ),
            array(
                'name' => sprintf('[%s][countries]', self::ID),
                'id' => 'advanced_flat_rate_countries',
                'title' => __('Select countries', 'jigoshop'),
                'type' => 'select',
                'value' => $this->settings['countries'],
                'options' => Country::getAllowed(),
                'multiple' => true,
                'hidden' => $this->settings['available_for'] == 'all',
            ),
            array(
                'name' => sprintf('[%s][rates]', self::ID),
                'title' => __('Rates', 'jigoshop'),
                'type' => 'user_defined',
                'value' => $this->settings['rates'],
                'display' => function ($field) {
                    Render::output('admin/settings/shipping/advanced_flat_rate', [
                        'name' => $field['name'],
                        'values' => $field['value'],
                    ]);
                }
            ),
        );
    }

    /**
     * @return array List of applicable tax classes.
     */
    public function getTaxClasses()
    {
        return array('standard');
    }

    /**
     * Validates and returns properly sanitized options.
     *
     * @param $settings array Input options.
     *
     * @return array Sanitized result.
     */
    public function validateOptions($settings)
    {
        $settings['enabled'] = $settings['enabled'] == 'on';
        $settings['taxable'] = $settings['taxable'] == 'on';
        if (isset($settings['rates'])) {
            $settings['rates'] = array_values($settings['rates']);
            for ($i = 0; $i < count($settings['rates']); $i++) {
                $settings['rates'][$i] = array_merge(array(
                    'label' => '',
                    'cost' => 0,
                    'country' => '',
                    'states' => array(),
                    'postcode' => ''
                ), $settings['rates'][$i]);
                $settings['rates'][$i]['cost'] = (float)$settings['rates'][$i]['cost'];
            }
        }

        return $settings;
    }

    /**
     * Checks whether current method is the one specified with selected rule.
     *
     * @param \Jigoshop\Shipping\Method $method Method to check.
     * @param Rate $rate Rate to check.
     *
     * @return boolean Is this the method?
     */
    public function is(\Jigoshop\Shipping\Method $method, $rate = null)
    {
        return $method->getId() == $this->getId() && $rate instanceof Rate && $rate->getId() == $this->getShippingRate();
    }

    /**
     * @param OrderInterface $order Order to calculate shipping for.
     *
     * @return float Calculates value of shipping for the order.
     * @throws Exception On error.
     */
    public function calculate(OrderInterface $order)
    {
        if ($this->rate !== null) {
            $rates = $this->getRates($order);
            if (empty($rates)) {
                throw new Exception(sprintf(__('%s - There are no rates to calculate, rate is empty',
                    'jigoshop_add_flat_rate_shipping'),
                    $this->getName()));
            }
            if (!isset($rates[$this->rate])) {
                throw new Exception(sprintf(__('%s - No rates have been choose', 'jigoshop_add_flat_rate_shipping'),
                    $this->getName()));
            }
            return $rates[$this->rate]->getPrice();
        } else {
            throw new Exception(sprintf(__('%s - There was an error during calculating rate, please try again.',
                'jigoshop_add_flat_rate_shipping'), $this->getName()));
        }
    }

    /**
     * @return array Minimal state to fully identify shipping method.
     */
    public function getState()
    {
        return array(
            'id' => $this->getId(),
            'rate' => $this->getShippingRate()
        );
    }

    /**
     * Restores shipping method state.
     *
     * @param array $state State to restore.
     */
    public function restoreState(array $state)
    {
        if (isset($state['rate'])) {
            $this->setShippingRate($state['rate']);
        }
    }

    /**
     * Returns list of available shipping rates.
     *
     * @param OrderInterface $order
     *
     * @return array List of available shipping rates.
     */
    public function getRates($order)
    {
        if ($this->rates == null) {
            $this->rates = array();
            foreach ($this->settings['rates'] as $key => $rawRate) {
                $address = $order->getCustomer()->getShippingAddress();
                if ($rawRate['country'] != '' && $rawRate['country'] != $address->getCountry()) {
                    continue;
                }
                if (count($rawRate['states']) != 0 && !in_array($address->getState(), $rawRate['states'])) {
                    continue;
                }
                $code = str_replace('*', '(.*)', str_replace(['-', ' '], '', strtoupper($rawRate['postcode'])));
                if ($code != '' && preg_match('/^'.$code.'$/', str_replace(['-', ' '], '', strtoupper($address->getPostcode()))) == false) {
                    continue;
                }

                $rate = new Rate();
                $rate->setId($key);
                $rate->setName($rawRate['label']);
                $rate->setPrice($rawRate['cost'] + (1 * $this->settings['fee']));
                $rate->setMethod($this);
                $this->rates[$key] = $rate;
            }
        }

        return $this->rates;
    }

    /**
     * @param $rate int Rate to use.
     */
    public function setShippingRate($rate)
    {
        $this->rate = $rate;
    }

    /**
     * @return int Currently used rate.
     */
    public function getShippingRate()
    {
        return $this->rate;
    }
}