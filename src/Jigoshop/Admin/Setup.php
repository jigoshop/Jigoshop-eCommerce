<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin\Settings\AdvancedTab;
use Jigoshop\Admin\Settings\GeneralTab;
use Jigoshop\Admin\Settings\ProductsTab;
use Jigoshop\Entity\Product\Attributes\StockStatus;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Integration;

/**
 * Class Setup
 * @package Jigoshop\Admin;
 * @author Krzysztof Kasowski
 */
class Setup implements DashboardInterface
{
    const SLUG = 'jigoshop_setup';

    public function __construct()
    {
        Styles::add('jigoshop.admin.setup', \JigoshopInit::getUrl().'/assets/css/admin/setup.css', ['jigoshop.admin']);
        Styles::add('jigoshop.admin.settings', \JigoshopInit::getUrl().'/assets/css/admin/settings.css', ['jigoshop.admin']);
        Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css', ['jigoshop.admin']);
        Styles::add('jigoshop.vendors.datepicker', \JigoshopInit::getUrl().'/assets/css/vendors/datepicker.css', ['jigoshop.admin']);
        Styles::add('jigoshop.vendors.bs_switch', \JigoshopInit::getUrl().'/assets/css/vendors/bs_switch.css', ['jigoshop.admin']);

        Scripts::add('jigoshop.admin.setup', \JigoshopInit::getUrl().'/assets/js/admin/setup.js', ['jigoshop.admin']);
        Scripts::add('jigoshop.admin.settings', \JigoshopInit::getUrl() . '/assets/js/admin/settings.js', ['jigoshop.admin'], ['in_footer' => true]);
        Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl() . '/assets/js/vendors/select2.js', [
            'jigoshop.admin.settings',
        ], ['in_footer' => true]);
        Scripts::add('jigoshop.vendors.bs_tab_trans_tooltip_collapse', \JigoshopInit::getUrl() . '/assets/js/vendors/bs_tab_trans_tooltip_collapse.js', [
            'jigoshop.admin.settings',
        ], ['in_footer' => true]);
        Scripts::add('jigoshop.vendors.bs_switch', \JigoshopInit::getUrl() . '/assets/js/vendors/bs_switch.js', [
            'jigoshop.admin.settings',
        ], ['in_footer' => true]);


        $states = [];
        foreach (Country::getAllStates() as $country => $stateList) {
            foreach ($stateList as $code => $state) {
                $states[$country][] = ['id' => $code, 'text' => $state];
            }
        }
        $currency = [];
        foreach (Currency::countries() as $key => $value) {
            $symbols = Currency::symbols();
            $symbol = $symbols[$key];
            $separator = Currency::decimalSeparator();
            $code = $key;

            $currency[$key] = [
                ['id' => '%1$s%3$s', 'text' => html_entity_decode(sprintf('%1$s0%2$s00', $symbol, $separator))],// symbol.'0'.separator.'00'
                ['id' => '%1$s %3$s', 'text'  => html_entity_decode(sprintf('%1$s 0%2$s00', $symbol, $separator))],// symbol.' 0'.separator.'00'
                ['id' => '%3$s%1$s', 'text'  => html_entity_decode(sprintf('0%2$s00%1$s', $symbol, $separator))],// '0'.separator.'00'.symbol
                ['id' => '%3$s %1$s', 'text'  => html_entity_decode(sprintf('0%2$s00 %1$s', $symbol, $separator))],// '0'.separator.'00 '.symbol
                ['id' => '%2$s%3$s', 'text'  => html_entity_decode(sprintf('%1$s0%2$s00', $code, $separator))],// code.'0'.separator.'00'
                ['id' => '%2$s %3$s', 'text'  => html_entity_decode(sprintf('%1$s 0%2$s00', $code, $separator))],// code.' 0'.separator.'00'
                ['id' => '%3$s%2$s', 'text'  => html_entity_decode(sprintf('0%2$s00%1$s', $code, $separator))],// '0'.separator.'00'.code
                ['id' => '%3$s %2$s', 'text'  => html_entity_decode(sprintf('0%2$s00 %1$s', $code, $separator))],// '0'.separator.'00 '.code
                ['id' => '%1$s%3$s%2$s', 'text'  => html_entity_decode(sprintf('%1$s0%2$s00%3$s', $symbol, $separator, $code))],// symbol.'0'.separator.'00'.code
                ['id' => '%1$s %3$s %2$s', 'text'  => html_entity_decode(sprintf('%1$s 0%2$s00 %3$s', $symbol, $separator, $code))],// symbol.' 0'.separator.'00 '.code
                ['id' => '%2$s%3$s%1$s', 'text'  => html_entity_decode(sprintf('%3$s0%2$s00%1$s', $symbol, $separator, $code))],// code.'0'.separator.'00'.symbol
                ['id' => '%2$s %3$s %1$s', 'text'  => html_entity_decode(sprintf('%3$s 0%2$s00 %1$s', $symbol, $separator, $code))],// code.' 0'.separator.'00 '.symbol
            ];
        }
        Scripts::localize('jigoshop.admin.setup', 'jigoshop_setup', [
            'states' => $states,
            'currency' => $currency,
        ]);

