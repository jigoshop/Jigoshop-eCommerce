<?php

namespace Jigoshop\Helper;

use Jigoshop\Core\Options as CoreOptions;
use Jigoshop\Endpoint\DownloadFile;
use Jigoshop\Entity\Customer\Guest;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Frontend\Pages;
use Jigoshop\Payment\Method;

class Order
{
	/** @var CoreOptions */
	private static $options;

	/**
	 * @param CoreOptions $options Options object.
	 */
	public static function setOptions($options)
	{
		self::$options = $options;
	}

	/**
	 * Get status slug and name
	 *
	 * @param \Jigoshop\Entity\Order $order
	 *
	 * @return array
	 */
	public static function checkGetStatus(\Jigoshop\Entity\Order $order)
	{
		$statuses = Status::getStatuses();
		$status = $order->getStatus();
		if (!isset($statuses[$status])) {
			$status = Status::PENDING;
		}

		return ['status' => $status, 'text' => $statuses[$status]];
	}

	/**
	 * @param \Jigoshop\Entity\Order $order
	 *
	 * @return string
	 */
	public static function getStatus(\Jigoshop\Entity\Order $order)
	{
		$status = static::checkGetStatus($order);

		return sprintf('<mark class="%s" title="%s">%s</mark>', $status['status'], $status['text'], $status['text']);
	}

	/**
	 * It shows the status of orders and possible options for change.
	 *
	 * @param \Jigoshop\Entity\Order $order
	 */
	public static function renderStatus(\Jigoshop\Entity\Order $order)
	{
		$status = static::checkGetStatus($order);

		Render::output('admin/orders/status', [
			'currentStatusText' => static::getStatus($order),
			'pendingTo'         => $status['status'] == Status::PENDING || $status['status'] == Status::ON_HOLD ? Status::PENDING : '',
			'processingTo'      => $status['status'] == Status::PROCESSING ? Status::PROCESSING : '',
			'hideCancel'      => $status['status'] == Status::COMPLETED ? true : ($status['status'] == Status::CANCELLED ? true : false),
			'orderId'           => $order->getId(),
			'statuses'          => [
				'processing' => Status::PROCESSING,
				'completed'  => Status::COMPLETED,
				'cancelled'  => Status::CANCELLED,
            ],
        ]);
	}

    /**
     * @param \Jigoshop\Entity\Order $order
     *
     * @return string
     */
    public static function getStatusAfterCompletePayment(\Jigoshop\Entity\Order $order)
    {
        if($order->isShippingRequired()) {
            return Status::PROCESSING;
        }

        return Status::COMPLETED;
	}

	public static function getUserLink($customer)
	{
		if ($customer instanceof Guest) {
			return $customer->getName();
		}

		return sprintf('<a href="%s">%s</a>', get_edit_user_link($customer->getId()), $customer->getName());
	}

	/**
	 * @param $order \Jigoshop\Entity\Order
	 *
	 * @return string Cancel order link.
	 */
	public static function getCancelLink($order)
	{
		$args = [
			'action' => 'cancel_order',
			'nonce' => wp_create_nonce('cancel_order'),
			'id' => $order->getId(),
			'key' => $order->getKey(),
        ];
		$url = add_query_arg($args, get_permalink(self::$options->getPageId(Pages::CART)));

		return apply_filters('jigoshop\helper\order\cancel_url', $url);
	}

	/**
	 * @param $key string Item key.
	 *
	 * @return string Link to remove item.
	 */
	public static function getRemoveLink($key)
	{
		return add_query_arg(['action' => 'remove-item', 'item' => $key]);
	}

	/**
	 * @param $order \Jigoshop\Entity\Order Order to generate link for.
     * @param $payment Method
	 *
	 * @return string Payment link.
	 */
	public static function getPayLink($order, $payment = null)
	{
	    $args = [
	        'key' => $order->getKey()
        ];

	    if($payment instanceof Method) {
	        $args['payment'] = $payment->getId();
        }

        $url = add_query_arg($args, Api::getEndpointUrl('pay', $order->getId(), get_permalink(self::$options->getPageId(Pages::CHECKOUT))));

		return apply_filters('jigoshop\helper\order\pay_url', $url);
	}

    /**
     * @param $order \Jigoshop\Entity\Order Order to generate link for.
     *
     * @return string Payment link.
     */
    public static function getThankYouLink($order)
    {
        $args = [
            'order' => $order->getId(),
            'key' => $order->getKey(),
        ];
        $url = add_query_arg($args, get_permalink(self::$options->getPageId(Pages::THANK_YOU)));

        return apply_filters('jigoshop\helper\order\thank_you_url', $url);
    }

    /**
     * @param \Jigoshop\Entity\Order $order
     * @param \Jigoshop\Entity\Order\Item $item
     * @return string
     */
    public static function getItemDownloadLink($order, $item)
    {
        if($item->getMeta('file') && $item->getMeta('downloads')) {
            return add_query_arg(['file' => $order->getKey().'.'.$order->getId().'.'.$item->getKey()], Endpoint::getUrl(DownloadFile::NAME));
        }

        return '';
    }
}
