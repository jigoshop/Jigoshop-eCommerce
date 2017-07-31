<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\ProductCategory;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

class ProductCategories
{
	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp) {
		$wp->wpSafeRedirect('edit.php?post_type=product&page=jigoshop_product_categories');
		exit;
	}
}