<?php

namespace Jigoshop\Shipping;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Types;
use Jigoshop\Core\Options;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Render;
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
    /** @var  Messages */
    private $messages;

    /**
     * AdvancedFlatRate constructor.
     * @param Wordpress $wp
     * @param Options $options
     * @param CartServiceInterface $cartService
     */
    public function __construct(Wordpress $wp, Options $options, CartServiceInterface $cartService, Messages $messages)
    {
        $this->settings = $options->get('shipping.' . self::ID);
        $this->cartService = $cartService;
        $this->messages = $messages;
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
        return is_admin() ? __('Advanced flat rate', 'jigoshop-ecommerce') : $this->settings['title'];
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
                    $this->settings['countries']));
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
        if(count($this->settings['rates_order'])) {
            $this->settings['rates'] = array_merge(array_flip($this->settings['rates_order'], $this->settings['rates']));
        }

        return [
            [
                'name' => sprintf('[%s][enabled]', self::ID),
                'type' => 'checkbox',
                'title' => __('Enable', 'jigoshop-ecommerce'),
                'checked' => $this->settings['enabled'],
                'classes' => ['switch-medium'],
            ],
            [
                'name' => sprintf('[%s][title]', self::ID),
                'type' => 'text',
                'title' => __('Title', 'jigoshop-ecommerce'),
                'value' => $this->settings['title'],
            ],
            [
                'title' => __('Enable Only for Admin', 'jigoshop-ecommerce'),
                'description' => __('Enable this if you would like to test it only for Site Admin', 'jigoshop-ecommerce'),
                'name' => sprintf('[%s][adminOnly]', self::ID),
                'type' => 'checkbox',
                'checked' => $this->settings['adminOnly'],
                'classes' => ['switch-medium'],
            ],
            [
                'name' => sprintf('[%s][taxable]', self::ID),
                'type' => 'checkbox',
                'title' => __('Is taxable?', 'jigoshop-ecommerce'),
                'checked' => $this->settings['taxable'],
                'classes' => ['switch-medium'],
            ],
            [
                'name' => sprintf('[%s][fee]', self::ID),
                'type' => 'text',
                'title' => __('Fee', 'jigoshop-ecommerce'),
                'value' => $this->settings['fee'],
            ],
            [
                'name' => sprintf('[%s][available_for]', self::ID),
                'id' => 'advanced_flat_rate_available_for',
                'title' => __('Available for', 'jigoshop-ecommerce'),
                'type' => 'select',
                'value' => $this->settings['available_for'],
                'options' => [
                    'all' => __('All allowed countries', 'jigoshop-ecommerce'),
                    'specific' => __('Selected countries', 'jigoshop-ecommerce'),
                ],
            ],
            [
                'name' => sprintf('[%s][countries]', self::ID),
                'id' => 'advanced_flat_rate_countries',
                'title' => __('Select countries', 'jigoshop-ecommerce'),
                'type' => 'select',
                'value' => $this->settings['countries'],
                'options' => Country::getAllowed(),
                'multiple' => true,
                'hidden' => $this->settings['available_for'] == 'all',
            ],
            [
                'name' => sprintf('[%s][multiple_rates]', self::ID),
                'title' => __('Show multiple rates', 'jigoshop-ecommerce'),
                'description' => __('If enabled then all matched rates will be visible in cart/checkout. '.
                    'Otherwise, the first available rate, from the rates list, which includes the destination, will be used in cart. '.
                    '<br/>You can change the order/priority of created rates by movinge them up and down on the list.', 'jigoshop'),
                'type' => 'checkbox',
                'checked' => $this->settings['multiple_rates'],
                'classes' => ['switch-medium'],
            ],
            [
                'name' => sprintf('[%s][rates]', self::ID),
                'title' => __('Rates', 'jigoshop-ecommerce'),
                'type' => 'user_defined',
                'value' => $this->settings['rates'],
                'display' => function ($field) {
                    return Render::get('admin/settings/shipping/advanced_flat_rate', [
                        'name' => $field['name'],
                        'values' => $field['value'],
                    ]);
                }
            ],
        ];
    }

    /**
     * @return array List of applicable tax classes.
     */
    public function getTaxClasses()
    {
        return ['standard'];
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
        $settings['multiple_rates'] = $settings['multiple_rates'] == 'on';
        $settings['adminOnly'] = $settings['adminOnly'] == 'on';

        if (isset($settings['rates'])) {
            $settings['rates'] = array_values($settings['rates']);
            for ($i = 0; $i < count($settings['rates']); $i++) {
                $settings['rates'][$i] = array_merge([
                    'label' => '',
                    'cost' => 0,
                    'continents' => [],
                    'countries' => [],
                    'states' => [],
                    'postcode' => '',
                    'rest_of_the_world' => false,
                ], $settings['rates'][$i]);
                $settings['rates'][$i]['cost'] = (float)$settings['rates'][$i]['cost'];
                $settings['rates'][$i]['rest_of_the_world'] = $settings['rates'][$i]['rest_of_the_world'] == 'on';
            }
        }

        if ($settings['fee'] >= 0) {
            $settings['fee'] = (float)$settings['fee'];
        } else {
            $settings['fee'] = $this->options['fee'];
            $this->messages->addWarning(__('Fee was below 0 - value is left unchanged.', 'jigoshop-ecommerce'));
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
        return [
            'id' => $this->getId(),
            'rate' => $this->getShippingRate()
        ];
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
            $this->rates = [];
            if(count($this->settings['rates_order'])) {
                $this->settings['rates'] = array_merge(array_flip($this->settings['rates_order'], $this->settings['rates']));
            }
            foreach ($this->settings['rates'] as $key => $rawRate) {
                $address = $order->getCustomer()->getShippingAddress();
                $code = str_replace('*', '(.*)', str_replace(['-', ' '], '', strtoupper($rawRate['postcode'])));

                if ((count($rawRate['continents']) && in_array(Country::getContinentByCountry($address->getCountry()), $rawRate['continents'])) ||
                    (count($rawRate['countries']) && in_array($address->getCountry(), $rawRate['countries'])) ||
                    (count($rawRate['states']) && in_array($address->getCountry().':'.$address->getState(), $rawRate['states'])) ||
                    ($code != '' && preg_match('/^'.$code.'$/', str_replace(['-', ' '], '', strtoupper($address->getPostcode())))) ||
                    (empty($rawRate['continents']) && empty($rawRate['countries']) && empty($rawRate['states']) && empty($code) && !$rawRate['rest_of_the_world'])
                ) {
                    $rate = new Rate();
                    $rate->setId($key);
                    $rate->setName($rawRate['label']);
                    $rate->setPrice($rawRate['cost'] + (1 * $this->settings['fee']));
                    $rate->setMethod($this);
                    $this->rates[$key] = $rate;
                    if(!$this->settings['multiple_rates']) {
                        break;
                    }
                }
            }
            if(empty($this->rates)) {
                foreach ($this->settings['rates'] as $key => $rawRate) {
                    if(isset($rawRate['rest_of_the_world']) && $rawRate['rest_of_the_world']) {
                        $rate = new Rate();
                        $rate->setId($key);
                        $rate->setName($rawRate['label']);
                        $rate->setPrice($rawRate['cost'] + (1 * $this->settings['fee']));
                        $rate->setMethod($this);
                        $this->rates[$key] = $rate;
                        if(!$this->settings['multiple_rates']) {
                            break;
                        }
                    }
                }
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

    /**
     * Whenever method was enabled by the user.
     *
     * @return boolean Method enable state.
     */
    public function isActive()
    {
        return isset($this->settings['enabled']) && $this->settings['enabled'];
    }

    /**
     * Set method enable state.
     *
     * @param boolean $state Method enable state.
     *
     * @return array Method current settings (after enable state change).
     */
    public function setActive($state)
    {
        $this->settings['enabled'] = $state;

        return $this->settings;
    }

    /**
     * Whenever method was configured by the user (all required data was filled for current scenario).
     *
     * @return boolean Method config state.
     */
    public function isConfigured()
    {
        return true;
    }

    /**
     * Whenever method has some sort of test mode.
     *
     * @return boolean Method test mode presence.
     */
    public function hasTestMode()
    {
        return false;
    }

    /**
     * Whenever method test mode was enabled by the user.
     *
     * @return boolean Method test mode state.
     */
    public function isTestModeEnabled()
    {
        return false;
    }

    /**
     * Set Method test mode state.
     *
     * @param boolean $state Method test mode state.
     *
     * @return array Method current settings (after test mode state change).
     */
    public function setTestMode($state)
    {
        return $this->settings;
    }

    /**
     * Whenever method requires SSL to be enabled to function properly.
     *
     * @return boolean Method SSL requirment.
     */
    public function isSSLRequired()
    {
        return false;
    }

    /**
     * Whenever method is set to enabled for admin only.
     *
     * @return boolean Method admin only state.
     */
    public function isAdminOnly()
    {
        return isset($this->settings['adminOnly']) && $this->settings['adminOnly'];
    }

    /**
     * Sets admin only state for the method and returns complete method options.
     *
     * @param boolean $state Method admin only state.
     *
     * @return array Complete method options after change was applied.
     */
    public function setAdminOnly($state)
    {
        $this->settings['adminOnly'] = $state;

        return $this->settings;
    }
}