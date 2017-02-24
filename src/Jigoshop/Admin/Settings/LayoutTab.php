<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

/**
 * Class LayoutTab
 * @package Jigoshop\Admin\Settings;
 * @author Krzysztof Kasowski
 */
class LayoutTab implements TabInterface
{
    const SLUG = 'layout';
    /** @var  array  */
    private $options;
    /** @var  Messages  */
    private $messages;

    /**
     * LayoutTab constructor.
     * @param Wordpress $wp
     * @param Options $options
     * @param Messages $messages
     */
    public function __construct(Wordpress $wp, Options $options, Messages $messages)
    {
        $wp;
        $this->options = $options->get(self::SLUG);
        $this->messages = $messages;
    }

    /**
     * @return string Title of the tab.
     */
    public function getTitle()
    {
        return __('Layout', 'jigoshop');
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
        return [
            [
                'title' => __('Main', 'jigoshop'),
                'id' => 'main',
                'fields' => [
                    [
                        'name' => '[enabled]',
                        'title' => __('Enable custom layout', 'jigoshop'),
                        'type' => 'checkbox',
                        'checked' => isset($this->options['enabled']) ? $this->options['enabled'] : false,
                        'classes' => ['switch-medium'],
                    ],
                    [
                        'name' => '[page_width]',
                        'title' => __('Page width', 'jigoshop'),
                        'type' => 'select',
                        'value' => isset($this->options['page_width']) ? $this->options['page_width'] : false,
                        'options' => [
                            '960px' => '960 px',
                            '1000px' => '1000 px',
                        ]
                    ],
                ],
            ],
            [
                'title' => __('Default', 'jigoshop'),
                'id' => 'default',
                'fields' => $this->getFields('default', false),
            ],
            [
                'title' => __('Product List', 'jigoshop'),
                'id' => Pages::PRODUCT_LIST,
                'fields' => $this->getFields(Pages::PRODUCT_LIST),
            ],
            [
                'title' => __('Cart', 'jigoshop'),
                'id' => Pages::CART,
                'fields' => $this->getFields(Pages::CART),
            ],
            [
                'title' => __('Checkout', 'jigoshop'),
                'id' => Pages::CHECKOUT,
                'fields' => $this->getFields(Pages::CHECKOUT),
            ],
            [
                'title' => __('Product', 'jigoshop'),
                'id' => Pages::PRODUCT,
                'fields' => $this->getFields(Pages::PRODUCT),
            ],
            [
                'title' => __('Product Category', 'jigoshop'),
                'id' => Pages::PRODUCT_CATEGORY,
                'fields' => $this->getFields(Pages::PRODUCT_CATEGORY),
            ],
            [
                'title' => __('Product Tag', 'jigoshop'),
                'id' => Pages::PRODUCT_TAG,
                'fields' => $this->getFields(Pages::PRODUCT_TAG),
            ],
            [
                'title' => __('Account', 'jigoshop'),
                'id' => Pages::ACCOUNT,
                'fields' => $this->getFields(Pages::ACCOUNT),
            ],
            [
                'title' => __('Thank You', 'jigoshop'),
                'id' => Pages::THANK_YOU,
                'fields' => $this->getFields(Pages::THANK_YOU),
            ],
        ];
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
     * @param bool $allowToDisable
     * @return array
     */
    private function getFields($parent, $allowToDisable = true)
    {
        if(!isset($this->options[$parent])) {
            $this->options[$parent] = [
                'enabled' => '',
                'structure' => 'only_content',
                'sidebar' => '',
                'proportions' => '',
                'custom_proportions' => [
                    'content' => '',
                    'sidebar' => '',
                ],
                'css' => ''
            ];
        }
        $fields = [];
        if($allowToDisable) {
            $fields[] = [
                'name' => '['.$parent.'][enabled]',
                'title' => __('Is enabled?', 'jigoshop'),
                'type' => 'checkbox',
                'checked' => $this->options[$parent]['enabled'],
                'classes' => ['switch-medium'],
            ];
        }
        $fields[] = [
            'name' => '['.$parent.'][structure]',
            'title' => __('Structure', 'jigoshop'),
            'type' => 'user_defined',
            'value' => $this->options[$parent]['structure'],
            'display' => function($field) {
                Render::output('admin/settings/layout/structure', [
                    'id' => $field['id'],
                    'name' => $field['name'],
                    'value' => $field['value'],
                ]);
            }
        ];
        $fields[] = [
            'name' => '['.$parent.'][sidebar]',
            'title' => __('Sidebar', 'jigoshop'),
            'type' => 'select',
            'value' => $this->options[$parent]['sidebar'],
            'options' => [
                '1' => __('Jigoshop Sidebar 1', 'jigoshop'),
                '2' => __('Jigoshop Sidebar 2', 'jigoshop'),
                '3' => __('Jigoshop Sidebar 3', 'jigoshop'),
                '4' => __('Jigoshop Sidebar 4', 'jigoshop'),
                '5' => __('Jigoshop Sidebar 5', 'jigoshop'),
                '6' => __('Jigoshop Sidebar 6', 'jigoshop'),
                '7' => __('Jigoshop Sidebar 7', 'jigoshop'),
                '8' => __('Jigoshop Sidebar 8', 'jigoshop'),
                '9' => __('Jigoshop Sidebar 9', 'jigoshop'),
            ]
        ];
        $fields[] = [
            'name' => '['.$parent.'][proportions]',
            'title' => __('Proportions', 'jigoshop'),
            'type' => 'select',
            'value' => $this->options[$parent]['proportions'],
            'options' => [
                '66-34' => '66% - 34%',
                '70-30' => '70% - 30%',
                'custom' => __('Custom', 'jigoshop'),
            ]
        ];
        $fields[] = [
            'name' => '['.$parent.'][custom_proportions]',
            'title' => __('Custom Proportions', 'jigoshop'),
            'type' => 'user_defined',
            'value' => $this->options[$parent]['custom_proportions'],
            'display' => function($field) {
                Render::output('admin/settings/layout/custom_proportions', [
                    'id' => $field['id'],
                    'name' => $field['name'],
                    'value' => $field['value'],
                ]);
            }
        ];
        $fields[] = [
            'name' => '['.$parent.'][css]',
            'title' => __('Custom CSS', 'jigoshop'),
            'type' => 'user_defined',
            'value' => $this->options[$parent]['css'],
            'display' => function($field) {
                Render::output('admin/settings/layout/css', [
                    'id' => $field['id'],
                    'name' => $field['name'],
                    'value' => $field['value'],
                ]);
            }
        ];

        return $fields;
    }
}