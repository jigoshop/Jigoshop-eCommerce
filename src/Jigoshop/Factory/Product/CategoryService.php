<?php
namespace Jigoshop\Factory\Product;

use Jigoshop\Core\Options;
use Jigoshop\Service\Product\CategoryService as Service;
use WPAL\Wordpress;

class CategoryService {
	private $wp;
	private $factory;

	public function __construct(Wordpress $wp, Category $factory)
	{
		$this->wp = $wp;
		$this->factory = $factory;
	}

	/**
	 * @return CategoryServiceInterface Product category service.
	 * @since 2.0
	 */
	public function getService()
	{
		/** @var \WPAL\Wordpress $wp */
		$service = new Service($this->wp, $this->factory);

		$service = $this->wp->applyFilters('jigoshop\core\get_product_category_service', $service);

		return $service;
	}	
}