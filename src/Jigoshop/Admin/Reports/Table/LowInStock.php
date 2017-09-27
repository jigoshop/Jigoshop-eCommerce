<?php

namespace Jigoshop\Admin\Reports\Table;

use Jigoshop\Admin\Reports\TableInterface;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

class LowInStock implements TableInterface
{
	const SLUG = 'low_in_stock';
	private $wp;
    private $options;
	private $totalItems;
	private $activePageNumber;
	private $totalPages;
	private $items = [];
	private $columns = [];

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTitle()
	{
		return __('Low In Stock', 'jigoshop-ecommerce');
	}

	public function getColumns()
	{
		if (!empty($this->columns)) {
			return $this->columns;
		}
		$this->columns = [
			'product' => [
				'name' => __('Product', 'jigoshop-ecommerce'),
				'size' => 4
            ],
			'parent' => [
				'name' => __('Parent', 'jigoshop-ecommerce'),
				'size' => 4
            ],
			'units_in_stock' => [
				'name' => __('Units in stock', 'jigoshop-ecommerce'),
				'size' => 2
            ],
			'user_actions' => [
				'name' => __('Actions', 'jigoshop-ecommerce'),
				'size' => 2
            ]
        ];

		return $this->wp->applyFilters('jigoshop\admin\reports\table\low_in_stock\columns', $this->columns);
	}

	public function getSearch()
	{
		return isset($_GET['search']) ? $_GET['search'] : '';
	}

	public function getItems($columns)
	{
		$products = $this->getProducts();
		foreach ($products as $product) {
			$item = [];
			foreach ($columns as $columnKey => $columnName) {
				$item[$columnKey] = $this->getRow($product, $columnKey);
			}
			$this->items[] = $item;
		}

		return $this->items;
	}

	public function noItems()
	{
		return __('No low in stock products found.', 'jigoshop-ecommerce');
	}

	public function display()
	{
		Render::output('admin/reports/table', [
			'columns' => $this->getColumns(),
			'items' => $this->getItems($this->getColumns()),
			'no_items' => $this->noItems(),
			'total_items' => $this->totalItems,
			'total_pages' => $this->totalPages,
			'active_page' => $this->activePageNumber,
			'search_title' => __('Search Products'),
			'search' => $this->getSearch(),
        ]);
	}

	private function getProducts()
	{
		$wpdb = $this->wp->getWPDB();
		$lowStockThreshold = $this->options->get('products.low_stock_threshold');

		$search = '';
		if ($this->getSearch() != '') {
			$search .= 'AND posts.post_title LIKE "%%' . esc_sql($this->getSearch()) . '%%"';
		}

		$this->totalItems = $wpdb->get_var($wpdb->prepare("SELECT COUNT(posts.ID) FROM {$wpdb->posts} AS posts
			LEFT JOIN {$wpdb->postmeta} AS stock_manage ON posts.ID = stock_manage.post_id AND stock_manage.meta_key = 'stock_manage'
			LEFT JOIN {$wpdb->postmeta} AS stock_stock ON posts.ID = stock_stock.post_id AND stock_stock.meta_key = 'stock_stock'
			WHERE posts.post_type IN ('product', 'product_variation') ".$search." AND posts.post_status = 'publish' AND stock_manage.meta_value = 1 AND stock_stock.meta_value > 0 AND stock_stock.meta_value <= %d", $lowStockThreshold));

		$this->totalPages = ceil($this->totalItems/20);

		$products = $wpdb->get_results($wpdb->prepare("SELECT posts.ID AS id, posts.post_parent AS parent, stock_stock.meta_value AS stock FROM {$wpdb->posts} AS posts
			LEFT JOIN {$wpdb->postmeta} AS stock_manage ON posts.ID = stock_manage.post_id AND stock_manage.meta_key = 'stock_manage'
			LEFT JOIN {$wpdb->postmeta} AS stock_stock ON posts.ID = stock_stock.post_id AND stock_stock.meta_key = 'stock_stock'
			WHERE posts.post_type IN ('product', 'product_variation') ".$search." AND posts.post_status = 'publish' AND stock_manage.meta_value = 1 AND stock_stock.meta_value > 0 AND stock_stock.meta_value <= %d LIMIT 20 OFFSET %d", $lowStockThreshold, ($this->getCurrentPage() - 1) * 20));

		return $products;
	}

	private function getRow($item, $columnKey)
	{
		switch ($columnKey) {
			case 'product' :
				return $this->getPostTitle($item->id);
			case 'parent' :
				if ($item->parent > 0) {
					return $this->getPostTitle($item->parent);
				} else {
					return '-';
				}
			case 'units_in_stock' :
				return $item->stock;
			case 'user_actions' :
				$actions = [];
				$action_id = $item->parent != 0 ? $item->parent : $item->id;

				$actions['edit'] = [
					'url' => admin_url('post.php?post='.$action_id.'&action=edit'),
					'name' => __('Edit', 'jigoshop-ecommerce'),
					'action' => "edit"
                ];

				if (!$this->isProductHidden($action_id)) {
					$actions['view'] = [
						'url' => get_permalink($action_id),
						'name' => __('View', 'jigoshop-ecommerce'),
						'action' => "view"
                    ];
				}
				$actions = $this->wp->applyFilters('jigoshop\admin\reports\table\low_in_stock\user_actions', $actions, $item);

				return $actions;
			default:
				return $this->wp->applyFilters('jigoshop\admin\reports\table\low_in_stock\row', '', $item, $columnKey);
		}
	}

	private function getCurrentPage()
	{
		$this->activePageNumber = 1;
		if(isset($_GET['paged']) && !empty($_GET['paged'])) {
			$this->activePageNumber = $_GET['paged'];
		}

		return $this->activePageNumber;
	}

	private function getPostTitle($postId)
	{
		$wpdb = $this->wp->getWPDB();

		return $wpdb->get_var($wpdb->prepare("SELECT post_title FROM {$wpdb->posts} WHERE ID = %d", $postId));
	}

	private function isProductHidden($productId)
	{
		$wpdb = $this->wp->getWPDB();

		return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'visibility' AND meta_value = 0", $productId));
	}
}