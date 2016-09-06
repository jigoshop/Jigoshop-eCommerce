<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Container;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

/**
 * Advanced tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class AdvancedTab implements TabInterface
{
    const SLUG = 'advanced';

    /** @var Wordpress */
    private $wp;
    /** @var Options */
    private $options;
    /** @var array */
    private $settings;
    /** @var Messages */
    private $messages;
    /** @var array */
    private $caches;
    /** @var array */
    private $sessionTypes;
    /** @var \Jigoshop\Container */
    private $di;

    public function __construct(Wordpress $wp, Container $di, Options $options, Messages $messages)
    {
        $this->wp = $wp;
        $this->di = $di;
        $this->options = $options;
        $this->settings = $options->get(self::SLUG);
        $this->messages = $messages;

        $this->caches = array(
            'simple' => _x('Simple', 'cache', 'jigoshop'),
            'php_fast_cache' => _x('Php Fast Cache - Use this option only if your database is responding slowly. ',
                'cache', 'jigoshop'),
        );
        $this->sessionTypes = array(
            'php' => __('Php session', 'jigoshop'),
            'transient' => __('Wordpress transient', 'jigoshop'),
        );

        $wp->addAction('admin_enqueue_scripts', function () use ($options){
            if (!isset($_GET['tab']) || $_GET['tab'] != AdvancedTab::SLUG) {
                return;
            }
            Scripts::add('jigoshop.admin.settings.taxes', \Jigoshop::getUrl().'/assets/js/admin/settings/advanced.js',
                array('jquery', 'wp-util'), array('page' => 'jigoshop_page_jigoshop_settings'));
        });
    }

    /**
     * @return string Title of the tab.
     */
    public function getTitle()
    {
        return __('Advanced', 'jigoshop');
    }

    /**
     * @return string Tab slug.
     */
    public function getSlug()
    {
        return self::SLUG;
    }

    /**
     * @return array List of items to display.
     */
    public function getSections()
    {
        $pages = $this->_getPages();
        $termsPages = $pages;
        $termsPages[0] = __('None', 'jigoshop');

        return array(
            array(
                'title' => __('Cron jobs', 'jigoshop'),
                'id' => 'cron',
                'fields' => array(
                    array(
                        'name' => '[automatic_complete]',
                        'title' => __('Complete processing orders', 'jigoshop'),
                        'description' => __("Change all 'Processing' orders older than one month to 'Completed'",
                            'jigoshop'),
                        'tip' => __("For orders that have been completed but the status is still set to 'processing'.  This will move them to a 'completed' status without sending an email out to all the customers.",
                            'jigoshop'),
                        'type' => 'checkbox',
                        'checked' => $this->settings['automatic_complete'],
                        'classes' => array('switch-medium'),
                    ),
                    array(
                        'name' => '[automatic_reset]',
                        'title' => __('Reset pending orders', 'jigoshop'),
                        'description' => __("Change all 'Pending' orders older than one month to 'On Hold'",
                            'jigoshop'),
                        'tip' => __("For customers that have not completed the Checkout process or haven't paid for an order after a period of time, this will reset the Order to On Hold allowing the Shop owner to take action.  WARNING: For the first use on an existing Shop this setting <em>can</em> generate a <strong>lot</strong> of email!",
                            'jigoshop'),
                        'type' => 'checkbox',
                        'checked' => $this->settings['automatic_reset'],
                        'classes' => array('switch-medium'),
                    ),
                ),
            ),
//			Tak na przyszłość, jak będziemy chcieli wrócić, coś dorobić
//			array(
//				'title' => __('Enforcing', 'jigoshop'),
//				'id' => 'enforcing',
//				'fields' => array(
//					array(
//						'name' => '[force_ssl]',
//						'title' => __('Force SSL on checkout', 'jigoshop'),
//						'description' => __('Enforces WordPress to use SSL on checkout pages.', 'jigoshop'),
//						'type' => 'checkbox',
//						'checked' => $this->settings['force_ssl'],
//						'classes' => array('switch-medium'),
//					),
//				),
//			),
            array(
                'title' => __('Integration', 'jigoshop'),
                'id' => 'integration',
                'fields' => array(
                    // TODO: Share This integration
//					array(
//						'name' => '[integration][share_this]',
//						'title' => __('ShareThis Publisher ID', 'jigoshop'),
//						'description' => __("Enter your <a href='http://sharethis.com/account/'>ShareThis publisher ID</a> to show ShareThis on product pages.", 'jigoshop'),
//						'tip' => __('ShareThis is a small social sharing widget for posting links on popular sites such as Twitter and Facebook.', 'jigoshop'),
//						'type' => 'text',
//						'value' => $this->settings['integration']['share_this'],
//					),
                    array(
                        'name' => '[integration][google_analytics]',
                        'title' => __('Google Analytics ID', 'jigoshop'),
                        'description' => __('Log into your Google Analytics account to find your ID. e.g. <code>UA-XXXXXXX-X</code>',
                            'jigoshop'),
                        'type' => 'text',
                        'value' => $this->settings['integration']['google_analytics'],
                    ),
                ),
            ),
            array(
                'title' => __('Products list', 'jigoshop'),
                'id' => 'products_list',
                'fields' => array(
                    array(
                        'name' => '[products_list][variations_sku_stock]',
                        'title' => __('Show variation\'s SKU and stock', 'jigoshop'),
                        'description' => __("Show all variation's SKU and stock on admin products list page.",
                            'jigoshop'),
                        'type' => 'checkbox',
                        'checked' => $this->settings['products_list']['variations_sku_stock'],
                        'classes' => array('switch-medium'),
                    ),
                ),
            ),
            array(
                'title' => __('Others', 'jigoshop'),
                'id' => 'others',
                'fields' => array(
                    array(
                        'name' => '[cache]',
                        'title' => __('Caching mechanism', 'jigoshop'),
                        'description' => __('Decides which mechanism for caching is used on the page.', 'jigoshop'),
                        'type' => 'select',
                        'value' => $this->settings['cache'],
                        'options' => $this->caches,
                    ),
                    array(
                        'name' => '[session]',
                        'title' => __('Session mechanism', 'jigoshop'),
                        'description' => __('Decides which mechanism for session is used on the page.', 'jigoshop'),
                        'type' => 'select',
                        'value' => $this->settings['session'],
                        'options' => $this->sessionTypes,
                    ),
                    array(
                        'name' => '[ignore_meta_queries]',
                        'title' => __('Ignore meta queries on product list', 'jigoshop'),
                        'description' => __('Ignores products\' visibility to enhance the loading time.
Warning : This will result in showing "out of stock" products on the catalog page, as well as making all products visible in the catalog and search pages.',
                            'jigoshop'),
                        'type' => 'checkbox',
                        'checked' => $this->settings['ignore_meta_queries'],
                        'classes' => array('switch-medium'),
                    ),
                    array(
                        'name' => '[install_emails]',
                        'title' => __('Create default emails', 'jigoshop'),
                        'description' => __('Creates default emails for Jigoshop email system.', 'jigoshop'),
                        'type' => 'user_defined',
                        'display' => function () {
                            Render::output('admin/settings/create_emails', array());
                        },
                    ),
                ),
            ),
            array(
                'title' => __('API', 'jigoshop'),
                'id' => 'api',
                'description' => __('API DESC', 'jigoshop'),
                'fields' => array(
                    array(
                        'name' => '[api][keys]',
                        'title' => __('Keys', 'jigoshop'),
                        'type' => 'user_defined',
                        'value' => $this->settings['api']['keys'],
                        'description' => __('Logged users don\'t need to use api keys, guest can see products and manage their carts', 'jigoshop'),
                        'display' => function ($field) {
                            \WpDebugBar\Debugger::addMessage($field, 'field');
                            Render::output('admin/settings/api_keys', array(
                                'name' => $field['name'],
                                'values' => $field['value'],
                                'description' => $field['description'],
                                'availablePermissions' => $this->wp->applyFilters('jigoshop\settings\api\available_permissions', array(
                                    'read_orders' => __('Read orders', 'jigoshop'),
                                    'manage_orders' => __('Manage orders', 'jigoshop'),
                                    'read_coupons' => __('Read coupons', 'jigoshop'),
                                    'manage_coupons' => __('Manage coupons', 'jigoshop'),
                                    'read_customers' => __('Read Customers', 'jigoshop'),
                                    'manage_customers' => __('Manage Customers', 'jigoshop'),
                                    'manage_products' => __('Manage products', 'jigoshop'),
                                    'manage_emails' => __('Manage Emails', 'jigoshop'),
                                )),
                            ));
                        }
                    ),
                )
            ),
            array(
                'title' => __('Pages', 'jigoshop'),
                'id' => 'pages',
                'description' => __('This section allows you to change content source page for each part of Jigoshop. It will not change the main behaviour though.',
                    'jigoshop'),
                'fields' => array(
                    array(
                        'name' => '[pages][shop]',
                        'title' => __('Shop page', 'jigoshop'),
                        'type' => 'select',
                        'value' => $this->settings['pages']['shop'],
                        'options' => $pages,
                    ),
                    array(
                        'name' => '[pages][cart]',
                        'title' => __('Cart page', 'jigoshop'),
                        'type' => 'select',
                        'value' => $this->settings['pages']['cart'],
                        'options' => $pages,
                    ),
                    array(
                        'name' => '[pages][checkout]',
                        'title' => __('Checkout page', 'jigoshop'),
                        'type' => 'select',
                        'value' => $this->settings['pages']['checkout'],
                        'options' => $pages,
                    ),
                    array(
                        'name' => '[pages][checkout_thank_you]',
                        'title' => __('Thanks page', 'jigoshop'),
                        'type' => 'select',
                        'value' => $this->settings['pages']['checkout_thank_you'],
                        'options' => $pages,
                    ),
                    array(
                        'name' => '[pages][account]',
                        'title' => __('My account page', 'jigoshop'),
                        'type' => 'select',
                        'value' => $this->settings['pages']['account'],
                        'options' => $pages,
                    ),
                    array(
                        'name' => '[pages][terms]',
                        'title' => __('Terms page', 'jigoshop'),
                        'type' => 'select',
                        'value' => $this->settings['pages']['terms'],
                        'options' => $termsPages
                    ),
                ),
            ),
        );
    }

    private function _getPages()
    {
        $pages = array();
        foreach ($this->wp->getPages() as $page) {
            $pages[$page->ID] = $page->post_title;
        }

        return $pages;
    }

    /**
     * Validate and sanitize input values.
     *
     * @param array $settings Input fields.
     *
     * @return array Sanitized and validated output.
     * @throws ValidationException When some items are not valid.
     */
    public function validate($settings)
    {
        if (isset($settings['install_emails'])) {
            unset($settings['install_emails']);
            // TODO add this to WPAL
            remove_all_actions('save_post_' . Types\Email::NAME);
            $this->di->get('jigoshop.installer')->installEmails();
            $this->messages->addNotice(__('Emails created.', 'jigoshop'));
        }

        $settings['automatic_complete'] = $settings['automatic_complete'] == 'on';
        $settings['automatic_reset'] = $settings['automatic_reset'] == 'on';
        $settings['products_list']['variations_sku_stock'] = $settings['products_list']['variations_sku_stock'] == 'on';

        if (!in_array($settings['cache'], array_keys($this->caches))) {
            $this->messages->addWarning(sprintf(__('Invalid cache mechanism: "%s". Value set to %s.', 'jigoshop'),
                $settings['cache'], $this->caches['simple']));
            $settings['cache'] = 'simple';
        }
        $settings['ignore_meta_queries'] = $settings['ignore_meta_queries'] == 'on';

        if(isset($settings['api'], $settings['api']['keys'])) {
            $settings['api']['keys'] = array_filter($settings['api']['keys'], function($item) {
                return !empty($item['key']);
            });
            $settings['api']['keys'] = array_map(function($item) {
                return array_merge(array('key' => '', 'permissions' => array()), $item);
            }, $settings['api']['keys']);
        }

        $pages = $this->_getPages();

        if (!in_array($settings['pages']['shop'], array_keys($pages))) {
            $this->messages->addError(__('Invalid shop page, please select again.', 'jigoshop'));
        } else {
            $this->options->setPageId(Pages::SHOP, $settings['pages']['shop']);
        }

        if (!in_array($settings['pages']['cart'], array_keys($pages))) {
            $this->messages->addError(__('Invalid cart page, please select again.', 'jigoshop'));
        } else {
            $this->options->setPageId(Pages::CART, $settings['pages']['cart']);
        }

        if (!in_array($settings['pages']['checkout'], array_keys($pages))) {
            $this->messages->addError(__('Invalid checkout page, please select again.', 'jigoshop'));
        } else {
            $this->options->setPageId(Pages::CHECKOUT, $settings['pages']['checkout']);
        }

        if (!in_array($settings['pages']['checkout_thank_you'], array_keys($pages))) {
            $this->messages->addError(__('Invalid thank you page, please select again.', 'jigoshop'));
        } else {
            $this->options->setPageId(Pages::THANK_YOU, $settings['pages']['checkout_thank_you']);
        }

        if (!in_array($settings['pages']['account'], array_keys($pages))) {
            $this->messages->addError(__('Invalid My account page, please select again.', 'jigoshop'));
        } else {
            $this->options->setPageId(Pages::ACCOUNT, $settings['pages']['account']);
        }
        if (!empty($settings['pages']['terms']) && $settings['pages']['terms'] != 0 && !in_array($settings['pages']['terms'],
                array_keys($pages))
        ) {
            $this->messages->addError(__('Invalid terms page, please select again.', 'jigoshop'));
        }

        return $settings;
    }
}
