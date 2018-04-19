<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Product\Attributes\StockStatus;
use WPAL\Wordpress;

/**
 * Products tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class ProductsTab implements TabInterface
{
	const SLUG = 'products';

	/** @var array */
	private $options;
	/** @var Messages */
	private $messages;
	/** @var array */
	private $weightUnit;
	/** @var array */
	private $dimensionUnit;
	/** @var array */
	private $stockStatuses;

	public function __construct(Wordpress $wp, Options $options, Messages $messages)
	{
		$this->options = $options->get(self::SLUG);
		$this->messages = $messages;

		$this->weightUnit = [
			'kg' => __('Kilograms', 'jigoshop-ecommerce'),
			'lbs' => __('Pounds', 'jigoshop-ecommerce'),
        ];
		$this->dimensionUnit = [
			'cm' => __('Centimeters', 'jigoshop-ecommerce'),
			'in' => __('Inches', 'jigoshop-ecommerce'),
        ];
		$this->stockStatuses = [
			StockStatus::IN_STOCK => __('In stock', 'jigoshop-ecommerce'),
			StockStatus::OUT_STOCK => __('Out of stock', 'jigoshop-ecommerce'),
        ];
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Products', 'jigoshop-ecommerce');
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
				'title' => __('Products options', 'jigoshop-ecommerce'),
				'id' => 'products_options',
				'fields' => [
					[
						'name' => '[related]',
						'title' => __('Related products', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'description' => __("Show or hide the related products section on a single product page based on the same category.", 'jigoshop-ecommerce'),
						'checked' => $this->options['related'],
						'classes' => ['switch-medium'],
                    ],
                    [
                        'name' => '[reviews]',
                        'title' => __('Reviews', 'jigoshop-ecommerce'),
                        'type' => 'checkbox',
                        'description' => __("Show or hide the product reviews tab on a product page.", 'jigoshop-ecommerce'),
                        'checked' => $this->options['reviews'],
                        'classes' => ['switch-medium'],
                    ],
                    [
                        'name' => '[up_sells_product_limit]',
                        'title' => __('Number of up sell products to display', 'jigoshop-ecommerce'),
                        'tip' => __('Enter the number of products to limit the items displayed in Product page', 'jigoshop-ecommerce'),
                        'description' => '',
                        'type' => 'number',
                        'value' => $this->options['up_sells_product_limit'],
                    ],
                ],
            ],
			[
				'title' => __('Units', 'jigoshop-ecommerce'),
				'id' => 'units',
				'fields' => [
					[
						'name' => '[weight_unit]',
						'title' => __('Weight units', 'jigoshop-ecommerce'),
						'type' => 'select',
						'value' => $this->options['weight_unit'],
						'options' => $this->weightUnit,
                    ],
					[
						'name' => '[dimensions_unit]',
						'title' => __('Dimensions unit', 'jigoshop-ecommerce'),
						'type' => 'select',
						'value' => $this->options['dimensions_unit'],
						'options' => $this->dimensionUnit,
                    ],
                ],
            ],
			[
				'title' => __('Stock management', 'jigoshop-ecommerce'),
				'id' => 'stock_management',
				'fields' => [
					[
						'name' => '[manage_stock]',
						'title' => __('Enable for all items', 'jigoshop-ecommerce'),
						'description' => __("You can always disable management per item, it's just default value.", 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->options['manage_stock'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[stock_status]',
						'title' => __('Stock status', 'jigoshop-ecommerce'),
						'description' => __('This option allows you to change default stock status for new products.', 'jigoshop-ecommerce'),
						'type' => 'select',
						'value' => $this->options['stock_status'],
						'options' => $this->stockStatuses,
                    ],
					[
						'name' => '[show_stock]',
						'title' => __('Show stock amounts', 'jigoshop-ecommerce'),
						'description' => __('This option allows you to show available amounts on product page.', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->options['show_stock'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[low_stock_threshold]',
						'title' => __('Low stock threshold', 'jigoshop-ecommerce'),
						'type' => 'number',
						'value' => $this->options['low_stock_threshold'],
                    ],
					// TODO: Add support for hiding out of stock items
//					array(
//						'name' => '[hide_out_of_stock]',
//						'title' => __('Hide out of stock products?', 'jigoshop-ecommerce'),
//						'description' => __('This option allows you to hide products which are out of stock from lists.', 'jigoshop-ecommerce'),
//						'type' => 'checkbox',
//						'checked' => $this->options['hide_out_of_stock'],
//					),
                    [
                    	'name' => '[hide_out_of_stock_variations]',
                    	'title' => __('Hide out of stock variations', 'jigoshop-ecommerce'),
                    	'description' => __('This option allows you to hide product variations which are out of stock.', 'jigoshop-ecommerce'),
                    	'type' => 'checkbox',
                    	'classes' => ['switch-medium'],
                    	'checked' => $this->options['hide_out_of_stock_variations']
                    ]
                ],
            ],
			[
				'title' => __('Stock notifications', 'jigoshop-ecommerce'),
				'id' => 'stock_notifications',
				'fields' => [
					[
						'name' => '[notify_low_stock]',
						'title' => __('Low stock', 'jigoshop-ecommerce'),
						'description' => __('Notify when product reaches low stock', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->options['notify_low_stock'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[notify_out_of_stock]',
						'title' => __('Out of stock', 'jigoshop-ecommerce'),
						'description' => __('Notify when product becomes out of stock', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->options['notify_out_of_stock'],
						'classes' => ['switch-medium'],
                    ],
					// TODO: Backorders notifications
//					array(
//						'name' => '[notify_on_backorders]',
//						'title' => __('On backorders', 'jigoshop-ecommerce'),
////						'description' => __('Notify when product reaches backorders', 'jigoshop-ecommerce'), // TODO: How to describe this?
//						'type' => 'checkbox',
//						'checked' => $this->options['notify_on_backorders'],
//					),
                ],
            ],
			[
				'title' => __('Images', 'jigoshop-ecommerce'),
				'description' => __('Changing any of those settings will affect image sizes on your page. If you have cropping enabled you will need to regenerate thumbnails.', 'jigoshop-ecommerce'),
				'id' => 'images',
				'fields' => [
					[
						'name' => '[images][tiny][width]',
						'title' => __('Tiny image width', 'jigoshop-ecommerce'),
						'tip' => __('Used in cart for product image.', 'jigoshop-ecommerce'),
						'type' => 'number',
						'value' => $this->options['images']['tiny']['width'],
                    ],
					[
						'name' => '[images][tiny][height]',
						'title' => __('Tiny image height', 'jigoshop-ecommerce'),
						'tip' => __('Used in cart for product image.', 'jigoshop-ecommerce'),
						'type' => 'number',
						'value' => $this->options['images']['tiny']['height'],
                    ],
					[
						'name' => '[images][tiny][crop]',
						'title' => __('Crop tiny image', 'jigoshop-ecommerce'),
						'tip' => __('Leave disabled to scale images proportionally, enable to do real cropping.', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->options['images']['tiny']['crop'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[images][thumbnail][width]',
						'title' => __('Thumbnail image width', 'jigoshop-ecommerce'),
						'tip' => __('Used in single product view for other images thumbnails.', 'jigoshop-ecommerce'),
						'type' => 'number',
						'value' => $this->options['images']['thumbnail']['width'],
                    ],
					[
						'name' => '[images][thumbnail][height]',
						'title' => __('Thumbnail image height', 'jigoshop-ecommerce'),
						'tip' => __('Used in single product view for other images thumbnails.', 'jigoshop-ecommerce'),
						'type' => 'number',
						'value' => $this->options['images']['thumbnail']['height'],
                    ],
					[
						'name' => '[images][thumbnail][crop]',
						'title' => __('Crop thumbnail image', 'jigoshop-ecommerce'),
						'tip' => __('Leave disabled to scale images proportionally, enable to do real cropping.', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->options['images']['thumbnail']['crop'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[images][small][width]',
						'title' => __('Small image width', 'jigoshop-ecommerce'),
						'tip' => __('Used in catalog for product thumbnails.', 'jigoshop-ecommerce'),
						'type' => 'number',
						'value' => $this->options['images']['small']['width'],
                    ],
					[
						'name' => '[images][small][height]',
						'title' => __('Small image height', 'jigoshop-ecommerce'),
						'tip' => __('Used in catalog for product thumbnails.', 'jigoshop-ecommerce'),
						'type' => 'number',
						'value' => $this->options['images']['small']['height'],
                    ],
					[
						'name' => '[images][small][crop]',
						'title' => __('Crop small image', 'jigoshop-ecommerce'),
						'tip' => __('Leave disabled to scale images proportionally, enable to do real cropping.', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->options['images']['small']['crop'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[images][large][width]',
						'title' => __('Large image width', 'jigoshop-ecommerce'),
						'tip' => __('Used in single product view for featured image.', 'jigoshop-ecommerce'),
						'type' => 'number',
						'value' => $this->options['images']['large']['width'],
                    ],
					[
						'name' => '[images][large][height]',
						'title' => __('Large image height', 'jigoshop-ecommerce'),
						'tip' => __('Used in single product view for featured image.', 'jigoshop-ecommerce'),
						'type' => 'number',
						'value' => $this->options['images']['large']['height'],
                    ],
					[
						'name' => '[images][large][crop]',
						'title' => __('Crop large image', 'jigoshop-ecommerce'),
						'tip' => __('Leave disabled to scale images proportionally, enable to do real cropping.', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->options['images']['large']['crop'],
						'classes' => ['switch-medium'],
                    ],
                ],
            ],
            [
            	'id' => 'categoryAttributes',
            	'title' => __('Category attributes', 'jigoshop-ecommerce'),
            	'fields' => [
            		[
            			'name' => '[categoryAttributes][inheritance][defaultEnabled]',
            			'title' => __('Inheritance enabled by default', 'jigoshop-ecommerce'),
            			'type' => 'checkbox',
            			'classes' => ['switch-medium'],
            			'checked' => $this->options['categoryAttributes']['inheritance']['defaultEnabled']
            		],
            		[
            			'name' => '[categoryAttributes][inheritance][defaultMode]',
            			'title' => __('Inheritance default mode', 'jigoshop-ecommerce'),
            			'type' => 'select',
            			'options' => [
            				'all' => __('All parent categories', 'jigoshop-ecommerce'),
            				'direct' => __('Direct parent category', 'jigoshop-ecommerce')
            			],
            			'value' => $this->options['categoryAttributes']['inheritance']['defaultMode']
            		]
            	]
            ]
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
		if (!in_array($settings['weight_unit'], array_keys($this->weightUnit))) {
			$this->messages->addWarning(sprintf(__('Invalid weight unit: "%s". Value set to %s.', 'jigoshop-ecommerce'), $settings['weight_unit'], $this->weightUnit['kg']));
			$settings['weight_unit'] = 'kg';
		}
		if (!in_array($settings['dimensions_unit'], array_keys($this->dimensionUnit))) {
			$this->messages->addWarning(sprintf(__('Invalid dimensions unit: "%s". Value set to %s.', 'jigoshop-ecommerce'), $settings['dimensions_unit'], $this->dimensionUnit['cm']));
			$settings['dimensions_unit'] = 'cm';
		}
		if (!in_array($settings['stock_status'], array_keys($this->stockStatuses))) {
			$this->messages->addWarning(sprintf(__('Invalid default stock status: "%s". Value set to %s.', 'jigoshop-ecommerce'), $settings['stock_status'], $this->stockStatuses[StockStatus::IN_STOCK]));
			$settings['stock_status'] = StockStatus::IN_STOCK;
		}

		$settings['manage_stock'] = $settings['manage_stock'] == 'on';
		$settings['show_stock'] = $settings['show_stock'] == 'on';
		$settings['hide_out_of_stock_variations'] = $settings['hide_out_of_stock_variations'] == 'on';
		$settings['related'] = $settings['related'] == 'on';
		$settings['reviews'] = $settings['reviews'] == 'on';
        $settings['up_sells_product_limit'] = $settings['up_sells_product_limit'] >= 0 ? $settings['up_sells_product_limit'] : 0;

		$settings['low_stock_threshold'] = (int)$settings['low_stock_threshold'];
		if ($settings['low_stock_threshold'] < 0) {
			$this->messages->addWarning(sprintf(__('Invalid low stock threshold: "%d". Value set to 2.', 'jigoshop-ecommerce'), $settings['low_stock_threshold']));
			$settings['low_stock_threshold'] = 2;
		}

		$settings['notify_low_stock'] = $settings['notify_low_stock'] == 'on';
		$settings['notify_out_of_stock'] = $settings['notify_out_of_stock'] == 'on';
		//$settings['notify_on_backorders'] = $settings['notify_on_backorders'] == 'on';

		$settings['images']['tiny'] = [
			'width' => (int)$settings['images']['tiny']['width'],
			'height' => (int)$settings['images']['tiny']['height'],
			'crop' => $settings['images']['tiny']['crop'] == 'on',
        ];
		$settings['images']['thumbnail'] = [
			'width' => (int)$settings['images']['thumbnail']['width'],
			'height' => (int)$settings['images']['thumbnail']['height'],
			'crop' => $settings['images']['thumbnail']['crop'] == 'on',
        ];
		$settings['images']['small'] = [
			'width' => (int)$settings['images']['small']['width'],
			'height' => (int)$settings['images']['small']['height'],
			'crop' => $settings['images']['small']['crop'] == 'on',
        ];
		$settings['images']['large'] = [
			'width' => (int)$settings['images']['large']['width'],
			'height' => (int)$settings['images']['large']['height'],
			'crop' => $settings['images']['large']['crop'] == 'on',
        ];

        $settings['categoryAttributes']['inheritance']['defaultEnabled'] = $settings['categoryAttributes']['inheritance']['defaultEnabled'] == 'on';

		return $settings;
	}
}
