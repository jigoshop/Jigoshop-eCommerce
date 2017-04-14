<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product as Entity;
use Jigoshop\Exception;
use Jigoshop\Frontend\NotEnoughStockException;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

abstract class AbstractProductList implements PageInterface
{
	/** @var \WPAL\Wordpress */
	protected $wp;
	/** @var \Jigoshop\Core\Options */
	protected $options;
	/** @var ProductServiceInterface */
	protected $productService;
	/** @var CartServiceInterface */
	protected $cartService;
	/** @var Messages */
	protected $messages;

	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, CartServiceInterface $cartService, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->cartService = $cartService;
		$this->messages = $messages;

		$wp->addFilter('jigoshop\cart\add', function ($item) use ($productService){
			/** @var $item Item */
			$item->setKey($productService->generateItemKey($item));

			return $item;
		});

		Styles::add('jigoshop.shop.list', \JigoshopInit::getUrl().'/assets/css/shop/list.css', [
			'jigoshop.shop',
        ]);
		Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css', [
			'jigoshop.shop',
        ]);

		Scripts::add('jigoshop.shop');
	}

	public function action()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'add-to-cart') {
			/** @var Entity $product */
			$product = $this->productService->find($_POST['item']);

			try {
				$item = $this->wp->applyFilters('jigoshop\cart\add', null, $product);

				if ($item === null) {
					throw new Exception(__('Unable to add product to the cart.', 'jigoshop'));
				}

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
					case 'product':
					default:
						$url = $this->wp->getPermalink($product->getId());
					case 'same_page':
					case 'product_list':
						$button = sprintf('<a href="%s" class="btn btn-warning pull-right">%s</a>', $this->wp->getPermalink($this->options->getPageId(Pages::CART)), __('View cart', 'jigoshop'));
				}

				$this->messages->addNotice(sprintf(__('%s successfully added to your cart. %s', 'jigoshop'), $product->getName(), $button));
				if ($url !== false) {
					$this->messages->preserveMessages();
					$this->wp->wpRedirect($url);
					exit;
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
				$this->messages->addError(sprintf(__('A problem ocurred when adding to cart: %s', 'jigoshop'), $e->getMessage()), false);
			}
		}
	}

	public function render()
	{
		$query = $this->wp->getQuery();
		$products = $this->productService->findByQuery($query);
		$content = do_shortcode($this->getContent());

		return Render::get('shop', [
			'content' => $content,
			'products' => $products,
			'product_count' => $query->max_num_pages,
			'messages' => $this->messages,
			'title' => $this->getTitle(),
        ]);
	}

	public abstract function getContent();

	public abstract function getTitle();
}
