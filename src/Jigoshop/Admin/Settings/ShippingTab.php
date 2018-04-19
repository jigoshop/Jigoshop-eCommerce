<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Shipping\Method;
use WPAL\Wordpress;

/**
 * Shipping tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class ShippingTab implements TabInterface
{
	const SLUG = 'shipping';

	/** @var array */
	private $settings;
	private $options;
	/** @var ShippingServiceInterface */
	private $shippingService;

	public function __construct(Wordpress $wp, Options $options, ShippingServiceInterface $shippingService)
	{
		$this->settings = $options->get(self::SLUG);;
		$this->options = $options;

		$this->shippingService = $shippingService;

        $wp->addAction('admin_enqueue_scripts', function () {
            if (isset($_GET['tab']) && $_GET['tab'] == ShippingTab::SLUG) {
            	Scripts::add('jigoshop.admin.settings.shipping_payment', \JigoshopInit::getUrl() . '/assets/js/admin/settings/shipping_payment.js', 
            	    ['jquery', 'wp-util', 'jquery-ui-sortable'], 
                    ['page' => 'jigoshop_page_jigoshop_settings']);

            	Scripts::add('jigoshop.admin.settings.shipping.advanced_flat_rate', \JigoshopInit::getUrl() . '/assets/js/admin/settings/shipping/advanced_flat_rate.js', 
            	    ['jquery', 'wp-util', 'jquery-ui-sortable'], 
                    ['page' => 'jigoshop_page_jigoshop_settings']);

            	Scripts::add('jigoshop.admin.settings.shipping.flat_rate', \JigoshopInit::getUrl() . '/assets/js/admin/settings/shipping/flat_rate.js', 
            	    ['jquery', 'wp-util', 'jquery-ui-sortable'], 
                    ['page' => 'jigoshop_page_jigoshop_settings']);            	

            	Scripts::add('jigoshop.admin.settings.shipping.free_shipping', \JigoshopInit::getUrl() . '/assets/js/admin/settings/shipping/free_shipping.js', 
            	    ['jquery', 'wp-util', 'jquery-ui-sortable'], 
                    ['page' => 'jigoshop_page_jigoshop_settings']);                	

                Scripts::add('jigoshop.magnific-popup', \JigoshopInit::getUrl() . '/assets/js/vendors/magnific_popup.js', ['jquery']);
                
                Styles::add('jquery-ui-sortable');
                Styles::add('jigoshop.magnific-popup', \JigoshopInit::getUrl() . '/assets/css/vendors/magnific_popup.css');
            }
        });

        $wp->addAction('wp_ajax_getMethodOptions', [$this, 'ajaxGetMethodOptions']);
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Shipping', 'jigoshop-ecommerce');
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
		$options = [
			[
				'title' => __('Main', 'jigoshop-ecommerce'),
				'id' => 'main',
				'fields' => [
					[
						'name' => '[enabled]',
						'title' => __('Enable shipping', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->settings['enabled'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[calculator]',
						'title' => __('Enable shipping calculator', 'jigoshop-ecommerce'),
						'description' => __('This enables calculator in cart for available shipping methods.', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->settings['calculator'],
						'classes' => ['switch-medium'],
                    ],
                ],
            ],
			[
				'title' => __('Options', 'jigoshop-ecommerce'),
				'id' => 'options',
				'fields' => [
					[
						'name' => '[only_to_billing]',
						'title' => __('Ship only to billing address?', 'jigoshop-ecommerce'),
						'description' => __('This forces customer to use billing address as shipping address.', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->settings['only_to_billing'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[always_show_shipping]',
						'title' => __('Always show shipping fields', 'jigoshop-ecommerce'),
						'description' => __('This forces shipping fields to be always visible in checkout.', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->settings['always_show_shipping'],
						'classes' => ['switch-medium'],
                    ],
                ],
            ],
            [
            	'title' => __('Shipping methods', 'jigoshop-ecommerce'),
            	'id' => 'shippingMethodsSection',
            	'display' => [$this, 'generateShippingMethods']
            ]
        ];

		return $options;
	}

	public function generateShippingMethods() {
		$methods = [];
		foreach($this->shippingService->getAvailable() as $method) {
			$methods[] = [
				'id' => $method->getId(),
				'name' => $method->getName(),
				'enabled' => $method->isEnabled(),
				'options' => $method->getOptions()
			];
		}

		return Render::get('admin/settings/shipping/methodContainer', [
				'methods' => $methods
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
		$settings['calculator'] = $settings['calculator'] == 'on';
		$settings['only_to_billing'] = $settings['only_to_billing'] == 'on';
		$settings['always_show_shipping'] = $settings['always_show_shipping'] == 'on';

		foreach ($this->shippingService->getAvailable() as $method) {
			/** @var $method Method */
			$settings[$method->getId()] = $method->validateOptions($settings[$method->getId()]);
		}

		return $settings;
	}
}
