<?php
namespace Jigoshop\Admin\SystemInfo;

use Jigoshop\Admin\Settings\TabInterface;
use Jigoshop\Admin\SystemInfo;
use Jigoshop\Core;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

class ToolsTab implements TabInterface
{
	const SLUG = 'tools';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Options */
	private $options;

	/**
	 * ToolsTab constructor.
	 *
	 * @param Wordpress $wp
	 * @param Options   $options
	 */
	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;

		$wp->addAction('current_screen', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), array('admin.php'))) {
				return;
			}

			if (isset($_REQUEST['tab']) && $_REQUEST['tab'] != self::SLUG){
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, array('jigoshop_page_'.SystemInfo::NAME))) {
				return;
			}

			if (isset($_REQUEST['request'])){
				$this->processRequest($_REQUEST['request']);
			}
		});
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
					array(
						'id' => 'clear-logs',
						'name' => 'clear-logs',
						'title' => __('Clear Logs', 'jigoshop'),
						'description' => __('Clears jigoshop.log and jigoshop.debug.log', 'jigoshop'),
						'tip' => '',
						'classes' => array(),
						'type' => 'user_defined',
						'display' => function($field){
							return Render::output('admin/system_info/tools', $field);
						}
					),
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

	/**
	 * @param string $request
	 */
	private function processRequest($request)
	{
		switch($request){
			case 'clear-logs':
				$this->clearLogs();
				break;
		}
	}

	/**
	 * Clears jigoshop.log and jigoshop.debug.log
	 */
	private  function clearLogs()
	{
		$logFiles = $this->wp->applyFilters('jigoshop/admin/system_info/tools/log_files', array('jigoshop', 'jigoshop.debug'));
		foreach($logFiles as $logFile){
			if (@fopen(JIGOSHOP_LOG_DIR.'/'.$logFile.'.log', 'a')) {
				file_put_contents(JIGOSHOP_LOG_DIR.'/'.$logFile.'.log', '');
			}
		}
	}
}