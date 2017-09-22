<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Options;
use Jigoshop\Core\Messages;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Service\TaxServiceInterface;
use WPAL\Wordpress;

/**
 * Taxes tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class TaxesTab implements TabInterface
{
	const SLUG = 'tax';

	/** @var array */
	private $options;
	/** @var TaxServiceInterface */
	private $taxService;

	public function __construct(Wordpress $wp, Options $options, TaxServiceInterface $taxService, Messages $messages)
	{
		$this->options = $options->get(self::SLUG);
		$this->taxService = $taxService;
		$options = $this->options;

		$wp->addAction('admin_enqueue_scripts', function () use ($options){
			if (!isset($_GET['tab']) || $_GET['tab'] != TaxesTab::SLUG) {
				return;
			}

			$classes = [];
			foreach ($options['classes'] as $class) {
				$classes[$class['class']] = $class['label'];
			}

			$states = [];
			foreach (Country::getAllStates() as $country => $stateList) {
				$states[$country] = [
					['id' => '', 'text' => _x('All states', 'admin_taxing', 'jigoshop')],
                ];
				foreach ($stateList as $code => $state) {
					$states[$country][] = ['id' => $code, 'text' => $state];
				}
			}

			$countries = array_merge(
				['' => __('All countries', 'jigoshop-ecommerce')],
				Country::getAll()
			);

			Scripts::add('jigoshop.admin.settings.taxes', \JigoshopInit::getUrl().'/assets/js/admin/settings/taxes.js', [
				'jquery',
            ], ['page' => 'jigoshop_page_jigoshop_settings']);
			Scripts::localize('jigoshop.admin.settings.taxes', 'jigoshop_admin_taxes', [
				'new_class' => Render::get('admin/settings/tax/class', ['class' => ['label' => '', 'class' => '']]),
				'new_rule' => Render::get('admin/settings/tax/rule', [
					'rule' => ['id' => '', 'label' => '', 'class' => '', 'is_compound' => false, 'rate' => '', 'country' => '', 'states' => [], 'postcodes' => []],
					'classes' => $classes,
					'countries' => $countries,
                ]),
				'states' => $states,
            ]);
		});
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Taxes', 'jigoshop-ecommerce');
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
		$classes = [];
		foreach ($this->options['classes'] as $class) {
			$classes[$class['class']] = $class['label'];
		}

		return [
			[
				'title' => __('Main', 'jigoshop-ecommerce'),
				'id' => 'main',
				'fields' => [
//                    [
//                        'id' => 'default_country',
//                        'name' => '[default_country]',
//                        'title' => __('Default country', 'jigoshop-ecommerce'),
//                        'type' => 'select',
//                        'value' => $this->options['country'],
//                        'options' => Country::getAll(),
//                    ],
//                    [
//                        'id' => 'default_state',
//                        'name' => '[default_state]',
//                        'title' => __('Default state', 'jigoshop-ecommerce'),
//                        'type' => 'text',
//                        'value' => $this->options['state'],
//                    ],
//                    [
//                        'id' => 'default_postcode',
//                        'name' => '[default_postcode]',
//                        'title' => __('Default postcode', 'jigoshop-ecommerce'),
//                        'type' => 'text',
//                        'value' => $this->options['postcode'],
//                    ],
					[
						'name' => '[shipping]',
						'title' => __('Taxes based on shipping country?', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'description' => __('By default, taxes based on billing country.', 'jigoshop-ecommerce'),
						'checked' => $this->options['shipping'],
						'classes' => ['switch-medium'],
					],
				],
			],
            [
                'title' => __('Prices', 'jigoshop-ecommerce'),
                'id' => 'prices',
                'fields' => [
                    [
                        'name' => '[prices_entered]',
                        'title' => __('Entered prices tax status', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->options['prices_entered'],
                        'options' => [
                            'without_tax' => __('I will enter prices without tax', 'jigoshop-ecommerce'),
                            'with_tax' => __('I will enter prices with tax included', 'jigoshop-ecommerce'),
                        ]
                    ],
                    [
                        'name' => '[item_prices]',
                        'title' => __('Show prices in cart and checkout', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->options['item_prices'],
                        'options' => [
                            'including_tax' => __('Including tax', 'jigoshop-ecommerce'),
                            'excluding_tax' => __('Excluding tax', 'jigoshop-ecommerce'),
                            'both_including_first' => __('Both (including tax first)', 'jigoshop-ecommerce'),
                            'both_excluding_first' => __('Both (excluding tax first)', 'jigoshop-ecommerce')
                        ]
                    ],
                    [
                        'name' => '[product_prices]',
                        'title' => __('Show product prices', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->options['product_prices'],
                        'options' => [
                            'including_tax' => __('Including tax', 'jigoshop-ecommerce'),
                            'excluding_tax' => __('Excluding tax', 'jigoshop-ecommerce'),
                            'both_including_first' => __('Both (including tax first)', 'jigoshop-ecommerce'),
                            'both_excluding_first' => __('Both (excluding tax first)', 'jigoshop-ecommerce')
                        ]
                    ],
                    [
                        'name' => '[show_suffix]',
                        'title' => __('Show tax suffix', 'jigoshop-ecommerce'),
                        'type' => 'select',
                        'value' => $this->options['show_suffix'],
                        'options' => [
                            'in_cart_totals' => __('In cart totals', 'jigoshop-ecommerce'),
                            'everywhere' => __('Everywhere', 'jigoshop-ecommerce'),
                        ]
                    ],
                    [
                        'name' => '[suffix_for_included]',
                        'title' => __('Suffix for prices with tax included', 'jigoshop-ecommerce'),
                        'type' => 'text',
                        'value' => $this->options['suffix_for_included'],
                    ],
                    [
                        'name' => '[suffix_for_excluded]',
                        'title' => __('Suffix for prices without tax', 'jigoshop-ecommerce'),
                        'type' => 'text',
                        'value' => $this->options['suffix_for_excluded'],
                    ],
                ]
            ],
			[
				'title' => __('Classes', 'jigoshop-ecommerce'),
				'id' => 'classes',
				'fields' => [
					[
						'title' => '',
						'name' => '[classes]',
						'type' => 'user_defined',
						'display' => [$this, 'displayClasses'],
                    ],
                ],
            ],
			[
				'title' => __('Rules', 'jigoshop-ecommerce'),
				'id' => 'rules',
				'fields' => [
					[
						'title' => '',
						'name' => '[rules]',
						'type' => 'user_defined',
						'display' => [$this, 'displayRules'],
                    ],
                ],
            ],
			[
				'title' => __('New products', 'jigoshop-ecommerce'),
				'description' => __('This section defines default tax settings for new products.', 'jigoshop-ecommerce'),
				'id' => 'defaults',
				'fields' => [
					[
						'title' => __('Is taxable?', 'jigoshop-ecommerce'),
						'name' => '[defaults][taxable]',
						'type' => 'checkbox',
						'checked' => $this->options['defaults']['taxable'],
						'classes' => ['switch-medium'],
                    ],
					[
						'title' => __('Tax classes', 'jigoshop-ecommerce'),
						'name' => '[defaults][classes]',
						'type' => 'select',
						'multiple' => true,
						'options' => $classes,
						'value' => $this->options['defaults']['classes'],
                    ],
                ],
            ],
        ];
	}

	public function displayClasses()
	{
		Render::output('admin/settings/tax/classes', [
			'classes' => $this->options['classes'],
        ]);
	}

	public function displayRules()
	{
		$classes = [];
		foreach ($this->options['classes'] as $class) {
			$classes[$class['class']] = $class['label'];
		}
		$countries = array_merge(
			['' => __('All countries', 'jigoshop-ecommerce')],
			Country::getAll()
		);
		Render::output('admin/settings/tax/rules', [
			'rules' => $this->taxService->getRules(),
			'classes' => $classes,
			'countries' => $countries,
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
		$settings['shipping'] = $settings['shipping'] == 'on';
		$classes = isset($settings['classes']) ? $settings['classes'] : [];
		$settings['classes'] = [];
		foreach ($classes['class'] as $key => $class) {
			$settings['classes'][] = [
				'class' => $class,
				'label' => $classes['label'][$key],
            ];
		}

		$settings['defaults']['taxable'] = $settings['defaults']['taxable'] == 'on';
		if(isset($settings['defaults']['classes'])) {
            $settings['defaults']['classes'] = array_filter($settings['defaults']['classes'],
                function ($class) use ($classes) {
                    return in_array($class, $classes['class']);
                });
        } else {
            $settings['defaults']['classes'] = [];
        }

		if (!isset($settings['rules'])) {
			$settings['rules'] = ['id' => []];
		}

		$this->taxService->removeAllExcept($settings['rules']['id']);

		$currentKey = 0;
		foreach ($settings['rules']['id'] as $key => $id) {
			if (empty($id) && $settings['rules']['compound'][$key + 1] == 'on') {
				$currentKey++;
			}

			$this->taxService->save([
				'id' => $id,
				'rate' => $settings['rules']['rate'][$key],
				'is_compound' => $settings['rules']['compound'][$key + $currentKey] == 'on',
				'label' => $settings['rules']['label'][$key],
				'class' => $settings['rules']['class'][$key],
				'country' => $settings['rules']['country'][$key],
				'states' => $settings['rules']['states'][$key],
				'postcodes' => $settings['rules']['postcodes'][$key],
            ]);
		}
		unset($settings['rules']);

		//if (!in_array($settings['price_tax'], array('with_tax', 'without_tax'))) {
		//	$this->messages->addWarning(sprintf(__('Invalid prices option: "%s". Value set to %s.', 'jigoshop'), $settings['price_tax'], __('Without tax', 'jigoshop-ecommerce')));
		//	$settings['price_tax'] = 'without_tax';
		//}

		return $settings;
	}
}
