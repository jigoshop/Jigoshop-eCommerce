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
			if (!in_array($wp->getPageNow(), ['admin.php'])) {
				return;
			}

			if (isset($_REQUEST['tab']) && $_REQUEST['tab'] != self::SLUG){
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, ['jigoshop_page_'.SystemInfo::NAME])) {
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
		return [
			[
				'title' => __('Available Tools', 'jigoshop'),
				'id' => 'available-tools',
				'fields' => [
					[
						'id' => 'clear-logs',
						'name' => 'clear-logs',
						'title' => __('Clear Logs', 'jigoshop'),
						'description' => __('Clears jigoshop.log and jigoshop.debug.log', 'jigoshop'),
						'tip' => '',
						'classes' => [],
						'type' => 'user_defined',
						'display' => function($field){
							return Render::output('admin/system_info/tool', $field);
						}
                    ],
                    [
                        'id' => 'remove-zombie-variations',
                        'name' => 'remove-zombie-variations',
                        'title' => __('Remove zombie variations', 'jigoshop'),
                        'description' => __('Removes variations ', 'jigoshop'),
                        'tip' => '',
                        'classes' => [],
                        'type' => 'user_defined',
                        'display' => function($field){
                            return Render::output('admin/system_info/tool', $field);
                        }
                    ],
                    [
                        'id' => 'remove-zombie-meta',
                        'name' => 'remove-zombie-meta',
                        'title' => __('Remove zombie meta', 'jigoshop'),
                        'description' => __('Removes meta', 'jigoshop'),
                        'tip' => '',
                        'classes' => [],
                        'type' => 'user_defined',
                        'display' => function($field){
                            return Render::output('admin/system_info/tool', $field);
                        }
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
	 * @param string $request
	 */
	private function processRequest($request)
	{
		switch($request){
			case 'clear-logs':
				$this->clearLogs();
				break;
            case 'remove-zombie-variations':
                $this->removeZombieVariations();
                break;
            case 'remove-zombie-meta':
                $this->removeZombieMeta();
                break;
		}
	}

	/**
	 * Clears jigoshop.log and jigoshop.debug.log
	 */
	private  function clearLogs()
	{
		$logFiles = $this->wp->applyFilters('jigoshop/admin/system_info/tools/log_files', ['jigoshop', 'jigoshop.debug']);
		foreach($logFiles as $logFile){
			if (@fopen(JIGOSHOP_LOG_DIR.'/'.$logFile.'.log', 'a')) {
				file_put_contents(JIGOSHOP_LOG_DIR.'/'.$logFile.'.log', '');
			}
		}
	}

	/**
     * Removes zombie variations from database.
     */
	private function removeZombieVariations()
    {
        $wpdb = $this->wp->getWPDB();
        $query = $wpdb->prepare("DELETE FROM {$wpdb->posts} as posts WHERE post_type = %s AND post_parent > 0 AND NOT EXISTS (
          SELECT * FROM {$wpdb->posts} as posts2 WHERE posts2.ID = posts.post_parent
        )", Core\Types\Product\Variable::TYPE);

        $wpdb->query($query);
    }

    /**
     * Removes zombie variations from database.
     */
    private function removeZombieMeta()
    {
        $wpdb = $this->wp->getWPDB();
        $wpdb->query("DELETE FROM {$wpdb->postmeta} as meta WHERE NOT EXISTS (
              SELECT * FROM {$wpdb->posts} as posts WHERE posts.ID = meta.post_id
        )");
    }
}