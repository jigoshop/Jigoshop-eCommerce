<?php

namespace Jigoshop\Container\Configurations;

use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;

/**
 * Clas MainConfiguration
 *
 * @package Jigoshop\Container\Configuration
 * @author  Krzysztof Kasowski
 */
class MainConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function addServices(Services $services)
	{
		$services->setDetails('wpal', 'WPAL\Wordpress', array());
		$services->setDetails('parsedown', 'Parsedown', array());
		$services->setDetails('jigoshop.product_type.simple', 'Jigoshop\Core\Types\Product\Simple', array());
		$services->setDetails('jigoshop.product_type.virtual', 'Jigoshop\Core\Types\Product\Virtual', array());
		$services->setDetails('jigoshop.product_type.variable.initializer', 'Jigoshop\Core\Installer\Product\Variable', array());
		$services->setDetails('jigoshop.product_type.external', 'Jigoshop\Core\Types\Product\External', array());
		$services->setDetails('jigoshop.core', 'Jigoshop\Core', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.messages',
			'jigoshop.template',
			'jigoshop.widget'
		));
		$services->setDetails('jigoshop.integration', 'Jigoshop\Integration', array(
			'di'
		));
		$services->setDetails('jigoshop.widget', 'Jigoshop\Widget', array(
			'di',
			'wpal'
		));
		$services->setDetails('jigoshop.installer', 'Jigoshop\Core\Installer', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.cron',
			'jigoshop.service.email'
		));
		$services->setDetails('jigoshop.options', 'Jigoshop\Core\Options', array(
			'wpal'
		));
		$services->setDetails('jigoshop.cron', 'Jigoshop\Core\Cron', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.order',
			'jigoshop.service.email'
		));
		$services->setDetails('jigoshop.emails', 'Jigoshop\Core\Emails', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.email'
		));
		$services->setDetails('jigoshop.endpoint', 'Jigoshop\Endpoint', array(
            'wpal',
            'di'
        ));
        $services->setDetails('jigoshop.ajax', 'Jigoshop\Ajax', array(
            'wpal',
            'di'
        ));
        $services->setDetails('jigoshop.api', 'Jigoshop\Api', array(
            'wpal',
            'jigoshop.options',
            'di'
        ));
		$services->setDetails('jigoshop.messages', 'Jigoshop\Core\Messages', array(
			'wpal',
            'jigoshop.service.session'
		));
		$services->setDetails('jigoshop.types', 'Jigoshop\Core\Types', array(
			'wpal'
		));
		$services->setDetails('jigoshop.roles.initializer', 'Jigoshop\Core\Installer\Roles', array());
		$services->setDetails('jigoshop.template', 'Jigoshop\Core\Template', array(
			'wpal',
			'jigoshop.options'
		));
		$services->setDetails('jigoshop.permalinks', 'Jigoshop\Core\Permalinks', array(
			'wpal',
			'jigoshop.options'
		));
		$services->setDetails('jigoshop.post_type.product', 'Jigoshop\Core\Types\Product', array(
			'di',
			'wpal',
			'jigoshop.options',
			'jigoshop.service.product'
		));
		$services->setDetails('jigoshop.post_type.email', 'Jigoshop\Core\Types\Email', array(
			'wpal'
		));
		$services->setDetails('jigoshop.post_type.coupon', 'Jigoshop\Core\Types\Coupon', array(
			'wpal',
			'jigoshop.options'
		));
		$services->setDetails('jigoshop.post_type.order', 'Jigoshop\Core\Types\Order', array(
			'wpal'
		));
		$services->setDetails('jigoshop.taxonomy.product_category', 'Jigoshop\Core\Types\ProductCategory', array(
			'jigoshop.options'
		));
		$services->setDetails('jigoshop.taxonomy.product_tag', 'Jigoshop\Core\Types\ProductTag', array(
			'jigoshop.options'
		));
		$services->setDetails('jigoshop.product_type.variable', 'Jigoshop\Core\Types\Product\Variable', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.product',
			'jigoshop.service.product.variable',
			'jigoshop.factory.product.variable'
		));
		$services->setDetails('jigoshop.product_type.downloadable', 'Jigoshop\Core\Types\Product\Downloadable', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
		));
		$services->setDetails('jigoshop.endpoint.download_file', 'Jigoshop\Endpoint\DownloadFile', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.messages',
			'jigoshop.service.order'
		));
        $services->setDetails('jigoshop.upgrade', 'Jigoshop\Core\Upgrade', [
            'di',
            'wpal'
        ]);
        $services->setDetails('jigoshop.upgrade.add_tax_classes_to_order_items', 'Jigoshop\Core\Upgrade\AddTaxClassesToOrderItems', []);
        $services->setDetails('jigoshop.upgrade.replace_attachment_types', 'Jigoshop\Core\Upgrade\ReplaceAttachmentTypes', []);
        $services->setDetails('jigoshop.upgrade.change_up_sells_cross_sells_meta_names', 'Jigoshop\Core\Upgrade\ChangeUpSellsCrossSellsMetaNames', []);
        $services->setDetails('jigoshop.upgrade.add_zones_to_advanced_flat_rate', 'Jigoshop\Core\Upgrade\AddZonesToAdvancedFlatRate', []);
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function addTags(Tags $tags)
	{
		$tags->add('jigoshop.type.post', 'jigoshop.post_type.product');
		$tags->add('jigoshop.type.post', 'jigoshop.post_type.email');
		$tags->add('jigoshop.type.post', 'jigoshop.post_type.coupon');
		$tags->add('jigoshop.type.post', 'jigoshop.post_type.order');
		$tags->add('jigoshop.type.taxonomy', 'jigoshop.taxonomy.product_category');
		$tags->add('jigoshop.type.taxonomy', 'jigoshop.taxonomy.product_tag');
		$tags->add('jigoshop.installer', 'jigoshop.product_type.variable.initializer');
		$tags->add('jigoshop.installer', 'jigoshop.roles.initializer');
        $tags->add('jigoshop.upgrade.2', 'jigoshop.upgrade.add_tax_classes_to_order_items');
        $tags->add('jigoshop.upgrade.2', 'jigoshop.upgrade.replace_attachment_types');
        $tags->add('jigoshop.upgrade.3', 'jigoshop.upgrade.change_up_sells_cross_sells_meta_names');
        $tags->add('jigoshop.upgrade.4', 'jigoshop.upgrade.add_zones_to_advanced_flat_rate');
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