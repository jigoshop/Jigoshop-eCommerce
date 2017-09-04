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

class ProductCategories {
	public function __construct(Wordpress $wp) {
		if(isset($_GET['tag_ID']) && $_GET['tag_ID']) {
			$wp->wpSafeRedirect(sprintf('edit.php?post_type=product&page=jigoshop_product_categories&edit=%s', $_GET['tag_ID']));
		}
		else {
			$wp->wpSafeRedirect('edit.php?post_type=product&page=jigoshop_product_categories');
		}
		
		exit;
	}
}