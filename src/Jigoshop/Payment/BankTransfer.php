<?php

namespace Jigoshop\Payment;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Order;
use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

class BankTransfer implements Method2
{
	const ID = 'bank_transfer';

	/** @var Wordpress */
	private $wp;
	/** @var array */
	private $options;
    /** @var OrderServiceInterface */
    private $orderService;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService)
	{
		$this->wp = $wp;
		$this->options = $options->get('payment.' . self::ID);
        $this->orderService = $orderService;
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
		return $this->wp->isAdmin() ? __('Bank Transfer', 'jigoshop-ecommerce') : $this->options['title'];
	}

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		return $this->options['enabled'];
	}

	public function isActive() {
		if(isset($this->options['enabled'])) {
			return $this->options['enabled'];
		}
	}

	public function setActive($state) {
		if(is_array($this->options)) {
			$this->options['enabled'] = $state;
		}

		return $this->options;
	}

	public function isConfigured() {
		return true;
	}

	public function hasTestMode() {
		return false;
	}

	public function isTestModeEnabled() {
		return false;
	}

	public function setTestMode($state) {
		return $this->options;
	}

	/**
	 * @return array List of options to display on Payment settings page.
	 */
	public function getOptions()
	{
		return [
			[
				'name'    => sprintf('[%s][enabled]', self::ID),
				'title'   => __('Is enabled?', 'jigoshop-ecommerce'),
				'type'    => 'checkbox',
				'checked' => $this->options['enabled'],
				'classes' => ['switch-medium'],
            ],
			[
				'name'  => sprintf('[%s][title]', self::ID),
				'title' => __('Title', 'jigoshop-ecommerce'),
				'type'  => 'text',
				'value' => $this->options['title'],
            ],
			[
				'name'  => sprintf('[%s][description]', self::ID),
				'title' => __('Description', 'jigoshop-ecommerce'),
				'tip'   => sprintf(__('Allowed HTML tags are: %s', 'jigoshop-ecommerce'), '<p>, <a>, <strong>, <em>, <b>, <i>'),
				'type'  => 'text',
				'value' => $this->options['description'],
            ],
			[
				'name'  => sprintf('[%s][bank_name]', self::ID),
				'title' => __('Bank Name', 'jigoshop-ecommerce'),
				'type'  => 'text',
				'value' => $this->options['bank_name'],
            ],
			[
				'name'  => sprintf('[%s][account_number]', self::ID),
				'title' => __('Account Number', 'jigoshop-ecommerce'),
				'type'  => 'text',
				'value' => $this->options['account_number'],
            ],
			[
				'name'  => sprintf('[%s][account_holder]', self::ID),
				'title' => __('Account Holder', 'jigoshop-ecommerce'),
				'tip'   => __('The account name your account is registered to.', 'jigoshop-ecommerce'),
				'type'  => 'text',
				'value' => $this->options['account_holder'],
            ],
            [
                'name'  => sprintf('[%s][sort_code]', self::ID),
                'title' => __('Sort Code', 'jigoshop-ecommerce'),
                'tip'   => __('Your branch Sort Code.','jigoshop'),
                'type'  => 'text',
                'value' => $this->options['sort_code'],
            ],
            [
                'name'  => sprintf('[%s][iban]', self::ID),
                'title' => __('IBAN', 'jigoshop-ecommerce'),
                'tip'   => __('Your IBAN number. (for International transfers)','jigoshop'),
                'type'  => 'text',
                'value' => $this->options['iban'],
            ],
            [
                'name'  => sprintf('[%s][bic]', self::ID),
                'title' => __('BIC Code', 'jigoshop-ecommerce'),
                'tip'   => __('Your Branch Identification Code. (BIC Number)','jigoshop'),
                'type'  => 'text',
                'value' => $this->options['bic'],
            ],
			[
				'name'  => sprintf('[%s][additional_info]', self::ID),
				'title' => __('Additional Info', 'jigoshop-ecommerce'),
				'tip'   => sprintf(__('Additional information you want to display to your customer. Allowed HTML tags are: %s', 'jigoshop-ecommerce'), '<p>, <a>, <strong>, <em>, <b>, <i>'),
				'type'  => 'textarea',
				'value' => $this->options['additional_info'],
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

		$settings['bank_name'] = trim(htmlspecialchars(strip_tags($settings['bank_name'])));
		$settings['account_number'] = trim(htmlspecialchars(strip_tags($settings['account_number'])));
		$settings['account_holder'] = trim(htmlspecialchars(strip_tags($settings['account_holder'])));
        $settings['sort_code'] = trim(htmlspecialchars(strip_tags($settings['sort_code'])));
        $settings['iban'] = trim(htmlspecialchars(strip_tags($settings['iban'])));
		$settings['bic'] = trim(htmlspecialchars(strip_tags($settings['bic'])));
		$settings['additional_info'] = trim(htmlspecialchars(strip_tags($settings['additional_info'], '<p><a><strong><em><b><i>')));

		return $settings;
	}

	/**
	 * Renders method fields and data in Checkout page.
	 */
	public function render()
	{
		$bank_info = '';
		if ($this->options['description']) $bank_info .= '<strong>'.__('Description', 'jigoshop-ecommerce').'</strong>: ' . wptexturize($this->options['description']) . '<br />';
		if ($this->options['bank_name']) $bank_info .= '<strong>'.__('Bank Name', 'jigoshop-ecommerce').'</strong>: ' . wptexturize($this->options['bank_name']) . '<br />';
		if ($this->options['account_number']) $bank_info .= '<strong>'.__('Account Number', 'jigoshop-ecommerce').'</strong>: '.wptexturize($this->options['account_number']) . '<br />';
		if ($this->options['account_holder']) $bank_info .= '<strong>'.__('Account Holder', 'jigoshop-ecommerce').'</strong>: '.wptexturize($this->options['account_holder']) . '<br />';
		if ($this->options['sort_code']) $bank_info .= '<strong>'.__('Sort Code', 'jigoshop-ecommerce').'</strong>: '. wptexturize($this->options['sort_code']) . '<br />';
		if ($this->options['iban']) $bank_info .= '<strong>'.__('IBAN', 'jigoshop-ecommerce').'</strong>: '. wptexturize($this->options['iban']) . '<br />';
		if ($this->options['bic']) $bank_info .= '<strong>'.__('BIC Code', 'jigoshop-ecommerce').'</strong>: '. wptexturize($this->options['bic']) . '<br />';
		if ($this->options['additional_info']) $bank_info .= '<strong>'.__('Additional Info', 'jigoshop-ecommerce').'</strong>: '. wptexturize($this->options['additional_info']) . '<br />';

		echo $bank_info;
	}

	/**
	 * @param Order $order Order to process payment for.
	 *
	 * @return string URL to redirect to.
	 * @throws Exception On any payment error.
	 */
	public function process($order)
	{
		$order->setStatus(Order\Status::ON_HOLD, __('Waiting for the confirmation of the bank transfer.', 'jigoshop-ecommerce'));
        $this->orderService->save($order);

		return '';
	}
}
