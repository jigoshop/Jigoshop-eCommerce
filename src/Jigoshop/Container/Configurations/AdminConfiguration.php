<?php
namespace Jigoshop\Container\Configurations;

use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;
/**
 * Class AdminConfiguration
 *
 * @package Jigoshop\Container\Configuration
 * @author  Krzysztof Kasowski
 */
class AdminConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function addServices(Services $services)
	{
		$services->setDetails('jigoshop.admin', 'Jigoshop\Admin', [
			'wpal',
			'jigoshop.admin.dashboard',
			'jigoshop.admin.permalinks'
        ]);
		$services->setDetails('jigoshop.admin.pages', 'Jigoshop\Admin\Pages', [
			'wpal',
			'jigoshop.options'
        ]);
		$services->setDetails('jigoshop.admin.permalinks', 'Jigoshop\Admin\Permalinks', [
			'wpal',
			'jigoshop.options'
        ]);
		$services->setDetails('jigoshop.admin.dashboard', 'Jigoshop\Admin\Dashboard', [
			'wpal',
			'jigoshop.options',
			'jigoshop.service.order',
			'jigoshop.service.product'
        ]);
        $services->setDetails('jigoshop.admin.setup', 'Jigoshop\Admin\Setup', []);
		$services->setDetails('jigoshop.admin.reports', 'Jigoshop\Admin\Reports', [
			'wpal',
			'jigoshop.messages',
			'jigoshop.service.order'
        ]);
		$services->setDetails('jigoshop.admin.settings', 'Jigoshop\Admin\Settings', [
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
        ]);
		$services->setDetails('jigoshop.admin.system_info', 'Jigoshop\Admin\SystemInfo', [
			'wpal'
        ]);
		$services->setDetails('jigoshop.admin.licences', 'Jigoshop\Admin\Licences', [
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
        ]);
		$services->setDetails('jigoshop.admin.migration', 'Jigoshop\Admin\Migration', [
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
        ]);
		$services->setDetails('jigoshop.admin.product.categories', 'Jigoshop\Admin\Product\Categories', [
			'wpal',
			'jigoshop.messages',
			'jigoshop.service.product',
			'jigoshop.service.product.category'
        ]);        
		$services->setDetails('jigoshop.admin.product.attributes', 'Jigoshop\Admin\Product\Attributes', [
			'wpal',
			'jigoshop.messages',
			'jigoshop.service.product'
        ]);
		$services->setDetails('jigoshop.admin.notices', 'Jigoshop\Admin\Notices', [
		    'wpal',
            'jigoshop.options',
        ]);
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function addTags(Tags $tags)
	{
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.dashboard');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.reports');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.settings');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.system_info');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.licences');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.migration');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.product.categories');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.product.attributes');
		$tags->add('jigoshop.admin.dashboard', 'jigoshop.admin.setup');
	}

	/**
	 * @param Triggers $triggers
	 *
	 * @return mixed
	 */
	public function addTriggers(Triggers $triggers)
	{
        $triggers->add('jigoshop.admin', 'jigoshop.admin.page_resolver', 'resolve', ['di']);
        $triggers->add('jigoshop.admin', 'jigoshop.admin.notices', 'init', []);
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