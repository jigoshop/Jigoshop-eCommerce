<?php
namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Reports;
use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Admin\Helper\Forms;

class ProductSearch implements WidgetInterface
{
	const SLUG = 'product_search';

	public function __construct()
	{
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTitle()
	{
		return __('Search for Products', 'jigoshop');
	}

	public function getArgs()
	{
		$productIds = array();
		if (isset($_GET['product_ids']) && is_array($_GET['product_ids'])) {
			$productIds = array_filter(array_map('absint', $_GET['product_ids']));
		} elseif (isset($_GET['product_ids'])) {
			$productIds = array($_GET['product_ids']);
		}

		return array(
			'id' => 'jigoshop_find_products',
			'name' => 'product_ids',
			'value' => join(',', $productIds),
			'size' => 14,
		);
	}

	public function display()
	{
		Forms::text($this->getArgs());
	}
}