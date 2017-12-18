<?php

namespace Jigoshop\Payment;

use Jigoshop\Endpoint\Processable;
use Jigoshop\Container;
use Jigoshop\Core;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Customer\CompanyAddress;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Api;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Endpoint;
use Jigoshop\Helper\Order as OrderHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Validation;
use Monolog\Registry;
use WPAL\Wordpress;
use Jigoshop\Exception;

/**
 * Class WordPay
 * @package Jigoshop\Payment;
 * @author Krzysztof Kasowski
 */
class WordPay implements Method3, RenderPayInterface
{

	const ID = 'wordpay';
	const STANDARD_PAYMENT = 'standard';

	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var Messages */
	private $messages;
	/** @var array */
	private $settings;
	/** @var Container */
	private $di;

	public function __construct(Wordpress $wp, Container $di, Options $options, Messages $messages)
	{
		$this->wp = $wp;
		$this->di = $di;
		$this->options = $options;
		$this->messages = $messages;
		$this->settings = $options->get('payment.'.self::ID);
	}


	/**
	 * @return string ID of payment method.
	 */
	public function getId()
	{
		return self::ID;
	}

	/**
	 * @return string Human readable name of method.
	 */
	public function getName()
	{
		return $this->wp->isAdmin() ? $this->getLogoImage().' '.__('WordPay', 'jigoshop-ecommerce') : $this->settings['title'];
	}

