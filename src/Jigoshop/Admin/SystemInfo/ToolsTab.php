<?php
namespace Jigoshop\Admin\SystemInfo;

use Jigoshop\Admin\Settings\TabInterface;
use Jigoshop\Admin\SystemInfo;
use Jigoshop\Core;
use Jigoshop\Core\Options;
use WPAL\Wordpress;

class ToolsTab implements TabInterface
{
	const SLUG = 'tools';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Tools', 'jigoshop');
	}

	/**
	 * @return string Tab slug.
	 */
	public function getSlug()
	{
		return self::SLUG;
	}

	/**
	 * @return array List of items to display.
	 */
	public function getSections()
	{
		return array(
			array(
				'title' => __('Available Tools', 'jigoshop'),
				'id' => 'available-tools',
				'fields' => array(
				)
			)
		);
	}

	/**
	 * Validate and sanitize input values.
	 *
	 * @param array $settings Input fields.
	 *
	 * @return array Sanitized and validated output.
	 * @throws ValidationException When some items are not valid.
	 */
	public function validate($settings)
	{
		return $settings;
	}
}