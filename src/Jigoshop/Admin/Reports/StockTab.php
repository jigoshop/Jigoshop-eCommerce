<?php

namespace Jigoshop\Admin\Reports;

use Jigoshop\Admin\Reports;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

class StockTab implements TabInterface
{
	const SLUG = 'stock';
	/** @var  Wordpress */
	private $wp;
	/** @var  Options */
	private $options;
	private $content;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->content = $this->getContent();
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Stock', 'jigoshop-ecommerce');
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
		Render::output('admin/reports/stock', [
				'types' => $this->getTypes(),
				'current_type' => $this->getCurrentType(),
				'content' => $this->content
        ]);
	}

	private function getTypes()
	{
		return $this->wp->applyFilters('jigoshop\admin\reports\stock\types', [
				'low_in_stock' => __('Low in Stock', 'jigoshop-ecommerce'),
				'out_of_stock' => __('Out of Stock', 'jigoshop-ecommerce'),
				'most_stocked' => __('Most Stocked', 'jigoshop-ecommerce'),
        ]);
	}

	private function getCurrentType()
	{
		$type = 'low_in_stock';
		if(isset($_GET['type'])) {
			$type = $_GET['type'];
		}

		return $type;
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
			case 'low_in_stock':
				return new Table\LowInStock($this->wp, $this->options);
			case 'out_of_stock':
				return new Table\OutOfStock($this->wp, $this->options);
			case 'most_stocked':
				return new Table\MostStocked($this->wp, $this->options);
			default:
				return $this->wp->applyFilters('jigoshop\admin\reports\stock\custom', null, $this->getCurrentType());
		}
	}
}