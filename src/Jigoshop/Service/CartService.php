<?php

namespace Jigoshop\Service;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Cart;
use Jigoshop\Entity\Customer\Address;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Entity\Session;
use Jigoshop\Exception;
use Jigoshop\Factory\Customer;
use Jigoshop\Factory\Order as OrderFactory;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Tax as TaxHelper;
use Jigoshop\Helper\Validation;
use Jigoshop\Shipping\Dummy;
use Jigoshop\Shipping\Method;
use WPAL\Wordpress;

class CartService implements CartServiceInterface
{
	const CART = 'jigoshop_cart';
	const CART_ID = 'jigoshop_cart_id';

	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var CustomerServiceInterface */
	private $customerService;
	/** @var ProductServiceInterface */
	private $productService;
    /** @var ShippingServiceInterface */
	private $shippingService;
    /** @var  Session */
    private $session;
    /** @var PaymentServiceInterface */
	private $paymentService;
	/** @var OrderFactory */
	private $orderFactory;
	/** @var string */
	private $currentUserCartId;

	private $carts = [];

	public function __construct(Wordpress $wp, Options $options, CustomerServiceInterface $customerService,
		ProductServiceInterface $productService, ShippingServiceInterface $shippingService,
		SessionServiceInterface $sessionService, PaymentServiceInterface $paymentService, OrderFactory $orderFactory)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->customerService = $customerService;
		$this->productService = $productService;
		$this->shippingService = $shippingService;
        $this->session = $sessionService->get($sessionService->getCurrentKey());
		$this->paymentService = $paymentService;
		$this->orderFactory = $orderFactory;

		if ($this->session->getField(self::CART) == '') {
			$this->session->setField(self::CART, []);
		}

		$this->currentUserCartId = $this->generateCartId();

