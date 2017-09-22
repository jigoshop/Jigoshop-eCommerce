<?php
namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Entity\Order\Status;

class OrderStatusFilter implements WidgetInterface
{
	const SLUG = 'order_statuses';
	private $orderStatus = [];

	public function __construct($orderStatus)
	{
		$this->orderStatus = $orderStatus;
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTitle()
	{
		return __('Order Status Filter', 'jigoshop-ecommerce');
	}

	public function getArgs()
	{
		return [
			'id' => 'order_status',
			'name' => 'order_status',
			'value' => $this->orderStatus,
			'multiple' => true,
			'classes' => [],
			'options' => Status::getStatuses(),
			'size' => 14,
        ];
	}

	public function isVisible()
	{
		return false;
	}
	
	public function display()
	{
		Forms::select($this->getArgs());
	}
}