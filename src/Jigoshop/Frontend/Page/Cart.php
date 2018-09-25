<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Coupon;
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Exception;
use Jigoshop\Frontend\NotEnoughStockException;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Helper\Tax;
use Jigoshop\Helper\Validation;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\CouponServiceInterface;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Shipping\Method;
use Jigoshop\Shipping\MultipleMethod;
use Jigoshop\Shipping\Rate;
use WPAL\Wordpress;

class Cart implements PageInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Messages */
	private $messages;
	/** @var CartServiceInterface */
	private $cartService;
	/** @var ProductServiceInterface */
	private $productService;
	/** @var CustomerServiceInterface */
	private $customerService;
	/** @var ShippingServiceInterface */
	private $shippingService;
	/** @var OrderServiceInterface */
	private $orderService;
	/** @var CouponServiceInterface */
	private $couponService;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, CartServiceInterface $cartService, ProductServiceInterface $productService,
		CustomerServiceInterface $customerService, OrderServiceInterface $orderService, ShippingServiceInterface $shippingService, CouponServiceInterface $couponService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->cartService = $cartService;
		$this->productService = $productService;
		$this->customerService = $customerService;
		$this->shippingService = $shippingService;
		$this->orderService = $orderService;
		$this->couponService = $couponService;

//		Styles::add('jigoshop.shop.cart', \JigoshopInit::getUrl().'/assets/css/shop/cart.css', [
//			'jigoshop.shop',
//        ]);
		Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css', [
			'jigoshop',
        ]);

		Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/js/vendors/select2.js', ['jquery']);
		Scripts::add('jigoshop.vendors.bs_tab_trans_tooltip_collapse', \JigoshopInit::getUrl().'/assets/js/vendors/bs_tab_trans_tooltip_collapse.js', ['jquery']);
		Scripts::add('jigoshop.shop.cart', \JigoshopInit::getUrl().'/assets/js/shop/cart.js', [
			'jquery',
			'jquery-blockui',
			'jigoshop.shop',
			'jigoshop.helpers',
			'jigoshop.vendors.select2',
			'jigoshop.vendors.bs_tab_trans_tooltip_collapse',
        ]);


		Scripts::localize('jigoshop.shop.cart', 'jigoshop_cart', [
			'assets' => \JigoshopInit::getUrl().'/assets',
			'i18n' => [
				'loading' => __('Loading...', 'jigoshop-ecommerce'),
            ],
        ]);

		$wp->addAction('wp_ajax_jigoshop_cart_update_item', [$this, 'ajaxUpdateItem']);
		$wp->addAction('wp_ajax_nopriv_jigoshop_cart_update_item', [$this, 'ajaxUpdateItem']);
		$wp->addAction('wp_ajax_jigoshop_cart_select_shipping', [$this, 'ajaxSelectShipping']);
		$wp->addAction('wp_ajax_nopriv_jigoshop_cart_select_shipping', [$this, 'ajaxSelectShipping']);
		$wp->addAction('wp_ajax_jigoshop_cart_update_discounts', [$this, 'ajaxUpdateDiscounts']);
		$wp->addAction('wp_ajax_nopriv_jigoshop_cart_update_discounts', [$this, 'ajaxUpdateDiscounts']);
		$wp->addAction('wp_ajax_jigoshop_cart_change_country', [$this, 'ajaxChangeCountry']);
		$wp->addAction('wp_ajax_nopriv_jigoshop_cart_change_country', [$this, 'ajaxChangeCountry']);
		$wp->addAction('wp_ajax_jigoshop_cart_change_state', [$this, 'ajaxChangeState']);
		$wp->addAction('wp_ajax_nopriv_jigoshop_cart_change_state', [$this, 'ajaxChangeState']);
		$wp->addAction('wp_ajax_jigoshop_cart_change_postcode', [$this, 'ajaxChangePostcode']);
		$wp->addAction('wp_ajax_nopriv_jigoshop_cart_change_postcode', [$this, 'ajaxChangePostcode']);

        $wp->addAction('jigoshop\template\cart\form\before', [$this, 'crossSells']);
	}

	/**
	 * Ajax action for changing country.
	 */
	public function ajaxChangeCountry()
	{
	    try {
            $customer = $this->customerService->getCurrent();

            if (!Country::isAllowed($_POST['value'])) {
                $locations = array_map(function ($location){
                    return Country::getName($location);
                }, $this->options->get('shopping.selling_locations'));
                echo json_encode([
                    'success' => false,
                    'error' => sprintf(__('This location is not supported, we sell only to %s.'), join(', ', $locations)),
                ]);
                exit;
            }

            if ($customer->hasMatchingAddresses()) {
                $customer->getBillingAddress()->setCountry($_POST['value']);
            }
            $customer->getShippingAddress()->setCountry($_POST['value']);

            $this->customerService->save($customer);
            $cart = $this->cartService->getCurrent();
            $cart->setCustomer($customer);
            $this->cartService->save($cart);

            $response = $this->getAjaxLocationResponse($customer, $cart);
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

		echo json_encode($response);
		exit;
	}

	/**
	 * Abstraction for location update response.
	 * Prepares and returns array of updated data for location change requests.
	 *
	 * @param Customer              $customer The customer (for location).
	 * @param \Jigoshop\Entity\Cart $cart     Current cart.
	 *
	 * @return array
	 */
	private function getAjaxLocationResponse(Customer $customer, \Jigoshop\Entity\Cart $cart)
	{
		$response = $this->getAjaxCartResponse($cart);
		$address = $customer->getShippingAddress();
		// Add some additional fields
		$response['has_states'] = Country::hasStates($address->getCountry());
		$response['states'] = Country::getStates($address->getCountry());
		$response['html']['estimation'] = $address->getLocation();

		return $response;
	}

	/**
	 * Abstraction for cart update response.
	 * Prepares and returns response array for cart update requests.
	 *
	 * @param \Jigoshop\Entity\Cart $cart Current cart.
	 *
	 * @return array
	 */
	private function getAjaxCartResponse(\Jigoshop\Entity\Cart $cart)
	{
		$tax = [];
		foreach ($cart->getCombinedTax() as $class => $value) {
			$tax[$class] = [
				'label' => Tax::getLabel($class, $cart),
				'value' => Product::formatPrice($value),
            ];
		}

		$shipping = [];
		$shippingHtml = [];
		if($cart->isShippingRequired()) {
            foreach ($this->shippingService->getAvailable() as $method) {
                /** @var $method Method */
                if ($method instanceof MultipleMethod) {
                    /** @var $method MultipleMethod */
                    foreach ($method->getRates($cart) as $rate) {
                        /** @var $rate Rate */
                        $shipping[ $method->getId() . '-' . $rate->getId() ] = $method->isEnabled() ? $rate->calculate($cart) : -1;
                        if ($method->isEnabled()) {
                            $shippingHtml[ $method->getId() . '-' . $rate->getId() ] = [
                                'price' => Product::formatPrice($rate->calculate($cart)),
                                'html' => Render::get('shop/cart/shipping/rate', ['method' => $method, 'rate' => $rate, 'cart' => $cart]),
                            ];
                        }
                    }
                } else {
                    $shipping[ $method->getId() ] = $method->isEnabled() ? $method->calculate($cart) : -1;
                    if ($method->isEnabled()) {
                        $shippingHtml[ $method->getId() ] = [
                            'price' => Product::formatPrice($cart->getShippingPrice()),
                            'html' => Render::get('shop/cart/shipping/method', ['method' => $method, 'cart' => $cart]),
                        ];
                    }
                }
            }
        }

		$shippingMethod = $cart->getShippingMethod();
        if($shippingMethod) {
            try {
                $cart->setShippingMethod($shippingMethod);
            } catch(Exception $e) {
                $cart->removeShippingMethod();
            }
        }

        $productSubtotal = $cart->getProductSubtotal();
        $productSubtotalWithTax = $cart->getProductSubtotal() + $cart->getTotalTax();

        $productSubtotalPrices = Product::generatePrices($productSubtotal, $productSubtotalWithTax, 1);
        if(count($productSubtotalPrices) == 2) {
        	$productSubtotalPricesStr = sprintf('%s (%s)', $productSubtotalPrices[0], $productSubtotalPrices[1]);
        }
        else {
        	$productSubtotalPricesStr = $productSubtotalPrices[0];
        }

		$coupons = join(',', array_map(function ($coupon){
			/** @var $coupon Coupon */
			return $coupon->getCode();
		}, $cart->getCoupons()));
		$response = [
			'success' => true,
			'shipping' => $shipping,
			'subtotal' => $cart->getSubtotal(),
			'product_subtotal' => $productSubtotal,
			'discount' => $cart->getDiscount(),
			'coupons' => $coupons,
			'tax' => $cart->getCombinedTax(),
			'total' => $cart->getTotal(),
			'html' => [
				'shipping' => $shippingHtml,
				'discount' => Product::formatPrice($cart->getDiscount()),
				'subtotal' => Product::formatPrice($cart->getSubtotal()),
				'product_subtotal' => $productSubtotalPricesStr,
				'tax' => $tax,
				'total' => Product::formatPrice($cart->getTotal()),
            ],
        ];

		return $response;
	}

	/**
	 * Ajax action for changing state.
	 */
	public function ajaxChangeState()
	{
	    try {
            $customer = $this->customerService->getCurrent();
            if ($customer->hasMatchingAddresses()) {
                $customer->getBillingAddress()->setState($_POST['value']);
            }
            $customer->getShippingAddress()->setState($_POST['value']);
            $this->customerService->save($customer);
            $cart = $this->cartService->getCurrent();
            $cart->setCustomer($customer);
            $this->cartService->save($cart);

            $response = $this->getAjaxLocationResponse($customer, $cart);
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

		echo json_encode($response);
		exit;
	}

	/**
	 * Ajax action for changing postcode.
	 */
	public function ajaxChangePostcode()
	{
	    try {
            $customer = $this->customerService->getCurrent();

            if ($this->options->get('shopping.validate_zip') && !Validation::isPostcode($_POST['value'], $customer->getShippingAddress()->getCountry())) {
                echo json_encode([
                    'success' => false,
                    'error' => __('Postcode is not valid!', 'jigoshop-ecommerce'),
                ]);
                exit;
            }

            if ($customer->hasMatchingAddresses()) {
                $customer->getBillingAddress()->setPostcode($_POST['value']);
            }

            $customer->getShippingAddress()->setPostcode($_POST['value']);
            $this->customerService->save($customer);
            $cart = $this->cartService->getCurrent();
            $cart->setCustomer($customer);
            $this->cartService->save($cart);

            $response = $this->getAjaxLocationResponse($customer, $cart);
        } catch(Exception $e) {
            $response = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

		echo json_encode($response);
		exit;
	}

	/**
	 * Processes change of selected shipping method and returns updated cart details.
	 */
	public function ajaxSelectShipping()
	{
		try {
			$method = $this->shippingService->get($_POST['method']);
			$cart = $this->cartService->getCurrent();

			if ($method instanceof MultipleMethod) {
				if (!isset($_POST['rate'])) {
					throw new Exception(__('Method rate is required.', 'jigoshop-ecommerce'));
				}

				$method->setShippingRate($_POST['rate']);
			}

			$cart->setShippingMethod($method);
			$this->cartService->save($cart);

			$response = $this->getAjaxCartResponse($cart);
		} catch (Exception $e) {
			$response = [
				'success' => false,
				'error' => $e->getMessage(),
            ];
		}

		echo json_encode($response);
		exit;
	}

	/**
	 * Processes updates of coupons and returns updated cart details.
	 */
	public function ajaxUpdateDiscounts()
	{
		try {
			$cart = $this->cartService->getCurrent();

			if (isset($_POST['coupons'])) {
				$errors = [];
				$codes = array_filter(explode(',', $_POST['coupons']));
				$cart->removeAllCouponsExcept($codes);
				$coupons = $this->couponService->getByCodes($codes);

				foreach ($coupons as $coupon) {
					try {
						$cart->addCoupon($coupon);
					} catch (Exception $e) {
						$errors[] = $e->getMessage();
					}
				}

				if (!empty($errors)) {
					throw new Exception(join('<br/>', $errors));
				}
			}

			// TODO: Add support for other discounts

			$this->cartService->save($cart);

			$response = $this->getAjaxCartResponse($cart);
		} catch (Exception $e) {
			$response = [
				'success' => false,
				'error' => $e->getMessage(),
            ];
		}

		echo json_encode($response);
		exit;
	}

	/**
	 * Processes change of item quantity and returns updated item value and cart details.
	 */
	public function ajaxUpdateItem()
	{
		$cart = $this->cartService->getCurrent();

		try {
			$cart->updateQuantity($_POST['item'], (int)$_POST['quantity']);
			$this->cartService->save($cart);
			$item = $cart->getItem($_POST['item']);

			if ($item === null) {
				throw new Exception(__('Item not found.', 'jigoshop-ecommerce'));
			}

            $response = $this->getAjaxCartResponse($cart);

			$price = $item->getPrice();
			$priceWithTax = $item->getPrice() + ($item->getTax() / $item->getQuantity());

			$prices = Product::generatePrices($price, $priceWithTax, 1);
			if(count($prices) == 2) {
				$pricesStr = sprintf('%s 
					(%s)', $prices[0], $prices[1]);
			}
			else {
				$pricesStr = $prices[0];
			}

			$priceTotal = $item->getQuantity() * $price;
			$priceTotalWithTax = $item->getQuantity() * $priceWithTax;

			$pricesTotal = Product::generatePrices($priceTotal, $priceTotalWithTax, 1);
			if(count($pricesTotal) == 2) {
				$pricesTotalStr = sprintf('%s 
					(%s)', $pricesTotal[0], $pricesTotal[1]);
			}
			else {
				$pricesTotalStr = $pricesTotal[0];
			}

			// Add some additional fields
			$response['item_price'] = $price;
			$response['item_subtotal'] = $price * $item->getQuantity();
			$response['html']['item_price'] = $pricesStr;
			$response['html']['item_subtotal'] = $pricesTotalStr;
		} catch (NotEnoughStockException $e) {
			$response = [
				'success' => false,
				'error' => sprintf(__('Sorry, we do not have enough units in stock. We have got only %s in stock', 'jigoshop-ecommerce'), $e->getStock())
            ];
		} catch (Exception $e) {
			if ($cart->isEmpty()) {
				$response = [
					'success' => true,
					'empty_cart' => true,
					'html' => Render::get('shop/cart/empty', ['shopUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::SHOP))]),
                ];
			} else {
				$response = $this->getAjaxCartResponse($cart);
				$response['remove_item'] = true;
			}
		}

		echo json_encode($response);
		exit;
	}

	public function action()
	{
		if (isset($_REQUEST['action'])) {
			switch ($_REQUEST['action']) {
				case 'cancel_order':
					if ($this->wp->getHelpers()->verifyNonce($_REQUEST['nonce'], 'cancel_order')) {
						/** @var Order $order */
						$order = $this->orderService->find((int)$_REQUEST['id']);

						if ($order->getKey() != $_REQUEST['key']) {
							$this->messages->addError(__('Invalid order key.', 'jigoshop-ecommerce'));

							return;
						}

						if ($order->getStatus() != Status::PENDING) {
							$this->messages->addError(__('Unable to cancel order.', 'jigoshop-ecommerce'));

							return;
						}

						$order->setStatus(Status::CANCELLED);
                        $this->orderService->save($order);
						$cart = $this->cartService->createFromOrder($this->cartService->getCartIdForCurrentUser(), $order);
						$this->cartService->save($cart);
						$this->messages->addNotice(__('The order has been cancelled', 'jigoshop-ecommerce'));
					}
					break;
				case 'update-shipping':
					$customer = $this->customerService->getCurrent();
					$this->updateCustomer($customer);
					break;
				case 'checkout':
					try {
						$cart = $this->cartService->getCurrent();

						// Update quantities
						$this->updateQuantities($cart);
						// Update customer (if needed)
						if ($this->options->get('shipping.calculator')) {
							$customer = $this->customerService->getCurrent();
							$this->updateCustomer($customer);
						}

						if (isset($_POST['jigoshop_order']['shipping_method'])) {
							// Select shipping method
							$method = $this->shippingService->get($_POST['jigoshop_order']['shipping_method']);
							$cart->setShippingMethod($method);
						}

						if ($cart->getShippingMethod() && !$cart->getShippingMethod()->isEnabled()) {
							$cart->removeShippingMethod();
							$this->messages->addWarning(__('Previous shipping method is unavailable. Please select different one.', 'jigoshop-ecommerce'));
						}

						if ($this->options->get('shopping.validate_zip')) {
							$address = $cart->getCustomer()->getShippingAddress();
							if ($address->getPostcode() && !Validation::isPostcode($address->getPostcode(), $address->getCountry())) {
								throw new Exception(__('Postcode is not valid!', 'jigoshop-ecommerce'));
							}
						}

						$this->wp->doAction('jigoshop\cart\before_checkout', $cart);

						$this->cartService->save($cart);
						$this->messages->preserveMessages();
						$this->wp->redirectTo($this->options->getPageId(Pages::CHECKOUT));
					} catch (Exception $e) {
						$this->messages->addError(sprintf(__('Error occurred while updating cart: %s', 'jigoshop-ecommerce'), $e->getMessage()));
					}
					break;
				case 'update-cart':
					if (isset($_POST['cart']) && is_array($_POST['cart'])) {
						try {
							$cart = $this->cartService->getCurrent();
							$this->updateQuantities($cart);
							$this->cartService->save($cart);
							$this->messages->addNotice(__('Successfully updated the cart.', 'jigoshop-ecommerce'));
						} catch (Exception $e) {
							$this->messages->addError(sprintf(__('Error occurred while updating cart: %s', 'jigoshop-ecommerce'), $e->getMessage()));
						}
					}
			}
		}

		if (isset($_GET['action']) && isset($_GET['item']) && $_GET['action'] === 'remove-item' && $_GET['item']) {
			$cart = $this->cartService->getCurrent();
			$cart->removeItem($_GET['item']);
			$this->cartService->save($cart);
			$this->messages->addNotice(__('Successfully removed item from cart.', 'jigoshop-ecommerce'), false);
		}
	}

	private function updateCustomer(Customer $customer)
	{
		$address = $customer->getShippingAddress();

		if ($customer->hasMatchingAddresses()) {
			$billingAddress = $customer->getBillingAddress();
			$billingAddress->setCountry($_POST['country']);
			$billingAddress->setState($_POST['state']);
			$billingAddress->setPostcode($_POST['postcode']);
		}

		$address->setCountry($_POST['country']);
		$address->setState($_POST['state']);
		$address->setPostcode($_POST['postcode']);
	}

	private function updateQuantities(\Jigoshop\Entity\Cart $cart)
	{
		if (isset($_POST['cart']) && is_array($_POST['cart'])) {
			foreach ($_POST['cart'] as $item => $quantity) {
				$cart->updateQuantity($item, (int)$quantity);
			}
		}
	}

	public function render()
	{
		$cart = $this->cartService->getCurrent();
		$content = $this->wp->getPostField('post_content', $this->options->getPageId(Pages::CART));
		$content = do_shortcode($content);

		$termsUrl = '';
		$termsPage = $this->options->get('advanced.pages.terms');
		if ($termsPage > 0) {
			$termsUrl = $this->wp->getPermalink($termsPage);
		}

        $showWithTax = $this->options->get('tax.item_prices', 'excluding_tax');
        $suffixExcludingTax = $this->options->get('tax.suffix_for_excluded', '');
        $suffixIncludingTax = $this->options->get('tax.suffix_for_included', '');

		return Render::get('shop/cart', [
			'content' => $content,
			'cart' => $cart,
			'messages' => $this->messages,
			'productService' => $this->productService,
			'customer' => $this->customerService->getCurrent(),
			'shippingMethods' => $this->shippingService->getEnabled(),
			'shopUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::SHOP)),
			'showShippingCalculator' => $this->options->get('shipping.calculator'),
			'termsUrl' => $termsUrl,
        ]);
	}

    public function crossSells()
    {
        $ids = [];
        $inCart = [];
        $cart = $this->cartService->getCurrent();

        foreach ($cart->getItems() as $item) {
            $ids = array_merge($item->getProduct()->getCrossSells(), $ids);
            $inCart[] = $item->getProductId();
        }

        $ids = array_diff($ids, $inCart);
        shuffle($ids);
        if (!empty($ids)) {
            $products = [];
            $limit = $this->options->get('shopping.cross_sells_product_limit', 3);
            foreach ($ids as $id) {
                if (sizeof($products) >= $limit) {
                    break;
                }
                $product = $this->productService->find($id);
                if($product instanceof \Jigoshop\Entity\Product) {
                    $products[] = $this->productService->find($id);
                }
            }

            if(count($products)) {
                Render::output('shop/cart/cross_sells', [
                    'products' => $products,
                ]);
            }
        }
	}
}
