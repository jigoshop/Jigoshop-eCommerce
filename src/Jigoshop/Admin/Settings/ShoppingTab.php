<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

/**
 * Shopping tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class ShoppingTab implements TabInterface
{
	const SLUG = 'shopping';

	/** @var array */
	private $options;
	/** @var Messages */
	private $messages;
	/** @var array */
	private $addToCartRedirectionOptions;
	/** @var array */
	private $backToShopRedirectionOptions;
	/** @var array */
	private $catalogOrderBy;
	/** @var array */
	private $catalogOrder;
	/** @var  array */
	private $productButtonType;

	public function __construct(Wordpress $wp, Options $options, Messages $messages)
	{
		$this->options = $options->get(self::SLUG);
		$this->messages = $messages;

		$this->addToCartRedirectionOptions = $wp->applyFilters('jigoshop\admin\settings\shopping\add_to_cart_redirect', [
			'same_page' => __('Stay on the same page', 'jigoshop'),
			'product' => __('Redirect to product page', 'jigoshop'),
			'cart' => __('Redirect to cart', 'jigoshop'),
			'checkout' => __('Redirect to checkout', 'jigoshop'),
			'product_list' => __('Redirect to product list', 'jigoshop'),
        ]);
		$this->backToShopRedirectionOptions = $wp->applyFilters('jigoshop\admin\settings\shopping\continue_shopping_redirect', [
			'product_list' => __('Product list', 'jigoshop'),
			'my_account' => __('My account', 'jigoshop'),
        ]);
		$this->catalogOrderBy = $wp->applyFilters('jigoshop\admin\settings\shopping\catalog_order_by', [
			'post_date' => __('Date', 'jigoshop'),
			'post_title' => __('Product name', 'jigoshop'),
			'menu_order' => __('Product post order', 'jigoshop'),
        ]);
		$this->catalogOrder = $wp->applyFilters('jigoshop\admin\settings\shopping\catalog_order', [
			'ASC' => __('Ascending', 'jigoshop'),
			'DESC' => __('Descending', 'jigoshop'),
        ]);
		$this->productButtonType = $wp->applyFilters('jigoshop\admin\settings\shopping\catalog_product_button_type', [
			'add_to_cart' => __('Add to cart', 'jigoshop'),
			'view_product' => __('View Product', 'jigoshop'),
			'no_button' => __('No button', 'jigoshop'),
        ]);

		$wp->addAction('admin_enqueue_scripts', function (){
			if (isset($_GET['tab']) && $_GET['tab'] == ShoppingTab::SLUG) {
				Scripts::add('jigoshop.admin.settings.shopping', \JigoshopInit::getUrl().'/assets/js/admin/settings/shopping.js', ['jquery'], ['page' => 'jigoshop_page_jigoshop_settings']);
			}
		});
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Shopping', 'jigoshop');
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
				'title' => __('Catalog', 'jigoshop'),
				'id' => 'catalog',
				'fields' => [
					[
						'name' => '[catalog_per_page]',
						'title' => __('Items per page', 'jigoshop'),
						'type' => 'number',
						'value' => $this->options['catalog_per_page'],
                    ],
					[
						'name' => '[catalog_order_by]',
						'title' => __('Order by', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['catalog_order_by'],
						'options' => $this->catalogOrderBy,
                    ],
					[
						'name' => '[catalog_order]',
						'title' => __('Ordering', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['catalog_order'],
						'options' => $this->catalogOrder,
                    ],
					[
						'name' => '[catalog_product_button_type]',
						'title' => __('Product button type', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['catalog_product_button_type'],
						'options' => $this->productButtonType,
                    ],
					[
						'name' => '[hide_out_of_stock]',
						'title' => __('Hide out of stock items', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['hide_out_of_stock'],
						'classes' => ['switch-medium'],
                    ],
                ],
            ],
			[
				'title' => __('Cart', 'jigoshop'),
				'id' => 'redirection',
				'fields' => [
					[
						'name' => '[redirect_add_to_cart]',
						'title' => __('After adding to cart', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['redirect_add_to_cart'],
						'options' => $this->addToCartRedirectionOptions,
                    ],
					[
						'name' => '[redirect_continue_shopping]',
						'title' => __('Coming back to shop', 'jigoshop'),
						'description' => __("This will point users to the page you set for buttons like 'Return to shop' or 'Continue Shopping'.", 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['redirect_continue_shopping'],
						'options' => $this->backToShopRedirectionOptions,
                    ],
                    [
                        'name' => '[cross_sells_product_limit]',
                        'title' => __('Number of cross sell products to display', 'jigoshop'),
                        'tip' => __('Enter the number of products to limit the items displayed in Cart page',
                            'jigoshop'),
                        'description' => '',
                        'type' => 'number',
                        'value' => $this->options['cross_sells_product_limit'],
                    ],
                ],
            ],
			[
				'title' => __('Checkout', 'jigoshop'),
				'id' => 'checkout',
				'description' => __('This section allows you to modify checkout requirements for being signed in.', 'jigoshop'),
				'fields' => [
					[
						'name' => '[validate_zip]',
						'title' => __('Validate postcode', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['validate_zip'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[restrict_selling_locations]',
						'id' => 'restrict_selling_locations',
						'title' => __('Restrict selling locations?', 'jigoshop'),
						'description' => __('This option allows you to select what countries you want to sell to.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['restrict_selling_locations'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[selling_locations]',
						'id' => 'selling_locations',
						'title' => __('Selling locations', 'jigoshop'),
						'type' => 'select',
						'multiple' => true,
						'value' => $this->options['selling_locations'],
						'options' => Country::getAll(),
						'classes' => [$this->options['restrict_selling_locations'] ? '' : 'not-active'],
                    ],
					[
						'name' => '[enable_verification_message]',
						'id' => 'enable_verification_message',
						'title' => __('Enable verification message', 'jigoshop'),
						'tip' => __('Enabling this setting will display a message at the bottom of the Checkout asking customers to verify all their informatioin is correctly entered before placing their Order.  This is useful in particular for Countries that have states to ensure the correct shipping state is selected.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['enable_verification_message'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[verification_message]',
						'id' => 'verification_message',
						'title' => __('Verification message', 'jigoshop'),
						'description' => __('For example: "Please verify that all your information is correctly entered before placing your Order".'),
						'type' => 'textarea',
						'value' => $this->options['verification_message'],
						'classes' => [$this->options['enable_verification_message'] ? '' : 'not-active'],
                    ],
					[
						'name' => '[guest_purchases]',
						'title' => __('Allow guest purchases', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['guest_purchases'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[show_login_form]',
						'title' => __('Show login form', 'jigoshop'),
						'description' => __('Add login form on checkout page.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['show_login_form'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[allow_registration]',
						'title' => __('Allow registration', 'jigoshop'),
						'description' => __('Add registration form on checkout page.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['allow_registration'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[login_for_downloads]',
						'title' => __('Require login for downloads', 'jigoshop'),
						'description' => __('Forces user to log in before they could download files.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['login_for_downloads'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[unpaid_orders_number]',
						'title' => __('Number of unpaid orders in My Account', 'jigoshop'),
						'type' => 'number',
						'value' => $this->options['unpaid_orders_number'],
                    ],
					[
						'name'        => '[force_ssl]',
						'title'       => __('Force SSL on checkout', 'jigoshop'),
						'description' => __('Enforces WordPress to use SSL on checkout pages.', 'jigoshop'),
						'type'        => 'checkbox',
						'checked'     => $this->options['force_ssl'],
						'classes'     => ['switch-medium'],
                    ],
                ],
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
		$settings['catalog_per_page'] = (int)$settings['catalog_per_page'];
		if ($settings['catalog_per_page'] <= 0) {
			$this->messages->addWarning(sprintf(__('Invalid products per page value: "%d". Value set to 12.', 'jigoshop'), $settings['catalog_per_page']));
			$settings['catalog_per_page'] = 12;
		}
		if (!in_array($settings['catalog_order_by'], array_keys($this->catalogOrderBy))) {
			$this->messages->addWarning(sprintf(__('Invalid products sorting: "%s". Value set to %s.', 'jigoshop'), $settings['catalog_order_by'], $this->catalogOrderBy['post_date']));
			$settings['catalog_order_by'] = 'post_date';
		}
		if (!in_array($settings['catalog_order'], array_keys($this->catalogOrder))) {
			$this->messages->addWarning(sprintf(__('Invalid products sorting orientation: "%s". Value set to %s.', 'jigoshop'), $settings['catalog_order'], $this->catalogOrder['DESC']));
			$settings['catalog_order'] = 'DESC';
		}

		$settings['hide_out_of_stock'] = $settings['hide_out_of_stock'] == 'on';
		$settings['enable_verification_message'] = $settings['enable_verification_message'] == 'on';
		$settings['guest_purchases'] = $settings['guest_purchases'] == 'on';
		$settings['show_login_form'] = $settings['show_login_form'] == 'on';
		$settings['allow_registration'] = $settings['allow_registration'] == 'on';
		$settings['login_for_downloads'] = $settings['login_for_downloads'] == 'on';
		$settings['force_ssl'] = $settings['force_ssl'] == 'on';

		$settings['validate_zip'] = $settings['validate_zip'] == 'on';
		$settings['restrict_selling_locations'] = $settings['restrict_selling_locations'] == 'on';

		if (!$settings['restrict_selling_locations']) {
			$settings['selling_locations'] = [];
		} else {
			$settings['selling_locations'] = array_intersect($settings['selling_locations'], array_keys(Country::getAll()));
		}
		if (!in_array($settings['redirect_add_to_cart'], array_keys($this->addToCartRedirectionOptions))) {
			$this->messages->addWarning(sprintf(__('Invalid add to cart redirection: "%s". Value set to %s.', 'jigoshop'), $settings['redirect_add_to_cart'], $this->addToCartRedirectionOptions['same_page']));
			$settings['redirect_add_to_cart'] = 'same_page';
		}
		if (!in_array($settings['redirect_continue_shopping'], array_keys($this->backToShopRedirectionOptions))) {
			$this->messages->addWarning(sprintf(__('Invalid continue shopping redirection: "%s". Value set to %s.', 'jigoshop'), $settings['redirect_continue_shopping'], $this->backToShopRedirectionOptions['product_list']));
			$settings['redirect_continue_shopping'] = 'product_list';
		}
        $settings['cross_sells_product_limit'] = $settings['cross_sells_product_limit'] >= 0 ? $settings['cross_sells_product_limit'] : 0;

		return $settings;
	}
}
