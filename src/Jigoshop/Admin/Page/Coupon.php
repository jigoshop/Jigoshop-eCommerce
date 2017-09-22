<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Exception;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Payment\Method;
use Jigoshop\Service\CouponServiceInterface as Service;
use Jigoshop\Service\PaymentServiceInterface;
use WPAL\Wordpress;

class Coupon
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Service */
	private $couponService;
	/** @var PaymentServiceInterface */
	private $paymentService;

	public function __construct(Wordpress $wp, Options $options, Service $couponService, PaymentServiceInterface $paymentService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->couponService = $couponService;
		$this->paymentService = $paymentService;

		$that = $this;
		$wp->addAction('wp_ajax_jigoshop.admin.coupon.find_category', [
			$this,
			'ajaxFindCategory'
        ], 10, 0);
		$wp->addAction('add_meta_boxes_'.Types::COUPON, function () use ($wp, $that){
			$wp->addMetaBox('jigoshop-coupon-data', __('Coupon Data', 'jigoshop-ecommerce'), [
				$that,
				'box'
            ], Types::COUPON, 'normal', 'default');
		});

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			if ($wp->getPostType() == Types::COUPON) {
				Styles::add('jigoshop.admin.coupon', \JigoshopInit::getUrl().'/assets/css/admin/coupon.css', ['jigoshop.admin']);
				Scripts::add('jigoshop.admin.coupon', \JigoshopInit::getUrl().'/assets/js/admin/coupon.js', [
					'jquery',
					'jigoshop.admin',
					'jigoshop.helpers.ajax_search',
                ]);
				Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css', ['jigoshop.admin.coupon']);
				Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl() . '/assets/js/vendors/select2.js', ['jigoshop.admin.coupon'], ['in_footer' => true]);
				Styles::add('jigoshop.vendors.datepicker', \JigoshopInit::getUrl().'/assets/css/vendors/datepicker.css', ['jigoshop.admin.coupon']);
				Scripts::add('jigoshop.vendors.datepicker', \JigoshopInit::getUrl() . '/assets/js/vendors/datepicker.js', ['jquery', 'jigoshop.admin.coupon']);
				$wp->doAction('jigoshop\admin\coupon\assets', $wp);
			}
		});
	}

	public function ajaxFindCategory()
	{
		try {
			$categories = [];
			if (isset($_POST['query'])) {
				$query = trim(htmlspecialchars(strip_tags($_POST['query'])));
				if (!empty($query)) {
					$categories = $this->wp->getCategories([
						'taxonomy' => Types\ProductCategory::NAME,
						'name__like' => $query,
						'hide_empty' => 0,
                    ]);
				}
			} else if (isset($_POST['value'])) {
				$query = explode(',', trim(htmlspecialchars(strip_tags($_POST['value']))));
				foreach ($query as $id) {
					$categories[] = $this->wp->getTerm($id, Types\ProductCategory::NAME);
				}
			} else {
				throw new Exception(__('Neither query nor value is provided to find categories.', 'jigoshop-ecommerce'));
			}

			$result = [
				'success' => true,
				'results' => array_map(function ($item){
					/** @var $item \stdClass */
					return [
						'id' => $item->term_id,
						'text' => $item->name,
                    ];
				}, $categories),
            ];
		} catch (Exception $e) {
			$result = [
				'success' => false,
				'error' => $e->getMessage(),
            ];
		}

		echo json_encode($result);
		exit;
	}

	/**
	 * Displays the product data box, tabbed, with several panels covering price, stock etc
	 *
	 * @since    1.0
	 */
	public function box()
	{
		$post = $this->wp->getGlobalPost();
		$coupon = $this->couponService->findForPost($post);
		$methods = [];
		foreach ($this->paymentService->getAvailable() as $method) {
			/** @var $method Method */
			$methods[$method->getId()] = $method->getName();
		}

		Render::output('admin/coupon/box', [
			'coupon' => $coupon,
			'types' => $this->couponService->getTypes(),
			'paymentMethods' => $methods,
        ]);
	}
}
