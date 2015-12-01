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
	private $items = array();
	private $columns = array();

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTite()
	{
		return __('Customer List', 'jigoshop');
	}

	public function getColumns()
	{
		if (!empty($this->columns)) {
			return $this->columns;
		}
		$this->columns = array(
			'customer_name' => array(
				'name' => __('Name (Last, First)', 'jigoshop'),
				'size' => 2
			),
			'username' => array(
				'name' => __('Username', 'jigoshop'),
				'size' => 1
			),
			'email' => array(
				'name' => __('Email', 'jigoshop'),
				'size' => 1
			),
			'location' => array(
				'name' => __('Location', 'jigoshop'),
				'size' => 2
			),
			'orders' => array(
				'name' => __('Orders', 'jigoshop'),
				'size' => 1
			),
			'spent' => array(
				'name' => __('Money Spent', 'jigoshop'),
				'size' => 1
			),
			'last_order' => array(
				'name' => __('Last order', 'jigoshop'),
				'size' => 2
			),
			'user_actions' => array(
				'name' => __('Actions', 'jigoshop'),
				'size' => 2
			)
		);

		return $this->wp->applyFilters('jigoshop/admin/reports/table/customer_list/columns', $this->columns);
	}

	public function getActions()
	{
		// TODO: Implement getActions() method.
	}

	public function getItems()
	{
		$users = $this->getUsers();
		foreach ($users as $user) {
			$item = array();
			foreach ($this->getColumns() as $columnKey => $columnName) {
				$item[$columnKey] = $this->getRow($user, $columnKey);
			}
			$this->items[] = $item;
		}

		return $this->items;
	}

	public function noItems()
	{
		return __('No customers found.', 'jigoshop');
	}

	public function display()
	{
		Render::output('admin/reports/table', array(
			'columns' => $this->getColumns(),
			'items' => $this->getItems(),
			'no_items' => $this->noItems(),
			'total_items' => $this->totalItems,
			'total_pages' => $this->totalPages,
			'active_page' => $this->activePageNumber
		));
	}

	private function getUsers()
	{
		$adminUsers = new \WP_User_Query(
			array(
				'role' => 'administrator',
				'fields' => 'ID'
			)
		);

		$managerUsers = new \WP_User_Query(
			array(
				'role' => 'shop_manager',
				'fields' => 'ID'
			)
		);

		$query = new \WP_User_Query(array(
			'exclude' => array_merge($adminUsers->get_results(), $managerUsers->get_results()),
			'number' => 20,
			'offset' => ($this->getCurrentPage() - 1) * 20,
		));

		$this->totalItems = $query->get_total();
		$this->totalPages = ceil($query->get_total() / 20);

		return $query->get_results();
	}

	private function getRow($user, $columnKey)
	{
		switch ($columnKey) {
			case 'customer_name' :
				return ($user->last_name && $user->first_name) ? $user->last_name.', '.$user->first_name : '-';
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
				//return Product::formatPrice($this->getCustomerTotalSpent($user->ID));
				return Product::formatPrice(0);
			case 'orders' :
				//return $this->getCustomerOrderCount($user->ID);
				return '0';
			case 'last_order' :
				//TODO czekaj na migracje
				return '-';
			case 'user_actions' :
				$actions = array();

				$actions['refresh'] = array(
					'url' => wp_nonce_url(add_query_arg('refresh', $user->ID), 'refresh'),
					'name' => __('Refresh stats', 'jigoshop'),
					'action' => 'refresh'
				);
				$actions['edit'] = array(
					'url' => admin_url('user-edit.php?user_id='.$user->ID),
					'name' => __('Edit', 'jigoshop'),
					'action' => 'edit'
				);
				$actions = $this->wp->applyFilters('jigoshop/admin/reports/table/customer_list/user_actions', $actions, $user);

				return $actions;
			default:
				return $this->wp->applyFilters('jigoshop/admin/reports/table/customer_list/row', '', $user, $columnKey);
		}
	}

	public function getCustomerTotalSpent($userId)
	{
		$spent = $this->wp->getUserMeta($userId, 'money_spent', true);
		if (!$spent) {
			/** @var $wpdb \wpdb */
			$wpdb = $this->wp->getWPDB();

			//TODO Zmień po skończeniu migracji!!!
			$orders = $this->orderService->findForUser($userId);

			/*$spent = array_sum(array_map(function($order){
				$order = maybe_unserialize($order->meta_value);
				return $order['order_total'];
			}, $orders));*/

			$this->wp->updateUserMeta($userId, 'money_spent', $spent);
		}

		return $spent;
	}

	/**
	 * Get total orders by customer
	 *
	 * @param  int $userId
	 *
	 * @return int
	 */
	private function getCustomerOrderCount($userId)
	{
		$count = $this->wp->getUserMeta($userId, 'money_count', true);
		if (!$count) {
			/** @var $wpdb \wpdb */
			$wpdb = $this->wp->getWPDB();

			//TODO Zmień po skończeniu migracji!!!
			$count = $wpdb->get_var("SELECT COUNT(*)
				FROM $wpdb->posts as posts

				LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
				LEFT JOIN {$wpdb->term_relationships} AS tr ON posts.ID = tr.object_id
				LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				LEFT JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id

				WHERE   meta.meta_key       = 'customer_user'
				AND     posts.post_type     IN ('".implode("','", array('shop_order'))."')
				AND     posts.post_status   IN ('publish')
				AND     meta_value          = $userId
				AND     tt.taxonomy         = 'shop_order_status'
				AND     t.name              IN ( 'processing', 'completed' )
			");

			$this->wp->updateUserMeta($userId, 'money_count', $count);
		}

		return $count;
	}

	private function getCurrentPage($query = null)
	{
		$this->activePageNumber = 1;
		if(isset($_GET['paged']) && !empty($_GET['paged'])) {
			$this->activePageNumber = $_GET['paged'];
		}

		return $this->activePageNumber;
	}
}