<?php
namespace Jigoshop\Service\Product;

use Jigoshop\Factory\Product\Category as Factory;
use WPAL\Wordpress;

class CategoryService implements CategoryServiceInterface {
	private $wp;
	private $factory;

	public function __construct(Wordpress $wp, Factory $factory) {
		$this->wp = $wp;
		$this->factory = $factory;
	}

	public function find($id) {
		return $this->factory->fetch($id);
	}

	public function findAll() {
		$categories = $this->findFromParent(0);
		foreach($categories as $category) {
			$categories = array_merge($categories, $this->findFromParent($category->getId()));
		}

		return $categories;
	}

	public function findFromParent($parentId) {
		$terms = $this->wp->getTerms([
			'taxonomy' => 'product_category',
			'hide_empty' => 0,
			'parent' => $parentId
		]);	

		$categories = [];
		foreach($terms as $term) {
			$categories[] = $this->find($term->term_id);
		}

		return $categories;
	}
}