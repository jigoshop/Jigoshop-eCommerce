<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Options;
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
	private $options;
	/** @var ShippingServiceInterface */
	private $shippingService;

	public function __construct(Wordpress $wp, Options $options, ShippingServiceInterface $shippingService)
	{
		$this->options = $options->get(self::SLUG);
		$this->shippingService = $shippingService;

        $wp->addAction('admin_enqueue_scripts', function (){
            if (isset($_GET['tab']) && $_GET['tab'] == ShippingTab::SLUG) {
                Scripts::add('jigoshop.admin.settings.shopping', \JigoshopInit::getUrl().'/assets/js/admin/settings/shipping.js',
                    ['jquery', 'wp-util', 'jquery-ui-sortable'],
                    ['page' => 'jigoshop_page_jigoshop_settings']);
                Styles::add('jquery-ui-sortable');
            }
        });
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Shipping', 'jigoshop');
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
				'title' => __('Main', 'jigoshop'),
				'id' => 'main',
				'fields' => [
					[
						'name' => '[enabled]',
						'title' => __('Enable shipping', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['enabled'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[calculator]',
						'title' => __('Enable shipping calculator', 'jigoshop'),
						'description' => __('This enables calculator in cart for available shipping methods.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['calculator'],
						'classes' => ['switch-medium'],
                    ],
                ],
            ],
			[
				'title' => __('Options', 'jigoshop'),
				'id' => 'options',
				'fields' => [
					[
						'name' => '[only_to_billing]',
						'title' => __('Ship only to billing address?', 'jigoshop'),
						'description' => __('This forces customer to use billing address as shipping address.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['only_to_billing'],
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[always_show_shipping]',
						'title' => __('Always show shipping fields', 'jigoshop'),
						'description' => __('This forces shipping fields to be always visible in checkout.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['always_show_shipping'],
						'classes' => ['switch-medium'],
                    ],
                ],
            ],
        ];

		foreach ($this->shippingService->getAvailable() as $method) {
			/** @var $method Method */
			$options[] = [
				'title' => $method->getName(),
                'description' => apply_filters('jigoshop\admin\settings\shipping\method\description', '', $method),
				'id' => $method->getId(),
				'fields' => $method->getOptions(),
            ];
		}

		return $options;
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
