<?php
namespace Jigoshop\Service\Product;

use Jigoshop\Core;
use Jigoshop\Entity\Product\Category as Entity;
use Jigoshop\Factory\Product\Category as Factory;
use WPAL\Wordpress;

class CategoryService implements CategoryServiceInterface {
	private $wp;
	private $factory;

	public function __construct(Wordpress $wp, Factory $factory) {
		$this->wp = $wp;
		$this->factory = $factory;
	}

	public function find($id, $level = 0) {
		$category = $this->factory->fetch($id);
		$category->setLevel($level);
		$category->setChildCategories($this->findFromParent($id, $level + 1));

		$meta = get_metadata(Core::TERMS, $id, 'category_meta', true);
		if(is_array($meta)) {
			$category->fromMeta($meta);
		}

		return $category;		
	}

	public function findAll() {
		$categories = $this->findFromParent(0);
		foreach($categories as $category) {
			$categories = array_merge($categories, $this->findFromParent($category->getId()));
		}

		return $categories;
	}

	public function findFromParent($parentId, $level = 0) {
		$terms = $this->wp->getTerms([
			'taxonomy' => 'product_category',
			'hide_empty' => 0,
			'parent' => $parentId
		]);	

		$categories = [];
		foreach($terms as $term) {
			$categories[] = $this->find($term->term_id, $level);
		}

		return $categories;
	}

	public function save($category) {
		if(!$category instanceof Entity) {
			throw new Exception('Tried to save not a product category.');
		}

		$args = [
			'name' => $category->getName(),
			'slug' => $category->getSlug(),
			'taxonomy' => 'product_category',
			'description' => $category->getDescription(),
			'parent' => $category->getParentId()
		];

		if(!term_exists($category->getId(), 'product_category')) {
			$result = wp_insert_term($category->getName(), 'product_category', $args); 
		}
		else {
			$args['term_id'] = $category->getId();

			$result = wp_update_term($category->getId(), 'product_category', $args);
		}

		if($result instanceof \WP_Error) {
			$errors = [];
			foreach($result->errors as $errorField => $errorValues) {
				$errors = array_merge($errors, $errorValues);
			}

			throw new \Exception(implode('<br />', $errors));
		}

		update_metadata(Core::TERMS, $result['term_id'], 'thumbnail_id', (int)$category->getThumbnailId());
		update_metadata(Core::TERMS, $result['term_id'], 'category_meta', $category->toMeta());
	}

	public function remove($category) {
		if($category instanceof Entity) {
			$category = $category->getId();
		}

		wp_delete_term($category, 'product_category');
	}
}