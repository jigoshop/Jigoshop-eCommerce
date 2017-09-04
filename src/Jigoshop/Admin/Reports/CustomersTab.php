<?php

namespace Jigoshop\Admin\Reports;

use Jigoshop\Admin\Reports;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

class CustomersTab implements TabInterface
{
	const SLUG = 'customers';

	/** @var  Wordpress */
	private $wp;
	/** @var  Options */
	private $options;
	/** @var  OrderServiceInterface */
	private $orderService;
	private $content;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
		$this->content = $this->getContent();
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Customers', 'jigoshop-ecommerce');
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
	public function display()
	{
		Render::output('admin/reports/customers', [
			'types' => $this->getTypes(),
			'current_type' => $this->getCurrentType(),
			'content' => $this->content
        ]);
	}

	private function getTypes()
	{
		return $this->wp->applyFilters('jigoshop\admin\reports\customers\types', [
			'customers_vs_guests' => __('Customers vs Guests', 'jigoshop-ecommerce'),
			'customer_list' => __('Customer List', 'jigoshop-ecommerce')
        ]);
	}

	private function getCurrentType()
	{
		$type = 'customers_vs_guests';
		if(isset($_GET['type'])) {
			$type = $_GET['type'];
		}

		return $type;
	}

	private function getCurrentRange()
	{
		$range = '30day';
		if((isset($_GET['start_date']) && !empty($_GET['start_date'])) || (isset($_GET['end_date']) && !empty($_GET['end_date']))) {
			$range = 'custom';
		} else if (isset($_GET['range'])) {
			$range = $_GET['range'];
		}

		return $range;
	}

	private function getContent()
	{
		if (!in_array($this->wp->getPageNow(), ['admin.php', 'options.php'])) {
			return null;
		}

		if (!isset($_GET['page']) || $_GET['page'] != Reports::NAME) {
			return null;
		}

		if(!isset($_GET['tab']) || $_GET['tab'] != self::SLUG) {
			return null;
		}

		switch($this->getCurrentType()){
			case 'customers_vs_guests':
				return new Chart\CustomersVsGuests($this->wp, $this->options, $this->getCurrentRange());
			case 'customer_list':
				return new Table\CustomerList($this->wp, $this->options, $this->orderService);
            default:
                return $this->wp->applyFilters('jigoshop\admin\reports\customers\custom', null, $this->getCurrentType());
		}
	}
}