		//TODO: do something with this
        TaxHelper::setCartService($this);
	}

	private function generateCartId()
	{
		if ($this->session->getField(self::CART_ID)) {
			$id = $this->session->getField(self::CART_ID);
		} elseif (isset($_COOKIE[self::CART_ID])) {
			$id = $_COOKIE[self::CART_ID];
		} elseif ($this->wp->getCurrentUserId() > 0) {
            $id = $this->wp->getCurrentUserId();
        } else {
			$id = md5((isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') .time().$_SERVER['REMOTE_ADDR'].rand(1, 10000000));
		}

		if ($this->session->getField(self::CART_ID) == '') {
            $this->session->setField(self::CART_ID, $id);
		}
		if (!isset($_COOKIE[self::CART_ID])) {
			setcookie(self::CART_ID, $id, null, '/', null, null, true);
		}

		return $id;
	}

	public function init()
	{
		$this->wp->doAction('jigoshop\service\cart');
	}

	/**
	 * Find and fetches cart for current user.
	 * If cart is not found - returns new empty one.
	 *
	 * @return Cart Prepared cart instance.
	 */
	public function getCurrent()
	{
		return $this->get($this->getCartIdForCurrentUser());
	}

	/**
	 * Find and fetches saved cart.
	 * If cart is not found - returns new empty one.
	 *
	 * @param $id string Id of cart to fetch.
	 *
	 * @return \Jigoshop\Entity\Cart Prepared cart instance.
	 */
	public function get($id)
	{
		if (!isset($this->carts[$id])) {
		    try {
                $cart = new Cart($this->options->get('tax.classes'));
                $cart->setTaxIncluded($this->options->get('tax.prices_entered', 'without_tax') == 'with_tax');
                $cart->setCustomer($this->customerService->getCurrent());
                $cart->getCustomer()->selectTaxAddress($this->options->get('taxes.shipping') ? 'shipping' : 'billing');

                // Fetch data from session if available
                $cart->setId($id);

                $state = $this->getStateFromSession($id);
                if (isset($_POST['jigoshop_order']) && Pages::isCheckout()) {
                    $state = $this->getStateFromCheckout($state);
                }

                // TODO: Support for transients?
                $cart = $this->orderFactory->fill($cart, $state);
                $this->carts[ $id ] = $this->wp->applyFilters('jigoshop\service\cart\get', $cart, $state);
            } catch(\Exception $e) {
		        //in case of error for eg. 'not enough stock' clear cart items.
		        $cart->removeItems();
                $this->carts[ $id ] = $this->wp->applyFilters('jigoshop\service\cart\get', $cart, $state);
            }
		}

		return $this->carts[$id];
	}

	private function getStateFromSession($id)
	{
		$state = [];

        $session = $this->session->getField(self::CART);

        if (isset($session[$id])) {
            $state = $session[$id];

            $state['customer'] = $this->customerService->getCurrent();

			if (isset($state['items'])) {
				$productService = $this->productService;
				$this->wp->addFilter('jigoshop\internal\order\item', function ($value, $data) use ($productService){
					return $productService->findForState($data);
				}, 10, 2);
				$state['items'] = unserialize($state['items']);
			}

			if (isset($state['shipping'])) {
				$shipping = $state['shipping'];
				if (!empty($shipping['method'])) {
					$state['shipping'] = [
						'method' => $this->shippingService->findForState($shipping['method']),
						'price' => $shipping['price'],
						'rate' => isset($shipping['rate']) ? $shipping['rate'] : null,
                    ];
				}
			}

			if (isset($state['payment']) && !empty($state['payment'])) {
				$state['payment'] = $this->paymentService->get($state['payment']);
			}
		}

		return $state;
	}

	private function getStateFromCheckout($state)
	{
		$state['customer_note'] = $_POST['jigoshop_order']['customer_note'];
		$state['billing_address'] = $_POST['jigoshop_order']['billing_address'];

		if ($this->options->get('shipping.only_to_billing') == false && $_POST['jigoshop_order']['different_shipping_address'] == 'on') {
			$state['shipping_address'] = $_POST['jigoshop_order']['shipping_address'];
		} else {
			$state['shipping_address'] = $state['billing_address'];
		}

		if (isset($_POST['jigoshop_order']['payment_method'])) {
			$payment = $this->paymentService->get($_POST['jigoshop_order']['payment_method']);
			$this->wp->doAction('jigoshop\service\cart\payment', $payment);
			$state['payment'] = $payment;
		}

		if (isset($_POST['jigoshop_order']['shipping_method'])) {
			$shipping = $this->shippingService->get($_POST['jigoshop_order']['shipping_method']);
			$this->wp->doAction('jigoshop\service\cart\shipping', $shipping);
			$state['shipping'] = [
				'method' => $shipping,
				'rate' => isset($_POST['jigoshop_order']['shipping_method_rate']) ? $_POST['jigoshop_order']['shipping_method_rate'] : null,
				'price' => -1,
            ];
		}

		return $state;
	}

	/**
	 * Returns cart ID for current user.
	 * If the user is logged in - returns his ID so his cart will be properly loaded.
	 * Otherwise generates random string based on available user data to preserve it's cart.
	 *
	 * @return string Cart ID for currently logged in user.
	 */
	public function getCartIdForCurrentUser()
	{
		return $this->currentUserCartId;
	}

	/**
	 * Validates whether
	 *
	 * @param OrderInterface $cart
	 */
	public function validate(OrderInterface $cart)
	{
		$customer = $cart->getCustomer();
		$billingErrors = $this->validateAddress($customer->getBillingAddress());

		if ($customer->getBillingAddress()->getEmail() == null) {
			$billingErrors[] = __('Email address is empty.', 'jigoshop-ecommerce');
		}
		if ($customer->getBillingAddress()->getPhone() == null) {
			$billingErrors[] = __('Phone is empty.', 'jigoshop-ecommerce');
		}

		if (!Validation::isEmail($customer->getBillingAddress()->getEmail())) {
			$billingErrors[] = __('Email address is invalid.', 'jigoshop-ecommerce');
		}

		$shippingErrors = $this->validateAddress($customer->getShippingAddress());

		$billingErrors = $this->wp->applyFilters(
			'jigoshop\service\cart\billing_address_validation',
			$billingErrors,
			$customer->getBillingAddress()
		);

		$shippingErrors = $this->wp->applyFilters(
			'jigoshop\service\cart\shipping_address_validation',
			$shippingErrors,
			$customer->getShippingAddress()
		);

		$error = '';
		if (!empty($billingErrors)) {
			$error .= $this->prepareAddressError(__('Billing address is not valid.', 'jigoshop-ecommerce'), $billingErrors);
		}
		if (!empty($shippingErrors)) {
			$error .= $this->prepareAddressError(__('Shipping address is not valid.', 'jigoshop-ecommerce'), $shippingErrors);
		}
		if (!empty($error)) {
			throw new Exception($error);
		}
	}

	/**
	 * @param $address Address
	 *
	 * @return array
	 */
	private function validateAddress($address)
	{
		$errors = [];

		if ($address->isValid()) {
			if ($address->getFirstName() == null) {
				$errors[] = __('First name is empty.', 'jigoshop-ecommerce');
			}
			if ($address->getLastName() == null) {
				$errors[] = __('Last name is empty.', 'jigoshop-ecommerce');
			}
			if ($address->getAddress() == null) {
				$errors[] = __('Address is empty.', 'jigoshop-ecommerce');
			}
			if ($address->getCountry() == null) {
				$errors[] = __('Country is not selected.', 'jigoshop-ecommerce');
			}
			if ($address->getState() == null) {
				$errors[] = __('State or province is not selected.', 'jigoshop-ecommerce');
			}
			if ($address->getCity() == null) {
				$errors[] = __('City is empty.', 'jigoshop-ecommerce');
			}
			if ($address->getPostcode() == null) {
				$errors[] = __('Postcode is empty.', 'jigoshop-ecommerce');
			}
			if ($this->options->get('shopping.validate_zip') && !Validation::isPostcode($address->getPostcode(), $address->getCountry())) {
				$errors[] = __('Invalid postcode.', 'jigoshop-ecommerce');
			}
		}

		if (!Country::exists($address->getCountry())) {
			$errors[] = sprintf(__('Country "%s" does not exist.', 'jigoshop-ecommerce'), $address->getCountry());
		}
		if (Country::hasStates($address->getCountry()) && !Country::hasState($address->getCountry(), $address->getState())) {
			$errors[] = sprintf(__('Country "%s" does not have state "%s".', 'jigoshop-ecommerce'), $address->getCountry(), $address->getState());
		}

		return $errors;
	}

	private function prepareAddressError($message, $errors)
	{
		return $message.'<ul><li>'.join('</li><li>', $errors).'</li></ul>';
	}

	/**
	 * Saves cart for current user.
	 *
	 * @param \Jigoshop\Entity\Cart $cart Cart to save.
	 */
	public function save(Cart $cart)
	{
		// TODO: Support for transients?
		$cart->recalculateCoupons();
		if($cart->getShippingMethod() == null) {
            $method = $this->shippingService->getCheapest($cart);
            if($method instanceof Method) {
                $cart->setShippingMethod($method);
            }
        } else {
            try {
                $cart->setShippingMethod($cart->getShippingMethod());
            } catch(\Exception $e) {
                $method = $this->shippingService->getCheapest($cart);
                if($method instanceof Method) {
                    $cart->setShippingMethod($method);
                } else {
                    $cart->removeShippingMethod();
                }
            }
        }

        $session = $this->session->getField(self::CART);
        $session[$cart->getId()] = $cart->getStateToSave();
        $this->session->setField(self::CART, $session);

		do_action('jigoshop\cart\save', $cart);
	}

	/**
	 * Removes cart.
	 *
	 * @param \Jigoshop\Entity\Cart $cart Cart to remove.
	 */
	public function remove(Cart $cart)
	{
        $session = $this->session->getField(self::CART);
		if (isset($session[$cart->getId()])) {
			unset($session[$cart->getId()]);
            $this->session->setField(self::CART, $session);
		}
	}

	/**
	 * Creates cart from order - useful for cancelling orders.
	 *
	 * @param $cartId string Cart ID to use.
	 * @param $order  Order Order to base cart on.
	 *
	 * @return \Jigoshop\Entity\Cart The cart.
	 */
	public function createFromOrder($cartId, $order)
	{
		$cart = new \Jigoshop\Entity\Cart($this->options->get('tax.classes'));

		$cart->setId($cartId);
		$cart->setCustomer($order->getCustomer());
		$cart->setCustomerNote($order->getCustomerNote());
		$cart->setTaxDefinitions($order->getTaxDefinitions());

		foreach ($order->getItems() as $item) {
			/** @var $item Order\Item */
			$item = clone $item;
			$item->setId(null);
			$cart->addItem($item);
		}

//		foreach ($order->getCoupons() as $coupon) {
//			$cart->addCoupon()
//		}

		$shipping = $order->getShippingMethod();
		if ($shipping !== null) {
			$cart->setShippingMethod($shipping);
			$cart->setShippingTax($order->getShippingTax());
		}

		$payment = $order->getPaymentMethod();
		if ($payment !== null) {
			$cart->setPaymentMethod($payment);
		}

		return $cart;
	}
}
