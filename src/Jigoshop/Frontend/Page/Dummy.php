<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Exception;
use Jigoshop\Frontend\NotEnoughStockException;
use Jigoshop\Frontend\Pages;
use Jigoshop\Service\CartService;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\ProductService;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

/**
 * Class Dummy
 * @package Jigoshop\Frontend\Page;
 * @author Krzysztof Kasowski
 */
class Dummy implements PageInterface
{
    /** @var Wordpress|\WP  */
    private $wp;
    /** @var Options  */
    private $options;
    /** @var ProductService  */
    private $productService;
    /** @var CartService  */
    private $cartService;
    /** @var Messages  */
    private $messages;

    /**
     * Dummy constructor.
     * @param Wordpress $wp
     * @param Options $options
     * @param ProductServiceInterface $productService
     * @param CartServiceInterface $cartService
     * @param Messages $messages
     */
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
    }
    /**
     * Executes actions associated with selected page.
     */
    public function action()
    {
        if (isset($_POST['action']) && $_POST['action'] == 'add-to-cart') {
            /** @var \Jigoshop\Entity\Product $product */
            $product = $this->productService->find($_POST['item']);

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

    /**
     * Renders page template.
     *
     * @return string Page template.
     */
    public function render()
    {
        // Silence, this method will be never used.
    }
}