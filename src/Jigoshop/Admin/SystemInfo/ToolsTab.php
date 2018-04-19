<?php
namespace Jigoshop\Admin\SystemInfo;

use Jigoshop\Admin\Settings\TabInterface;
use Jigoshop\Admin\SystemInfo;
use Jigoshop\Core;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Product\Attributes\StockStatus;
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
		return __('Tools', 'jigoshop-ecommerce');
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
				'title' => __('Available Tools', 'jigoshop-ecommerce'),
				'id' => 'available-tools',
				'fields' => [
					[
						'id' => 'clear-logs',
						'name' => 'clear-logs',
						'title' => __('Clear Logs', 'jigoshop-ecommerce'),
						'description' => __('Clears jigoshop.log and jigoshop.debug.log', 'jigoshop-ecommerce'),
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
                        'title' => __('Remove zombie variations', 'jigoshop-ecommerce'),
                        'description' => __('Removes variations ', 'jigoshop-ecommerce'),
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
                        'title' => __('Remove zombie meta', 'jigoshop-ecommerce'),
                        'description' => __('Removes meta', 'jigoshop-ecommerce'),
                        'tip' => '',
                        'classes' => [],
                        'type' => 'user_defined',
                        'display' => function($field){
                            return Render::output('admin/system_info/tool', $field);
                        }
                    ],
                    [
                        'id' => 'fix-order-items-migration',
                        'name' => 'fix-order-items-migration',
                        'title' => __('Fix order items migration', 'jigoshop-ecommerce'),
                        'description' => __('To version 2.1.3 was some issue with order items migration. Use only if you migrated your store from Jigoshop 1.x before version 2.1.3.', 'jigoshop-ecommerce'),
                        'tip' => '',
                        'classes' => [],
                        'type' => 'user_defined',
                        'display' => function($field){
                            return Render::output('admin/system_info/tool', $field);
                        }
                    ],
                    [
                        'id' => 'disable-manage-stock-and-set-in-stock-status-for-all-products',
                        'name' => 'disable-manage-stock-and-set-in-stock-status-for-all-products',
                        'title' => __('Disable manage stock and set in stock status for all products', 'jigoshop-ecommerce'),
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
            case 'fix-order-items-migration':
                $this->fixMigratedOrderItems();
                break;
            case 'disable-manage-stock-and-set-in-stock-status-for-all-products':
                $this->disableManageStockAndSetInStockStatusForAllProducts();
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
			if (@fopen(\JigoshopInit::getLogDir().'/'.$logFile.'.log', 'a')) {
				file_put_contents(\JigoshopInit::getLogDir().'/'.$logFile.'.log', '');
			}
		}
	}

	/**
     * Removes zombie variations from database.
     */
	private function removeZombieVariations()
    {
        $wpdb = $this->wp->getWPDB();
        $query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} AS posts WHERE post_type = %s AND post_parent > 0 AND NOT EXISTS 
          (SELECT * FROM {$wpdb->posts} AS posts2 WHERE posts2.ID = posts.post_parent)"
            , Core\Types\Product\Variable::TYPE);
        $result = $wpdb->get_results($query, ARRAY_A);

        if(count($result)) {
            $result = array_map(function($item) {
                return $item['ID'];
            }, $result);

            $wpdb->query("DELETE FROM {$wpdb->posts} WHERE ID IN (".join(',', $result).")");
        }
    }

    /**
     * Removes zombie variations from database.
     */
    private function removeZombieMeta()
    {
        $wpdb = $this->wp->getWPDB();
        $wpdb->query("DELETE FROM `{$wpdb->postmeta}` WHERE NOT EXISTS 
          (SELECT * FROM {$wpdb->posts} WHERE {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id)
          ");
    }

    /**
     * Fix migration issue.
     */
    private function fixMigratedOrderItems()
    {
        set_time_limit(0);
        $wpdb = $this->wp->getWPDB();
        $js1Orders = $wpdb->get_results("SELECT post_id as id FROM {$wpdb->postmeta} WHERE meta_key = 'order_items'", ARRAY_A);

        foreach($js1Orders as $order) {
            $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}jigoshop_order_item WHERE order_id = %d", $order['id']), ARRAY_A);
            foreach ($items as $item) {
                $taxRate = $item['tax']/ $item['price'];
                $item['price'] = $item['cost'];
                $item['cost'] = $item['price'] * $item['quantity'];
                $item['tax'] = $item['cost'] * $taxRate;
                $wpdb->update("{$wpdb->prefix}jigoshop_order_item", $item, [
                    'id' => $item['id'],
                ]);
            }
        }
    }

    /**
     * Fix migration issue.
     */
    private function disableManageStockAndSetInStockStatusForAllProducts()
    {
        set_time_limit(0);
        $wpdb = $this->wp->getWPDB();
        $wpdb->query("UPDATE {$wpdb->postmeta} SET meta_value = 0 WHERE meta_key = 'stock_manage' ");
        $wpdb->query("UPDATE {$wpdb->postmeta} SET meta_value = 1 WHERE meta_key = 'stock_status' ");
    }
}