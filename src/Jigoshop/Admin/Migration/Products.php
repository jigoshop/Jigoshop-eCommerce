<?php

namespace Jigoshop\Admin\Migration;

use Jigoshop\Admin\Helper\Migration;
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
	private $taxClasses = [];

	public function __construct(Wordpress $wp, \Jigoshop\Core\Options $options, ProductServiceInterface $productService, TaxServiceInterface $taxService) {

		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;

		$wp->addAction('wp_ajax_jigoshop.admin.migration.products', [$this, 'ajaxMigrationProducts'], 10, 0);
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
			SELECT ID FROM {$wpdb->posts}
				WHERE post_type IN (%s, %s) AND post_status <> %s",
			'product', 'product_variation', 'auto-draft')));

		$countRemain = 0;
		$countDone = 0;

		if (($itemsFromBase = $this->wp->getOption('jigoshop_products_migrate_id')) !== false) {
			$countRemain = count(unserialize($itemsFromBase));
			$countDone = $countAll - $countRemain;
		}

		Render::output('admin/migration/products', ['countAll' => $countAll, 'countDone' => $countDone]);
	}

	/**
	 * Check SQL error for rollback transaction
	 */
	public function checkSql()
	{
		if (!empty($this->wp->getWPDB()->last_error)) {
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

//		Open transaction for save migration products
		$var_autocommit_sql = $wpdb->get_var("SELECT @@AUTOCOMMIT");

		try {
			$this->checkSql();
			$wpdb->query("SET AUTOCOMMIT=0");
			$this->checkSql();
			$wpdb->query("START TRANSACTION");
			$this->checkSql();

			// Register product type taxonomy to fetch old product types
			$this->wp->registerTaxonomy('product_type', ['product'], [
				'hierarchical' => false,
				'show_ui' => false,
				'query_var' => true,
				'show_in_nav_menus' => false,
            ]);
			$this->checkSql();

            if($this->wp->getOption('jigoshop_migration_product_first', false) == false) {
                // Update product_cat into product_category
                $wpdb->query($wpdb->prepare("UPDATE {$wpdb->term_taxonomy} SET taxonomy = %s WHERE taxonomy = %s",
                    ['product_category', 'product_cat']));
                $this->checkSql();
                $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = %s AND meta_value = %s",
                    ['product_category', '_menu_item_object', 'product_cat']));
                $this->checkSql();

                foreach($wpdb->get_results("SELECT * FROM {$wpdb->prefix}jigoshop_termmeta", ARRAY_A) as $termMeta) {
                    $wpdb->insert($wpdb->prefix.'jigoshop_term_meta', $termMeta);
                }

                $this->wp->updateOption('jigoshop_migration_product_first', true);
            }

			$productIds = [];
			$attributes = [];
			$productAttributes = [];
			$globalAttributes = [];
			$stock = [];
			foreach ($wpdb->get_results("SELECT * FROM {$wpdb->prefix}jigoshop_attribute_taxonomies") as $attribute) {
				$this->checkSql();
				$globalAttributes[$this->wp->getHelpers()
					->sanitizeTitle($attribute->attribute_name)] = $attribute;
			}

			foreach ($wpdb->get_results("SELECT id AS attribute_id, slug AS attribute_name, label AS attribute_label, type AS attribute_type FROM {$wpdb->prefix}jigoshop_attribute") as $attribute) {
				$this->checkSql();
				$globalAttributes[$attribute->attribute_name] = $attribute;
			}

			for ($i = 0, $endI = count($products); $i < $endI;) {
				$product = $products[$i];
				$productIds[] = $product->ID;
				$productAttributes[$product->ID] = [
					'attributes' => [],
					'variations' => [],
                ];

				// Add product types
				$types = $this->wp->getTheTerms($product->ID, 'product_type');
				$this->checkSql();
                $productType = Product\Simple::TYPE;
				if (is_array($types)) {
					if (!in_array($types[0]->slug, [
						Product\Simple::TYPE,
						Product\Virtual::TYPE,
						Product\Downloadable::TYPE,
						Product\External::TYPE,
						Product\Variable::TYPE,
                    ])
					) {
						Migration::saveLog(sprintf(__('We detected a product <a href="%s" target="_blank">(#%d) %s </a> of type "subscription" - this type is not supported by Jigoshop without an additional plugin. We changed its type to "simple" and set it as private.',
							'jigoshop'), get_permalink($product->ID), $product->ID, get_the_title($product->ID),
							$types[0]->slug));

						$wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts} SET post_status = 'private' WHERE ID = %d",
							$product->ID));
						$types[0]->slug = 'simple';
					}
                    $productType = $types[0]->slug;

					$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} VALUES (NULL, %d, %s, %s)",
						[$product->ID, 'type', $types[0]->slug]));
					$this->checkSql();
				}

				$attachments = $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_parent = %s AND post_type = 'attachment'", $products[$i]->ID), ARRAY_A);
				$thumbnail = get_post_meta($product->ID, '_thumbnail_id', true);
				foreach ($attachments as $attachment) {
				    if($thumbnail == false || $thumbnail != $attachment['ID']) {
                        $wpdb->insert($wpdb->prefix . 'jigoshop_product_attachment', [
                            'product_id' => $product->ID,
                            'attachment_id' => $attachment['ID'],
                            'type' => Product\Attachment\Image::TYPE
                        ]);
                        $this->checkSql();
                    }
                }

				$regularPrice = 0.0;
				$taxClasses = [];

				// Update columns
				do {
					// Sales support
					if ($products[$i]->meta_key == 'sale_price' && !empty($products[$i]->meta_value)) {
						$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)",
							[
								$product->ID,
								'sales_enabled',
								true
                            ]));
						$this->checkSql();
					}

					// Product attributes support
					if ($products[$i]->meta_key == 'product_attributes') {
						$attributeData = unserialize($products[$i]->meta_value);

						if (is_array($attributeData)) {
							foreach ($attributeData as $slug => $source) {
								if (empty($source['value'])) {
									continue;
								}

								$changeLocalToGlobal = (isset($source['variation']) && $source['variation'] == true
									&& $source['is_taxonomy'] != true && $productType == Product\Variable::TYPE);

								$productAttributes[$product->ID]['attributes'][$slug] = [
									'is_visible' => $source['visible'],
									'is_variable' => isset($source['variation']) && $source['variation'] == true,
									'values' => $changeLocalToGlobal ? str_replace(',', '|',
										$source['value']) : $source['value'],
                                ];

								if (!isset($attributes[$slug])) {
									$type = isset($globalAttributes[$slug]) ? $this->_getAttributeType($globalAttributes[$slug]) : Text::TYPE;
									if ($changeLocalToGlobal) {
										$type = Multiselect::TYPE;
									}
									$label = isset($globalAttributes[$slug]) ? !empty($globalAttributes[$slug]->attribute_label) ? $globalAttributes[$slug]->attribute_label : $globalAttributes[$slug]->attribute_name : $source['name'];

									$attribute = $this->productService->createAttribute($type);
									$attribute->setSlug($slug);
									$attribute->setLabel($label);
									$attribute->setLocal(($source['is_taxonomy'] != true && $changeLocalToGlobal != true));

									if ($changeLocalToGlobal) {
										foreach (explode('|',
											$productAttributes[$product->ID]['attributes'][$slug]['values']) as $attributeOption) {
											$option = new Option();
											$option->setLabel($attributeOption);
											$option->setValue(sanitize_title($attributeOption));
											$attribute->addOption($option);
										}

										$productAttributes[$product->ID]['attributes'][$slug]['values'] = array_map(function (
											$item
										) {
											return sanitize_title($item);
										}, explode('|',
											$productAttributes[$product->ID]['attributes'][$slug]['values']));
									}


									$attributes[$slug] = $attribute;
								}
							}
						}
					}

					// Product variation data
					if ($products[$i]->meta_key == 'variation_data') {
						$variations = unserialize($products[$i]->meta_value);
						foreach ($variations as $variation => $value) {
							$productAttributes[$product->ID]['variations'][str_replace('tax_', '',
								$variation)] = sanitize_title($value);
						}
					}

					$key = $this->_transformKey($products[$i]->meta_key);


					if ($key !== null) {
						$value = $this->_transform($products[$i]->meta_key, $products[$i]->meta_value);
                        if(in_array($key, ['stock_manage', 'stock_stock'])) {
                            $stock[$key] = $value;
                        }
						if ($key == 'regular_price') {
							$regularPrice = $value;
						}
						if ($key == 'tax_classes') {
							$taxClasses = $value;
						}

						$wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %s, meta_key = %s WHERE meta_id = %d",
							[
								$value,
								$key,
								$products[$i]->meta_id,
                            ]));
						$this->checkSql();
					}

					$i++;
				} while ($i < $endI && $products[$i]->ID == $product->ID);

                if(isset($stock['stock_manage'], $stock['stock_stock'])) {
                    if($stock['stock_manage'] && $stock['stock_stock'] == 0) {
                        $wpdb->update($wpdb->postmeta, [
                            'meta_value' => StockStatus::OUT_STOCK,
                        ], [
                            'post_id' => $product->ID,
                            'meta_key' => 'stock_status',
                        ]);
                    }
                }
			}



			foreach ($globalAttributes as $slug => $attributeData) {

				if(isset($attributes[$slug])) {
					continue;
				}
				$type = $this->_getAttributeType($attributeData);
				$label = !empty($attributeData->attribute_label) ? $attributeData->attribute_label : $attributeData->attribute_name;

				$attribute = $this->productService->createAttribute($type);
				$attribute->setSlug($slug);
				$attribute->setLabel($label);
				$attribute->setLocal(false);

				$attributes[$slug] = $attribute;
			}

			foreach ($attributes as $slug => $attribute) {
				/** @var $attribute Product\Attribute */
				$antiDuplicateAttributes = unserialize($this->wp->getOption('jigoshop_attributes_anti_duplicate',
					serialize([])));
				if (!isset($antiDuplicateAttributes[$attribute->getSlug()]) || $attribute->isLocal()) {
					if (!$attribute->isLocal()) {
						// Fetch options if attribute is a taxonomy
						$options = $wpdb->get_results("
						SELECT t.name, t.slug FROM {$wpdb->terms} t
							LEFT JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
						  WHERE tt.taxonomy = 'pa_{$slug}'
				  	     ");
						$this->checkSql();

						$createdOptions = [];
						foreach ($options as $source) {
							$option = new Option();
							$option->setLabel($source->name);
							$option->setValue($source->slug);
							$attribute->addOption($option);
							$createdOptions[] = $source->slug;
						}
					}

					$this->productService->saveAttribute($attribute);
					$this->checkSql();
					if (!$attribute->isLocal()) {
						$antiDuplicateAttributes[$attribute->getSlug()] = $attribute->getId();
						$this->wp->updateOption('jigoshop_attributes_anti_duplicate',
							serialize($antiDuplicateAttributes));
						$this->checkSql();
					}
				} else {
					//merge attributes
					$attribute = $this->productService->getAttribute($antiDuplicateAttributes[$attribute->getSlug()]);
					if($attribute instanceof Product\Attribute) {
						$savedOptions = array_map(function($item){
							return $item->getValue();
						}, $attribute->getOptions());

						foreach($attributes[$slug]->getOptions() as $option) {
							if(!in_array($option->getValue(), $savedOptions)) {
								$attribute->addOption($option);
							}
						}

						$attributes[$slug] = $attribute;
						$this->productService->saveAttribute($attribute);
					}
				}

				// Add attribute to the products
				if($attribute instanceof Product\Attribute) {
					foreach ($productIds as $id) {
						if (isset($productAttributes[$id]['attributes'][$attribute->getSlug()])) {
							$data = $productAttributes[$id]['attributes'][$attribute->getSlug()];
							$value = [];
							if (is_array($data['values'])) {
								foreach ($attribute->getOptions() as $option) {
									/** @var $option Option */
									if (in_array($option->getValue(), $data['values'])) {
										$value[] = $option->getId();
									}
								}
							}

							if (empty($value)) {
								$value = $data['values'];
							}

							$wpdb->insert($wpdb->prefix . 'jigoshop_product_attribute', [
								'product_id' => $id,
								'attribute_id' => $attribute->getId(),
								'value' => is_array($value) ? join('|', $value) : $value,
                            ]);
							$this->checkSql();

							$query = [
								'product_id' => $id,
								'attribute_id' => $attribute->getId(),
								'meta_key' => 'is_visible',
								'meta_value' => $data['is_visible'],
                            ];
							$wpdb->insert($wpdb->prefix . 'jigoshop_product_attribute_meta', $query);
							$this->checkSql();
							if ($data['is_variable']) {
								$query = [
									'product_id' => $id,
									'attribute_id' => $attribute->getId(),
									'meta_key' => 'is_variable',
									'meta_value' => true,
                                ];
								$wpdb->insert($wpdb->prefix . 'jigoshop_product_attribute_meta', $query);
								$this->checkSql();
							}
						}
					}
				}
			}

			foreach ($productIds as $id) {
				foreach ($productAttributes[$id]['variations'] as $taxonomy => $value) {
					if (!isset($attributes[$taxonomy])) {
						continue;
					}

					$attribute = $attributes[$taxonomy];
					$option = $this->_findOption($attribute->getOptions(), $value);
					$query = [
						'variation_id' => $id,
						'attribute_id' => $attribute->getId(),
						'value' => $option,
                    ];
					$wpdb->insert($wpdb->prefix . 'jigoshop_product_variation_attribute', $query);
					$this->checkSql();
				}
			}

			// Add found tax classes
			$currentTaxClasses = $this->options->get('tax.classes');
			$currentTaxClassesKeys = array_map(function ($item) {
				return $item['class'];
			}, $currentTaxClasses);
			$this->taxClasses = array_filter(array_unique($this->taxClasses),
				function ($item) use ($currentTaxClassesKeys) {
					return !in_array($item, $currentTaxClassesKeys);
				});

			foreach ($this->taxClasses as $class) {
				$currentTaxClasses[] = [
					'label' => ucfirst($class),
					'class' => $class,
                ];
			}

			$this->options->update('tax.classes', $currentTaxClasses);

//		    commit sql transation and restore value of autocommit
			$wpdb->query("COMMIT");
			$wpdb->query("SET AUTOCOMMIT=" . $var_autocommit_sql);
			return true;

		} catch (Exception $e) {
//		    rollback sql transation and restore value of autocommit
			if (WP_DEBUG) {
				\Monolog\Registry::getInstance(JIGOSHOP_LOGGER)->addDebug($e);
			}
			$wpdb->query("ROLLBACK");
			$wpdb->query("SET AUTOCOMMIT=" . $var_autocommit_sql);

			Migration::saveLog(__('Migration products end with error: ', 'jigoshop-ecommerce') . $e);

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
				$result = [];

				if (!is_array($value)) {
					$value = [];
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
            case 'crosssell_ids':
                return 'cross_sells';
            case 'upsell_ids':
                return 'up_sells';
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
//			1 - if first time ajax request
			if ($_POST['msgLog'] == 1) {
				Migration::saveLog(__('Migration products START.', 'jigoshop-ecommerce'), true);
			}

			$wpdb = $this->wp->getWPDB();
			$productsIdsMigration = [];
			if (($TMP_productsIdsMigration = $this->wp->getOption('jigoshop_products_migrate_id')) === false) {
				$query = $wpdb->prepare("
				SELECT ID FROM {$wpdb->posts}
					WHERE post_type IN (%s, %s) AND post_status <> %s",
					'product', 'product_variation', 'auto-draft');

				$products = $wpdb->get_results($query);

				$countMeta = count($products);

				for ($aa = 0; $aa < $countMeta; $aa++) {
					$productsIdsMigration[] = $products[$aa]->ID;
				}

				$productsIdsMigration = array_unique($productsIdsMigration);
				$this->wp->updateOption('jigoshop_products_migrate_id', serialize($productsIdsMigration));
				$this->wp->updateOption('jigoshop_products_migrate_count', count($productsIdsMigration));

			} else {
				$productsIdsMigration = unserialize($TMP_productsIdsMigration);
			}

			$countAll = $this->wp->getOption('jigoshop_products_migrate_count');
			$singleProductId = array_shift($productsIdsMigration);
			$countRemain = count($productsIdsMigration);

			$query = $wpdb->prepare("
			SELECT DISTINCT p.ID, pm.* FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
				WHERE p.post_type IN (%s, %s) AND p.post_status <> %s AND p.ID = %d",
				'product', 'product_variation', 'auto-draft', $singleProductId);
			$product = $wpdb->get_results($query);

			$ajax_response = [
				'success' => true,
				'percent' => floor(($countAll - $countRemain) / $countAll * 100),
				'processed' => $countAll - $countRemain,
				'remain' => $countRemain,
				'total' => $countAll,
            ];

			if ($singleProductId) {
				if ($this->migrate($product)) {
					$this->wp->updateOption('jigoshop_products_migrate_id', serialize($productsIdsMigration));
				} else {
					$ajax_response['success'] = false;
					Migration::saveLog(__('Migration products end with error.', 'jigoshop-ecommerce'));
				}
			} elseif ($countRemain == 0) {
				$this->wp->updateOption('jigoshop_products_migrate_id', serialize($productsIdsMigration));
				$this->wp->deleteOption('jigoshop_attributes_anti_duplicate');
				Migration::saveLog(__('Migration products END.', 'jigoshop-ecommerce'));
			}

			echo json_encode($ajax_response);

		} catch (Exception $e) {
			if (WP_DEBUG) {
				\Monolog\Registry::getInstance(JIGOSHOP_LOGGER)->addDebug($e);
			}
			echo json_encode([
				'success' => false,
            ]);

			Migration::saveLog(__('Migration products end with error: ', 'jigoshop-ecommerce') . $e);
		}

		exit;
	}
}
