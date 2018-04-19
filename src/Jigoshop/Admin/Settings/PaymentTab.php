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

	private $processingFeeRuleMethods = [];

	public function __construct(Wordpress $wp, Options $options, PaymentServiceInterface $paymentService, Messages $messages)
	{
		$this->settings = $options->get(self::SLUG);
		$this->options = $options;
		$this->paymentService = $paymentService;
		$this->messages = $messages;

		$wp->addAction('admin_enqueue_scripts', function() {
			if(isset($_GET['tab']) && $_GET['tab'] == PaymentTab::SLUG) {
				Scripts::add('jigoshop.admin.settings.shipping_payment', \JigoshopInit::getUrl() . '/assets/js/admin/settings/shipping_payment.js', [
						'jquery',
						'wp-util'
					], [
						'page' => 'jigoshop_page_jigoshop_settings'
					]);

				Scripts::add('jigoshop.admin.settings.payment', \JigoshopInit::getUrl() . '/assets/js/admin/settings/payment.js', [
						'jquery',
						'jquery-ui-sortable',
						'wp-util'
					], [
						'page' => 'jigoshop_page_jigoshop_settings'
					]);

				Scripts::add('jigoshop.magnific-popup', \JigoshopInit::getUrl() . '/assets/js/vendors/magnific_popup.js', ['jquery']);

				Styles::add('jigoshop.magnific-popup', \JigoshopInit::getUrl() . '/assets/css/vendors/magnific_popup.css');

				Scripts::localize('jigoshop.admin.settings.payment', 'jigoshop_admin_payment', [
					'processingFeeRule' => Render::get('admin/settings/payment/processingFeeRules/processingFeeRule', [
						'id' => '%RULE_ID%',
						'methods' => $this->processingFeeRuleMethods
					])
				]);
			}
		});

        $wp->addAction('wp_ajax_paymentMethodSaveEnable', [$this, 'ajaxPaymentMethodSaveEnable']);
        $wp->addAction('wp_ajax_paymentMethodSaveTestMode', [$this, 'ajaxPaymentMethodSaveTestMode']);

        foreach($this->paymentService->getAvailable() as $method) {
        	$this->processingFeeRuleMethods[$method->getId()] = strip_tags($method->getName());
        }
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Payment', 'jigoshop-ecommerce');
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
				'title'  => __('Default Gateway', 'jigoshop-ecommerce'),
				'id'     => 'default_gateway',
				'fields' => [
					[
						'name'    => "[default_gateway]",
						'title'   => __('Set default gataway', 'jigoshop-ecommerce'),
						'type'    => "select",
						'value'   => $this->settings['default_gateway'],
						'options' => $this->getDefaultGatewayOptions()
                    ]
                ],
            ],
            [
            	'title' => __('Payment methods', 'jigoshop-ecommerce'),
            	'id' => 'paymentMethodsSection',
            	'display' => [$this, 'generatePaymentMethods']
            ],
            [
            	'title' => __('Processing fee rules', 'jigoshop-ecommerce'),
            	'id' => 'processingFeeRulesSection',
            	'display' => [$this, 'generateProcessingFeeRules']
            ]
        ];		
	}

	private function getDefaultGatewayOptions() {
		$options = [];
		$methods = $this->paymentService->getAvailable();

		if(count($methods) > 0) {
			$options[] = __('Please select a gateway', 'jigoshop-ecommerce');

			foreach($methods as $method) {
				$options[] = trim(strip_tags($method->getName()));
			}
		}
		else {
			$options['no_default_gateway'] = __('All gateways are disabled. Please turn on a gateway.', 'jigoshop-ecommerce');
		}

		return $options;
	}

	public function generatePaymentMethods() {
		$methods = [];
		foreach($this->paymentService->getAvailable() as $method) {
			if($method instanceof Method2) {
				$status = '';

				if(!$method->isActive()) {
					$status = __('Disabled', 'jigoshop-ecommerce');
				}
				else {
					if(!$method->isConfigured()) {
						$status = __('Disabled; Not configured', 'jigoshop-ecommerce');
					}
					else {
						if($method->hasTestMode() && $method->isTestModeEnabled()) {
							$status = __('Enabled in test mode', 'jigoshop-ecommerce');
						}
						else {
							$status = __('Enabled', 'jigoshop-ecommerce');
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

	public function generateProcessingFeeRules() {
		if(!isset($this->settings['processingFeeRules']) || !is_array($this->settings['processingFeeRules'])) {
			$this->settings['processingFeeRules'] = [];
		}

		return Render::get('admin/settings/payment/processingFeeRules', [
			'methods' => $this->processingFeeRuleMethods,
			'rules' => $this->settings['processingFeeRules']
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
		}

		if ($_POST['jigoshop'][$this->settings['default_gateway']]['enabled'] == 'off' && !empty($settings['default_gateway']))
		{
			$settings['default_gateway'] = $activeGatewayFromPost[0];

		}

		$settings['processingFeeRules'] = [];
		foreach($_POST['processingFeeRules'] as $index => $processingFeeRule) {
			$processingFeeRule['value'] = str_replace(',', '.', $processingFeeRule['value']);
			if(!preg_match('/^[\d\.]*%?$/', $processingFeeRule['value']) || $processingFeeRule['value'] < 0) {
				$processingFeeRule['value'] = 0;
			}

			$processingFeeRule['minValue'] = str_replace(',', '.', $processingFeeRule['minValue']);
			if(!preg_match('/^[\d\.]*$/', $processingFeeRule['minValue']) || $processingFeeRule['minValue'] < 0) {
				$processingFeeRule['minValue'] = '';
			}

			$processingFeeRule['maxValue'] = str_replace(',', '.', $processingFeeRule['maxValue']);
			if(!preg_match('/^[\d\.]*$/', $processingFeeRule['maxValue']) || $processingFeeRule['maxValue'] < 0) {
				$processingFeeRule['maxValue'] = '';
			}

			if($processingFeeRule['alternateMode'] === 'on') {
				$processingFeeRule['alternateMode'] = true;
			}			
			else {
				$processingFeeRule['alternateMode'] = false;
			}

			$settings['processingFeeRules'][] = [
				'id' => count($settings['processingFeeRules']),
				'methods' => $processingFeeRule['methods'],
				'minValue' => $processingFeeRule['minValue'],
				'maxValue' => $processingFeeRule['maxValue'],
				'value' => $processingFeeRule['value'],
				'alternateMode' => $processingFeeRule['alternateMode']
			];
		}

		return $settings;
	}

	public function ajaxPaymentMethodSaveEnable() {
		$method = $this->paymentService->get($_POST['method']);

		if($method instanceof Method2) {
			if($_POST['state'] == 'true') {
				if(!$method->isConfigured()) {
					$this->messages->addWarning(sprintf(__('%s was not enabled, as it isn\'t configured properly.', 'jigoshop-ecommerce'), $method->getName()));

					exit;
				}

				$state = true;

				$this->messages->addNotice(sprintf(__('%s enabled.', 'jigoshop-ecommerce'), $method->getName()));
			}
			else {
				$state = false;

				$this->messages->addNotice(sprintf(__('%s disabled.', 'jigoshop-ecommerce'), $method->getName()));
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

				$this->messages->addNotice(sprintf(__('%s test mode enabled.', 'jigoshop-ecommerce'), $method->getName()));
			}
			else {
				$state = false;

				$this->messages->addNotice(sprintf(__('%s test mode disabled.', 'jigoshop-ecommerce'), $method->getName()));
			}

			$settings = $method->setTestMode($state);

			$this->options->update('payment.' . $method->getId(), $settings);
			$this->options->saveOptions();
		}

		exit;
	}	
}