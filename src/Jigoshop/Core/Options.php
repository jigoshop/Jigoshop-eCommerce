<?php

namespace Jigoshop\Core;

use Jigoshop\Entity\Product\Attributes\StockStatus;
use Jigoshop\Frontend\Pages;
use WPAL\Wordpress;

/**
 * Options holder.
 * Use this class instead of manually calling to WordPress options database as it will cache all retrieves and updates to speed-up.
 *
 * @package Jigoshop\Core
 * @author  Amadeusz Starzykiewicz
 */
class Options
{
	const NAME = 'jigoshop';

	const IMAGE_TINY = 'shop_tiny';
	const IMAGE_THUMBNAIL = 'shop_thumbnail';
	const IMAGE_SMALL = 'shop_small';
	const IMAGE_LARGE = 'shop_large';

	/** @var Wordpress */
	private $wp;

	private $defaults = [
		'general' => [
			'country' => 'GB',
			'state' => '',
			'email' => '',
			'show_message' => false,
			'message' => 'Demo store',
			'demo_store' => false,
			'company_name' => '',
			'company_address_1' => '',
			'company_address_2' => '',
			'company_tax_number' => '',
			'company_phone' => '',
			'company_email' => '',
			'currency' => 'GBP',
			'currency_position' => '%1$s%3$s', // Currency symbol on the left without spaces
			'currency_decimals' => 2,
			'currency_thousand_separator' => ',',
			'currency_decimal_separator' => '.',
			'emails' => [
				'from' => '',
				'footer' => '',
            ],
        ],
		'shopping' => [
			'catalog_per_page' => 12,
			'catalog_order_by' => 'post_date',
			'catalog_order' => 'DESC',
			'catalog_product_button_type' => 'add_to_cart',
			'hide_out_of_stock' => false,
			'redirect_add_to_cart' => 'same_page',
			'redirect_continue_shopping' => 'product_list',
            'cross_sells_product_limit' => 3,
			'guest_purchases' => true,
			'show_login_form' => false,
			'allow_registration' => false,
			'login_for_downloads' => true,
			'unpaid_orders_number' => 5,
			'validate_zip' => true,
			'restrict_selling_locations' => false,
			'selling_locations' => [],
			'force_ssl' => false,
			'enable_verification_message' => false,
			'verification_message' => '',
        ],
		'products' => [
			'related' => false,
            'reviews' => false,
            'up_sells_product_limit' => 3,
			'weight_unit' => 'kg',
			'dimensions_unit' => 'cm',
			'manage_stock' => false,
			'stock_status' => StockStatus::IN_STOCK,
			'show_stock' => true,
			'low_stock_threshold' => 2,
			'hide_out_of_stock_variations' => false,
			'notify_low_stock' => false,
			'notify_out_of_stock' => true,
			'notify_on_backorders' => false,
			'images' => [
				'tiny' => [
					'width' => 36,
					'height' => 36,
					'crop' => false,
                ],
				'thumbnail' => [
					'width' => 90,
					'height' => 90,
					'crop' => false,
                ],
				'small' => [
					'width' => 150,
					'height' => 150,
					'crop' => false,
                ],
				'large' => [
					'width' => 300,
					'height' => 300,
					'crop' => false,
                ],
            ],
            'categoryAttributes' => [
            	'inheritance' => [
            		'defaultEnabled' => false,
            		'defaultMode' => 'all'
            	]
            ]
        ],
		'tax' => [
            'shipping' => false,
            'prices_entered' => 'without_tax',
            'item_prices' => 'excluding_tax',
            'product_prices' => 'excluding_tax',
            'show_suffix' => 'in_cart_totals',
            'suffix_for_included' => 'inc. Tax',
            'suffix_for_excluded' => '',
            'price_tax' => 'without_tax',
			'classes' => [
				['label' => 'Standard', 'class' => 'standard'],
            ],
			'defaults' => [
				'taxable' => true,
				'classes' => ['standard'],
            ],
            'euVat' => [
            	'enabled' => false,
            	'fieldDescription' => '',
            	'removeVatIfCustomerIsLocatedInShopCountry' => false,
            	'failedValidationHandling' => 'reject',
            	'forceB2BTransactions' => false
            ]
        ],
		'shipping' => [
			'enabled' => true,
			'calculator' => true,
			'only_to_billing' => false,
			'always_show_shipping' => false,
			'flat_rate' => [
				'enabled' => true,
				'title' => 'Flat rate',
				'type' => 'per_order',
				'is_taxable' => true,
				'cost' => 0,
				'fee' => 0,
				'available_for' => 'all',
				'countries' => [],
				'adminOnly' => false
            ],
			'free_shipping' => [
				'enabled' => false,
				'title' => 'Free',
				'minimum' => 0,
				'available_for' => 'all',
				'countries' => [],
				'adminOnly' => false
            ],
			'local_pickup' => [
				'enabled' => false,
				'title' => 'Local pickup',
				'fee' => 0,
				'adminOnly' => false
            ],
            'advanced_flat_rate' => [
                'enabled' => false,
                'title' => '',
                'taxable' => false,
                'fee' => 0,
                'available_for' => 'all',
                'countries' => [],
                'multiple_rates' => false,
                'rates' => [],
                'rates_order' => [],
                'adminOnly' => false
            ],
        ],
		'payment' => [
			'default_gateway' => 'no_default_gateway',
			'cheque' => [
				'enabled' => false,
				'title' => 'Cheque',
				'description' => 'Pay with cheque sent to shop prior to dispatching your order.',
            ],
			'on_delivery' => [
				'enabled' => false,
				'title' => 'On delivery',
				'description' => 'Pay when your order arrives at your doorstep!',
            ],
			'paypal' => [
				'enabled' => false,
				'title' => 'PayPal',
				'description' => "Pay via PayPal; you can pay with your credit card if you don't have a PayPal account.",
				'email' => '',
				'send_shipping' => true,
				'force_payment' => false,
				'test_mode' => true,
				'test_email' => '',
            ],
			'worldpay' => [
				'enabled' => false,
				'title' => 'WorldPay',
				'description' => "Pay via Worldpay.",
				'admin_only' => false,
				'client_key' => '',
				'service_key' => '',
				'test_mode' => true,
				'test_service_key' => '',
			],
            'bank_transfer' => [
                'enabled' => false,
                'title' => 'Bank Transfer',
                'description' => 'Please use the details below to transfer the payment for your order, once payment is received your order will be processed.',
                'bank_name' => '',
                'account_number' => '',
                'account_holder' => '',
                'sort_code' => '',
                'iban' => '',
                'bic' => '',
                'additional_info' => '',
            ],
        ],
        'layout' => [
            'enabled' => false,
            'page_width' => '960px',
            'global_css' => '',
            'default' => [
                'structure' => 'only_content',
                'sidebar' => '',
                'proportions' => '',
                'custom_proportions' => [
                    'content' => '',
                    'sidebar' => '',
                ],
                'css' => ''
            ],
            Pages::PRODUCT_LIST => [
                'enabled' => false,
                'structure' => 'only_content',
                'sidebar' => '',
                'proportions' => '',
                'custom_proportions' => [
                    'content' => '',
                    'sidebar' => '',
                ],
                'css' => ''
            ],
            Pages::CART => [
                'enabled' => false,
                'structure' => 'only_content',
                'sidebar' => '',
                'proportions' => '',
                'custom_proportions' => [
                    'content' => '',
                    'sidebar' => '',
                ],
                'css' => ''
            ],
            Pages::CHECKOUT => [
                'enabled' => false,
                'structure' => 'only_content',
                'sidebar' => '',
                'proportions' => '',
                'custom_proportions' => [
                    'content' => '',
                    'sidebar' => '',
                ],
                'css' => ''
            ],
            Pages::PRODUCT => [
                'enabled' => false,
                'structure' => 'only_content',
                'sidebar' => '',
                'proportions' => '',
                'custom_proportions' => [
                    'content' => '',
                    'sidebar' => '',
                ],
                'css' => ''
            ],
            Pages::PRODUCT_CATEGORY => [
                'enabled' => false,
                'structure' => 'only_content',
                'sidebar' => '',
                'proportions' => '',
                'custom_proportions' => [
                    'content' => '',
                    'sidebar' => '',
                ],
                'css' => ''
            ],
            Pages::PRODUCT_TAG => [
                'enabled' => false,
                'structure' => 'only_content',
                'sidebar' => '',
                'proportions' => '',
                'custom_proportions' => [
                    'content' => '',
                    'sidebar' => '',
                ],
                'css' => ''
            ],
            Pages::ACCOUNT => [
                'enabled' => false,
                'structure' => 'only_content',
                'sidebar' => '',
                'proportions' => '',
                'custom_proportions' => [
                    'content' => '',
                    'sidebar' => '',
                ],
                'css' => ''
            ],
            Pages::THANK_YOU => [
                'enabled' => false,
                'structure' => 'only_content',
                'sidebar' => '',
                'proportions' => '',
                'custom_proportions' => [
                    'content' => '',
                    'sidebar' => '',
                ],
                'css' => ''
            ],
        ],
		'advanced' => [
			'automatic_complete' => false,
			'automatic_reset' => false,
			'integration' => [
				'share_this' => '',
				'google_analytics' => '',
            ],
			'products_list' => [
				'variations_sku_stock' => false,
            ],
			'cache' => 'simple',
            'session' => 'php',
            'ignore_meta_queries' => false,
			'api' => [
			    'enable' => false,
			    'secret' => '',
			    'users' => []
            ],
			'pages' => [
				'shop' => 0,
				'cart' => 0,
				'checkout' => 0,
				'checkout_thank_you' => 0,
				'account' => 0,
				'terms' => 0,
            ],
        ],
		'permalinks' => [
			'product' => '',
			'category' => 'product-category',
			'tag' => 'product-tag',
			'verbose' => false,
            'with_front' => true,
        ],
    ];
	private $options = [];
	private $dirty = false;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
		$this->_loadOptions();
		$this->_addImageSizes();
	}

	/**
	 * Loads stored options and merges them with default ones.
	 */
	private function _loadOptions()
	{
		$options = (array)$this->wp->getOption(self::NAME);
		foreach ($this->defaults as $key => $value) {
			$options[$key] = array_replace_recursive($value, isset($options[$key]) ? $options[$key] : []);
		}
		$this->options = array_merge($this->defaults, $options);
	}

	private function _addImageSizes()
	{
		$sizes = $this->getImageSizes();

		foreach ($sizes as $size => $options) {
			$this->wp->addImageSize($size, $options['width'], $options['height'], $options['crop']);
		}

		$this->wp->addImageSize('admin_product_list', 70, 70, true);
	}

	public function getImageSizes()
	{
		$sizes = $this->get('products.images');

		return $this->wp->applyFilters('jigoshop\image\sizes', [
			self::IMAGE_TINY => [
				'crop' => $this->wp->applyFilters('jigoshop\image\size\crop', $sizes['tiny']['crop'], self::IMAGE_TINY),
				'width' => $sizes['tiny']['width'],
				'height' => $sizes['tiny']['height'],
            ],
			self::IMAGE_THUMBNAIL => [
				'crop' => $this->wp->applyFilters('jigoshop\image\size\crop', $sizes['thumbnail']['crop'], self::IMAGE_THUMBNAIL),
				'width' => $sizes['thumbnail']['width'],
				'height' => $sizes['thumbnail']['height'],
            ],
			self::IMAGE_SMALL => [
				'crop' => $this->wp->applyFilters('jigoshop\image\size\crop', $sizes['small']['crop'], self::IMAGE_SMALL),
				'width' => $sizes['small']['width'],
				'height' => $sizes['small']['height'],
            ],
			self::IMAGE_LARGE => [
				'crop' => $this->wp->applyFilters('jigoshop\image\size\crop', $sizes['large']['crop'], self::IMAGE_LARGE),
				'width' => $sizes['large']['width'],
				'height' => $sizes['large']['height'],
            ],
        ]);
	}

	/**
	 * @param $name    string Name of option to retrieve.
	 * @param $default mixed Default value (if not found).
	 *
	 * @return mixed Result.
	 */
	public function get($name, $default = null)
	{
		return $this->_get(explode('.', $name), $this->options, $default);
	}

	private function _get(array $names, array $options, $default = null)
	{
		$name = array_shift($names);

		if (!isset($options[$name])) {
			return $default;
		}

		if (empty($names)) {
			return $options[$name];
		}

		return $this->_get($names, $options[$name], $default);
	}

	/**
	 * @return array All available options.
	 */
	public function getAll()
	{
		return $this->options;
	}

	/**
	 * @return array All default options.
	 */
	public function getDefaults()
	{
		return $this->defaults;
	}

	/**
	 * @param $name  string Name of option to update.
	 * @param $value mixed Value to set.
	 */
	public function update($name, $value)
	{
		$this->_update(explode('.', $name), $this->options, $value);
		$this->dirty = true;
	}

	private function _update(array $names, array &$options, $value)
	{
		$name = array_shift($names);

		if (empty($names)) {
			$options[$name] = $value;

			return;
		}

		if (!isset($options[$name])) {
			$options[$name] = [];
		}

		$this->_update($names, $options[$name], $value);
	}

	/**
	 * @param $name string Name of option to remove.
	 *
	 * @return bool Whether value was removed.
	 */
	public function remove($name)
	{
		if (!isset($this->options[$name])) {
			return false;
		}

		unset($this->options[$name]);
		$this->dirty = true;

		return true;
	}

	/**
	 * @param $name string Name of option to check.
	 *
	 * @return bool Whether selected option exists.
	 */
	public function exists($name)
	{
		return isset($this->options[$name]);
	}

	/**
	 * Saves current option values (if needed).
	 */
	public function saveOptions()
	{
		if ($this->dirty) {
			$this->wp->updateOption(self::NAME, $this->options);
		}
	}

	/**
	 * Retrieves id of specified Jigoshop page.
	 *
	 * @param $page string Page slug.
	 *
	 * @return mixed Page ID.
	 */
	public function getPageId($page)
	{
		return $this->wp->getOption('jigoshop_'.$page.'_id');
	}

	/**
	 * Sets id of specified Jigoshop page.
	 *
	 * @param $page string Page slug.
	 * @param $id   int Page ID.
	 */
	public function setPageId($page, $id)
	{
		$this->wp->updateOption('jigoshop_'.$page.'_id', $id);
	}

	/**
	 * @return array List of names of enabled product types.
	 */
	public function getEnabledProductTypes()
	{
		// TODO: Add product types to extensions tab
		return $this->wp->applyFilters('jigoshop\product\types', $this->get('enabled_product_types', [
			'jigoshop.product_type.simple',
			'jigoshop.product_type.virtual',
			'jigoshop.product_type.variable',
			'jigoshop.product_type.external',
			'jigoshop.product_type.downloadable',
        ]));
	}
}
