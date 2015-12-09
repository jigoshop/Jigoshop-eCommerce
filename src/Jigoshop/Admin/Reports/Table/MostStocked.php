<?php

namespace Jigoshop\Admin\Reports\Table;

use Jigoshop\Admin\Reports\TableInterface;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

class MostStocked implements TableInterface
{
	const SLUG = 'most_stocked';
	private $wp;
	private $totalItems;
	private $activePageNumber;
	private $totalPages;
	private $items = array();
	private $columns = array();

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
		//DEBUG
		\WpDebugBar\Debugger::init($wp);
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTite()
	{
		return __('Most Stocked', 'jigoshop');
	}

	public function getColumns()
	{
		if (!empty($this->columns)) {
			return $this->columns;
		}
		$this->columns = array(
			'product' => array(
				'name' => __('Product', 'jigoshop'),
				'size' => 4
			),
			'parent' => array(
				'name' => __('Parent', 'jigoshop'),
				'size' => 4
			),
			'units_in_stock' => array(
				'name' => __('Units in stock', 'jigoshop'),
				'size' => 1
			),
			'stock_status' => array(
				'name' => __('Stock status', 'jigoshop'),
				'size' => 1
			),
			'user_actions' => array(
				'name' => __('Actions', 'jigoshop'),
				'size' => 2
			)
		);

		return $this->wp->applyFilters('jigoshop/admin/reports/table/most_stocked/columns', $this->columns);
	}

	public function getSearch()
	{
		return isset($_GET['search']) ? $_GET['search'] : '';
	}

	public function getItems()
	{
		$products = $this->getProducts();
		foreach ($products as $product) {
			$item = array();
			foreach ($this->getColumns() as $columnKey => $columnName) {
				$item[$columnKey] = $this->getRow($product, $columnKey);
			}
			$this->items[] = $item;
		}

		return $this->items;
	}

	public function noItems()
	{
		return __('No products found.', 'jigoshop');
	}

	public function display()
	{
		Render::output('admin/reports/table', array(
			'columns' => $this->getColumns(),
			'items' => $this->getItems(),
			'no_items' => $this->noItems(),
			'total_items' => $this->totalItems,
			'total_pages' => $this->totalPages,
			'active_page' => $this->activePageNumber,
			'search_title' => __('Search Products'),
			'search' => $this->getSearch(),
		));
	}

	private function getProducts()
	{
		$wpdb = $this->wp->getWPDB();

		$this->totalItems = $wpdb->get_var($wpdb->prepare("SELECT COUNT(posts.ID) FROM {$wpdb->posts} AS posts
													LEFT JOIN {$wpdb->postmeta} AS stock_manage ON posts.ID = stock_manage.post_id AND stock_manage.meta_key = 'stock_manage'
													LEFT JOIN {$wpdb->postmeta} AS stock_stock ON posts.ID = stock_stock.post_id AND stock_stock.meta_key = 'stock_stock'
													WHERE stock_manage.meta_value = %d AND stock_stock.meta_value > %d", 1, 0));

		$this->totalPages = ceil($this->totalItems / 20);

		$products = $wpdb->get_results($wpdb->prepare("SELECT posts.ID AS id, posts.post_parent AS parent, stock_stock.meta_value AS stock FROM {$wpdb->posts} AS posts
													LEFT JOIN {$wpdb->postmeta} AS stock_manage ON posts.ID = stock_manage.post_id AND stock_manage.meta_key = 'stock_manage'
													LEFT JOIN {$wpdb->postmeta} AS stock_stock ON posts.ID = stock_stock.post_id AND stock_stock.meta_key = 'stock_stock'
													WHERE stock_manage.meta_value = %d AND stock_stock.meta_value > %d
													ORDER BY stock_stock.meta_value DESC
													LIMIT 20 OFFSET %d", 1, 0, ($this->getCurrentPage() - 1) * 20));

		\WpDebugBar\Debugger::getInstance()->getDebugBar()['messages']->addMessage($products);

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
				$actions = array();
				$action_id = $item->parent != 0 ? $item->parent : $item->id;

				$actions['edit'] = array(
					'url' => admin_url('post.php?post='.$action_id.'&action=edit'),
					'name' => __('Edit', 'jigoshop'),
					'action' => "edit"
				);

				if (!$this->isProductHidden($action_id)) {
					$actions['view'] = array(
							'url' => get_permalink($action_id),
							'name' => __('View', 'jigoshop'),
							'action' => "view"
					);
				}
				$actions = $this->wp->applyFilters('jigoshop/admin/reports/table/most_stocked/user_actions', $actions, $item);

				return $actions;
			default:
				return $this->wp->applyFilters('jigoshop/admin/reports/table/most_stocked/row', '', $item, $columnKey);
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