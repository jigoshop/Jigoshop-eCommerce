<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Api\Permission;
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

        $this->caches = [
            'simple' => _x('Simple', 'cache', 'jigoshop-ecommerce'),
            'php_fast_cache' => _x('Php Fast Cache - Use this option only if your database is responding slowly. ', 'cache', 'jigoshop-ecommerce'),
        ];
        $this->sessionTypes = [
            'php' => __('Php session', 'jigoshop-ecommerce'),
            'transient' => __('Wordpress transient', 'jigoshop-ecommerce'),
        ];

        $wp->addAction('admin_enqueue_scripts', function () use ($options){
            if (!isset($_GET['tab']) || $_GET['tab'] != AdvancedTab::SLUG) {
                return;
            }
            Scripts::add('jigoshop.admin.settings.taxes', \JigoshopInit::getUrl().'/assets/js/admin/settings/advanced.js',
                ['jquery', 'wp-util'], ['page' => 'jigoshop_page_jigoshop_settings']);
        });
    }

    /**
     * @return string Title of the tab.
     */
    public function getTitle()
    {
        return __('Advanced', 'jigoshop-ecommerce');
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
        $termsPages[0] = __('None', 'jigoshop-ecommerce');

        return [
            [
                'title' => __('Cron jobs', 'jigoshop-ecommerce'),
                'id' => 'cron',
                'fields' => [
                    [
                        'name' => '[automatic_complete]',
                        'title' => __('Complete processing orders', 'jigoshop-ecommerce'),
                        'description' => __("Change all 'Processing' orders older than one month to 'Completed'",
                            'jigoshop-ecommerce'),
                        'tip' => __("For orders that have been completed but the status is still set to 'processing'.  This will move them to a 'completed' status without sending an email out to all the customers.",
                            'jigoshop-ecommerce'),
                        'type' => 'checkbox',
                        'checked' => $this->settings['automatic_complete'],
                        'classes' => ['switch-medium'],
                    ],
                    [
                        'name' => '[automatic_reset]',
                        'title' => __('Reset pending orders', 'jigoshop-ecommerce'),
                        'description' => __("Change all 'Pending' orders older than one month to 'On Hold'",
                            'jigoshop-ecommerce'),
                        'tip' => __("For customers that have not completed the Checkout process or haven't paid for an order after a period of time, this will reset the Order to On Hold allowing the Shop owner to take action.  WARNING: For the first use on an existing Shop this setting <em>can</em> generate a <strong>lot</strong> of email!",
                            'jigoshop-ecommerce'),
                        'type' => 'checkbox',
                        'checked' => $this->settings['automatic_reset'],
                        'classes' => ['switch-medium'],
                    ],
                ],
            ],
            [
                'title' => __('Integration', 'jigoshop-ecommerce'),
                'id' => 'integration',
                'fields' => [
                    // TODO: Share This integration
//					array(
//						'name' => '[integration][share_this]',
//						'title' => __('ShareThis Publisher ID', 'jigoshop-ecommerce'),
//						'description' => __("Enter your <a href='http://sharethis.com/account/'>ShareThis publisher ID</a> to show ShareThis on product pages.", 'jigoshop-ecommerce'),
//						'tip' => __('ShareThis is a small social sharing widget for posting links on popular sites such as Twitter and Facebook.', 'jigoshop-ecommerce'),
//						'type' => 'text',
//						'value' => $this->settings['integration']['share_this'],
//					),
                    [
                        'name' => '[integration][google_analytics]',
                        'title' => __('Google Analytics ID', 'jigoshop-ecommerce'),
                        'description' => __('Log into your Google Analytics account to find your ID. e.g. <code>UA-XXXXXXX-X</code>',
                            'jigoshop-ecommerce'),
                        'type' => 'text',
                        'value' => $this->settings['integration']['google_analytics'],
                    ],
                ],
            ],
            [
                'title' => __('Products list', 'jigoshop-ecommerce'),
                'id' => 'products_list',
                'fields' => [
                    [
                        'name' => '[products_list][variations_sku_stock]',
                        'title' => __('Show variation\'s SKU and stock', 'jigoshop-ecommerce'),
                        'description' => __("Show all variation's SKU and stock on admin products list page.",
                            'jigoshop-ecommerce'),
                        'type' => 'checkbox',
                        'checked' => $this->settings['products_list']['variations_sku_stock'],
                        'classes' => ['switch-medium'],
                    ],
                ],
            ],
            [
                'title' => __('Others', 'jigoshop-ecommerce'),
                'id' => 'others',
                'fields' => [
                    [
                        'name' => '[cache]',
                        'title' => __('Caching mechanism', 'jigoshop-ecommerce'),
                        'description' => __('Decides which mechanism for caching is used on the page.', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->settings['cache'],
                        'options' => $this->caches,
                    ],
                    [
                        'name' => '[session]',
                        'title' => __('Session mechanism', 'jigoshop-ecommerce'),
                        'description' => __('Decides which mechanism for session is used on the page.', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->settings['session'],
                        'options' => $this->sessionTypes,
                    ],
                    [
                        'name' => '[ignore_meta_queries]',
                        'title' => __('Ignore meta queries on product list', 'jigoshop-ecommerce'),
                        'description' => __('Ignores products\' visibility to enhance the loading time.
Warning : This will result in showing "out of stock" products on the catalog page, as well as making all products visible in the catalog and search pages.',
                            'jigoshop-ecommerce'),
                        'type' => 'checkbox',
                        'checked' => $this->settings['ignore_meta_queries'],
                        'classes' => ['switch-medium'],
                    ],
                    [
                        'name' => '[install_emails]',
                        'title' => __('Create default emails', 'jigoshop-ecommerce'),
                        'description' => __('Creates default emails for Jigoshop email system.', 'jigoshop-ecommerce'),
                        'type' => 'user_defined',
                        'display' => function () {
                            Render::output('admin/settings/create_emails', []);
                        },
                    ],
                ],
            ],
            [
                'title' => __('API', 'jigoshop-ecommerce'),
                'id' => 'api',
                'description' => __('', 'jigoshop-ecommerce'),
                'fields' => [
                    [
                        'name' => '[api][enable]',
                        'title' => __('Enable', 'jigoshop-ecommerce'),
                        'type' => 'checkbox',
                        'checked' => $this->settings['api']['enable'],
                        'classes' => ['switch-medium'],
                    ],
                    [
                        'name' => '[api][secret]',
                        'title' => __('Secret key', 'jigoshop-ecommerce'),
                        'type' => 'user_defined',
                        'value' => $this->settings['api']['secret'],
                        'description' => __('', 'jigoshop-ecommerce'),
                        'display' => function($field) {
                            Render::output('admin/settings/api_key', [
                                'name' => $field['name'],
                                'value' => $field['value'],
                                'description' => $field['description'],
                            ]);
                        }
                    ],
                    [
                        'name' => '[api][users]',
                        'title' => __('Users', 'jigoshop-ecommerce'),
                        'type' => 'user_defined',
                        'value' => $this->settings['api']['users'],
                        'description' => '',
                        'display' => function ($field) {
                            Render::output('admin/settings/api_users', [
                                'name' => $field['name'],
                                'values' => $field['value'],
                                'description' => $field['description'],
                                'availablePermissions' => Permission::getPermisions(),
                            ]);
                        }
                    ],
                ]
            ],
            [
                'title' => __('Pages', 'jigoshop-ecommerce'),
                'id' => 'pages',
                'description' => __('This section allows you to change content source page for each part of Jigoshop. It will not change the main behaviour though.',
                    'jigoshop-ecommerce'),
                'fields' => [
                    [
                        'name' => '[pages][shop]',
                        'title' => __('Shop page', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->settings['pages']['shop'],
                        'options' => $pages,
                    ],
                    [
                        'name' => '[pages][cart]',
                        'title' => __('Cart page', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->settings['pages']['cart'],
                        'options' => $pages,
                    ],
                    [
                        'name' => '[pages][checkout]',
                        'title' => __('Checkout page', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->settings['pages']['checkout'],
                        'options' => $pages,
                    ],
                    [
                        'name' => '[pages][checkout_thank_you]',
                        'title' => __('Thanks page', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->settings['pages']['checkout_thank_you'],
                        'options' => $pages,
                    ],
                    [
                        'name' => '[pages][account]',
                        'title' => __('My account page', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->settings['pages']['account'],
                        'options' => $pages,
                    ],
                    [
                        'name' => '[pages][terms]',
                        'title' => __('Terms page', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->settings['pages']['terms'],
                        'options' => $termsPages
                    ],
                ],
            ],
        ];
    }

    private function _getPages()
    {
        $pages = [];
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
        // This is required when installin emails this function is used twice,
        // once for advanced settings and once for all jigoshop settings.
        if (isset($settings['general']) && is_array($settings['general'])) {
            return $settings;
        }

        if (isset($settings['install_emails'])) {
            unset($settings['install_emails']);
            // TODO add this to WPAL
            remove_all_actions('save_post_' . Types\Email::NAME);
            $this->di->get('jigoshop.installer')->installEmails();
            $this->messages->addNotice(__('Emails created.', 'jigoshop-ecommerce'));
        }

        $settings['automatic_complete'] = $settings['automatic_complete'] == 'on';
        $settings['automatic_reset'] = $settings['automatic_reset'] == 'on';
        $settings['products_list']['variations_sku_stock'] = $settings['products_list']['variations_sku_stock'] == 'on';

        if (!in_array($settings['cache'], array_keys($this->caches))) {
            $this->messages->addWarning(sprintf(__('Invalid cache mechanism: "%s". Value set to %s.', 'jigoshop-ecommerce'),
                $settings['cache'], $this->caches['simple']));
            $settings['cache'] = 'simple';
        }
        $settings['ignore_meta_queries'] = $settings['ignore_meta_queries'] == 'on';

        $settings['api']['enable'] = $settings['api']['enable'] == 'on';

        $pages = $this->_getPages();

        if (!in_array($settings['pages']['shop'], array_keys($pages))) {
            $this->messages->addError(__('Invalid shop page, please select again.', 'jigoshop-ecommerce'));
        } else {
            $this->options->setPageId(Pages::SHOP, $settings['pages']['shop']);
        }

        if (!in_array($settings['pages']['cart'], array_keys($pages))) {
            $this->messages->addError(__('Invalid cart page, please select again.', 'jigoshop-ecommerce'));
        } else {
            $this->options->setPageId(Pages::CART, $settings['pages']['cart']);
        }

        if (!in_array($settings['pages']['checkout'], array_keys($pages))) {
            $this->messages->addError(__('Invalid checkout page, please select again.', 'jigoshop-ecommerce'));
        } else {
            $this->options->setPageId(Pages::CHECKOUT, $settings['pages']['checkout']);
        }

        if (!in_array($settings['pages']['checkout_thank_you'], array_keys($pages))) {
            $this->messages->addError(__('Invalid thank you page, please select again.', 'jigoshop-ecommerce'));
        } else {
            $this->options->setPageId(Pages::THANK_YOU, $settings['pages']['checkout_thank_you']);
        }

        if (!in_array($settings['pages']['account'], array_keys($pages))) {
            $this->messages->addError(__('Invalid My account page, please select again.', 'jigoshop-ecommerce'));
        } else {
            $this->options->setPageId(Pages::ACCOUNT, $settings['pages']['account']);
        }
        if (!empty($settings['pages']['terms']) && $settings['pages']['terms'] != 0 && !in_array($settings['pages']['terms'],
                array_keys($pages))
        ) {
            $this->messages->addError(__('Invalid terms page, please select again.', 'jigoshop-ecommerce'));
        }

        return $settings;
    }
}
