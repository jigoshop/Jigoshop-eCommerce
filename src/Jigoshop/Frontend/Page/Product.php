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

//        Styles::add('jigoshop.shop.list', \JigoshopInit::getUrl().'/assets/css/shop/list.css', [
//            'jigoshop.shop',
//        ]);
        Styles::add('jigoshop.vendors.blueimp', \JigoshopInit::getUrl().'/assets/css/vendors/blueimp.css');
        Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css');
//		Styles::add('jigoshop.shop.product', \JigoshopInit::getUrl().'/assets/css/shop/product.css', [
////			'jigoshop.shop',
//			'jigoshop.vendors.select2',
//			'jigoshop.vendors.blueimp',
//        ]);

        Scripts::add('jigoshop.vendors.blueimp', \JigoshopInit::getUrl().'/assets/js/vendors/blueimp.js', ['jquery']);
        Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/js/vendors/select2.js', ['jquery']);
		Scripts::add('jigoshop.vendors.bs_tab_trans_tooltip_collapse', \JigoshopInit::getUrl().'/assets/js/vendors/bs_tab_trans_tooltip_collapse.js', ['jquery']);
		Scripts::add('jigoshop.shop.product', \JigoshopInit::getUrl().'/assets/js/shop/product.js', [
			'jquery',
			'jigoshop.shop',
			'jigoshop.vendors.select2',
			'jigoshop.vendors.blueimp',
			'jigoshop.vendors.bs_tab_trans_tooltip_collapse',
        ]);

		$wp->addFilter('jigoshop\cart\add', function ($item) use ($productService){
			/** @var $item Item */
			if($item instanceof Item) {
                $item->setKey($productService->generateItemKey($item));
            }

			return $item;
		});
		
		$wp->addAction('jigoshop\template\product\before_summary', [
			$this,
			'productImages'
        ], 10, 1);
		$wp->addAction('jigoshop\template\product\after_summary', [$this, 'productTabs'], 10, 1);
		if($this->options->get('products.related')) {
			$wp->addAction('jigoshop\template\product\after_summary', [$this, 'relatedProducts'], 20, 1);
		}
		$wp->addAction('jigoshop\template\product\after', [$this, 'upSells']);
		$wp->addAction('jigoshop\template\product\tab_panels', [
			$this,
			'productDescription'
        ], 10, 2);
		$wp->addAction('jigoshop\template\product\tab_panels', [
			$this,
			'productAttributes'
        ], 15, 2);
		$wp->addAction('jigoshop\template\product\tab_panels', [
			$this,
			'productDownloads'
        ], 20, 2);
        $wp->addAction('jigoshop\template\product\tab_panels', [
            $this,
            'productReviews'
        ], 25, 2);
        $wp->addAction('wp_footer', function() {
            Render::output('shop/product/gallery_container', []);
        });
		$wp->doAction('jigoshop\product\assets', $wp);
	}

	public function action()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'add-to-cart') {
		    if(isset($_POST['item'])) {
                /** @var \Jigoshop\Entity\Product $product */
		        $product = $this->productService->find((int)$_POST['item']);
            } else {
		        $post = $this->wp->getGlobalPost();
                /** @var \Jigoshop\Entity\Product $product */
                $product = $this->productService->findForPost($post);
            }

			try {
				/** @var Item $item */
				$item = $this->wp->applyFilters('jigoshop\cart\add', null, $product);

				if ($item === null) {
					throw new Exception(__('Unable to add product to the cart.', 'jigoshop-ecommerce'));
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
						$button = sprintf('<a href="%s" class="btn btn-warning pull-right">%s</a>', $this->wp->getPermalink($this->options->getPageId(Pages::CART)), __('View cart', 'jigoshop-ecommerce'));
				}

				$this->messages->addNotice(sprintf(__('%s successfully added to your cart. %s', 'jigoshop-ecommerce'), $product->getName(), $button));
				if ($url !== false) {
					$this->messages->preserveMessages();
					$this->wp->wpRedirect($url);
				}
			} catch (NotEnoughStockException $e) {
				if ($e->getStock() == 0) {
					$message = sprintf(__('Sorry, we do not have "%s" in stock.', 'jigoshop-ecommerce'), $product->getName());
				} else if ($this->options->get('products.show_stock')) {
					$message = sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. We only have %d available at this time. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop-ecommerce'), $product->getName(), $e->getStock());
				} else {
					$message = sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop-ecommerce'), $product->getName());
				}

				$this->messages->addError($message);
			} catch (Exception $e) {
				$this->messages->addError(sprintf(__('A problem ocurred when adding to cart: %s', 'jigoshop-ecommerce'), $e->getMessage()));
			}
		}
	}

	public function render()
	{
		$post = $this->wp->getGlobalPost();
		$product = $this->productService->findForPost($post);

		return Render::get('shop/product', [
			'product' => $product,
			'messages' => $this->messages,
        ]);
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
			return [];
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
		$imageClasses = apply_filters('jigoshop\product\image_classes', ['featured-image'], $product);
		$featured = ProductHelper::getFeaturedImage($product, Options::IMAGE_LARGE);
		$featuredUrl = ProductHelper::hasFeaturedImage($product) ? $this->wp->wpGetAttachmentUrl($this->wp->getPostThumbnailId($product->getId())) : '';
		$featuredTitle = ProductHelper::hasFeaturedImage($product) ? get_the_title($this->wp->getPostThumbnailId($product->getId())) : '';
		$thumbnails = ProductHelper::filterAttachments($this->productService->getAttachments($product, Options::IMAGE_THUMBNAIL), Image::TYPE);

		Render::output('shop/product/images', [
			'product' => $product,
			'featured' => $featured,
			'featuredUrl' => $featuredUrl,
            'featuredTitle' => $featuredTitle,
			'thumbnails' => $thumbnails,
			'imageClasses' => $imageClasses,
        ]);
	}

	/**
	 * @param $product \Jigoshop\Entity\Product Shown product.
	 */
	public function productTabs($product)
	{
		$tabs = [];
		if ($product->getDescription()) {
			$tabs['description'] = __('Description', 'jigoshop-ecommerce');
		}
		if ($product->getVisibleAttributes()) {
			$tabs['attributes'] = __('Additional information', 'jigoshop-ecommerce');
		}
		if (array_filter($product->getAttachments(), function($item) {
		    return $item['type'] == Datafile::TYPE;
        })) {
			$tabs['downloads'] = __('Files to download', 'jigoshop-ecommerce');
		}
        if ($this->options->get('products.reviews', false)) {
            $tabs['reviews'] = __('Reviews', 'jigoshop-ecommerce');
        }

		$tabs = $this->wp->applyFilters('jigoshop\product\tabs', $tabs, $product);
		$availableTabs = array_keys($tabs);

		Render::output('shop/product/tabs', [
			'product' => $product,
			'tabs' => $tabs,
			'currentTab' => reset($availableTabs),
        ]);
	}

	/**
	 * @param \Jigoshop\Entity\Product $product
	 */
	public function relatedProducts($product)
	{
		Render::output('shop/product/related', [
			'products' => $this->getRelated($product),
        ]);
	}

    /**
     * @param \Jigoshop\Entity\Product $product
     */
    public function upSells($product)
    {
        $productCount = $this->options->get('products.up_sells_product_limit', 3);
        $ids = $product->getUpSells();
        $products = [];

        shuffle($ids);
        foreach($ids as $id){
            if(sizeof($products) >= $productCount){
                break;
            }
            $product = $this->productService->find($id);
            if($product instanceof \Jigoshop\Entity\Product) {
                $products[] = $this->productService->find($id);
            }
        }

        Render::output('shop/product/up_sells', [
            'products' => $products,
        ]);
	}

	/**
	 * @param $currentTab string Current tab name.
	 * @param $product    \Jigoshop\Entity\Product Shown product.
	 */
	public function productAttributes($currentTab, $product)
	{
		Render::output('shop/product/attributes', [
			'product' => $product,
			'currentTab' => $currentTab,
        ]);
	}

	/**
	 * @param $currentTab string Current tab name.
	 * @param $product    \Jigoshop\Entity\Product Shown product.
	 */
	public function productDescription($currentTab, $product)
	{
		Render::output('shop/product/description', [
			'product' => $product,
			'currentTab' => $currentTab,
        ]);
	}

	/**
	 * @param $currentTab string Current tab name.
	 * @param $product    \Jigoshop\Entity\Product Shown product.
	 */
	public function productDownloads($currentTab, $product)
	{
        if (empty(array_filter($product->getAttachments(), function($item) {
            return $item['type'] == Datafile::TYPE;
        }))) {
            return;
        }
		Render::output('shop/product/downloads', [
			'product' => $product,
			'currentTab' => $currentTab,
			'attachments' => ProductHelper::filterAttachments($this->productService->getAttachments($product), Datafile::TYPE),
        ]);
	}

    /**
     * @param $currentTab string Current tab name.
     * @param $product    \Jigoshop\Entity\Product Shown product.
     */
    public function productReviews($currentTab, $product)
    {
        if($this->options->get('products.reviews', false) == false) {
            return;
        }
        Render::output('shop/product/reviews', [
            'product' => $product,
            'currentTab' => $currentTab,
            'reviews' => $this->productService->getReviews($product),
        ]);
	}
}
