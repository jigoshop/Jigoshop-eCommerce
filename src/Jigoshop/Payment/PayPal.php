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
use Jigoshop\Helper\Order as OrderHelper;
use Jigoshop\Helper\Validation;
use Monolog\Registry;
use WPAL\Wordpress;

class PayPal implements Method, Processable
{
	const ID = 'paypal';
	const LIVE_URL = 'https://www.paypal.com/webscr';
	const TEST_URL = 'https://www.sandbox.paypal.com/webscr?test_ipn=1';

	// based on PayPal currency rule: https://developer.paypal.com/docs/classic/api/currency_codes/
	private static $noDecimalCurrencies = ['HUF', 'JPY', 'TWD'];

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
		$this->decimals = min($options->get('general.currency_decimals'), (in_array($options->get('general.currency'), self::$noDecimalCurrencies) ? 0 : 2));
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
		return $this->wp->isAdmin() ? $this->getLogoImage().' '.__('PayPal', 'jigoshop') : $this->settings['title'];
	}

	private function getLogoImage()
	{
		return '<img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg" alt="" class="payment-logo" />';
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
				'title' => __('Is enabled?', 'jigoshop'),
				'type' => 'checkbox',
				'checked' => $this->settings['enabled'],
				'classes' => ['switch-medium'],
            ],
			[
				'name' => sprintf('[%s][title]', self::ID),
				'title' => __('Title', 'jigoshop'),
				'type' => 'text',
				'value' => $this->settings['title'],
            ],
			[
				'name' => sprintf('[%s][description]', self::ID),
				'title' => __('Description', 'jigoshop'),
				'tip' => sprintf(__('Allowed HTML tags are: %s', 'jigoshop'), '<p>, <a>, <strong>, <em>, <b>, <i>'),
				'type' => 'text',
				'value' => $this->settings['description'],
            ],
			[
				'name' => sprintf('[%s][email]', self::ID),
				'title' => __('PayPal email address', 'jigoshop'),
				'tip' => __('Please enter your PayPal email address; this is needed in order to take payment!', 'jigoshop'),
				'type' => 'text',
				'value' => $this->settings['email'],
            ],
			[
				'name' => sprintf('[%s][send_shipping]', self::ID),
				'title' => __('Send shipping details to PayPal', 'jigoshop'),
				'tip' => __('If your checkout page does not ask for shipping details, or if you do not want to send shipping information to PayPal, set this option to no. If you enable this option PayPal may restrict where things can be sent, and will prevent some orders going through for your protection.', 'jigoshop'),
				'type' => 'checkbox',
				'checked' => $this->settings['send_shipping'],
				'classes' => ['switch-medium'],
            ],
			[
				'name' => sprintf('[%s][force_payment]', self::ID),
				'title' => __('Force payment', 'jigoshop'),
				'tip' => __('If product totals are free and shipping is also free (excluding taxes), this will force 0.01 to allow paypal to process payment. Shop owner is responsible for refunding customer.', 'jigoshop'),
				'type' => 'checkbox',
				'checked' => $this->settings['force_payment'],
				'classes' => ['switch-medium'],
            ],
			[
				'name' => sprintf('[%s][test_mode]', self::ID),
				'title' => __('Enable Sandbox', 'jigoshop'),
				'type' => 'checkbox',
				'checked' => $this->settings['test_mode'],
				'classes' => ['switch-medium'],
            ],
			[
				'name' => sprintf('[%s][test_email]', self::ID),
				'title' => __('PayPal test email address', 'jigoshop'),
				'tip' => __('Please enter your test PayPal email address; this is needed for testing purposes and used when test mode is enabled.', 'jigoshop'),
				'type' => 'text',
				'value' => $this->settings['test_email'],
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
	public function validateOptions($settings)
	{
		$settings['enabled'] = $settings['enabled'] == 'on';
		$settings['title'] = trim(htmlspecialchars(strip_tags($settings['title'])));
		$settings['description'] = trim(htmlspecialchars(strip_tags($settings['description'], '<p><a><strong><em><b><i>')));

		if (!Validation::isEmail($settings['email'])) {
			$settings['email'] = '';
			if ($settings['enabled']) {
				$this->messages->addWarning(__('Email address is not valid.', 'jigoshop'));
			}
		}

		$settings['send_shipping'] = $settings['send_shipping'] == 'on';
		$settings['force_payment'] = $settings['force_payment'] == 'on';
		$settings['test_mode'] = $settings['test_mode'] == 'on';

		if (!Validation::isEmail($settings['test_email'])) {
			$settings['test_email'] = '';
			if ($settings['enabled']) {
				$this->messages->addWarning(__('Test email address is not valid.', 'jigoshop'));
			}
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
	 * @return bool Is processing successful?
	 */
	public function process($order)
	{
		if ($this->settings['test_mode']) {
			$url = self::TEST_URL.'&';
		} else {
			$url = self::LIVE_URL.'?';
		}

		$billingAddress = $order->getCustomer()->getBillingAddress();
		if (in_array($billingAddress->getCountry(), ['US', 'CA'])) {
			$phone = str_replace(['(', '-', ' ', ')'], '', $billingAddress->getPhone());
			$phone = [
				'night_phone_a' => substr($phone, 0, 3),
				'night_phone_b' => substr($phone, 3, 3),
				'night_phone_c' => substr($phone, 6, 4),
				'day_phone_a' => substr($phone, 0, 3),
				'day_phone_b' => substr($phone, 3, 3),
				'day_phone_c' => substr($phone, 6, 4),
            ];
		} else {
			$phone = [
				'night_phone_b' => $billingAddress->getPhone(),
				'day_phone_b' => $billingAddress->getPhone(),
            ];
		}

		$args = array_merge(
			[
				'cmd' => '_cart',
				'business' => $this->settings['test_mode'] ? $this->settings['test_email'] : $this->settings['email'],
				'no_note' => 1,
				'currency_code' => Currency::code(),
				'charset' => 'UTF-8',
				'rm' => 2,
				'upload' => 1,
				'return' => OrderHelper::getThankYouLink($order),
				'cancel_return' => OrderHelper::getCancelLink($order),
				// Order key
				'custom' => $order->getId(),
				// IPN
				'notify_url' => Api::getUrl(self::ID),
				// Address info
				'first_name' => $billingAddress->getFirstName(),
				'last_name' => $billingAddress->getLastName(),
				'company' => $billingAddress instanceof CompanyAddress ? $billingAddress->getCompany() : '',
				'address1' => $billingAddress->getAddress(),
				'address2' => '',
				'city' => $billingAddress->getCity(),
				'state' => $billingAddress->getState(),
				'zip' => $billingAddress->getPostcode(),
				'country' => $billingAddress->getCountry(),
				'email' => $billingAddress->getEmail(),
				// Payment Info
				'invoice' => $order->getNumber(),
				'amount' => number_format($order->getTotal(), $this->options->get('general.currency_decimals')),
				//BN code
				'bn' => 'Jigoshop_SP'
            ],
			$phone
		);

		if ($this->settings['send_shipping']) {
			$shippingAddress = $order->getCustomer()->getShippingAddress();
			$args['no_shipping'] = 1;
			$args['address_override'] = 1;
			$args['first_name'] = $shippingAddress->getFirstName();
			$args['last_name'] = $shippingAddress->getLastName();
			$args['address1'] = $shippingAddress->getAddress();
			$args['address2'] = '';
			$args['city'] = $shippingAddress->getCity();
			$args['state'] = $shippingAddress->getState();
			$args['zip'] = $shippingAddress->getPostcode();
			$args['country'] = $shippingAddress->getCountry();
			// PayPal counts Puerto Rico as a US Territory, won't allow payment without it
			if ($args['country'] == 'PR') {
				$args['country'] = 'US';
				$args['state'] = 'PR';
			}
		} else {
			$args['no_shipping'] = 1;
			$args['address_override'] = 0;
		}

		// Cart Contents
		$item_loop = 0;
		foreach ($order->getItems() as $item) {
			/** @var $item Order\Item */
			$item_loop++;
			$product = $item->getProduct();
			$title = $product->getName();

			//if variation, insert variation details into product title
			if ($product instanceof Product\Variable) {
				$title .= '('.join(', ', array_filter(array_map(function ($attribute) use ($item){
						/** @var $attribute Product\Variable\Attribute */
						if ($attribute->getValue() !== '') {
							$value = $attribute->getValue();
						} else {
							$value = $item->getMeta($attribute->getAttribute()->getSlug())->getValue();
						}

						return sprintf(_x('%s: %s', 'product_variation', 'jigoshop'), $attribute->getAttribute()->getLabel(), $attribute->getAttribute()
							->getOption($value)
							->getLabel());
					}, $product->getVariation($item->getMeta('variation_id')->getValue())->getAttributes()))).')';
			}

			$args['item_name_'.$item_loop] = $title;
			$args['quantity_'.$item_loop] = $item->getQuantity();
			// Apparently, PayPal did not like "28.4525" as the amount. Changing that to "28.45" fixed the issue.
			$args['amount_'.$item_loop] = number_format($this->wp->applyFilters('jigoshop\paypal\item_price', $item->getPrice(), $item), $this->decimals);
		}

		// Shipping Cost
		if ($this->options->get('shipping.enabled') && $order->getShippingPrice() > 0) {
			$item_loop++;
			$args['item_name_'.$item_loop] = __('Shipping cost', 'jigoshop');
			$args['quantity_'.$item_loop] = '1';
			$args['amount_'.$item_loop] = number_format($order->getShippingPrice(), $this->decimals);
		}

		$args['tax'] = $args['tax_cart'] = number_format($order->getTotalCombinedTax(), $this->decimals);
		$args['discount_amount_cart'] = number_format($order->getDiscount(), $this->decimals);

		if ($this->settings['force_payment'] && $order->getTotal() == 0) {
			$item_loop++;
			$args['item_name_'.$item_loop] = __('Force payment on free orders', 'jigoshop');
			$args['quantity_'.$item_loop] = '1';
			$args['amount_'.$item_loop] = 0.01;
		}

		$args = $this->wp->applyFilters('jigoshop\paypal\args', $args);
		$order->setStatus(Order\Status::PENDING, __('Waiting for PayPal payment.', 'jigoshop'));

		return $url.http_build_query($args);
	}

	public function processResponse()
	{
		if ($this->isResponseValid()) {
			$posted = $this->wp->getHelpers()->stripSlashesDeep($_POST);

			// 'custom' holds post ID (Order ID)
			if (!empty($posted['custom']) && !empty($posted['txn_type']) && !empty($posted['invoice'])) {
				$accepted_types = ['cart', 'instant', 'express_checkout', 'web_accept', 'masspay', 'send_money', 'subscr_payment'];
				/** @var \Jigoshop\Service\OrderService $service */
				$service = $this->di->get('jigoshop.service.order');
				$order = $service->find((int)$posted['custom']);

				// Sandbox fix
				if (isset($posted['test_ipn']) && $posted['test_ipn'] == 1 && strtolower($posted['payment_status']) == 'pending') {
					$posted['payment_status'] = 'completed';
				}

				$merchant = $this->settings['test_mode'] ? $this->settings['test_email'] : $this->settings['email'];

				if ($order->getStatus() !== Order\Status::COMPLETED) {
					// We are here so lets check status and do actions
					switch (strtolower($posted['payment_status'])) {
						case 'completed':
							if (!in_array(strtolower($posted['txn_type']), $accepted_types)) {
								// Put this order on-hold for manual checking
								$order->setStatus(Order\Status::ON_HOLD, sprintf(__('PayPal Validation Error: Unknown "txn_type" of "%s" for Order ID: %s.', 'jigoshop'), $posted['txn_type'], $posted['custom']));
								break;
							}

							if ($order->getNumber() !== $posted['invoice']) {
								// Put this order on-hold for manual checking
								$order->setStatus(Order\Status::ON_HOLD, sprintf(__('PayPal Validation Error: Order Invoice Number does NOT match PayPal posted invoice (%s) for Order ID: .', 'jigoshop'), $posted['invoice'], $posted['custom']));
								$service->save($order);
								exit;
							}

							// Validate Amount
							if (number_format($order->getTotal(), $this->decimals, '.', '') != $posted['mc_gross']) {
								// Put this order on-hold for manual checking
								$order->setStatus(Order\Status::ON_HOLD, sprintf(__('PayPal Validation Error: Payment amounts do not match initial order (gross %s).', 'jigoshop'), $posted['mc_gross']));
								$service->save($order);
								exit;
							}

							if (strcasecmp(trim($posted['business']), trim($merchant)) != 0) {
								// Put this order on-hold for manual checking
								$order->setStatus(Order\Status::ON_HOLD, sprintf(__('PayPal Validation Error: Payment Merchant email received does not match PayPal Gateway settings. (%s)', 'jigoshop'), $posted['business']));
								$service->save($order);
								exit;
							}

							if ($posted['mc_currency'] != $this->options->get('general.currency')) {
								// Put this order on-hold for manual checking
								$order->setStatus(Order\Status::ON_HOLD, sprintf(__('PayPal Validation Error: Payment currency received (%s) does not match Shop currency.', 'jigoshop'), $posted['mc_currency']));
								$service->save($order);
								exit;
							}

							$order->setStatus(OrderHelper::getStatusAfterCompletePayment($order), __('PayPal payment completed', 'jigoshop'));
							break;
						case 'denied':
						case 'expired':
						case 'failed':
						case 'voided':
							// Failed order
							$order->setStatus(Order\Status::ON_HOLD, sprintf(__('Payment %s via PayPal.', 'jigoshop'), strtolower($posted['payment_status'])));
							break;
						case 'refunded':
						case 'reversed':
						case 'chargeback':
							// TODO: Implement refunds
							break;
						default:
							// No action
							break;
					}

					$service->save($order);
				}
			}
		}
	}

	/**
	 * Check PayPal IPN validity
	 */
	private function isResponseValid()
	{
		$values = $this->wp->getHelpers()->stripSlashesDeep($_POST);
		$values['cmd'] = '_notify-validate';

		// Send back post vars to PayPal
		$params = [
			'body' => $values,
			'sslverify' => false,
			'timeout' => 30,
			'user-agent' => 'Jigoshop/'.Core::VERSION,
        ];

		// Get url
		if ($this->settings['test_mode']) {
			$url = self::TEST_URL;
		} else {
			$url = self::LIVE_URL;
		}

		// Post back to get a response
		$response = $this->wp->wpSafeRemotePost($url, $params);

		// check to see if the request was valid
		if (!$this->wp->isWpError($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && (strcmp($response['body'], "VERIFIED") == 0)) {
			return true;
		}

		Registry::getInstance(JIGOSHOP_LOGGER)->addWarning('Received invalid response from PayPal!', ['response' => $response]);

		return false;
	}
}
