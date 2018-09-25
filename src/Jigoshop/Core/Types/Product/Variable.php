<?php

namespace Jigoshop\Core\Types\Product;

use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Exception;
use Jigoshop\Factory\Product\Variable as VariableFactory;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\Product\VariableServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

/**
 * Variable product type definition.
 *
 * @package Jigoshop\Core\Types\Product
 */
class Variable implements Type
{
	const TYPE = 'product_variation';

	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var VariableServiceInterface */
	private $service;
	/** @var VariableFactory */
	private $factory;
	/** @var ProductServiceInterface */
	private $productService;
	/** @var array */
	private $allowedSubtypes = [];

	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, VariableServiceInterface $service, VariableFactory $factory)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->service = $service;
		$this->factory = $factory;
	}

	/**
	 * Returns identifier for the type.
	 *
	 * @return string Type identifier.
	 */
	public function getId()
	{
		return Product\Variable::TYPE;
	}

	/**
	 * Returns human-readable name for the type.
	 *
	 * @return string Type name.
	 */
	public function getName()
	{
		return __('Variable', 'jigoshop-ecommerce');
	}

	/**
	 * Returns class name to use as type entity.
	 * This class MUST extend {@code \Jigoshop\Entity\Product}!
	 *
	 * @return string Fully qualified class name.
	 */
	public function getClass()
	{
		return '\Jigoshop\Entity\Product\Variable';
	}

	/**
	 * @return array
	 */
	public function getAllowedSubtypes()
	{
		return $this->allowedSubtypes;
	}

	/**
	 * @param array $allowedSubtypes
	 */
	public function setAllowedSubtypes($allowedSubtypes)
	{
		$this->allowedSubtypes = $allowedSubtypes;
	}

	/**
	 * Initializes product type.
	 *
	 * @param Wordpress $wp           WordPress Abstraction Layer
	 * @param array     $enabledTypes List of all available types.
	 */
	public function initialize(Wordpress $wp, array $enabledTypes)
	{
		$wp->addFilter('jigoshop\cart\add', [$this, 'addToCart'], 10, 2);
		$wp->addFilter('jigoshop\cart\generate_item_key', [$this, 'generateItemKey'], 10, 2);
		$wp->addFilter('jigoshop\checkout\is_shipping_required', [$this, 'isShippingRequired'], 10, 2);
		$wp->addAction('jigoshop\product\assets', [$this, 'addFrontendAssets'], 10, 1);
		$wp->addFilter('jigoshop\product\get_stock', [$this, 'getStock'], 10, 2);
		$wp->addAction('jigoshop\template\product\before_thumbnails', [$this, 'addVariationImages']);

		$wp->addAction('jigoshop\admin\product\assets', [$this, 'addAdminAssets'], 10, 1);
		$wp->addAction('jigoshop\admin\product\attribute\options', [$this, 'addVariableAttributeOptions']);
		$wp->addFilter('jigoshop\admin\product\menu', [$this, 'addProductMenu']);
		$wp->addFilter('jigoshop\admin\product\tabs', [$this, 'addProductTab'], 10, 2);

		$wp->addAction('wp_ajax_jigoshop.admin.product.add_variation', [$this, 'ajaxAddVariation'], 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.product.save_variation', [$this, 'ajaxSaveVariation'], 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.product.remove_variation', [$this, 'ajaxRemoveVariation'], 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.product.remove_all_variations', [$this, 'ajaxRemoveAllVariations'], 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.product.set_variation_image', [$this, 'ajaxSetImageVariation'], 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.product.create_variations_from_all_attributes', [$this, 'ajaxCreateVariationsFromAllAttributes'], 10, 0);

		$that = $this;
		$wp->addAction('jigoshop\run', function () use ($that, $wp, $enabledTypes){
			$allowedSubtypes = $wp->applyFilters('jigoshop\core\types\variable\subtypes', []);
			$that->setAllowedSubtypes(array_filter($enabledTypes, function ($type) use ($allowedSubtypes){
				/** @var $type Type */
				return in_array($type->getId(), $allowedSubtypes);
			}));
		});
	}

    public function addVariationImages($product)
    {
        if($product instanceof Product\Variable) {
            $images = [];
            foreach($product->getVariations() as $variation) {
                $variationProduct = $variation->getProduct();
                $image = \Jigoshop\Helper\Product::getFeaturedImage($variationProduct, Options::IMAGE_THUMBNAIL);
                $url = \Jigoshop\Helper\Product::hasFeaturedImage($variationProduct) ? $this->wp->wpGetAttachmentUrl($this->wp->getPostThumbnailId($variationProduct->getId())) : '';
                $title = \Jigoshop\Helper\Product::hasFeaturedImage($variationProduct) ? get_the_title($this->wp->getPostThumbnailId($variationProduct->getId())) : '';
                if($url) {
                    $images[] = [
                        'id' => $variationProduct->getId(),
                        'image' => $image,
                        'url' => $url,
                        'title' => $title,
                    ];
                }
            }
            $image = \Jigoshop\Helper\Product::getFeaturedImage($product, Options::IMAGE_THUMBNAIL);
            $url = \Jigoshop\Helper\Product::hasFeaturedImage($product) ? $this->wp->wpGetAttachmentUrl($this->wp->getPostThumbnailId($product->getId())) : '';
            $title = \Jigoshop\Helper\Product::hasFeaturedImage($product) ? get_the_title($this->wp->getPostThumbnailId($product->getId())) : '';
            if($url) {
                $images[] = [
                    'id' => 'parent',
                    'image' => $image,
                    'url' => $url,
                    'title' => $title,
                ];
            }

            Render::output('shop/product/images/variable', [
                'product' => $product,
                'images' => $images,
            ]);
        }
	}

	/**
	 * @param $stock bool|int Current stock value.
	 * @param $item  Item Item to check.
	 *
	 * @return bool Whether the product is out of stock.
	 */
	public function getStock($stock, $item)
	{
		if ($item->getType() == Product\Variable::TYPE) {
			/** @var Product\Variable $product */
			$product = $item->getProduct();
			$variation = $product->getVariation($item->getMeta('variation_id')->getValue());

            if($variation->getProduct()->getStock()->getManage()) {
                return $variation->getProduct()->getStock()->getStock();
            }
		}

		return $stock;
	}

	/**
	 * @param $status boolean
	 * @param $item   Item
	 *
	 * @return boolean
	 */
	public function isShippingRequired($status, $item)
	{
		if ($status) {
			return true;
		}

		$product = $item->getProduct();
		if ($product instanceof Product\Variable) {
			$product = $product->getVariation($item->getMeta('variation_id')->getValue())->getProduct();

			if ($product instanceof Product\Shippable && $product->isShippable()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $parts array
	 * @param $item  Item
	 *
	 * @return array
	 */
	public function generateItemKey($parts, $item)
	{
		if ($item->getProduct() instanceof Product\Variable) {
			foreach ($item->getAllMeta() as $meta) {
				/** @var $meta Item\Meta */
				$parts[] = $meta->getValue();
			}
		}

		return $parts;
	}

	public function addToCart($value, $product)
	{
		if ($product instanceof Product\Variable) {
			$item = new Item();
			$item->setProduct($product);

			$variation = $this->factory->getVariation($product, $_POST['variation_id']);

			foreach ($variation->getAttributes() as $attribute) {
				/** @var $attribute \Jigoshop\Entity\Product\Variable\Attribute */
				if ($attribute->getValue() === '') {
					$meta = new Item\Meta();
					$metaValue = isset($_POST['attributes']) ? $_POST['attributes'][$attribute->getAttribute()->getId()] : 'any';
					$meta->setKey($attribute->getAttribute()->getSlug());
					$meta->setValue($metaValue);
					$item->addMeta($meta);

					$attribute->setValue($metaValue);
				}
			}

			$item->setName($variation->getTitle());
			$item->setPrice($variation->getProduct()->getPrice());
			$item->setQuantity($_POST['quantity']);
			$item->setTaxClasses($variation->getProduct()->getTaxClasses());

			$meta = new Item\Meta();
			$meta->setKey('variation_id');
			$meta->setValue($variation->getId());
			$item->addMeta($meta);

			if($variation->getProduct() instanceof Product\Downloadable) {
                $meta = new Item\Meta('file', $variation->getProduct()->getUrl());
                $item->addMeta($meta);
                $meta = new Item\Meta('downloads', $variation->getProduct()->getLimit());
                $item->addMeta($meta);
            }

			return $item;
		}

		return $value;
	}

	/**
	 * Adds variable options to attribute field.
	 *
	 * @param Attribute|Attribute\Variable $attribute Attribute.
	 */
	public function addVariableAttributeOptions(Attribute $attribute)
	{
		if ($attribute instanceof Attribute\Variable) {
			/** @var $attribute Attribute|Attribute\Variable */
			Forms::checkbox([
				'name' => 'product[attributes]['.$attribute->getId().'][is_variable]',
				'id' => 'product_attributes_'.$attribute->getId().'_variable',
				'classes' => ['attribute-options', 'is-for-variations'],
				'label' => __('Is for variations?', 'jigoshop-ecommerce'),
				'checked' => $attribute->isVariable(),
				'size' => 6,
				// TODO: Visibility based on current product - if not variable should be hidden
            ]);
		}
	}

	/**
	 * Updates product menu.
	 *
	 * @param $menu array
	 *
	 * @return array
	 */
	public function addProductMenu($menu)
	{
		$menu['variations'] = ['label' => __('Variations', 'jigoshop-ecommerce'), 'visible' => [Product\Variable::TYPE]];
		$menu['advanced']['visible'][] = Product\Variable::TYPE;

		return $menu;
	}

	/**
	 * Updates product tabs.
	 *
	 * @param $tabs    array
	 * @param $product Product
	 *
	 * @return array
	 */
	public function addProductTab($tabs, $product)
	{
		$types = [];
		foreach ($this->allowedSubtypes as $type) {
			/** @var $type Type */
			$types[$type->getId()] = $type->getName();
		}

		$taxClasses = [];
		foreach ($this->options->get('tax.classes') as $class) {
			$taxClasses[$class['class']] = $class['label'];
		}

		$bulkActions = $this->wp->applyFilters('jigoshop\core\types\variable\bulk_actions', [
            1 => __('Add Variation', 'jigoshop-ecommerce'),
            2 => __('Create variations from all attributes', 'jigoshop-ecommerce'),
            3 => __('Remove all variations', 'jigoshop-ecommerce'),
            'type' => [
                'label' => __('Type', 'jigoshop-ecommerce'),
                'items' => [],
            ],
            'pricing' => [
                'label' => __('Pricing', 'jigoshop-ecommerce'),
                'items' => [
                    5 => __('Set regular price', 'jigoshop-ecommerce'),
                    6 => __('Increase regular price', 'jigoshop-ecommerce'),
                    7 => __('Decrease regular price', 'jigoshop-ecommerce'),
                    8 => __('Set sale prices', 'jigoshop-ecommerce'),
                    9 => __('Increase sale prices', 'jigoshop-ecommerce'),
                    10 => __('Decrease sale prices', 'jigoshop-ecommerce'),
                    11 => __('Set scheduled sale dates', 'jigoshop-ecommerce'),
                ],
            ],
            'inventory' => [
                'label' => __('Inventory', 'jigoshop-ecommerce'),
                'items' => [
                    12 => __('Toggle manage stock', 'jigoshop-ecommerce'),
                    13 => __('Set stock', 'jigoshop-ecommerce'),
                    14 => __('Increase stock', 'jigoshop-ecommerce'),
                    15 => __('Decrease stock', 'jigoshop-ecommerce'),
                ],
            ],
            'dimensions' => [
                'label' => __('Dimensions', 'jigoshop-ecommerce'),
                'items' => [
                    16 => __('Set length', 'jigoshop-ecommerce'),
                    17 => __('Set width', 'jigoshop-ecommerce'),
                    18 => __('Set height', 'jigoshop-ecommerce'),
                    19 => __('Set weight', 'jigoshop-ecommerce'),
                ],
            ],
            'downloads' => [
                'label' => __('Downloads', 'jigoshop-ecommerce'),
                'items' => [
                    20 => __('Set download limit', 'jigoshop-ecommerce'),
                ]
            ],
        ]);

		foreach ($this->allowedSubtypes as $type) {
            /** @var $type Type */
            $bulkActions['type']['items']['4-'.$type->getId()] = sprintf(__('Set "%s"', 'jigoshop-ecommerce'), $type->getName());
        }

		$tabs['variations'] = [
			'product' => $product,
			'allowedSubtypes' => $types,
			'taxClasses' => $taxClasses,
            'bulkActions' => $bulkActions
        ];

		return $tabs;
	}

	/**
	 * @param Wordpress $wp
	 */
	public function addAdminAssets(Wordpress $wp)
	{
		$wp->wpEnqueueMedia();
		Styles::add('jigoshop.admin.product.variable', \JigoshopInit::getUrl().'/assets/css/admin/product/variable.css', [
		    'impromptu'
        ]);
		Scripts::add('jigoshop.admin.product.variable', \JigoshopInit::getUrl().'/assets/js/admin/product/variable.js', [
			'jquery',
			'jigoshop.media',
            'jquery-blockui',
            'impromptu'
        ]);
		Scripts::localize('jigoshop.admin.product.variable', 'jigoshop_admin_product_variable', [
			'i18n' => [
				'confirm_remove' => __('Are you sure?', 'jigoshop-ecommerce'),
				'variation_removed' => __('Variation successfully removed.', 'jigoshop-ecommerce'),
				'saved' => __('Variation saved.', 'jigoshop-ecommerce'),
                'create_all_variations_confirmation' => __('Are you sure you want to create all variations? It will take some time.', 'jigoshop-ecommerce'),
                'remove_all_variations' => __('Are you sure you want to remove all variations? This cannot be undone.', 'jigoshop-ecommerce'),
                'set_field' => __('Enter a value', 'jigoshop-ecommerce'),
                'modify_field' => __('Enter a value (fixed or %)', 'jigoshop-ecommerce'),
                'sale_start_date' => __('Sale start date (MM/DD/YYYY format or leave blank)', 'jigoshop-ecommerce'),
                'sale_end_date' => __('Sale end date (MM/DD/YYYY format or leave blank)', 'jigoshop-ecommerce'),
                'buttons' => [
                    'done' => __('Done!', 'jigoshop-ecommerce'),
                    'cancel' => __('Cancel', 'jigoshop-ecommerce'),
                    'next' => __('Next', 'jigoshop-ecommerce'),
                    'back' => __('Back', 'jigoshop-ecommerce'),
                    'yes' => __('Yes', 'jigoshop-ecommerce'),
                    'no' => __('No', 'jigoshop-ecommerce'),
                ],
            ],
        ]);
	}

	/**
	 * @param Wordpress $wp
	 */
	public function addFrontendAssets(Wordpress $wp)
	{
		$post = $wp->getGlobalPost();
		$product = $this->productService->findForPost($post);

		if ($product instanceof Product\Variable) {
			$variations = [];
			foreach ($product->getVariations() as $variation) {
				/** @var $variation Product\Variable\Variation */
				$variations[$variation->getId()] = [
					'price' => $variation->getProduct()->getPrice(),
					'html' => [
						'price' => \Jigoshop\Helper\Product::getPriceHtml($variation->getProduct()),
                        'image' => '',
                    ],
					'attributes' => [],
                ];
				if($this->wp->hasPostThumbnail($variation->getId())) {
                    $variations[$variation->getId()]['html']['image'] = Render::get('shop/product/images/featured', [
                        'imageClasses' => apply_filters('jigoshop\product\image_classes', ['featured-image'], $variation->getProduct()),
                        'featured' => \Jigoshop\Helper\Product::getFeaturedImage($variation->getProduct(), Options::IMAGE_LARGE),
                        'featuredUrl' => $this->wp->wpGetAttachmentUrl($this->wp->getPostThumbnailId($variation->getId())),
                        'featuredTitle' => get_the_title($this->wp->getPostThumbnailId($variation->getId())),
                    ]);
                }
				foreach ($variation->getAttributes() as $attribute) {
					/** @var $attribute Product\Variable\Attribute */
					$variations[$variation->getId()]['attributes'][$attribute->getAttribute()->getId()] = $attribute->getValue();
				}
			}

			Scripts::add('jigoshop.product.variable', \JigoshopInit::getUrl().'/assets/js/shop/product/variable.js', ['jquery']);
			Scripts::localize('jigoshop.product.variable', 'jigoshop_product_variable', [
				'variations' => $variations,
            ]);
		}
	}

	public function ajaxAddVariation()
	{
		try {
			if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
				throw new Exception(__('Product was not specified.', 'jigoshop-ecommerce'));
			}
			if (!is_numeric($_POST['product_id'])) {
				throw new Exception(__('Invalid product ID.', 'jigoshop-ecommerce'));
			}

			$product = $this->productService->find((int)$_POST['product_id']);

			if (!$product->getId()) {
				throw new Exception(__('Product does not exists.', 'jigoshop-ecommerce'));
			}

			if (!($product instanceof Product\Variable)) {
				throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop-ecommerce'));
			}

			$variation = $this->factory->createVariation($product);
			$this->wp->doAction('jigoshop\admin\product_variation\add', $variation, $product);

			$product->addVariation($variation);
			$this->productService->save($product);

			$types = [];
			foreach ($this->allowedSubtypes as $type) {
				/** @var $type Type */
				$types[$type->getId()] = $type->getName();
			}

			$taxClasses = [];
			foreach ($this->options->get('tax.classes') as $class) {
				$taxClasses[$class['class']] = $class['label'];
			}

			echo json_encode([
				'success' => true,
				'html' => Render::get('admin/product/box/variations/variation', [
					'variation' => $variation,
					'attributes' => $product->getVariableAttributes(),
					'allowedSubtypes' => $types,
					'taxClasses' => $taxClasses,
                ]),
            ]);
		} catch (Exception $e) {
			echo json_encode([
				'success' => false,
				'error' => $e->getMessage(),
            ]);
		}

		exit;
	}

	public function ajaxSetImageVariation()
	{
		try {
			if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
				throw new Exception(__('Product was not specified.', 'jigoshop-ecommerce'));
			}
			if (!is_numeric($_POST['product_id'])) {
				throw new Exception(__('Invalid product ID.', 'jigoshop-ecommerce'));
			}
			if (!isset($_POST['variation_id']) || empty($_POST['variation_id'])) {
				throw new Exception(__('Variation was not specified.', 'jigoshop-ecommerce'));
			}
			if (!is_numeric($_POST['variation_id'])) {
				throw new Exception(__('Invalid variation ID.', 'jigoshop-ecommerce'));
			}

			if (!isset($_POST['image_id']) || !is_numeric($_POST['image_id'])) {
				throw new Exception(__('Image is not not specified.', 'jigoshop-ecommerce'));
			}

			$product = $this->productService->find((int)$_POST['product_id']);

			if (!$product->getId()) {
				throw new Exception(__('Product does not exists.', 'jigoshop-ecommerce'));
			}

			if (!($product instanceof Product\Variable)) {
				throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop-ecommerce'));
			}

			if (!$product->hasVariation((int)$_POST['variation_id'])) {
				throw new Exception(__('Variation does not exists.', 'jigoshop-ecommerce'));
			}

			if ($_POST['image_id'] > 0) {
				$this->wp->setPostThumbnail($_POST['variation_id'], $_POST['image_id']);

				$url = $this->wp->wpGetAttachmentImageSrc($_POST['image_id'], Options::IMAGE_SMALL);
			} else {
				delete_post_thumbnail($_POST['variation_id']);

				$url = \JigoshopInit::getUrl().'/assets/images/placeholder.png';
			}

			echo json_encode([
				'success' => true,
				'url' => $url,
            ]);
		} catch (Exception $e) {
			echo json_encode([
				'success' => false,
				'error' => $e->getMessage(),
            ]);
		}

		exit;
	}

    public function ajaxCreateVariationsFromAllAttributes()
    {
        try {
            if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
                throw new Exception(__('Product was not specified.', 'jigoshop-ecommerce'));
            }
            if (!is_numeric($_POST['product_id'])) {
                throw new Exception(__('Invalid product ID.', 'jigoshop-ecommerce'));
            }

            $product = $this->productService->find($_POST['product_id']);
            if(!$product instanceof Product\Variable) {
                throw new Exception(__('Invalid product type.', 'jigoshop-ecommerce'));
            }

            $attributes = $product->getVariableAttributes();
            $variations = $product->getVariations();
            $legend = [];
            $attributeValues = [];
            foreach($attributes as $attribute) {
                $legend[] = $attribute->getId();
                $values = $attribute->getValue();
                sort($values);
                $attributeValues[] = $values;
            }
            if(count($attributeValues) > 1) {
                $possibleCombinations = $this->createCombinations($attributeValues);
            } else {
                foreach($attributeValues as $value) {
                    foreach($value as $subvalue)
                    $possibleCombinations[] = [$subvalue];
                }
            }

            foreach($possibleCombinations as $key => $combination) {
                foreach ($variations as $variation) {
                    $exist = true;
                    foreach ($combination as $key2 => $value) {
                        if($variation->getAttribute($legend[$key2])->getValue() != $value) {
                            $exist = false;
                        }
                    }
                    if($exist) {
                        unset($possibleCombinations[$key]);
                        break;
                    }

                }
            }

            $createdVariations = [];
            foreach($possibleCombinations as $combination) {
                $variation = $this->factory->createVariation($product);
                $this->wp->doAction('jigoshop\admin\product_variation\add', $variation, $product);
                foreach($combination as $key => $value) {
                    $variation->getAttribute($legend[$key])->setValue($value);
                }
                $createdVariations[] = $variation;
                $variationProduct = $this->service->createVariableProduct($variation, $product);
                $variation->setId($variationProduct->getId());
                $variation->setProduct($variationProduct);
                $product->addVariation($variation);
            }
            $this->productService->save($product);

            $types = [];
            foreach ($this->allowedSubtypes as $type) {
                /** @var $type Type */
                $types[$type->getId()] = $type->getName();
            }

            $taxClasses = [];
            foreach ($this->options->get('tax.classes') as $class) {
                $taxClasses[$class['class']] = $class['label'];
            }

            echo json_encode([
                'success' => true,
                'html' => array_reduce($createdVariations, function($value, $variation) use ($product, $types, $taxClasses) {
                    $value .= Render::get('admin/product/box/variations/variation', [
                        'variation' => $variation,
                        'attributes' => $product->getVariableAttributes(),
                        'allowedSubtypes' => $types,
                        'taxClasses' => $taxClasses,
                    ]);

                    return $value;
                }),
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }

        exit;
	}

    private function createCombinations($arrays, $i = 0) {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        // get combinations from subsequent arrays
        $tmp = $this->createCombinations($arrays, $i + 1);

        $result = array();

        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge(array($v), $t) :
                    array($v, $t);
            }
        }

        return $result;
    }

	public function ajaxSaveVariation()
	{
		try {
			if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
				throw new Exception(__('Product was not specified.', 'jigoshop-ecommerce'));
			}
			if (!is_numeric($_POST['product_id'])) {
				throw new Exception(__('Invalid product ID.', 'jigoshop-ecommerce'));
			}
			if (!isset($_POST['variation_id']) || empty($_POST['variation_id'])) {
				throw new Exception(__('Variation was not specified.', 'jigoshop-ecommerce'));
			}
			if (!is_numeric($_POST['variation_id'])) {
				throw new Exception(__('Invalid variation ID.', 'jigoshop-ecommerce'));
			}

			if (!isset($_POST['attributes']) || !is_array($_POST['attributes'])) {
				throw new Exception(__('Attribute values are not specified.', 'jigoshop-ecommerce'));
			}

			$product = $this->productService->find((int)$_POST['product_id']);

			if (!$product->getId()) {
				throw new Exception(__('Product does not exists.', 'jigoshop-ecommerce'));
			}

			if (!($product instanceof Product\Variable)) {
				throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop-ecommerce'));
			}

			if (!$product->hasVariation((int)$_POST['variation_id'])) {
				throw new Exception(__('Variation does not exists.', 'jigoshop-ecommerce'));
			}

			$variation = $product->removeVariation((int)$_POST['variation_id']);
			foreach ($_POST['attributes'] as $attribute => $value) {
				if (!$variation->hasAttribute($attribute)) {
					continue;
					// TODO: Properly add attributes
//					$attr = $this->productService->getAttribute($attribute);
//					$variation->addAttribute();
				}

				$variation->getAttribute($attribute)->setValue(trim(htmlspecialchars(strip_tags($value))));
			}

			if (isset($_POST['product']) && is_array($_POST['product'])) {
				// For now - always manage variation product stock
				//$_POST['product']['stock']['manage'] = 'on';
				$_POST['product']['sales_enabled'] = $_POST['product']['sales_price'] !== '';
				$variation->getProduct()->restoreState($_POST['product']);
				$variation->getProduct()->markAsDirty($_POST['product']);
			}

			$this->wp->doAction('jigoshop\admin\product_variation\save', $variation);

			$product->addVariation($variation);
			$this->productService->save($product);
            $this->wp->updatePostMeta($variation->getProduct()->getId(), 'type', $_POST['product']['type']);

			$types = [];
			foreach ($this->allowedSubtypes as $type) {
				/** @var $type Type */
				$types[$type->getId()] = $type->getName();
			}

            $taxClasses = [];
            foreach ($this->options->get('tax.classes') as $class) {
                $taxClasses[$class['class']] = $class['label'];
            }

			echo json_encode([
				'success' => true,
				'html' => Render::get('admin/product/box/variations/variation', [
					'variation' => $variation,
					'attributes' => $product->getVariableAttributes(),
					'allowedSubtypes' => $types,
                    'taxClasses' => $taxClasses,
                ]),
            ]);
		} catch (Exception $e) {
			echo json_encode([
				'success' => false,
				'error' => $e->getMessage(),
            ]);
		}

		exit;
	}

	public function ajaxRemoveVariation()
	{
		try {
			if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
				throw new Exception(__('Product was not specified.', 'jigoshop-ecommerce'));
			}
			if (!is_numeric($_POST['product_id'])) {
				throw new Exception(__('Invalid product ID.', 'jigoshop-ecommerce'));
			}
			if (!isset($_POST['variation_id']) || empty($_POST['variation_id'])) {
				throw new Exception(__('Variation was not specified.', 'jigoshop-ecommerce'));
			}
			if (!is_numeric($_POST['variation_id'])) {
				throw new Exception(__('Invalid variation ID.', 'jigoshop-ecommerce'));
			}

			$product = $this->productService->find((int)$_POST['product_id']);

			if (!$product->getId()) {
				throw new Exception(__('Product does not exists.', 'jigoshop-ecommerce'));
			}

			if (!($product instanceof Product\Variable)) {
				throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop-ecommerce'));
			}

			$variation = $product->removeVariation((int)$_POST['variation_id']);
			$this->service->removeVariation($variation);
			$this->productService->save($product);
			echo json_encode([
				'success' => true,
            ]);
		} catch (Exception $e) {
			echo json_encode([
				'success' => false,
				'error' => $e->getMessage(),
            ]);
		}

		exit;
	}

    public function ajaxRemoveAllVariations()
    {
        try {
            if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
                throw new Exception(__('Product was not specified.', 'jigoshop-ecommerce'));
            }
            if (!is_numeric($_POST['product_id'])) {
                throw new Exception(__('Invalid product ID.', 'jigoshop-ecommerce'));
            }
            $product = $this->productService->find((int)$_POST['product_id']);
            if (!$product->getId()) {
                throw new Exception(__('Product does not exists.', 'jigoshop-ecommerce'));
            }
            if (!($product instanceof Product\Variable)) {
                throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop-ecommerce'));
            }

            foreach ($product->getVariations() as $variation) {
                $product->removeVariation($variation->getId());
                $this->service->removeVariation($variation);
            }
            $this->productService->save($product);
            echo json_encode([
                'success' => true,
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }

        exit;
    }
}
