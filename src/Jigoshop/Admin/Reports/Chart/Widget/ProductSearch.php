<?php
namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Reports;
use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Admin\Helper\Forms;

class ProductSearch implements WidgetInterface
{
	const SLUG = 'product_search';
	private $productIds = array();

	public function __construct($productIds)
	{
		$this->productIds = $productIds;
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
		return array(
			'id' => 'jigoshop_find_products',
			'name' => 'product_ids',
			'value' => join(',', $this->productIds),
			'size' => 14,
		);
	}

	public function display()
	{
		Forms::text($this->getArgs());
	}
}