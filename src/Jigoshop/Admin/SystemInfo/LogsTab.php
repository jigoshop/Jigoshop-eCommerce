<?php
namespace Jigoshop\Admin\SystemInfo;

use Jigoshop\Admin\Settings\TabInterface;
use Jigoshop\Admin\SystemInfo;

class LogsTab implements TabInterface
{
	const SLUG = 'logs';

	public function __construct()
	{
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Logs', 'jigoshop');
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
		return [
			[
				'title' => __('Available Logs', 'jigoshop'),
				'id' => 'available-logs',
				'fields' => [
					[
						'id' => 'logs',
						'name' => 'logs',
						'title' => __('Logs', 'jigoshop'),
						'classes' => ['plain-text'],
						'description' => __('If logs are empty, please make shure that log directory is writable.', 'jigoshop'),
						'type' => 'textarea',
						'value' => $this->getLogs('jigoshop')
                    ],
					[
						'id' => 'debug-logs',
						'name' => 'debug-logs',
						'title' => __('Debug Logs', 'jigoshop'),
						'classes' => ['plain-text'],
						'description' => __('Debug logs requires WP_DEBUG set to true in wp-config.php.'),
						'type' => 'textarea',
						'value' => $this->getLogs('jigoshop.debug')
                    ],
                ]
            ]
        ];
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
	 * Gets contents from specified log file
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	private function getLogs($filename)
	{
		if (@fopen(JIGOSHOP_LOG_DIR.'/'.$filename.'.log', 'a')) {
			$logs = esc_textarea(file_get_contents(JIGOSHOP_LOG_DIR.'/'.$filename.'.log'));
			return empty($logs) ? __('Logs are empty.', 'jigoshop') : $logs;
		}

		return __('Log file does not exists.', 'jigoshop');
	}
}