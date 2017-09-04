<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

/**
 * Class LayoutTab
 * @package Jigoshop\Admin\Settings;
 * @author Krzysztof Kasowski
 */
class LayoutTab implements TabInterface
{
    const SLUG = 'layout';
    /** @var  Wordpress */
    private $wp;
    /** @var  array */
    private $options;
    /** @var  Messages */
    private $messages;

    /**
     * LayoutTab constructor.
     * @param Wordpress $wp
     * @param Options $options
     * @param Messages $messages
     */
    public function __construct(Wordpress $wp, Options $options, Messages $messages)
    {
        $this->wp = $wp;
        $this->options = $options->get(self::SLUG);
        $this->messages = $messages;

        $wp->addAction('admin_enqueue_scripts', function () use ($options) {
            if (!isset($_GET['tab']) || $_GET['tab'] != LayoutTab::SLUG) {
                return;
            }
            Scripts::add('jigoshop.admin.layout', \JigoshopInit::getUrl() . '/assets/js/admin/settings/layout.js',
                ['jquery'], ['page' => 'jigoshop_page_jigoshop_settings']);
        });
    }

    /**
     * @return string Title of the tab.
     */
    public function getTitle()
    {
        return __('Layout', 'jigoshop-ecommerce');
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
        return $this->wp->applyFilters('jigoshop\admin\settings\layout\sections', [
            [
                'title' => __('Main', 'jigoshop-ecommerce'),
                'id' => 'main',
                'fields' => [
                    [
                        'name' => '[enabled]',
                        'title' => __('Enable custom layout', 'jigoshop-ecommerce'),
                        'type' => 'checkbox',
                        'checked' => $this->options['enabled'],
                        'classes' => ['switch-medium'],
                    ],
                    [
                        'name' => '[page_width]',
                        'title' => __('Page width', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->options['page_width'],
                        'options' => [
                            '960px' => '960 px',
                            '1000px' => '1000 px',
                        ]
                    ],
                    [
                        'name' => '[global_css]',
                        'title' => __('Custom global CSS', 'jigoshop-ecommerce'),
                        'type' => 'user_defined',
                        'value' => $this->options['global_css'],
                        'display' => function ($field) {
                            Render::output('admin/settings/layout/css', [
                                'id' => $field['id'],
                                'name' => $field['name'],
                                'value' => $field['value'],
                            ]);
                        }
                    ]
                ],
            ],
            [
                'title' => __('Default', 'jigoshop-ecommerce'),
                'id' => 'default',
                'fields' => $this->getFields('default'),
            ],
            [
                'title' => $this->getSectionTitle(__('Product List', 'jigoshop-ecommerce'), Pages::PRODUCT_LIST),
                'id' => Pages::PRODUCT_LIST,
                'fields' => $this->getFields(Pages::PRODUCT_LIST),
            ],
            [
                'title' => $this->getSectionTitle(__('Cart', 'jigoshop-ecommerce'), Pages::CART),
                'id' => Pages::CART,
                'fields' => $this->getFields(Pages::CART),
            ],
            [
                'title' => $this->getSectionTitle(__('Checkout', 'jigoshop-ecommerce'), Pages::CHECKOUT),
                'id' => Pages::CHECKOUT,
                'fields' => $this->getFields(Pages::CHECKOUT),
            ],
            [
                'title' => $this->getSectionTitle(__('Product', 'jigoshop-ecommerce'), Pages::PRODUCT),
                'id' => Pages::PRODUCT,
                'fields' => $this->getFields(Pages::PRODUCT),
            ],
            [
                'title' => $this->getSectionTitle(__('Product Category', 'jigoshop-ecommerce'), Pages::PRODUCT_CATEGORY),
                'id' => Pages::PRODUCT_CATEGORY,
                'fields' => $this->getFields(Pages::PRODUCT_CATEGORY),
            ],
            [
                'title' => $this->getSectionTitle(__('Product Tag', 'jigoshop-ecommerce'), Pages::PRODUCT_TAG),
                'id' => Pages::PRODUCT_TAG,
                'fields' => $this->getFields(Pages::PRODUCT_TAG),
            ],
            [
                'title' => $this->getSectionTitle(__('Account', 'jigoshop-ecommerce'), Pages::ACCOUNT),
                'id' => Pages::ACCOUNT,
                'fields' => $this->getFields(Pages::ACCOUNT),
            ],
            [
                'title' => $this->getSectionTitle(__('Thank You', 'jigoshop-ecommerce'), Pages::THANK_YOU),
                'id' => Pages::THANK_YOU,
                'fields' => $this->getFields(Pages::THANK_YOU),
            ],
        ]);
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
        $settings['enabled'] = $settings['enabled'] == 'on';
        $settings[Pages::PRODUCT_LIST]['enabled'] = $settings[Pages::PRODUCT_LIST]['enabled'] == 'on';
        $settings[Pages::CART]['enabled'] = $settings[Pages::CART]['enabled'] == 'on';
        $settings[Pages::CHECKOUT]['enabled'] = $settings[Pages::CHECKOUT]['enabled'] == 'on';
        $settings[Pages::PRODUCT]['enabled'] = $settings[Pages::PRODUCT]['enabled'] == 'on';
        $settings[Pages::PRODUCT_CATEGORY]['enabled'] = $settings[Pages::PRODUCT_CATEGORY]['enabled'] == 'on';
        $settings[Pages::PRODUCT_TAG]['enabled'] = $settings[Pages::PRODUCT_TAG]['enabled'] == 'on';
        $settings[Pages::ACCOUNT]['enabled'] = $settings[Pages::ACCOUNT]['enabled'] == 'on';
        $settings[Pages::THANK_YOU]['enabled'] = $settings[Pages::THANK_YOU]['enabled'] == 'on';

        return $settings;
    }

    /**
     * @param string $parent
     * @return array
     */
    private function getFields($parent)
    {
        return [
            [
                'name' => '[' . $parent . '][structure]',
                'title' => __('Structure', 'jigoshop-ecommerce'),
                'type' => 'user_defined',
                'value' => $this->options[$parent]['structure'],
                'display' => function ($field) {
                    Render::output('admin/settings/layout/structure', [
                        'id' => $field['id'],
                        'name' => $field['name'],
                        'value' => $field['value'],
                    ]);
                }
            ],
            [
                'name' => '[' . $parent . '][sidebar]',
                'title' => __('Sidebar', 'jigoshop-ecommerce'),
                'type' => 'select',
                'value' => $this->options[$parent]['sidebar'],
                'options' => [
                    '1' => __('Jigoshop Sidebar 1', 'jigoshop-ecommerce'),
                    '2' => __('Jigoshop Sidebar 2', 'jigoshop-ecommerce'),
                    '3' => __('Jigoshop Sidebar 3', 'jigoshop-ecommerce'),
                    '4' => __('Jigoshop Sidebar 4', 'jigoshop-ecommerce'),
                    '5' => __('Jigoshop Sidebar 5', 'jigoshop-ecommerce'),
                    '6' => __('Jigoshop Sidebar 6', 'jigoshop-ecommerce'),
                    '7' => __('Jigoshop Sidebar 7', 'jigoshop-ecommerce'),
                    '8' => __('Jigoshop Sidebar 8', 'jigoshop-ecommerce'),
                    '9' => __('Jigoshop Sidebar 9', 'jigoshop-ecommerce'),
                ]
            ],
            [
                'name' => '[' . $parent . '][proportions]',
                'title' => __('Proportions', 'jigoshop-ecommerce'),
                'type' => 'select',
                'value' => $this->options[$parent]['proportions'],
                'classes' => ['proportions'],
                'options' => [
                    '66-34' => '66% - 34%',
                    '70-30' => '70% - 30%',
                    'custom' => __('Custom', 'jigoshop-ecommerce'),
                ]
            ],
            [
                'name' => '[' . $parent . '][custom_proportions]',
                'title' => __('Custom Proportions', 'jigoshop-ecommerce'),
                'type' => 'user_defined',
                'value' => $this->options[$parent]['custom_proportions'],
                'display' => function ($field) {
                    Render::output('admin/settings/layout/custom_proportions', [
                        'id' => $field['id'],
                        'name' => $field['name'],
                        'value' => $field['value'],
                    ]);
                }
            ],
            [
                'name' => '[' . $parent . '][css]',
                'title' => __('Custom CSS', 'jigoshop-ecommerce'),
                'type' => 'user_defined',
                'value' => $this->options[$parent]['css'],
                'display' => function ($field) {
                    Render::output('admin/settings/layout/css', [
                        'id' => $field['id'],
                        'name' => $field['name'],
                        'value' => $field['value'],
                    ]);
                }
            ]
        ];
    }

    /**
     * @param $title
     * @param $id
     *
     * @return string
     */
    private function getSectionTitle($title, $id)
    {
        return sprintf('<table class="form-table"><tr><th scope="row">%s</th><td>%s</td></tr></table>', $title,
            $this->getEnabledField($id));
    }

    /**
     * @param $parent
     *
     * @return string
     */
    private function getEnabledField($parent)
    {
        ob_start();
        Forms::checkbox([
            'id' => $parent . '_enabled',
            'name' => 'jigoshop[' . $parent . '][enabled]',
            'title' => __('Is enabled?', 'jigoshop-ecommerce'),
            'description' => __('Override default settings for this page.', 'jigoshop-ecommerce'),
            'type' => 'checkbox',
            'checked' => $this->options[$parent]['enabled'],
            'classes' => ['switch-medium', 'enable_section'],
        ]);

        return ob_get_clean();
    }
}