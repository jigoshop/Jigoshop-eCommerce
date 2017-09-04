<?php

namespace Jigoshop\Admin\Migration;

use Jigoshop\Admin\Helper\Migration;
use Jigoshop\Entity\Coupon;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

class Coupons implements Tool
{
	const ID = 'jigoshop_coupons_migration';

	/** @var Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;

	public function __construct(Wordpress $wp, \Jigoshop\Core\Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
		$wp->addAction('wp_ajax_jigoshop.admin.migration.coupons', [$this, 'ajaxMigrationCoupons'], 10, 0);
	}

	/**
	 * @return string Tool ID.
	 */
	public function getId()
	{
		return self::ID;
	}

	/**
	 * Shows migration tool in Migration tab.
	 */
	public function display()
	{
		$wpdb = $this->wp->getWPDB();

		$countAll = count($wpdb->get_results($wpdb->prepare("
			SELECT DISTINCT p.ID FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
				WHERE p.post_type IN (%s) AND p.post_status <> %s
			GROUP BY p.ID",
			['shop_coupon', 'auto-draft'])));

		$countRemain = 0;
		$countDone = 0;

		if (($itemsFromBase = $this->wp->getOption('jigoshop_coupons_migrate_id')) !== false)
		{
			$countRemain = count(unserialize($itemsFromBase));
			$countDone = $countAll - $countRemain;
		}

		Render::output('admin/migration/coupons', ['countAll' => $countAll, 'countDone' => $countDone]);
	}

	/**
	 * Check SQL error for rollback transaction
	 */
	public function checkSql()
	{
		if(!empty($this->wp->getWPDB()->last_error))
		{
			throw new Exception($this->wp->getWPDB()->last_error);
		}
	}

	/**
	 * Migrates data from old format to new one.
	 * @param mixed $coupons
	 * @return bool migration coupon status: success or not
	 */
	public function migrate($coupons)
	{
		$wpdb = $this->wp->getWPDB();

//		Open transaction for save migration coupons
		$var_autocommit_sql = $wpdb->get_var("SELECT @@AUTOCOMMIT");

		try
		{
			$this->checkSql();
			$wpdb->query("SET AUTOCOMMIT=0");
			$this->checkSql();
			$wpdb->query("START TRANSACTION");
			$this->checkSql();

			for ($i = 0, $endI = count($coupons); $i < $endI;) {
				$coupon = $coupons[$i];

				// Update columns
				do {
					$key = $this->_transformKey($coupons[$i]->meta_key);

					if (!empty($key)) {
						$wpdb->query($wpdb->prepare(
							"UPDATE {$wpdb->postmeta} SET meta_value = %s, meta_key = %s WHERE meta_id = %d;",
							[
								$this->_transform($coupons[$i]->meta_key, $coupons[$i]->meta_value),
								$key,
								$coupons[$i]->meta_id,
                            ]
						));
						$this->checkSql();
					}
					$i++;
				} while ($i < $endI && $coupons[$i]->ID == $coupon->ID);
			}

//		    commit sql transation and restore value of autocommit
			$wpdb->query("COMMIT");
			$wpdb->query("SET AUTOCOMMIT=" . $var_autocommit_sql);
			return true;

		} catch (Exception $e)
		{
//		    rollback sql transation and restore value of autocommit
			if(WP_DEBUG)
			{
				\Monolog\Registry::getInstance(JIGOSHOP_LOGGER)->addDebug($e);
			}
			$wpdb->query("ROLLBACK");
			$wpdb->query("SET AUTOCOMMIT=" . $var_autocommit_sql);

			Migration::saveLog(__('Migration coupons end with error: ', 'jigoshop-ecommerce') . $e);

			return false;
		}
	}

	private function _transform($key, $value)
	{
		switch ($key) {
			case 'type':
				switch ($value) {
					case 'fixed_product':
						return Coupon::FIXED_PRODUCT;
					case 'percent_product':
						return Coupon::PERCENT_PRODUCT;
					case 'percent':
						return Coupon::PERCENT_CART;
					default:
						return Coupon::FIXED_CART;
				}
			default:
				return $value;
		}
	}

	private function _transformKey($key)
	{
		switch ($key) {
			case 'date_from':
				return 'from';
			case 'date_to':
				return 'to';
			case 'order_total_min':
				return 'order_total_minimum';
			case 'order_total_max':
				return 'order_total_maximum';
			case 'include_products':
				return 'products';
			case 'exclude_products':
				return 'excluded_products';
			case 'include_categories':
				return 'categories';
			case 'exclude_categories':
				return 'excluded_categories';
			case 'pay_methods':
				return 'payment_methods';
			default:
				return $key;
		}
	}

	public function ajaxMigrationCoupons()
	{
		try {
//			1 - if first time ajax request
			if($_POST['msgLog'] == 1)
			{
				Migration::saveLog(__('Migration coupons START.', 'jigoshop-ecommerce'), true);
			}

			$wpdb = $this->wp->getWPDB();

			$query = $wpdb->prepare("
			SELECT DISTINCT p.ID, pm.* FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
				WHERE p.post_type IN (%s) AND p.post_status <> %s",
				['shop_coupon', 'auto-draft']);
			$coupons = $wpdb->get_results($query);

			$joinCoupons = [];
			$couponsIdsMigration = [];

			for ($aa = 0; $aa < count($coupons); $aa++)
			{
				$joinCoupons[$coupons[$aa]->ID][$coupons[$aa]->meta_id] = new \stdClass();
				foreach ($coupons[$aa] as $k => $v)
				{
					$joinCoupons[$coupons[$aa]->ID][$coupons[$aa]->meta_id]->$k = $v;
					$couponsIdsMigration[] = $coupons[$aa]->ID;
				}
			}

			$couponsIdsMigration = array_unique($couponsIdsMigration);
			$countAll = count($couponsIdsMigration);

			if (($TMP_couponsIdsMigration = $this->wp->getOption('jigoshop_coupons_migrate_id')) !== false)
			{
				$couponsIdsMigration = unserialize($TMP_couponsIdsMigration);
			}

			$singleCouponsId = array_shift($couponsIdsMigration);
			$countRemain = count($couponsIdsMigration);

			sort($joinCoupons[$singleCouponsId]);

			$ajax_response = [
				'success' => true,
				'percent' => floor(($countAll - $countRemain) / $countAll * 100),
				'processed' => $countAll - $countRemain,
				'remain' => $countRemain,
				'total' => $countAll,
            ];

			if($countRemain > 0)
			{
				if ($this->migrate($joinCoupons[$singleCouponsId]))
				{
					$this->wp->updateOption('jigoshop_coupons_migrate_id', serialize($couponsIdsMigration));
				}
				else
				{
					$ajax_response['success'] = false;
					Migration::saveLog(__('Migration coupons end with error.', 'jigoshop-ecommerce'));
				}
			}
			elseif($countRemain == 0)
			{
				$this->wp->updateOption('jigoshop_coupons_migrate_id', serialize($couponsIdsMigration));
				Migration::saveLog(__('Migration coupons END.', 'jigoshop-ecommerce'));
			}

			echo json_encode($ajax_response);

		} catch (Exception $e) {
			if(WP_DEBUG)
			{
				\Monolog\Registry::getInstance(JIGOSHOP_LOGGER)->addDebug($e);
			}
			echo json_encode([
				'success' => false,
            ]);

			Migration::saveLog(__('Migration coupons end with error: ', 'jigoshop-ecommerce') . $e);
		}

		exit;
	}
}
