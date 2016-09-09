<?php
namespace Jigoshop\Container\Configurations\Admin;

use Jigoshop\Container\Configurations\ConfigurationInterface;
use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;

/**
 * Class PagesConfiguration
 *
 * @package Jigoshop\Container\Configuration
 * @author  Krzysztof Kasowski
 */
class PagesConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function addServices(Services $services)
	{
		$services->setDetails('jigoshop.admin.page_resolver', 'Jigoshop\Admin\PageResolver', array(
			'wpal',
			'jigoshop.admin.pages'
		));
		$services->setDetails('jigoshop.admin.page.orders', 'Jigoshop\Admin\Page\Orders', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.order'
		));
		$services->setDetails('jigoshop.admin.page.order', 'Jigoshop\Admin\Page\Order', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.order',
			'jigoshop.service.product',
			'jigoshop.service.customer',
			'jigoshop.service.shipping'
		));
		$services->setDetails('jigoshop.admin.page.products', 'Jigoshop\Admin\Page\Products', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.post_type.product',
			'jigoshop.service.product'
		));
		$services->setDetails('jigoshop.admin.page.product', 'Jigoshop\Admin\Page\Product', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.post_type.product',
			'jigoshop.service.product'
		));
		$services->setDetails('jigoshop.admin.page.email', 'Jigoshop\Admin\Page\Email', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.email'
		));
		$services->setDetails('jigoshop.admin.page.coupons', 'Jigoshop\Admin\Page\Coupons', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.coupon'
		));
		$services->setDetails('jigoshop.admin.page.coupon', 'Jigoshop\Admin\Page\Coupon', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.coupon',
			'jigoshop.service.payment'
		));
		$services->setDetails('jigoshop.admin.page.product_categories', 'Jigoshop\Admin\Page\ProductCategories', array(
			'wpal'
		));
		$services->setDetails('jigoshop.admin.page.product_tags', 'Jigoshop\Admin\Page\ProductTags', array(
			'wpal'
		));
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function addTags(Tags $tags)
	{
		$tags->add('jigoshop.admin.migration', 'jigoshop.admin.migration.options');
		$tags->add('jigoshop.admin.migration', 'jigoshop.admin.migration.coupons');
		$tags->add('jigoshop.admin.migration', 'jigoshop.admin.migration.emails');
		$tags->add('jigoshop.admin.migration', 'jigoshop.admin.migration.products');
		$tags->add('jigoshop.admin.migration', 'jigoshop.admin.migration.orders');
	}

	/**
	 * @param Triggers $triggers
	 *
	 * @return mixed
	 */
	public function addTriggers(Triggers $triggers)
	{

	}

	/**
	 * @param Factories $factories
	 *
	 * @return mixed
	 */
	public function addFactories(Factories $factories)
	{

	}
}