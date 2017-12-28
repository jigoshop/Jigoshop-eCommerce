<?php

namespace Jigoshop\Admin\Reports\Table;

use Jigoshop\Admin\Reports\TableInterface;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

class CustomerList implements TableInterface
{
	const SLUG = 'customer_list';
	private $wp;
	private $options;
	private $orderService;
	private $totalItems;
	private $activePageNumber;
	private $totalPages;
	private $items = [];
	private $columns = [];
	private $slugCsv = 'export_csv';
	private $csvExportStart = false;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
		if(isset($_GET['action']) && $_GET['action'] == $this->slugCsv)
		{
			$this->csvExportStart = true;
			$this->exportCsv();
		}
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTitle()
	{
		return __('Customer List', 'jigoshop-ecommerce');
	}

	public function getColumns()
	{
		if (!empty($this->columns)) {
			return $this->columns;
		}
		$this->columns = [
			'customer_name' => [
				'name' => __('Name (Last, First)', 'jigoshop-ecommerce'),
				'size' => 2
            ],
			'username' => [
				'name' => __('Username', 'jigoshop-ecommerce'),
				'size' => 1
            ],
			'email' => [
				'name' => __('Email', 'jigoshop-ecommerce'),
				'size' => 2
            ],
			'location' => [
				'name' => __('Location', 'jigoshop-ecommerce'),
				'size' => 2
            ],
			'orders' => [
				'name' => __('Orders', 'jigoshop-ecommerce'),
				'size' => 1
            ],
			'spent' => [
				'name' => __('Money Spent', 'jigoshop-ecommerce'),
				'size' => 1
            ],
			'last_order' => [
				'name' => __('Last order', 'jigoshop-ecommerce'),
				'size' => 2
            ],
			'user_actions' => [
				'name' => __('Actions', 'jigoshop-ecommerce'),
				'size' => 1
            ]
        ];

		return $this->wp->applyFilters('jigoshop\admin\reports\table\customer_list\columns', $this->columns);
	}

	public function getSearch()
	{
		return isset($_GET['search']) ? $_GET['search'] : '';
	}

	public function getItems($columns)
	{
		$users = $this->getUsers();
		foreach ($users as $user) {
			$item = [];
			foreach ($columns as $columnKey => $columnName) {
				$item[$columnKey] = $this->getRow($user, $columnKey);
			}
			$this->items[] = $item;
		}

		return $this->items;
	}