	/**
	 * @return string
	 */
	private function getLogoImage()
	{
		return '<img src="https://www.worldpay.com/sites/all/themes/worldpay_subthemev2/logo.png" alt="" class="payment-logo" />';
	}


	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		return $this->settings['enabled'];
	}

	/**
	 * @return array List of options to display on Payment settings page.
	 */
	public function getOptions()
	{
		return [
			[
				'name' => sprintf('[%s][enabled]', self::ID),
				'title' => __('Is enabled?', 'jigoshop-ecommerce'),
				'type' => 'checkbox',
				'checked' => $this->settings['enabled'],
				'classes' => ['switch-medium'],
			],
			[
				'name' => sprintf('[%s][title]', self::ID),
				'title' => __('Title', 'jigoshop-ecommerce'),
				'type' => 'text',
				'value' => $this->settings['title'],
			],
			[
				'name' => sprintf('[%s][description]', self::ID),
				'title' => __('Description', 'jigoshop-ecommerce'),
				'tip' => sprintf(__('Allowed HTML tags are: %s', 'jigoshop-ecommerce'), '<p>, <a>, <strong>, <em>, <b>, <i>'),
				'type' => 'text',
				'value' => $this->settings['description'],
			],
			[
				'name' => sprintf('[%s][admin_only]', self::ID),
				'title' => __('Admin only?', 'jigoshop-ecommerce'),
				'type' => 'checkbox',
				'tip' => __('Set this if payment has to be available only for admins.', 'jigoshop-ecommerce'),
				'checked' => $this->settings['admin_only'],
				'classes' => ['switch-medium'],
			],
			[
				'name' => sprintf('[%s][payment_type]', self::ID),
				'title' => __('Payment type', 'jigoshop-ecommerce'),
				'tip' => '',
				'type' => 'select',
				'options' => [
					self::STANDARD_PAYMENT => __('Standard', 'jigoshop-ecommerce'),
				],
			],
			[
				'name' => sprintf('[%s][api_key]', self::ID),
				'title' => __('Api key', 'jigoshop-ecommerce'),
				'tip' => __('Please enter your valid api key; this is needed in order to take payment!', 'jigoshop-ecommerce'),
				'type' => 'text',
				'value' => $this->settings['email'],
			],
			[
				'name' => sprintf('[%s][test_mode]', self::ID),
				'title' => __('Enable testing mode', 'jigoshop-ecommerce'),
				'type' => 'checkbox',
				'checked' => $this->settings['test_mode'],
				'classes' => ['switch-medium'],
			],
			[
				'name' => sprintf('[%s][test_api_key]', self::ID),
				'title' => __('Test api key', 'jigoshop-ecommerce'),
				'tip' => __('Please enter your test api key; this is needed for testing purposes and used when test mode is enabled.', 'jigoshop-ecommerce'),
				'type' => 'text',
				'value' => $this->settings['test_api_key'],
			],
		];
	}

	/**
	 * Validates and returns properly sanitized options.
	 *
	 * @param $settings array Input options.
	 *
	 * @return array Sanitized result.
	 */
	public function validateOptions( $settings )
    {
		$settings['enabled'] = $settings['enabled'] == 'on';
		$settings['test_mode'] = $settings['test_mode'] == 'on';
		$settings['admin_only'] = $settings['admin_only'] == 'on';
		$settings['title'] = trim(htmlspecialchars(strip_tags($settings['title'])));
		$settings['description'] = trim(htmlspecialchars(strip_tags($settings['description'], '<p><a><strong><em><b><i>')));

		if ($settings['enabled'] && !$settings['test_mode'] && $settings['api_key'] == '') {
			$this->messages->addWarning(__('Please enter Api key.', 'jigoshop-ecommerce'));
		} elseif($settings['enabled'] && $settings['test_mode'] && $settings['test_api_key'] == '') {
			$this->messages->addWarning(__('Please enter test Api key.', 'jigoshop-ecommerce'));
		}

		if($this->messages->hasErrors() || $this->messages->hasWarnings()) {
			$settings['enabled'] = false;
		}

		return $settings;
	}

	/**
	 * Renders method fields and data in Checkout page.
	 */
	public function render()
    {
		echo $this->settings['description'];
	}

	/**
	 * @param Order $order Order to process payment for.
	 *
	 * @return string URL to redirect to.
	 * @throws Exception On any payment error.
	 */
	public function process($order)
    {
		return OrderHelper::getPayLink($order, $this);
	}

	/**
	 * Whenever method was enabled by the user.
	 *
	 * @return boolean Method enable state.
	 */
	public function isActive()
    {
		if(isset($this->settings['enabled'])) {
			return $this->settings['enabled'];
		}
	}

	/**
	 * Set method enable state.
	 *
	 * @param boolean $state Method enable state.
	 *
	 * @return array Method current settings (after enable state change).
	 */
	public function setActive($state)
    {
		$this->settings['enabled'] = $state;

		return $this->settings;
	}

	/**
	 * Whenever method was configured by the user (all required data was filled for current scenario).
	 *
	 * @return boolean Method config state.
	 */
	public function isConfigured()
    {
		if(isset($this->settings['test_mode']) && $this->settings['test_mode']) {
			if(isset($this->settings['test_api_key']) && $this->settings['test_api_key']) {
				return true;
			}
			return false;
		}

		if(isset($this->settings['api_key']) && $this->settings['api_key']) {
			return true;
		}

		return false;
	}

	/**
	 * Whenever method has some sort of test mode.
	 *
	 * @return boolean Method test mode presence.
	 */
	public function hasTestMode()
    {
		return true;
	}

	/**
	 * Whenever method test mode was enabled by the user.
	 *
	 * @return boolean Method test mode state.
	 */
	public function isTestModeEnabled()
    {
		if(isset($this->settings['test_mode'])) {
			return $this->settings['test_mode'];
		}
	}

	/**
	 * Set Method test mode state.
	 *
	 * @param boolean $state Method test mode state.
	 *
	 * @return array Method current settings (after test mode state change).
	 */
	public function setTestMode( $state )
    {
		$this->settings['test_mode'] = $state;

		return $this->settings;
	}

	/**
	 * Whenever method requires SSL to be enabled to function properly.
	 *
	 * @return boolean Method SSL requirment.
	 */
	public function isSSLRequired()
    {
		return false;
	}

	/**
	 * Whenever method is set to enabled for admin only.
	 *
	 * @return boolean Method admin only state.
	 */
	public function isAdminOnly()
    {
		return isset($this->settings['admin_only']) && $this->settings['admin_only'];
	}

	/**
	 * Sets admin only state for the method and returns complete method options.
	 *
	 * @param boolean $state Method admin only state.
	 *
	 * @return array Complete method options after change was applied.
	 */
	public function setAdminOnly($state)
    {
		$this->settings['admin_only'] = $state;

		return $this->settings;
	}

    /**
	 * @param Order $order
	 *
	 * @return string
	 */
	public function renderPay($order)
    {
		Scripts::add('wordpay', 'https://cdn.worldpay.com/v1/worldpay.js');
		return Render::get('shop/checkout/pay/wordpay', [
		    'clientKey' => '',
            'order' => $order,
            'notifyUrl' => Endpoint::getUrl(self::ID),
        ]);
	}
}