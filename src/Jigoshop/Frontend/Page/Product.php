<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product\Attachment\Datafile;
use Jigoshop\Entity\Product\Attachment\Image;
use Jigoshop\Exception;
use Jigoshop\Frontend\NotEnoughStockException;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Product implements PageInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var ProductServiceInterface */
	private $productService;
	/** @var CartServiceInterface */
	private $cartService;
	/** @var Messages */
	private $messages;

	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, CartServiceInterface $cartService, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->cartService = $cartService;
		$this->messages = $messages;

		Styles::add('jigoshop.vendors.colorbox', \JigoshopInit::getUrl().'/assets/css/vendors/colorbox.css');
		Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css');
		Styles::add('jigoshop.shop.product', \JigoshopInit::getUrl().'/assets/css/shop/product.css', array(
			'jigoshop.shop',
			'jigoshop.vendors.select2',
			'jigoshop.vendors.colorbox',
		));

		if($this->options->get('products.related'))
		{
			Styles::add('jigoshop.shop.related_products', \JigoshopInit::getUrl() . '/assets/css/shop/related_products.css', array(
				'jigoshop.shop',
			));
		}

		Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/js/vendors/select2.js', array('jquery'));
		Scripts::add('jigoshop.vendors.colorbox', \JigoshopInit::getUrl().'/assets/js/vendors/colorbox.js', array('jquery'));
		Scripts::add('jigoshop.vendors.bs_tab_trans_tooltip_collapse', \JigoshopInit::getUrl().'/assets/js/vendors/bs_tab_trans_tooltip_collapse.js', array('jquery'));
		Scripts::add('jigoshop.shop.product', \JigoshopInit::getUrl().'/assets/js/shop/product.js', array(
			'jquery',
			'jigoshop.shop',
			'jigoshop.vendors.select2',
			'jigoshop.vendors.colorbox',
			'jigoshop.vendors.bs_tab_trans_tooltip_collapse',
		));

		$wp->addFilter('jigoshop\cart\add', function ($item) use ($productService){
			/** @var $item Item */
			if($item instanceof Item) {
                $item->setKey($productService->generateItemKey($item));
            }

			return $item;
		});
		
		$wp->addAction('jigoshop\template\product\before_summary', array(
			$this,
			'productImages'
		), 10, 1);
		$wp->addAction('jigoshop\template\product\after_summary', array($this, 'productTabs'), 10, 1);
		if($this->options->get('products.related')) {
			$wp->addAction('jigoshop\template\product\after_summary', array($this, 'relatedProducts'), 20, 1);
		}
		$wp->addAction('jigoshop\template\product\tab_panels', array(
			$this,
			'productDescription'
		), 10, 2);
		$wp->addAction('jigoshop\template\product\tab_panels', array(
			$this,
			'productAttributes'
		), 15, 2);
		$wp->addAction('jigoshop\template\product\tab_panels', array(
			$this,
			'productDownloads'
		), 20, 2);
		$wp->doAction('jigoshop\product\assets', $wp);
	}

	public function action()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'add-to-cart') {
			$post = $this->wp->getGlobalPost();
			/** @var \Jigoshop\Entity\Product $product */
			$product = $this->productService->findForPost($post);

			try {
				/** @var Item $item */
				$item = $this->wp->applyFilters('jigoshop\cart\add', null, $product);

				if ($item === null) {
					throw new Exception(__('Unable to add product to the cart.', 'jigoshop'));
				}

				if (isset($_POST['quantity'])) {
					$item->setQuantity($_POST['quantity']);
				}
				/** @var Cart $cart */
				$cart = $this->cartService->get($this->cartService->getCartIdForCurrentUser());
				$cart->addItem($item);
				$this->cartService->save($cart);

				$url = false;
				$button = '';
				switch ($this->options->get('shopping.redirect_add_to_cart')) {
					case 'cart':
						$url = $this->wp->getPermalink($this->options->getPageId(Pages::CART));
						break;
					case 'checkout':
						$url = $this->wp->getPermalink($this->options->getPageId(Pages::CHECKOUT));
						break;
					/** @noinspection PhpMissingBreakStatementInspection */
					case 'product_list':
						$url = $this->wp->getPermalink($this->options->getPageId(Pages::SHOP));
					case 'product':
					case 'same_page':
					default:
						$button = sprintf('<a href="%s" class="btn btn-warning pull-right">%s</a>', $this->wp->getPermalink($this->options->getPageId(Pages::CART)), __('View cart', 'jigoshop'));
				}

				$this->messages->addNotice(sprintf(__('%s successfully added to your cart. %s', 'jigoshop'), $product->getName(), $button));
				if ($url !== false) {
					$this->messages->preserveMessages();
					$this->wp->wpRedirect($url);
				}
			} catch (NotEnoughStockException $e) {
				if ($e->getStock() == 0) {
					$message = sprintf(__('Sorry, we do not have "%s" in stock.', 'jigoshop'), $product->getName());
				} else if ($this->options->get('products.show_stock')) {
					$message = sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. We only have %d available at this time. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'), $product->getName(), $e->getStock());
				} else {
					$message = sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'), $product->getName());
				}

				$this->messages->addError($message);
			} catch (Exception $e) {
				$this->messages->addError(sprintf(__('A problem ocurred when adding to cart: %s', 'jigoshop'), $e->getMessage()));
			}
		}
	}

	public function render()
	{
		$post = $this->wp->getGlobalPost();
		$product = $this->productService->findForPost($post);

		return Render::get('shop/product', array(
			'product' => $product,
			'messages' => $this->messages,
		));
	}

	/**
	 * Get related products based on the same parent product category.
	 * @param \Jigoshop\Entity\Product $product
	 *
	 * @return array
	 */
	protected function getRelated($product)
	{
		if (!$this->options->get('products.related')) {
			return array();
		}
		
		
		$count = $this->wp->applyFilters('jigoshop/frontend/page/product/render/related_products_count', 3);

		return $this->productService->findByQuery(ProductHelper::getRelated($product, $count));
	}

	/**
	 * Renders images section of product page.
	 *
	 * @param $product \Jigoshop\Entity\Product The product to render data for.
	 */
	public function productImages($product)
	{
		$imageClasses = apply_filters('jigoshop\product\image_classes', array('featured-image'), $product);
		$featured = ProductHelper::getFeaturedImage($product, Options::IMAGE_LARGE);
		$featuredUrl = ProductHelper::hasFeaturedImage($product) ? $this->wp->wpGetAttachmentUrl($this->wp->getPostThumbnailId($product->getId())) : '';
		$thumbnails = ProductHelper::filterAttachments($this->productService->getAttachments($product, Options::IMAGE_THUMBNAIL), Image::TYPE);

		Render::output('shop/product/images', array(
			'product' => $product,
			'featured' => $featured,
			'featuredUrl' => $featuredUrl,
			'thumbnails' => $thumbnails,
			'imageClasses' => $imageClasses,
		));
	}

	/**
	 * @param $product \Jigoshop\Entity\Product Shown product.
	 */
	public function productTabs($product)
	{
		$tabs = array();
		if ($product->getDescription()) {
			$tabs['description'] = __('Description', 'jigoshop');
		}
		if ($product->getVisibleAttributes()) {
			$tabs['attributes'] = __('Additional information', 'jigoshop');
		}
		if ($product->getAttachments()) {
			$tabs['downloads'] = __('Files to download', 'jigoshop');
		}

		$tabs = $this->wp->applyFilters('jigoshop\product\tabs', $tabs, $product);
		$availableTabs = array_keys($tabs);

		Render::output('shop/product/tabs', array(
			'product' => $product,
			'tabs' => $tabs,
			'currentTab' => reset($availableTabs),
		));
	}

	/**
	 * @param \Jigoshop\Entity\Product $product
	 */
	public function relatedProducts($product)
	{
		Render::output('shop/product/related', array(
			'products' => $this->getRelated($product),
		));
	}

	/**
	 * @param $currentTab string Current tab name.
	 * @param $product    \Jigoshop\Entity\Product Shown product.
	 */
	public function productAttributes($currentTab, $product)
	{
		Render::output('shop/product/attributes', array(
			'product' => $product,
			'currentTab' => $currentTab,
		));
	}

	/**
	 * @param $currentTab string Current tab name.
	 * @param $product    \Jigoshop\Entity\Product Shown product.
	 */
	public function productDescription($currentTab, $product)
	{
		Render::output('shop/product/description', array(
			'product' => $product,
			'currentTab' => $currentTab,
		));
	}

	/**
	 * @param $currentTab string Current tab name.
	 * @param $product    \Jigoshop\Entity\Product Shown product.
	 */
	public function productDownloads($currentTab, $product)
	{
		Render::output('shop/product/downloads', array(
			'product' => $product,
			'currentTab' => $currentTab,
			'attachments' => ProductHelper::filterAttachments($this->productService->getAttachments($product), Datafile::TYPE),
		));
	}
}
