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
		return __('Variable', 'jigoshop');
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
		$wp->addAction('wp_ajax_jigoshop.admin.product.set_variation_image', [$this, 'ajaxSetImageVariation'], 10, 0);

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
            if(count($images) < count($product->getVariations())) {
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
				'classes' => ['attribute-options'],
				'label' => __('Is for variations?', 'jigoshop'),
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
		$menu['variations'] = ['label' => __('Variations', 'jigoshop'), 'visible' => [Product\Variable::TYPE]];
		$menu['advanced']['visible'][] = Product\Variable::TYPE;
		$menu['sales']['visible'][] = Product\Variable::TYPE;

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

		$tabs['variations'] = [
			'product' => $product,
			'allowedSubtypes' => $types,
			'taxClasses' => $taxClasses,
        ];

		return $tabs;
	}

	/**
	 * @param Wordpress $wp
	 */
	public function addAdminAssets(Wordpress $wp)
	{
		$wp->wpEnqueueMedia();
		Styles::add('jigoshop.admin.product.variable', \JigoshopInit::getUrl().'/assets/css/admin/product/variable.css');
		Scripts::add('jigoshop.admin.product.variable', \JigoshopInit::getUrl().'/assets/js/admin/product/variable.js', [
			'jquery',
			'jigoshop.media'
        ]);
		Scripts::localize('jigoshop.admin.product.variable', 'jigoshop_admin_product_variable', [
			'i18n' => [
				'confirm_remove' => __('Are you sure?', 'jigoshop'),
				'variation_removed' => __('Variation successfully removed.', 'jigoshop'),
				'saved' => __('Variation saved.', 'jigoshop'),
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

			Styles::add('jigoshop.product.variable', \JigoshopInit::getUrl().'/assets/css/shop/product/variable.css');
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
				throw new Exception(__('Product was not specified.', 'jigoshop'));
			}
			if (!is_numeric($_POST['product_id'])) {
				throw new Exception(__('Invalid product ID.', 'jigoshop'));
			}

			$product = $this->productService->find((int)$_POST['product_id']);

			if (!$product->getId()) {
				throw new Exception(__('Product does not exists.', 'jigoshop'));
			}

			if (!($product instanceof Product\Variable)) {
				throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop'));
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
				throw new Exception(__('Product was not specified.', 'jigoshop'));
			}
			if (!is_numeric($_POST['product_id'])) {
				throw new Exception(__('Invalid product ID.', 'jigoshop'));
			}
			if (!isset($_POST['variation_id']) || empty($_POST['variation_id'])) {
				throw new Exception(__('Variation was not specified.', 'jigoshop'));
			}
			if (!is_numeric($_POST['variation_id'])) {
				throw new Exception(__('Invalid variation ID.', 'jigoshop'));
			}

			if (!isset($_POST['image_id']) || !is_numeric($_POST['image_id'])) {
				throw new Exception(__('Image is not not specified.', 'jigoshop'));
			}

			$product = $this->productService->find((int)$_POST['product_id']);

			if (!$product->getId()) {
				throw new Exception(__('Product does not exists.', 'jigoshop'));
			}

			if (!($product instanceof Product\Variable)) {
				throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop'));
			}

			if (!$product->hasVariation((int)$_POST['variation_id'])) {
				throw new Exception(__('Variation does not exists.', 'jigoshop'));
			}

			$this->wp->setPostThumbnail($_POST['variation_id'], $_POST['image_id']);

			if ($_POST['image_id'] > 0) {
				$url = $this->wp->wpGetAttachmentImageSrc($_POST['image_id'], Options::IMAGE_SMALL);
			} else {
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

	public function ajaxSaveVariation()
	{
		try {
			if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
				throw new Exception(__('Product was not specified.', 'jigoshop'));
			}
			if (!is_numeric($_POST['product_id'])) {
				throw new Exception(__('Invalid product ID.', 'jigoshop'));
			}
			if (!isset($_POST['variation_id']) || empty($_POST['variation_id'])) {
				throw new Exception(__('Variation was not specified.', 'jigoshop'));
			}
			if (!is_numeric($_POST['variation_id'])) {
				throw new Exception(__('Invalid variation ID.', 'jigoshop'));
			}

			if (!isset($_POST['attributes']) || !is_array($_POST['attributes'])) {
				throw new Exception(__('Attribute values are not specified.', 'jigoshop'));
			}

			$product = $this->productService->find((int)$_POST['product_id']);

			if (!$product->getId()) {
				throw new Exception(__('Product does not exists.', 'jigoshop'));
			}

			if (!($product instanceof Product\Variable)) {
				throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop'));
			}

			if (!$product->hasVariation((int)$_POST['variation_id'])) {
				throw new Exception(__('Variation does not exists.', 'jigoshop'));
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
				$_POST['product']['sales_enabled'] = $product->getSales()->isEnabled();
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

			echo json_encode([
				'success' => true,
				'html' => Render::get('admin/product/box/variations/variation', [
					'variation' => $variation,
					'attributes' => $product->getVariableAttributes(),
					'allowedSubtypes' => $types,
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
				throw new Exception(__('Product was not specified.', 'jigoshop'));
			}
			if (!is_numeric($_POST['product_id'])) {
				throw new Exception(__('Invalid product ID.', 'jigoshop'));
			}
			if (!isset($_POST['variation_id']) || empty($_POST['variation_id'])) {
				throw new Exception(__('Variation was not specified.', 'jigoshop'));
			}
			if (!is_numeric($_POST['variation_id'])) {
				throw new Exception(__('Invalid variation ID.', 'jigoshop'));
			}

			$product = $this->productService->find((int)$_POST['product_id']);

			if (!$product->getId()) {
				throw new Exception(__('Product does not exists.', 'jigoshop'));
			}

			if (!($product instanceof Product\Variable)) {
				throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop'));
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
}
