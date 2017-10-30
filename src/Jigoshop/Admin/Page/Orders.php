<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order as Entity;
use Jigoshop\Helper\Order as OrderHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Helper\Tax;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

class Orders
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Service\OrderServiceInterface */
	private $orderService;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;

		$wp->addFilter('request', [$this, 'request']);
		$wp->addFilter('the_title', [$this, 'replaceTitle']);
		$wp->addFilter('post_row_actions', [$this, 'displayTitle']);
		$wp->addFilter(sprintf('bulk_actions-edit-%s', Types::ORDER), [$this, 'bulkActions']);
		$wp->addFilter(sprintf('views_edit-%s', Types::ORDER), [$this, 'statusFilters']);
		$wp->addFilter(sprintf('manage_edit-%s_columns', Types::ORDER), [$this, 'columns']);
		$wp->addAction(sprintf('manage_%s_posts_custom_column', Types::ORDER), [$this, 'displayColumn'], 2);
		$wp->addAction(sprintf('manage_edit-%s_sortable_columns', Types::ORDER), [$this, 'disableSorting']);

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			if ($wp->getPostType() == Types::ORDER) {
				Styles::add('jigoshop.admin.orders', \JigoshopInit::getUrl().'/assets/css/admin/orders.css');
			}
		});

		Scripts::add('jigoshop.admin.page.orders_list', \JigoshopInit::getUrl().'/assets/js/admin/orders.js', ['jquery-blockui']);
		$wp->addAction('wp_ajax_jigoshop.admin.orders.change_status', [$this, 'ajaxChangeStatus'], 10, 0);
	}

	public function request($vars)
	{
		if ($this->wp->getPostType() === Types::ORDER) {
			if (!isset($vars['post_status'])) {
				$vars['post_status'] = array_keys(Entity\Status::getStatuses());
			}
		}

		return $vars;
	}

	public function columns()
	{
		$columns = [
			'cb' => '<input type="checkbox" />',
			'status' => _x('Status', 'order', 'jigoshop-ecommerce'),
			'title' => _x('Order', 'order', 'jigoshop-ecommerce'),
			'customer' => _x('Customer', 'order', 'jigoshop-ecommerce'),
			'billing_address' => _x('Billing address', 'order', 'jigoshop-ecommerce'),
			'shipping_address' => _x('Shipping address', 'order', 'jigoshop-ecommerce'),
			'shipping_payment' => _x('Shipping &amp; Payment', 'order', 'jigoshop-ecommerce'),
			'total' => _x('Total', 'order', 'jigoshop-ecommerce'),
			'products' => _x('Products', 'order', 'jigoshop-ecommerce'),
        ];

		return $columns;
	}

	public function displayColumn($column)
	{
		$post = $this->wp->getGlobalPost();
		if ($post === null) {
			return;
		}

		/** @var Entity $order */
		$order = $this->orderService->findForPost($post);
		switch ($column) {
			case 'status':
				OrderHelper::renderStatus($order);
				break;
			case 'customer':
				echo OrderHelper::getUserLink($order->getCustomer());
				break;
			case 'billing_address':
				Render::output('admin/orders/billingAddress', [
					'order' => $order,
                ]);
				break;
			case 'shipping_address':
				Render::output('admin/orders/shippingAddress', [
					'order' => $order,
                ]);
				break;
			case 'shipping_payment':
				Render::output('admin/orders/shippingPayment', [
					'order' => $order,
                ]);
				break;
			case 'total':
				Render::output('admin/orders/totals', [
					'order' => $order,
					'getTaxLabel' => function ($taxClass) use ($order){
						return Tax::getLabel($taxClass, $order);
					},
                ]);
				break;
			case 'products':
				$wpdb = $this->wp->getWPDB();
				$products = $wpdb->get_results("SELECT product_id, title FROM " . $wpdb->prefix . "jigoshop_order_item WHERE order_id = " . $order->getId());

				Render::output('admin/orders/products', [
					'products' => $products,
                ]);
			break;
		}
	}

	/**
	 * Disable all sortings.
	 * @param $sortableColumns
	 *
	 * @return array
	 */
	public function disableSorting($sortableColumns)
	{
		return [];
	}

	/**
	 * Replace order title.
	 * @param $title
	 *
	 * @return mixed
	 */
	public function replaceTitle($title)
	{
		$post = $this->wp->getGlobalPost();
		
		if ($post->post_type == Types::ORDER) {
			/** @var Entity $order */
			$order = $this->orderService->findForPost($post);
			$title = sprintf(__('Order #%s', 'jigoshop-ecommerce'), $order->getNumber());
		}

		return $title;
	}

	public function displayTitle($actions)
	{
		$post = $this->wp->getGlobalPost();

		// Remove "Quick edit" as we won't use it.
		unset($actions['inline hide-if-no-js']);

		if ($post->post_type == Types::ORDER) {
			$fullFormat = _x('Y/m/d g:i:s A', 'time', 'jigoshop-ecommerce');
			$format = _x('Y/m/d', 'time', 'jigoshop-ecommerce');
			$fullDate = $this->wp->getHelpers()->mysql2date($fullFormat, $post->post_date);
			$date = $this->wp->getHelpers()->mysql2date($format, $post->post_date);
			echo '<time title="'.$fullDate.'">'.$this->wp->applyFilters('post_date_column_time', $date, $post).'</time>';
		}

		return $actions;
	}

	public function bulkActions($actions)
	{
		unset($actions['edit']);

		return $actions;
	}

	public function statusFilters($views)
	{
		$current = (isset($_GET['post_status']) && Entity\Status::exists($_GET['post_status'])) ? $_GET['post_status'] : '';
		$statuses = Entity\Status::getStatuses();
		$counts = $this->wp->wpCountPosts(Types::ORDER, 'readable');

		$dates = isset($_GET['m']) ? '&amp;m='.$_GET['m'] : '';
		foreach ($statuses as $status => $label) {
			$count = isset($counts->$status) ? $counts->$status : 0;
			$views[$status] = '<a class="'.$status.($current == $status ? ' current' : '').'" href="?post_type='.Types::ORDER.'&amp;post_status='.$status.$dates.'">'.$label.' <span class="count">('.$count.')</a>';
		}

		if (!empty($current)) {
			$views['all'] = str_replace('current', '', $views['all']);
		}

		unset($views['publish']);

		if (isset($views['trash'])) {
			$trash = $views['trash'];
			unset($views['draft']);
			unset($views['trash']);
			$views['trash'] = $trash;
		}

		return $views;
	}

	/**
	 * Change order status.
	 */
	public function ajaxChangeStatus()
	{
		try {
			$status = trim($_POST['status']);
			if (empty($status)) {
				throw new \Exception('Empty status');
			}

			$orderId = (int)$_POST['orderId'];

			if ($orderId < 1) {
				throw new \Exception('Bad order id');
			}

			/** @var Entity $order */
			$order = $this->orderService->find($orderId);

			if($this->isAvailableChange($order->getStatus(), $status)) {
				$order->setStatus($status);
				$this->orderService->save($order);
				ob_start();
				OrderHelper::renderStatus($order);
				$html = ob_get_clean();
				echo json_encode(['success' => true, 'html' => $html]);
			} else {
				throw new \Exception('Not possible');
			}
		} catch (\Exception $e) {
			echo json_encode(['status' => false, 'error' => $e->getMessage()]);
		}
		exit;
	}

	/**
	 * Is the current status may be changed to the selected status
	 *
	 * @param string $from Status to change from
	 * @param string $to Status to change to
	 *
	 * @return bool
	 */
	public function isAvailableChange($from, $to)
	{
		$possibilities = [
			Status::PENDING    => [
				Status::PROCESSING => '',
				Status::CANCELLED  => '',
            ],
			Status::PROCESSING => [
				Status::COMPLETED => '',
				Status::CANCELLED => '',
            ],
            Status::ON_HOLD => [
                Status::CANCELLED => '',
            ],
        ];

		if (isset($possibilities[$from][$to]))
		{
			return true;
		}

		return false;
	}
}
