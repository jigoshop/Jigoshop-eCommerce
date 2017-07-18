<?php
namespace Jigoshop\Factory\Product;

use Jigoshop\Entity\Product\Category as Entity;
use Jigoshop\Factory\EntityFactoryInterface;
use WPAL\Wordpress;

class Category implements EntityFactoryInterface {
	const PRODUCT_CATEGORY = 'jigoshop_product_category';

	private $wp;

	public function __construct(Wordpress $wp) {
		$this->wp = $wp;
	}

	public function create($id) {
		$category = new Entity();

		$category->setId($id);

		if(!empty($_POST)) {
			$category->setLabel(isset($_POST['label'])?$_POST['label']:'');
			$category->setSlug(isset($_POST['slug'])?$_POST['slug']:'');
			$category->setDescription(isset($_POST['description'])?$this->wp->getHelpers()->parsePostBody(stripslashes_deep($_POST['description'])):'');
			$category->setParentId(isset($_POST['parentId'])?$_POST['parentId']:'');
		}

		return $category;
	}

	public function fetch($id) {
		$term = $this->wp->getTerm($id, 'product_category');
		if(!$term instanceof \WP_Term) {
			return null;
		}	

		$category = $this->create($term->term_id);
		$category->setLabel($term->name);
		$category->setSlug($term->slug);
		$category->setDescription($term->description);
		$category->setParentId($term->parent);

		return $category;
	}
}