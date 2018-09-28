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
use Jigoshop\Exception;
use Jigoshop\Extension\Subscriptions\Entity\Subscription\Status as SubscriptionStatus;
use Jigoshop\Extension\Subscriptions\Entity\Product\Subscription as SubscriptionProduct;
use Jigoshop\Extension\Subscriptions\Service\SubscriptionService;
use Jigoshop\Helper\Api;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Order as OrderHelper;
use Jigoshop\Helper\Validation;
use Monolog\Registry;
use WPAL\Wordpress;

class PayPal implements Method2, Processable, Subscribable
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
		return $this->wp->isAdmin() ? $this->getLogoImage().' '.__('PayPal', 'jigoshop-ecommerce') : $this->settings['title'];
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

	public function isActive() {
		if(isset($this->settings['enabled'])) {
			return $this->settings['enabled'];
		}
	}

	public function setActive($state) {
		$this->settings['enabled'] = $state;

		return $this->settings;
	}

	public function isConfigured() {
		if(isset($this->settings['test_mode']) && $this->settings['test_mode']) {
			if(isset($this->settings['test_email']) && $this->settings['test_email']) {
				return true;
			}
			return false;
		}

		if(isset($this->settings['email']) && $this->settings['email']) {
			return true;
		}

		return false;
	}

	public function hasTestMode() {
		return true;
	}

	public function isTestModeEnabled() {
		if(isset($this->settings['test_mode'])) {
			return $this->settings['test_mode'];
		}
	}

	public function setTestMode($state) {
		$this->settings['test_mode'] = $state;
	
		return $this->settings;
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
				'name' => sprintf('[%s][email]', self::ID),
				'title' => __('PayPal email address', 'jigoshop-ecommerce'),
				'tip' => __('Please enter your PayPal email address; this is needed in order to take payment!', 'jigoshop-ecommerce'),
				'type' => 'text',
				'value' => $this->settings['email'],
            ],
			[
				'name' => sprintf('[%s][send_shipping]', self::ID),
				'title' => __('Send shipping details to PayPal', 'jigoshop-ecommerce'),
				'tip' => __('If your checkout page does not ask for shipping details, or if you do not want to send shipping information to PayPal, set this option to no. If you enable this option PayPal may restrict where things can be sent, and will prevent some orders going through for your protection.', 'jigoshop-ecommerce'),
				'type' => 'checkbox',
				'checked' => $this->settings['send_shipping'],
				'classes' => ['switch-medium'],
            ],
			[
				'name' => sprintf('[%s][force_payment]', self::ID),
				'title' => __('Force payment', 'jigoshop-ecommerce'),
				'tip' => __('If product totals are free and shipping is also free (excluding taxes), this will force 0.01 to allow paypal to process payment. Shop owner is responsible for refunding customer.', 'jigoshop-ecommerce'),
				'type' => 'checkbox',
				'checked' => $this->settings['force_payment'],
				'classes' => ['switch-medium'],
            ],
			[
				'name' => sprintf('[%s][test_mode]', self::ID),
				'title' => __('Enable Sandbox', 'jigoshop-ecommerce'),
				'type' => 'checkbox',
				'checked' => $this->settings['test_mode'],
				'classes' => ['switch-medium'],
            ],
			[
				'name' => sprintf('[%s][test_email]', self::ID),
				'title' => __('PayPal test email address', 'jigoshop-ecommerce'),
				'tip' => __('Please enter your test PayPal email address; this is needed for testing purposes and used when test mode is enabled.', 'jigoshop-ecommerce'),
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
				$this->messages->addWarning(__('Email address is not valid.', 'jigoshop-ecommerce'));
			}
		}

		$settings['send_shipping'] = $settings['send_shipping'] == 'on';
		$settings['force_payment'] = $settings['force_payment'] == 'on';
		$settings['test_mode'] = $settings['test_mode'] == 'on';

		if($settings['test_mode'] && !Validation::isEmail($settings['test_email'])) {
			$settings['test_email'] = '';
			if($settings['enabled']) {
				$this->messages->addWarning(__('Test email address is not valid.', 'jigoshop-ecommerce'));
			}
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
				'amount' => number_format($order->getTotal(), $this->options->get('general.currency_decimals'), '.', ''),
				//BN code
				'bn' => 'JigoLtd_SP'
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

						return sprintf(_x('%s: %s', 'product_variation', 'jigoshop-ecommerce'), $attribute->getAttribute()->getLabel(), $attribute->getAttribute()
							->getOption($value)
							->getLabel());
					}, $product->getVariation($item->getMeta('variation_id')->getValue())->getAttributes()))).')';
			}

			$args['item_name_'.$item_loop] = $title;
			$args['quantity_'.$item_loop] = $item->getQuantity();
			// Apparently, PayPal did not like "28.4525" as the amount. Changing that to "28.45" fixed the issue.
			$args['amount_'.$item_loop] = number_format($this->wp->applyFilters('jigoshop\paypal\item_price', $item->getPrice(), $item), $this->decimals, '.','');
		}

		// Shipping Cost
		if ($this->options->get('shipping.enabled') && $order->getShippingPrice() > 0) {
			$item_loop++;
			$args['item_name_'.$item_loop] = __('Shipping cost', 'jigoshop-ecommerce');
			$args['quantity_'.$item_loop] = '1';
			$args['amount_'.$item_loop] = number_format($order->getShippingPrice(), $this->decimals, '.', '');
		}

		$args['tax'] = $args['tax_cart'] = number_format($order->getTotalCombinedTax(), $this->decimals, '.', '');
		$args['discount_amount_cart'] = number_format($order->getDiscount(), $this->decimals, '.', '');

		if ($this->settings['force_payment'] && $order->getTotal() == 0) {
			$item_loop++;
			$args['item_name_'.$item_loop] = __('Force payment on free orders', 'jigoshop-ecommerce');
			$args['quantity_'.$item_loop] = '1';
			$args['amount_'.$item_loop] = 0.01;
		}

		// Add processing fee to PayPal.
		if($order->getProcessingFee() > 0) {
			$item_loop++;
			$args['item_name_' . $item_loop] = __('Payment processing fee', 'jigoshop-ecommerce');
			$args['quantity_' . $item_loop] = '1';
			$args['amount_' . $item_loop] = number_format($order->getProcessingFee(), $this->decimals, '.', '');
		}

		$args = $this->wp->applyFilters('jigoshop\paypal\args', $args);
		$order->setStatus(Order\Status::PENDING, __('Waiting for PayPal payment.', 'jigoshop-ecommerce'));

		return $url.http_build_query($args, '', '&');
	}

    /**
     * @param \Jigoshop\Extension\Subscriptions\Entity\Subscription $subscription
     *
     * @return string
     */
    public function processSubscription(\Jigoshop\Extension\Subscriptions\Entity\Subscription $subscription)
    {
        if ($this->settings['test_mode']) {
            $url = self::TEST_URL . '&';
        } else {
            $url = self::LIVE_URL . '?';
        }

        $billingAddress = $subscription->getCustomer()->getBillingAddress();
        $intervalTypes = [
            SubscriptionProduct\Interval::DAYS => 'D',
            SubscriptionProduct\Interval::WEEKS => 'W',
            SubscriptionProduct\Interval::MONTHS => 'M',
            SubscriptionProduct\Interval::YEARS => 'Y',
        ];

        $args = array_merge(
            [
                'cmd' => '_xclick-subscriptions',
                'business' => $this->settings['test_mode'] ? $this->settings['test_email'] : $this->settings['email'],
                'no_note' => 1,
                'currency_code' => Currency::code(),
                'charset' => 'UTF-8',
                //'a1' => 1 //Free triar period.
                'a3' => $subscription->getItem()->getCost() + $subscription->getItem()->getTax(),
                /**
                 * Duration
                 */
                'p3' => $subscription->getItem()->getMeta('interval_value')->getValue(),
                /**
                 * D. Days. Valid range for p3 is 1 to 90.
                 * W. Weeks. Valid range for p3 is 1 to 52.
                 * M. Months. Valid range for p3 is 1 to 24.
                 * Y. Years. Valid range for p3 is 1 to 5.
                 */
                't3' => $intervalTypes[$subscription->getItem()->getMeta('interval_type')->getValue()],
                /**
                 * 0. Subscription payments do not recur.
                 * 1. Subscription payments recur.
                 */
                'src' => 1,
                /**
                 * Min 2, max 52
                 * Only if 'src=1'
                 */
                'srt' => $subscription->getItem()->getMeta('interval_number_of_recurs')->getValue(),
                /**
                 * 0. Do not reattempt failed recurring payments.
                 * 1. Reattempt failed recurring payments before canceling.
                 */
                'sra' => '1',
                //'return' => OrderHelper::getThankYouLink($order),
                //'cancel_return' => OrderHelper::getCancelLink($order),
                // Order key
                'custom' => $subscription->getId(),
                'invoice' => $subscription->getNumber(),
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
                'amount' => number_format($subscription->getItem()->getCost(), $this->options->get('general.currency_decimals'), '.',''),
                //BN code
                'bn' => 'JigoLtd_SP'
            ]
        );

        $args = $this->wp->applyFilters('jigoshop\paypal\subscription\args', $args);
        $subscription->setStatus(SubscriptionStatus::PENDING, __('Waiting for PayPal payment.', 'jigoshop-ecommerce'));

        return $url . http_build_query($args, '', '&');
    }

	public function processResponse()
	{
		if ($this->isResponseValid()) {
			$posted = $this->wp->getHelpers()->stripSlashesDeep($_POST);

            if(isset($posted['txn_type']) && in_array($posted['txn_type'], ['cart', 'instant', 'express_checkout', 'web_accept', 'masspay', 'send_money'])) {
                $this->processPaymentResponse($posted);
            } elseif (isset($posted['txn_type']) && in_array($posted['txn_type'],['subscr_payment', 'subscr_cancel', 'subscr_eot'])) {
                $this->processSubscriptionPayment($posted);
            } elseif (isset($posted['parent_txn_id'], $posted['payment_status']) && strtolower($posted['payment_status']) == 'refunded') {
                $this->refundPayment($posted);
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

    /**
     * @param $posted
     */
	private function processPaymentResponse($posted)
    {
        // 'custom' holds post ID (Order ID)
        if (!empty($posted['custom']) && !empty($posted['txn_type']) && !empty($posted['invoice'])) {
            /** @var \Jigoshop\Service\OrderService $service */
            $service = $this->di->get('jigoshop.service.order');
            $order = $service->find((int)$posted['custom']);

            // Sandbox fix
            if (isset($posted['test_ipn']) && $posted['test_ipn'] == 1 && strtolower($posted['payment_status']) == 'pending') {
                $posted['payment_status'] = 'completed';
            }

            $merchant = $this->settings['test_mode'] ? $this->settings['test_email'] : $this->settings['email'];

            // We are here so lets check status and do actions
            switch (strtolower($posted['payment_status'])) {
                case 'completed':
//                    if (!in_array(strtolower($posted['txn_type']), $accepted_types)) {
//                        // Put this order on-hold for manual checking
//                        $order->setStatus(Order\Status::ON_HOLD, sprintf(__('PayPal Validation Error: Unknown "txn_type" of "%s" for Order ID: %s.', 'jigoshop-ecommerce'), $posted['txn_type'], $posted['custom']));
//                        break;
//                    }

                    if ($order->getNumber() !== $posted['invoice']) {
                        // Put this order on-hold for manual checking
                        $order->setStatus(Order\Status::ON_HOLD, sprintf(__('PayPal Validation Error: Order Invoice Number does NOT match PayPal posted invoice (%s) for Order ID: .', 'jigoshop-ecommerce'), $posted['invoice'], $posted['custom']));
                        $service->save($order);
                        exit;
                    }

                    // Validate Amount
                    if (number_format($order->getTotal(), $this->decimals, '.', '') != $posted['mc_gross']) {
                        // Put this order on-hold for manual checking
                        $order->setStatus(Order\Status::ON_HOLD, sprintf(__('PayPal Validation Error: Payment amounts do not match initial order (gross %s).', 'jigoshop-ecommerce'), $posted['mc_gross']));
                        $service->save($order);
                        exit;
                    }

                    if (strcasecmp(trim($posted['business']), trim($merchant)) != 0) {
                        // Put this order on-hold for manual checking
                        $order->setStatus(Order\Status::ON_HOLD, sprintf(__('PayPal Validation Error: Payment Merchant email received does not match PayPal Gateway settings. (%s)', 'jigoshop-ecommerce'), $posted['business']));
                        $service->save($order);
                        exit;
                    }

                    if ($posted['mc_currency'] != $this->options->get('general.currency')) {
                        // Put this order on-hold for manual checking
                        $order->setStatus(Order\Status::ON_HOLD, sprintf(__('PayPal Validation Error: Payment currency received (%s) does not match Shop currency.', 'jigoshop-ecommerce'), $posted['mc_currency']));
                        $service->save($order);
                        exit;
                    }

                    $order->setStatus(OrderHelper::getStatusAfterCompletePayment($order), __('PayPal payment completed', 'jigoshop-ecommerce'));
                    break;
                case 'denied':
                case 'expired':
                case 'failed':
                case 'voided':
                    // Failed order
                    $order->setStatus(Order\Status::ON_HOLD, sprintf(__('Payment %s via PayPal.', 'jigoshop-ecommerce'), strtolower($posted['payment_status'])));
                    break;
                case 'refunded':
                case 'reversed':
                case 'chargeback':
                    // Refunded order
                    $order->setStatus(Order\Status::REFUNDED, sprintf(__('Payment %s via PayPal.', 'jigoshop-ecommerce'), strtolower($posted['payment_status'])));
                    break;
                default:
                    // No action
                    break;
            }

            $service->save($order);
            if(isset($posted['txn_id'])) {
                $this->wp->updatePostMeta($order->getId(), 'paypal_txn_id', $posted['txn_id']);
            }
        }
    }

    /**
     * @param $posted
     */
    private function processSubscriptionPayment($posted)
    {
        try {
            /** @var SubscriptionService $subscriptionsService */
            $subscriptionsService = $this->di->get('jigoshop.subscriptions.service');
        } catch (Exception $e) {
            Registry::getInstance(\JigoshopInit::getLogger())->addError(__('Subscriptions plugin is not active, cannot handle subscription payment.', 'jigoshop-ecommerce'));

            exit;
        }

        $merchant = $this->settings['test_mode'] ? $this->settings['test_email'] : $this->settings['email'];

        if(isset($posted['custom'], $posted['invoice']) && $posted['custom'] && $posted['invoice']) {
            $subscription = $subscriptionsService->find((int)$posted['custom']);

            if($subscription == null) {
                Registry::getInstance(\JigoshopInit::getLogger())->addError(sprintf(__('There is no subscription with ID(#%s).', 'jigoshop-ecommerce'), $posted['custom']));

                exit;
            }

            if ($subscription->getNumber() !== $posted['invoice']) {
                // Put this order on-hold for manual checking
                $subscription->setStatus(SubscriptionStatus::PENDING, sprintf(__('PayPal Validation Error: Order Invoice Number does NOT match PayPal posted invoice (%s) for Order ID: .', 'jigoshop-ecommerce'), $posted['invoice'], $posted['custom']));
                $subscriptionsService->save($subscription);
                exit;
            }

            // Validate Amount
            if (number_format(($subscription->getItem()->getCost() + $subscription->getItem()->getTax()), $this->decimals, '.', '') != $posted['mc_gross']) {
                // Put this order on-hold for manual checking
                $subscription->setStatus(SubscriptionStatus::PENDING, sprintf(__('PayPal Validation Error: Payment amounts do not match initial order (gross %s).', 'jigoshop-ecommerce'), $posted['mc_gross']));
                $subscriptionsService->save($subscription);
                exit;
            }

            if (strcasecmp(trim($posted['business']), trim($merchant)) != 0) {
                // Put this order on-hold for manual checking
                $subscription->setStatus(SubscriptionStatus::PENDING, sprintf(__('PayPal Validation Error: Payment Merchant email received does not match PayPal Gateway settings. (%s)', 'jigoshop-ecommerce'), $posted['business']));
                $subscriptionsService->save($subscription);
                exit;
            }

            if ($posted['mc_currency'] != $subscription->getCurrency()) {
                // Put this order on-hold for manual checking
                $subscription->setStatus(SubscriptionStatus::PENDING, sprintf(__('PayPal Validation Error: Payment currency received (%s) does not match Shop currency.', 'jigoshop-ecommerce'), $posted['mc_currency']));
                $subscriptionsService->save($subscription);
                exit;
            }

            if($posted['txn_type'] == 'subscr_payment') {
                /** @var \Jigoshop\Service\OrderService $orderService */
                $orderService = $this->di->get('jigoshop.service.order');
                // We are here so lets check status and do actions
                switch (strtolower($posted['payment_status'])) {
                    case 'completed':
                        $order = $subscriptionsService->createOrder($subscription);
                        $order->setPaymentMethod($this);
                        $order->setStatus(Order\Status::COMPLETED,
                            sprintf(__('PayPal payment completed, with txn_id (%s)', 'jigoshop-ecommerce'),
                                $posted['txn_id']));
                        $orderService->save($order);
                        $this->wp->updatePostMeta($order->getId(), 'paypal_txn_id', $posted['txn_id']);

                        $subscription->setStatus(SubscriptionStatus::ACTIVE,
                            sprintf(__('PayPal payment completed, with txn_id (%s)', 'jigoshop-ecommerce'),
                                $posted['txn_id']));
                        $subscriptionsService->save($subscription);
                        break;
                }
            } elseif ($posted['txn_type'] == 'subscr_cancel') {
                $subscription->setStatus(SubscriptionStatus::CANCELLED, __('Subscription was cancelled by a customer.', 'jigoshop-ecommerce'));
                $subscriptionsService->save($subscription);
            } elseif ($posted['txn_type'] == 'subscr_eot') {
                $subscription->setStatus(SubscriptionStatus::FINISHED, __('Subscription has expired.', 'jigoshop-ecommerce'));
                $subscriptionsService->save($subscription);
            }
        }
    }

    /**
     * @param $posted
     */
    private function refundPayment($posted)
    {
        /** @var \Jigoshop\Service\OrderService $orderService */
        $orderService = $this->di->get('jigoshop.service.order');
        if(isset($posted['custom'])) {
            $order = $orderService->find($posted['custom']);
            if($order == null) {
                $wpdb = $this->wp->getWPDB();
                $id = $wpdb->get_var(sprinf("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'paypal_txn_id' AND meta_value = '%s'", $posted['parent_txn_id']));
                if($id) {
                    $order = $orderService->find($id);
                }
            }

            if($order instanceof Order) {
                $order->setStatus(Order\Status::REFUNDED,
                    sprintf(__('Payment %s via PayPal.', 'jigoshop-ecommerce'), strtolower($posted['payment_status'])));
                $orderService->save($order);
            }
            exit;
        }
    }
}
