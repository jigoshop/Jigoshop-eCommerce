<?php
namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Entity\Order\Status;

class OrderStatusFilter implements WidgetInterface
{
	const SLUG = 'order_statuses';
	private $orderStatus = array();

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
		return __('Order Status Filter', 'jigoshop');
	}

	public function getArgs()
	{
		return array(
			'id' => 'order_status',
			'name' => 'order_status',
			'value' => $this->orderStatus,
			'multiple' => true,
			'classes' => array(),
			'options' => Status::getStatuses(),
			'size' => 14,
		);
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