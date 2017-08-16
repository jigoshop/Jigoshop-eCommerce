<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Payment\Method;
use Jigoshop\Payment\Method2;
use Jigoshop\Service\PaymentServiceInterface;
use WPAL\Wordpress;

/**
 * Payment tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class PaymentTab implements TabInterface
{
	const SLUG = 'payment';

	private $settings;
	private $options;
	private $paymentService;
	private $messages;

	public function __construct(Wordpress $wp, Options $options, PaymentServiceInterface $paymentService, Messages $messages)
	{
		$this->settings = $options->get(self::SLUG);
		$this->options = $options;
		$this->paymentService = $paymentService;
		$this->messages = $messages;

		$wp->addAction('admin_enqueue_scripts', function() {
			if(isset($_GET['tab']) && $_GET['tab'] == PaymentTab::SLUG) {
				Scripts::add('jigoshop.admin.settings.payment', \JigoshopInit::getUrl() . '/assets/js/admin/settings/payment.js', [
						'jquery',
						'wp-util'
					], [
						'page' => 'jigoshop_page_jigoshop_settings'
					]);

				Scripts::add('jigoshop.magnific-popup', \JigoshopInit::getUrl() . '/assets/js/vendors/magnific_popup.js', ['jquery']);

				Styles::add('jigoshop.magnific-popup', \JigoshopInit::getUrl() . '/assets/css/vendors/magnific_popup.css');
			}
		});

        $wp->addAction('wp_ajax_paymentMethodSaveEnable', [$this, 'ajaxPaymentMethodSaveEnable']);
        $wp->addAction('wp_ajax_paymentMethodSaveTestMode', [$this, 'ajaxPaymentMethodSaveTestMode']);		
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Payment', 'jigoshop');
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
				'title'  => __('Default Gateway', 'jigoshop'),
				'id'     => 'default_gateway',
				'fields' => [
					[
						'name'    => "[default_gateway]",
						'title'   => __('Set default gataway', 'jigoshop'),
						'type'    => "select",
						'value'   => $this->settings['default_gateway'],
						'options' => $this->getDefaultGatewayOptions()
                    ]
                ],
            ],
            [
            	'title' => __('Payment methods', 'jigoshop'),
            	'id' => 'paymentMethodsSection',
            	'display' => [$this, 'generatePaymentMethods']
            ]
        ];		
	}

	private function getDefaultGatewayOptions() {
		$options = [];
		$methods = $this->paymentService->getAvailable();

		if(count($methods) > 0) {
			$options[] = __('Please select a gateway', 'jigoshop');

			foreach($methods as $method) {
				$options[] = trim(strip_tags($method->getName()));
			}
		}
		else {
			$options['no_default_gateway'] = __('All gateways are disabled. Please turn on a gateway.', 'jigoshop');		
		}

		return $options;
	}

	public function generatePaymentMethods() {
		$methods = [];
		foreach($this->paymentService->getAvailable() as $method) {
			if($method instanceof Method2) {
				$status = '';

				if(!$method->isActive()) {
					$status = __('Disabled', 'jigoshop');
				}
				else {
					if(!$method->isConfigured()) {
						$status = __('Disabled; Not configured', 'jigoshop');
					}
					else {
						if($method->hasTestMode() && $method->isTestModeEnabled()) {
							$status = __('Enabled in test mode', 'jigoshop');
						}
						else {
							$status = __('Enabled', 'jigoshop');
						}
					}
				}

				$methods[] = [
					'basicSummary' => 0,
					'id' => $method->getId(),
					'name' => $method->getName(),
					'options' => $method->getOptions(),
					'status' => $status,
					'active' => $method->isActive(),
					'hasTestMode' => $method->hasTestMode(),
					'testModeActive' => $method->isTestModeEnabled()						
				];
			}
			elseif($method instanceof Method) {
				$methods[] = [
					'basicSummary' => 1,
					'id' => $method->getId(),
					'name' => trim(strip_tags($method->getName())),
					'options' => $method->getOptions()
				];
			}
		}

		return Render::get('admin/settings/payment/methodContainer', [
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
		$activeGatewayFromPost = [];

		foreach ($this->paymentService->getAvailable() as $method)
		{
			/** @var $method Method */
			$methodId = $method->getId();
			$settings[$methodId] = $method->validateOptions($settings[$methodId]);

			if(($method instanceof Method2 && $method->isActive()) || ($method instanceof Method && $_POST['jigoshop'][$methodId]['enabled'] == 'on'))
			{
				$activeGatewayFromPost[] = $methodId;
			}
		}

		if (count($activeGatewayFromPost) == 0)
		{
			$settings['default_gateway'] = 'no_default_gateway';

			return $settings;
		}

		if ($_POST['jigoshop'][$this->settings['default_gateway']]['enabled'] == 'off')
		{
			$settings['default_gateway'] = $activeGatewayFromPost[0];

		}

		return $settings;
	}

	public function ajaxPaymentMethodSaveEnable() {
		$method = $this->paymentService->get($_POST['method']);

		if($method instanceof Method2) {
			if($_POST['state'] == 'true') {
				if(!$method->isConfigured()) {
					$this->messages->addWarning(sprintf(__('%s was not enabled, as it isn\'t configured properly.', 'jigoshop'), $method->getName()));

					exit;
				}

				$state = true;

				$this->messages->addNotice(sprintf(__('%s enabled.', 'jigoshop'), $method->getName()));
			}
			else {
				$state = false;

				$this->messages->addNotice(sprintf(__('%s disabled.', 'jigoshop'), $method->getName()));
			}

			$settings = $method->setActive($state);

			$this->options->update('payment.' . $method->getId(), $settings);
			$this->options->saveOptions();
		}

		exit;
	}

	public function ajaxPaymentMethodSaveTestMode() {
		$method = $this->paymentService->get($_POST['method']);

		if($method instanceof Method2) {
			if($_POST['state'] == 'true') {
				$state = true;

				$this->messages->addNotice(sprintf(__('%s test mode enabled.', 'jigoshop'), $method->getName()));
			}
			else {
				$state = false;

				$this->messages->addNotice(sprintf(__('%s test mode disabled.', 'jigoshop'), $method->getName()));
			}

			$settings = $method->setTestMode($state);

			$this->options->update('payment.' . $method->getId(), $settings);
			$this->options->saveOptions();
		}

		exit;
	}	
}