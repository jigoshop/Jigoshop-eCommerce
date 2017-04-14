<?php

namespace Jigoshop\Payment;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Order;
use Jigoshop\Exception;
use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

class OnDelivery implements Method
{
	const ID = 'on_delivery';

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
		return $this->wp->isAdmin() ? __('On delivery', 'jigoshop') : $this->options['title'];
	}

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		return $this->options['enabled'];
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
				'checked' => $this->options['enabled'],
				'classes' => ['switch-medium'],
            ],
			[
				'name' => sprintf('[%s][title]', self::ID),
				'title' => __('Title', 'jigoshop'),
				'type' => 'text',
				'value' => $this->options['title'],
            ],
			[
				'name' => sprintf('[%s][description]', self::ID),
				'title' => __('Description', 'jigoshop'),
				'tip' => sprintf(__('Allowed HTML tags are: %s', 'jigoshop'), '<p>, <a>, <strong>, <em>, <b>, <i>'),
				'type' => 'text',
				'value' => $this->options['description'],
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

		return $settings;
	}

	/**
	 * Renders method fields and data in Checkout page.
	 */
	public function render()
	{
		echo $this->options['description'];
	}

	/**
	 * @param Order $order Order to process payment for.
	 *
	 * @return string URL to redirect to.
	 * @throws Exception On any payment error.
	 */
	public function process($order)
	{
		$order->setStatus(Order\Status::PROCESSING, __('Payment on delivery.', 'jigoshop'));
        $this->orderService->save($order);

		return '';
	}
}