        $this->display();
    }

    /** @return string Title of page. */
    public function getTitle()
    {
        return __('Setup', 'jigoshop-ecommerce');
    }

    /** @return string Required capability to view the page. */
    public function getCapability()
    {
        return 'manage_jigoshop';
    }

    /** @return string Menu slug. */
    public function getMenuSlug()
    {
        return self::SLUG;
    }

    public function getSteps()
    {
        return [
            'page-setup' => __('Page setup', 'jigoshop-ecommerce'),
            'store-settings' => __('Store Settings', 'jigoshop-ecommerce'),
            'shipping' => __('Shipping', 'jigoshop-ecommerce'),
            //'payments' => __('Payments', 'jigoshop-ecommerce'),
            //'theme' => __('Theme', 'jigoshop-ecommerce'),
            'ready' => __('Ready!', 'jigoshop-ecommerce'),
        ];
    }

    public function getCurrentStep()
    {
        $steps = $this->getSteps();

        return isset($_GET['step'], $steps[$_GET['step']]) ?  $_GET['step'] : '';
    }

    public function getNextStep()
    {
        $steps = $this->getSteps();
        $currentStep = $this->getCurrentStep();
        $keys = array_keys($steps);
        $currentId = array_search($currentStep, $keys);

        if($currentId === false) {
            return $keys[0];
        } elseif (isset($keys[$currentId + 1])) {
            return $keys[$currentId + 1];
        } else {
            return null;
        }
    }

    public function getOptions()
    {
        $pages = [];
        $pages[0] = __('None', 'jigoshop-ecommerce');
        foreach(get_pages() as $page) {
            $pages[$page->ID] = $page->post_title;
        }
        $settings = Integration::getOptions()->getAll();

        $weightUnit = [
            'kg' => __('Kilograms', 'jigoshop-ecommerce'),
            'lbs' => __('Pounds', 'jigoshop-ecommerce'),
        ];
        $dimensionUnit = [
            'cm' => __('Centimeters', 'jigoshop-ecommerce'),
            'in' => __('Inches', 'jigoshop-ecommerce'),
        ];
        $stockStatuses = [
            StockStatus::IN_STOCK => __('In stock', 'jigoshop-ecommerce'),
            StockStatus::OUT_STOCK => __('Out of stock', 'jigoshop-ecommerce'),
        ];

        $options = [
            'page-setup' => [
                [
                    'name' => 'jigoshop['.AdvancedTab::SLUG.'][pages][shop]',
                    'label' => __('Shop page', 'jigoshop-ecommerce'),
                    'type' => 'select',
                    'value' => $settings[AdvancedTab::SLUG]['pages']['shop'],
                    'options' => $pages,
                ],
                [
                    'name' => 'jigoshop['.AdvancedTab::SLUG.'][pages][cart]',
                    'label' => __('Cart page', 'jigoshop-ecommerce'),
                    'type' => 'select',
                    'value' => $settings[AdvancedTab::SLUG]['pages']['cart'],
                    'options' => $pages,
                ],
                [
                    'name' => 'jigoshop['.AdvancedTab::SLUG.'][pages][checkout]',
                    'label' => __('Checkout page', 'jigoshop-ecommerce'),
                    'type' => 'select',
                    'value' => $settings[AdvancedTab::SLUG]['pages']['checkout'],
                    'options' => $pages,
                ],
                [
                    'name' => 'jigoshop['.AdvancedTab::SLUG.'][pages][checkout_thank_you]',
                    'label' => __('Thank you page', 'jigoshop-ecommerce'),
                    'type' => 'select',
                    'value' => $settings[AdvancedTab::SLUG]['pages']['checkout_thank_you'],
                    'options' => $pages,
                ],
                [
                    'name' => 'jigoshop['.AdvancedTab::SLUG.'][pages][account]',
                    'label' => __('My account page', 'jigoshop-ecommerce'),
                    'type' => 'select',
                    'value' => $settings[AdvancedTab::SLUG]['pages']['account'],
                    'options' => $pages,
                ],
                [
                    'name' => 'jigoshop['.AdvancedTab::SLUG.'][pages][terms]',
                    'label' => __('Terms page', 'jigoshop-ecommerce'),
                    'type' => 'select',
                    'value' => $settings[AdvancedTab::SLUG]['pages']['terms'],
                    'options' => $pages,
                ],
            ],
            'store-settings' => [
                [
                    'name' => 'jigoshop['.GeneralTab::SLUG.'][email]',
                    'label' => __('Administrator e-mail', 'jigoshop-ecommerce'),
                    'type' => 'text',
                    'tip' => __('The email address used to send all Jigoshop related emails, such as order confirmations and notices.', 'jigoshop-ecommerce'),
                    'value' => $settings[GeneralTab::SLUG]['email'],
                ],
                [
                    'id' => 'country',
                    'name' => 'jigoshop['.GeneralTab::SLUG.'][country]',
                    'label' => __('Shop location (country)', 'jigoshop-ecommerce'),
                    'type' => 'select',
                    'value' => $settings[GeneralTab::SLUG]['country'],
                    'options' => Country::getAll(),
                ],
                [
                    'id' => 'state',
                    'name' => 'jigoshop['.GeneralTab::SLUG.'][state]',
                    'label' => __('Shop location (state)', 'jigoshop-ecommerce'),
                    'type' => 'text',
                    'value' => $settings[GeneralTab::SLUG]['state'],
                ],
                [
                    'id' => 'currency',
                    'name' => 'jigoshop['.GeneralTab::SLUG.'][currency]',
                    'label' => __('Currency', 'jigoshop-ecommerce'),
                    'type' => 'select',
                    'value' => $settings[GeneralTab::SLUG]['currency'],
                    'options' => Currency::countries(),
                ],
                [
                    'id' => 'currency_position',
                    'name' => 'jigoshop['.GeneralTab::SLUG.'][currency_position]',
                    'label' => __('Currency position', 'jigoshop-ecommerce'),
                    'type' => 'text',
                    'value' => $settings[GeneralTab::SLUG]['currency_position'],
                    //'options' => Currency::positions(),
                ],
                [
                    'name' => 'jigoshop['.GeneralTab::SLUG.'][currency_decimals]',
                    'label' => __('Number of decimals', 'jigoshop-ecommerce'),
                    'type' => 'text',
                    'value' => $settings[GeneralTab::SLUG]['currency_decimals'],
                ],
                [
                    'name' => 'jigoshop['.GeneralTab::SLUG.'][currency_thousand_separator]',
                    'label' => __('Thousands separator', 'jigoshop-ecommerce'),
                    'type' => 'text',
                    'value' => $settings[GeneralTab::SLUG]['currency_thousand_separator'],
                ],
                [
                    'name' => 'jigoshop['.GeneralTab::SLUG.'][currency_decimal_separator]',
                    'label' => __('Decimal separator', 'jigoshop-ecommerce'),
                    'type' => 'text',
                    'value' => $settings[GeneralTab::SLUG]['currency_decimal_separator'],
                ],
            ],
            'shipping' => [
                [
                    'name' => 'jigoshop['.ProductsTab::SLUG.'][weight_unit]',
                    'label' => __('Weight units', 'jigoshop-ecommerce'),
                    'type' => 'select',
                    'value' => $settings[ProductsTab::SLUG]['weight_unit'],
                    'options' => $weightUnit,
                ],
                [
                    'name' => 'jigoshop['.ProductsTab::SLUG.'][dimensions_unit]',
                    'label' => __('Dimensions unit', 'jigoshop-ecommerce'),
                    'type' => 'select',
                    'value' => $settings[ProductsTab::SLUG]['dimensions_unit'],
                    'options' => $dimensionUnit,
                ],
                [
                    'name' => 'jigoshop['.ProductsTab::SLUG.'][stock_status]',
                    'label' => __('Stock status', 'jigoshop-ecommerce'),
                    'description' => __('This option allows you to change default stock status for new products.', 'jigoshop-ecommerce'),
                    'type' => 'select',
                    'value' => $settings[ProductsTab::SLUG]['stock_status'],
                    'options' => $stockStatuses,
                ],
            ],
        ];

        if(!is_array($options[$this->getCurrentStep()])) {
            return [];
        }

        return $options[$this->getCurrentStep()];
    }

    /** Displays the page. */
    public function display()
    {
        Render::output('admin/setup', [
            'steps' => $this->getSteps(),
            'currentStep' => $this->getCurrentStep(),
            'nextStep' => $this->getNextStep(),
            'options' => $this->getOptions(),
        ]);

        exit;
    }
}