	public function noItems()
	{
		return __('No customers found.', 'jigoshop-ecommerce');
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
			'search_title' => __('Search Customers', 'jigoshop-ecommerce'),
			'search' => $this->getSearch(),
			'csv_download_link' => $this->getDownloadLink(),
        ]);
	}

	private function getUsers()
	{
		$adminUsers = new \WP_User_Query(
			[
				'role' => 'administrator',
				'fields' => 'ID'
            ]
		);

		$managerUsers = new \WP_User_Query(
			[
				'role' => 'shop_manager',
				'fields' => 'ID'
            ]
		);

		$query = new \WP_User_Query([
			'exclude' => array_merge($adminUsers->get_results(), $managerUsers->get_results()),
			'number' => $this->csvExportStart ? 0 : 20,
			'offset' => $this->csvExportStart ? 0 : ($this->getCurrentPage() - 1) * 20,
			'search' => '*'.$this->getSearch().'*'
        ]);

		$this->totalItems = $query->get_total();
		$this->totalPages = ceil($query->get_total() / 20);

		return $query->get_results();
	}

	private function getRow($user, $columnKey)
	{
		switch ($columnKey) {
			case 'customer_name' :
				return ($user->last_name && $user->first_name) ? $user->last_name.', '.$user->first_name : '-';
            case 'customer_first_name' :
                return $user->first_name;
            case 'customer_last_name' :
                return $user->last_name;
			case 'username' :
				return $user->user_login;
			case 'location' :
				$stateCode = $this->wp->getUserMeta($user->ID, 'billing_state', true);
				$countryCode = $this->wp->getUserMeta($user->ID, 'billing_country', true);

				$state = Country::hasState($countryCode, $stateCode) ? Country::getStateName($countryCode, $stateCode) : $stateCode;
				$country = Country::exists($countryCode) ? Country::getName($countryCode) : $countryCode;

				$value = '';
				if ($state) {
					$value .= $state.', ';
				}

				$value .= $country;

				if ($value) {
					return $value;
				} else {
					return '-';
				}
			case 'email' :
				return '<a href="mailto:'.$user->user_email.'">'.$user->user_email.'</a>';
			case 'spent' :
				return Product::formatPrice($this->getCustomerTotalSpent($user->ID));
			case 'orders' :
				return $this->getCustomerOrderCount($user->ID);
			case 'last_order' :
				$lastOrder = $this->getCustomerLastOrder($user->ID);
				if($lastOrder){
					/** @var \Jigoshop\Entity\Order $order */
					$order = $this->orderService->find($lastOrder->order_id);
					return '<a href="'.admin_url('post.php?post='.$lastOrder->order_id.'&action=edit').'">#'.$order->getNumber().'</a> &ndash; '.date_i18n(get_option('date_format'), strtotime($lastOrder->order_date));
				}
				return '-';
			case 'user_actions' :
				$actions = [];
				$actions['edit'] = [
					'url' => admin_url('user-edit.php?user_id='.$user->ID),
					'name' => __('Edit', 'jigoshop-ecommerce'),
					'action' => 'edit'
                ];
				$actions = $this->wp->applyFilters('jigoshop\admin\reports\table\customer_list\user_actions', $actions, $user);

				return $actions;
			default:
				return $this->wp->applyFilters('jigoshop\admin\reports\table\customer_list\row', '', $user, $columnKey);
		}
	}

	private function getCurrentPage()
	{
		$this->activePageNumber = 1;
		if (isset($_GET['paged']) && !empty($_GET['paged'])) {
			$this->activePageNumber = $_GET['paged'];
		}

		return $this->activePageNumber;
	}

	private function getCustomerTotalSpent($customerId)
	{
		$wpdb = $this->wp->getWPDB();

		return $wpdb->get_var($wpdb->prepare("SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d)", 'total', 'customer_id', $customerId));
	}

	private function getCustomerOrderCount($customerId)
	{
		$wpdb = $this->wp->getWPDB();

		return $wpdb->get_var($wpdb->prepare("SELECT COUNT(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d", 'customer_id', $customerId));
	}

	private function getCustomerLastOrder($customerId)
	{
		$wpdb = $this->wp->getWPDB();

		return $wpdb->get_row($wpdb->prepare("SELECT posts.ID AS order_id, posts.post_date AS order_date FROM {$wpdb->posts} AS posts LEFT JOIN {$wpdb->postmeta} AS meta ON meta.post_id = posts.ID AND meta.meta_key = %s WHERE meta.meta_value = %d ORDER BY posts.post_date DESC LIMIT 1", 'customer_id', $customerId));
	}

	private function getDownloadLink()
	{
		return add_query_arg([
			'page'   => $_GET['page'],
			'tab'    => $_GET['tab'],
			'type'   => $this->getSlug(),
			'action' => $this->slugCsv,
        ], '');
	}

	private function exportCsv()
	{
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=report_customer_list.csv');

		$csvSource = fopen('php://output', 'w');

		fputcsv($csvSource, $this->getCsvColumns());

		foreach ($this->getItems($this->getCsvColumns()) as $row)
		{
            $row = array_map(function($item) {
                $item = strip_tags($item);
                $item = html_entity_decode($item);

                return $item;
            }, $row);
			fputcsv($csvSource, $row);
		}

		exit;
	}

	private function getCsvColumns()
	{
		return [
			'username'   => __('Username', 'jigoshop-ecommerce'),
            'customer_first_name' => __('First Name', 'jigoshop-ecommerce'),
            'customer_last_name' => __('Last Name', 'jigoshop-ecommerce'),
			'email'      => __('Email', 'jigoshop-ecommerce'),
			'orders'     => __('Orders', 'jigoshop-ecommerce'),
			'spent'      => __('Money Spent', 'jigoshop-ecommerce'),
			'last_order' => __('Last order', 'jigoshop-ecommerce'),
        ];
	}
}
