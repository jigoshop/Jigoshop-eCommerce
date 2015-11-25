<?php

namespace Jigoshop\Admin\Migration;

use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attribute\Multiselect;
use Jigoshop\Entity\Product\Attribute\Option;
use Jigoshop\Entity\Product\Attribute\Select;
use Jigoshop\Entity\Product\Attribute\Text;
use Jigoshop\Entity\Product\Attributes\StockStatus;
use Jigoshop\Helper\Render;
use Jigoshop\Service\Exception;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\TaxServiceInterface;
use WPAL\Wordpress;

class Products implements Tool
{
	const ID = 'jigoshop_products_migration';

	/** @var Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var ProductServiceInterface */
	private $productService;
	private $taxes = array();
	private $taxClasses = array();

	public function __construct(Wordpress $wp, \Jigoshop\Core\Options $options, ProductServiceInterface $productService, TaxServiceInterface $taxService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;

		if ($this->options->get('tax.included')) {
			$address = new Customer\Address();
			$address->setCountry($this->options->get('general.country'));
			$address->setState($this->options->get('general.state'));

			$this->taxes = $taxService->getDefinitions($address);
		}

		$wp->addAction('wp_ajax_jigoshop.admin.migration.products', array($this, 'ajaxMigrationProducts'), 10, 0);
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
				WHERE p.post_type IN (%s, %s) AND p.post_status <> %s
			GROUP BY p.ID",
			array('product', 'product_variation', 'auto-draft'))));

		$countRemain = 0;

		if (($itemsFromBase = $this->wp->getOption('jigoshop_products_migrate_id')) !== false)
		{
			$countRemain = count(unserialize($itemsFromBase));
		}

		Render::output('admin/migration/products', array('countAll' => $countAll, 'countDone' => ($countAll - $countRemain)));
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
	 * @param array $products
	 * @return bool migration product status: success or not
	 */
	public function migrate($products)
	{
		$wpdb = $this->wp->getWPDB();

		// Open transaction for save migration products
		$var_autocommit_sql = $wpdb->get_var("SELECT @@AUTOCOMMIT");
		try
		{
			$wpdb->query("SET AUTOCOMMIT=0");
			$this->checkSql();
			$wpdb->query("START TRANSACTION");
			$this->checkSql();

			// Register product type taxonomy to fetch old product types
			$this->wp->registerTaxonomy('product_type', array('product'), array(
				'hierarchical'      => false,
				'show_ui'           => false,
				'query_var'         => true,
				'show_in_nav_menus' => false,
			));
			$this->checkSql();

			// Update product_cat into product_category
			$wpdb->query($wpdb->prepare("UPDATE {$wpdb->term_taxonomy} SET taxonomy = %s WHERE taxonomy = %s", array('product_category', 'product_cat')));
			$this->checkSql();

			$productIds = array();
			$attributes = array();
			$productAttributes = array();
			$globalAttributes = array();
			foreach ($wpdb->get_results("SELECT * FROM {$wpdb->prefix}jigoshop_attribute_taxonomies") as $attribute)
			{
				$this->checkSql();
				$globalAttributes[$this->wp->getHelpers()
				                           ->sanitizeTitle($attribute->attribute_name)] = $attribute;
			}

			for ($i = 0, $endI = count($products); $i < $endI;)
			{
				$product = $products[$i];
				$productIds[] = $product->ID;
				$productAttributes[$product->ID] = array(
					'attributes' => array(),
					'variations' => array(),
				);

				// Add product types
				$types = $this->wp->getTheTerms($product->ID, 'product_type');
				$this->checkSql();
				if (is_array($types))
				{
					$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} VALUES (NULL, %d, %s, %s)", array($product->ID, 'type', $types[0]->slug)));
					$this->checkSql();
				}

				$regularPrice = 0.0;
				$taxClasses = array();

				// Update columns
				do
				{
					// Sales support
					if ($products[$i]->meta_key == 'sale_price' && !empty($products[$i]->meta_value))
					{
						$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", array(
							$product->ID,
							'sales_enabled',
							true
						)));
						$this->checkSql();
					}

					// Product attributes support
					if ($products[$i]->meta_key == 'product_attributes')
					{
						$attributeData = unserialize($products[$i]->meta_value);

						if (is_array($attributeData))
						{
							foreach ($attributeData as $slug => $source)
							{
								$productAttributes[$product->ID]['attributes'][$slug] = array(
									'is_visible'  => $source['visible'],
									'is_variable' => isset($source['variation']) && $source['variation'] == true,
									'values'      => $source['value'],
								);

								if (!isset($attributes[$slug]))
								{
									$type = isset($globalAttributes[$slug]) ? $this->_getAttributeType($globalAttributes[$slug]) : Text::TYPE;
									$label = isset($globalAttributes[$slug]) ? !empty($globalAttributes[$slug]->attribute_label) ? $globalAttributes[$slug]->attribute_label : $globalAttributes[$slug]->attribute_name : $source['name'];

									$attribute = $this->productService->createAttribute($type);
									$attribute->setSlug($slug);
									$attribute->setLabel($label);
									$attribute->setLocal($source['is_taxonomy'] != true);

									$attributes[$slug] = $attribute;
								}
							}
						}
					}

					// Product variation data
					if ($products[$i]->meta_key == 'variation_data')
					{
						$variations = unserialize($products[$i]->meta_value);
						foreach ($variations as $variation => $value)
						{
							$productAttributes[$product->ID]['variations'][str_replace('tax_', '', $variation)] = $value;
						}
					}

					$key = $this->_transformKey($products[$i]->meta_key);

					if ($key !== null)
					{
						$value = $this->_transform($products[$i]->meta_key, $products[$i]->meta_value);

						if ($key == 'regular_price')
						{
							$regularPrice = $value;
						}
						if ($key == 'tax_classes')
						{
							$taxClasses = $value;
						}

						$wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %s, meta_key = %s WHERE meta_id = %d", array(
							$value,
							$key,
							$products[$i]->meta_id,
						)));
						$this->checkSql();
					}

					$i++;
				} while ($i < $endI && $products[$i]->ID == $product->ID);

				// Update regular price if it includes tax
				if (!empty($this->taxes))
				{
					foreach ($taxClasses as $taxClass)
					{
						if (isset($this->taxes['__compound__' . $taxClass]))
						{
							$regularPrice = $regularPrice / (100 + $this->taxes['__compound__' . $taxClass]['rate']) * 100;
						}
					}
					foreach ($taxClasses as $taxClass)
					{
						if (isset($this->taxes[$taxClass]))
						{
							$regularPrice = $regularPrice / (100 + $this->taxes[$taxClass]['rate']) * 100;
						}
					}

					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = %s AND post_id = %d", array(
						$regularPrice,
						'regular_price',
						$product->ID,
					)));
					$this->checkSql();
				}
			}

			foreach ($globalAttributes as $slug => $attributeData)
			{
				$type = $this->_getAttributeType($attributeData);
				$label = !empty($attributeData->attribute_label) ? $attributeData->attribute_label : $attributeData->attribute_name;

				$attribute = $this->productService->createAttribute($type);
				$attribute->setSlug($slug);
				$attribute->setLabel($label);
				$attribute->setLocal(false);

				$attributes[$slug] = $attribute;
			}

			foreach ($attributes as $slug => $attribute)
			{
				/** @var $attribute Product\Attribute */
				if (!$attribute->isLocal())
				{
					// Fetch options if attribute is a taxonomy
					$options = $wpdb->get_results("
						SELECT t.name, t.slug FROM {$wpdb->terms} t
							LEFT JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
						  WHERE tt.taxonomy = 'pa_{$slug}'
				  	     ");
					$this->checkSql();

					$createdOptions = array();
					foreach ($options as $source)
					{
						if (!in_array($source->slug, $createdOptions))
						{
							$option = new Option();
							$option->setLabel($source->name);
							$option->setValue($source->slug);
							$attribute->addOption($option);
							$createdOptions[] = $source->slug;
						}
					}
				}

				$this->productService->saveAttribute($attribute);
				$this->checkSql();

				// Add attribute to the products
				foreach ($productIds as $id)
				{
					if (isset($productAttributes[$id]['attributes'][$attribute->getSlug()]))
					{
						$data = $productAttributes[$id]['attributes'][$attribute->getSlug()];
						$value = array();
						if (is_array($data['values']))
						{
							foreach ($attribute->getOptions() as $option)
							{
								/** @var $option Option */
								if (in_array($option->getValue(), $data['values']))
								{
									$value[] = $option->getId();
								}
							}
						}

						if (empty($value))
						{
							$value = $data['values'];
						}

						$wpdb->insert($wpdb->prefix . 'jigoshop_product_attribute', array(
							'product_id'   => $id,
							'attribute_id' => $attribute->getId(),
							'value'        => is_array($value) ? join('|', $value) : $value,
						));
						$this->checkSql();

						$query = array(
							'product_id'   => $id,
							'attribute_id' => $attribute->getId(),
							'meta_key'     => 'is_visible',
							'meta_value'   => $data['is_visible'],
						);
						$wpdb->insert($wpdb->prefix . 'jigoshop_product_attribute_meta', $query);
						$this->checkSql();
						if ($data['is_variable'])
						{
							$query = array(
								'product_id'   => $id,
								'attribute_id' => $attribute->getId(),
								'meta_key'     => 'is_variable',
								'meta_value'   => true,
							);
							$wpdb->insert($wpdb->prefix . 'jigoshop_product_attribute_meta', $query);
							$this->checkSql();
						}
					}
				}
			}

			foreach ($productIds as $id)
			{
				foreach ($productAttributes[$id]['variations'] as $taxonomy => $value)
				{
					if (!isset($attributes[$taxonomy]))
					{
						continue;
					}

					$attribute = $attributes[$taxonomy];
					$option = $this->_findOption($attribute->getOptions(), $value);

					if ($option !== null)
					{
						$query = array(
							'variation_id' => $id,
							'attribute_id' => $attribute->getId(),
							'value'        => $option,
						);
						$wpdb->insert($wpdb->prefix . 'jigoshop_product_variation_attribute', $query);
						$this->checkSql();
					}
				}
			}

			// Add found tax classes
			$currentTaxClasses = $this->options->get('tax.classes');
			$currentTaxClassesKeys = array_map(function ($item)
			{
				return $item['class'];
			}, $currentTaxClasses);
			$this->taxClasses = array_filter(array_unique($this->taxClasses), function ($item) use ($currentTaxClassesKeys)
			{
				return !in_array($item, $currentTaxClassesKeys);
			});

			foreach ($this->taxClasses as $class)
			{
				$currentTaxClasses[] = array(
					'label' => ucfirst($class),
					'class' => $class,
				);
			}

			$this->options->update('tax.classes', $currentTaxClasses);

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
			return false;
		}
	}

	private function _transform($key, $value)
	{
		switch ($key) {
			case 'visibility':
				switch ($value) {
					case 'visible':
						return Product::VISIBILITY_PUBLIC;
					case 'catalog':
						return Product::VISIBILITY_CATALOG;
					case 'search':
						return Product::VISIBILITY_SEARCH;
					default:
						return Product::VISIBILITY_NONE;
				}
			case 'tax_status':
				if ($value == 'taxable') {
					return true;
				}

				return false;
			case 'tax_classes':
				$value = unserialize($value);
				$result = array();

				if (!is_array($value)) {
					$value = array();
				}

				foreach ($value as $taxClass) {
					if ($taxClass == '*') {
						$taxClass = 'standard';
					}

					$this->taxClasses[] = $taxClass;
					$result[] = $taxClass;
				}

				return serialize($result);
			case 'stock_status':
				switch ($value) {
					case 'outofstock':
						return StockStatus::OUT_STOCK;
					default:
						return StockStatus::IN_STOCK;
				}
			case 'backorders':
				switch ($value) {
					case 'notify':
						return StockStatus::BACKORDERS_NOTIFY;
					case 'yes':
						return StockStatus::BACKORDERS_ALLOW;
					default:
						return StockStatus::BACKORDERS_FORBID;
				}
			default:
				return $value;
		}
	}

	private function _transformKey($key)
	{
		switch ($key) {
			case 'tax_status':
				return 'is_taxable';
			case 'weight':
				return 'size_weight';
			case 'width':
				return 'size_width';
			case 'height':
				return 'size_height';
			case 'length':
				return 'size_length';
			case 'sale_price':
				return 'sales_price';
			case 'sale_price_dates_from':
				return 'sales_from';
			case 'sale_price_dates_to':
				return 'sales_to';
			case 'manage_stock':
				return 'stock_manage';
			case 'stock':
				return 'stock_stock';
			case 'backorders':
				return 'stock_allow_backorders';
			case 'quantity_sold':
				return 'stock_sold';
			case 'file_path':
				return 'url';
			case 'download_limit':
				return 'limit';
			case 'product_attributes':
			case 'variation_data':
				return null;
			default:
				return $key;
		}
	}

	private function _getAttributeType($source)
	{
		switch ($source->attribute_type) {
			case 'multiselect':
				return Multiselect::TYPE;
			case 'select':
				return Select::TYPE;
			default:
				return Text::TYPE;
		}
	}

	private function _findOption($options, $value)
	{
		foreach ($options as $option) {
			/** @var $option Option */
			if ($option->getValue() == $value) {
				return $option->getId();
			}
		}

		return null;
	}

	public function ajaxMigrationProducts()
	{
		try {
			$wpdb = $this->wp->getWPDB();

			$query = $wpdb->prepare("
			SELECT DISTINCT p.ID, pm.* FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
				WHERE p.post_type IN (%s, %s) AND p.post_status <> %s",
				array('product', 'product_variation', 'auto-draft'));
			$products = $wpdb->get_results($query);

			$joinProducts = array();
			$productsIdsMigration = array();

			for ($aa = 0; $aa < count($products); $aa++)
			{
				$joinProducts[$products[$aa]->ID][$products[$aa]->meta_id] = new \stdClass();
				foreach ($products[$aa] as $k => $v)
				{
					$joinProducts[$products[$aa]->ID][$products[$aa]->meta_id]->$k = $v;
					$productsIdsMigration[] = $products[$aa]->ID;
				}
			}

			$productsIdsMigration = array_unique($productsIdsMigration);
			$countAll = count($productsIdsMigration);

			//TODO usunac
			if(isset($_POST['wwee']))
			{
				$this->wp->updateOption('jigoshop_products_migrate_id', serialize($productsIdsMigration));
				echo json_encode(array(
					'success' => true,
				));
				exit;
			}

			if (($TMP_productsIdsMigration = $this->wp->getOption('jigoshop_products_migrate_id')) !== false)
			{
				$productsIdsMigration = unserialize($TMP_productsIdsMigration);
			}

			$singleProductId = array_shift($productsIdsMigration);
			$countRemain = count($productsIdsMigration);

			sort($joinProducts[$singleProductId]);

			if ($this->migrate($joinProducts[$singleProductId]))
			{
				$this->wp->updateOption('jigoshop_products_migrate_id', serialize($productsIdsMigration));
				echo json_encode(array(
					'success' => true,
					'percent' => floor(($countAll - $countRemain) / $countAll * 100),
					'processed' => $countAll - $countRemain,
					'remain' => $countRemain,
					'total' => $countAll,
				));
			}
			else
			{
				echo json_encode(array(
					'success' => false,
				));
			}

		} catch (Exception $e) {
			if(WP_DEBUG)
			{
				\Monolog\Registry::getInstance(JIGOSHOP_LOGGER)->addDebug($e);
			}
			echo json_encode(array(
				'success' => false,
			));
		}

		exit;
	}
}
