<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class ProductCategoryList extends AbstractProductList
{
	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, CartServiceInterface $cartService, Messages $messages)
	{
		parent::__construct($wp, $options, $productService, $cartService, $messages);
	}

	public function getTitle()
	{
		$term = $this->wp->getTermBy('slug', $this->wp->getQueryParameter(Types\ProductCategory::NAME), Types\ProductCategory::NAME);

		if ($term) {
			return $this->wp->applyFilters('jigoshop\frontend\page\category_list\title', sprintf(__('Products in category "%s"', 'jigoshop-ecommerce'), $term->name), $term);
		}

		return $this->wp->getQueryParameter(Types\ProductCategory::NAME);
	}

	public function getContent()
	{
		return '';
	}
}
