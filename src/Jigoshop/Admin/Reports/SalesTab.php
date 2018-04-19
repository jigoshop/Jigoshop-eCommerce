<?php

namespace Jigoshop\Admin\Reports;

use Jigoshop\Admin\Reports;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use Jigoshop\Integration;
use WPAL\Wordpress;

class SalesTab implements TabInterface
{
	const SLUG = 'sales';

	/** @var  Wordpress */
	private $wp;
	/** @var  options */
	private $options;
    private $chart;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;

        if (in_array($this->wp->getPageNow(), ['admin.php', 'options.php']) &&
            isset($_GET['page']) && $_GET['page'] == Reports::NAME
        ) {
            $this->chart = $this->getChart();
        }
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Sales', 'jigoshop-ecommerce');
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
		Render::output('admin/reports/sales', [
			'types' => $this->getTypes(),
			'current_type' => $this->getCurrentType(),
			'chart' => $this->chart
        ]);
	}

	private function getTypes()
	{
		return $this->wp->applyFilters('jigoshop\admin\reports\sales\types', [
			'by_date' => __('By Date', 'jigoshop-ecommerce'),
			'by_product' => __('By Product', 'jigoshop-ecommerce'),
			'by_category' => __('By Category', 'jigoshop-ecommerce'),
			'discount_summary' => __('Discount Summary', 'jigoshop-ecommerce')
        ]);
	}

	private function getCurrentType()
	{
		$type = 'by_date';
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

	public function getChart()
	{
		switch($this->getCurrentType()){
			case 'by_date':
				return new Chart\ByDate($this->wp, $this->options, $this->getCurrentRange());
			case 'by_product':
				return new Chart\ByProduct($this->wp, $this->options, $this->getCurrentRange());
			case 'by_category':
				return new Chart\ByCategory($this->wp, $this->options, $this->getCurrentRange());
			case 'discount_summary':
				return new Chart\DiscountSummary($this->wp, $this->options, $this->getCurrentRange());
			default:
                return $this->wp->applyFilters('jigoshop\admin\reports\sales\custom', null, $this->getCurrentType());
		}
	}
}