<?php

namespace Jigoshop\Admin\Reports;

use Jigoshop\Admin\Reports;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Styles;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

class SalesTab implements TabInterface
{
	const SLUG = 'sales';

	/** @var  Wordpress */
	private $wp;
	/** @var  options */
	private $options;
	/** @var  mixed */
	private $chart;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->chart = $this->getChart();
		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), array('admin.php', 'options.php'))) {
				return;
			}
			$screen = $wp->getCurrentScreen();
			if ($screen->base != 'jigoshop_page_'.Reports::NAME) {
				return;
			}
			$wp->wpEnqueueScript('common');
			$wp->wpEnqueueScript('wp-lists');
			$wp->wpEnqueueScript('postbox');
			$wp->wpEnqueueScript('jquery-ui-datepicker');
			Styles::add('jigoshop.jquery.ui', JIGOSHOP_URL.'/assets/css/jquery-ui.css');
		});

	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Sales', 'jigoshop');
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
		Render::output('admin/reports/sales', array(
			'types' => $this->getTypes(),
			'current_type' => $this->getCurrentType(),
			'chart' => $this->chart,
		));
	}

	private function getTypes()
	{

		return $this->wp->applyFilters('jigoshop/admin/reports/sales/types', array(
			'by_date' => __('By Date', 'jigoshop'),
			'by_product' => __('By Product', 'jigoshop'),
			'by_category' => __('By Category', 'jigoshop'),
			'coupons_by_date' => __('Coupons By Date', 'jigoshop')
		));
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
		$type = '30day';
		if(isset($_GET['range'])) {
			$type = $_GET['range'];
		}

		return $type;
	}

	private function getChart()
	{
		switch($this->getCurrentType()){
			case 'by_date':
				return new Chart\ByDate($this->wp, $this->options, $this->getCurrentRange());
			default:
				$this->wp->doAction('jigoshop/admin/reports/sales/custom_chart', $this->getCurrentType(), $this->wp, $this->options);
		}
	}
}