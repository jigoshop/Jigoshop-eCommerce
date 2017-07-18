<?php
namespace Jigoshop\Service\Product;

use WPAL\Wordpress;

class CategoryService implements CategoryServiceInterface {
	private $wp;

	public function __construct(Wordpress $wp) {
		$this->wp = $wp;
	}
}