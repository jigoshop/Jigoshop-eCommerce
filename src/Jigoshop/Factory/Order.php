<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer as CustomerEntity;
use Jigoshop\Entity\Customer\CompanyAddress;
use Jigoshop\Entity\Order as Entity;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Entity\Product as ProductEntity;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Geolocation;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Tax;
use Jigoshop\Shipping\Method as ShippingMethod;
use Jigoshop\Payment\Method as PaymentMethod;
use Jigoshop\Service\CouponServiceInterface;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\PaymentServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Shipping\MultipleMethod;
use WPAL\Wordpress;

class Order implements EntityFactoryInterface
{
    /** @var \WPAL\Wordpress */
    private $wp;
    /** @var Options */
    private $options;
    /** @var Messages */
    private $messages;
    /** @var CustomerServiceInterface */
    private $customerService;
    /** @var ProductServiceInterface */
    private $productService;
    /** @var ShippingServiceInterface */
    private $shippingService;
    /** @var PaymentServiceInterface */
    private $paymentService;
    /** @var CouponServiceInterface */
    private $couponService;

    public function __construct(Wordpress $wp, Options $options, Messages $messages)
    {
        $this->wp = $wp;
        $this->options = $options;
        $this->messages = $messages;
    }

    public function init(
        CustomerServiceInterface $customerService,
        ProductServiceInterface $productService,
        ShippingServiceInterface $shippingService,
        PaymentServiceInterface $paymentService,
        CouponServiceInterface $couponService
    ) {
        $this->customerService = $customerService;
        $this->productService = $productService;
        $this->shippingService = $shippingService;
        $this->paymentService = $paymentService;
        $this->couponService = $couponService;
    }

    /**
     * Creates new order properly based on POST variable data.
     *
     * @param $id int Post ID to create object for.
     *
     * @return Entity
     */
    public function create($id)
    {
        $post = $this->wp->getPost($id);

        $_POST = stripslashes_deep($_POST);
        // Support for our own post types and "Publish" button.
        if (isset($_POST['original_post_status'])) {
            $post->post_status = $_POST['original_post_status'];
        }

        $order = $this->fetch($post);
        $data = [
            'updated_at' => time(),
        ];

        if (isset($_POST['jigoshop_order']['status'])) {
            $order->setStatus($_POST['jigoshop_order']['status']);
        }
        if (isset($_POST['post_excerpt'])) {
            $data['customer_note'] = trim($_POST['post_excerpt']);
        }

        if (isset($_POST['jigoshop_order'])) {
            $data = array_merge($data, $_POST['jigoshop_order']);
        }

        $data['items'] = $this->getItems($id);
        $data['discounts'] = $this->getDiscounts($id);

        if (isset($_POST['order']['shipping'])) {
            $data['shipping'] = [
                'method' => null,
                'rate' => null,
                'price' => -1,
            ];

            $method = $this->shippingService->get($_POST['order']['shipping']);
            if ($method instanceof MultipleMethod && isset($_POST['order']['shipping_rate'], $_POST['order']['shipping_rate'][$method->getId()])) {
                $method->setShippingRate($_POST['order']['shipping_rate'][$method->getId()]);
                $data['shipping']['rate'] = $method->getShippingRate();
            }

            $data['shipping']['method'] = $method;
        }

        return $order = $this->wp->applyFilters('jigoshop\factory\order\create', $this->fill($order, $data));
    }

    /**
     * Fetches order from database.
     *
     * @param $post \WP_Post Post to fetch order for.
     *
     * @return \Jigoshop\Entity\Order
     */
    public function fetch($post)
    {
        if ($post->post_type != Types::ORDER) {
            return null;
        }

        $order = new Entity($this->options->get('tax.classes'));
        /** @var Entity $order */
        $order = $this->wp->applyFilters('jigoshop\factory\order\fetch\before', $order);
        $state = [];

        if ($post) {
            $state = array_map(function ($item) {
                return $item[0];
            }, $this->wp->getPostMeta($post->ID));

            $order->setId($post->ID);
            if (isset($state['customer'])) {
                // Customer must be unserialized twice "thanks" to WordPress second serialization.
                /** @var CustomerEntity */
                $state['customer'] = unserialize(unserialize($state['customer']));
                /*
                This code seems unecessary and was causing issues with PDF invoices.
                if ($state['customer'] instanceof CustomerEntity &&
                    !($state['customer'] instanceof CustomerEntity\Guest) &&
                    $state['customer_id'] > 0
                ) {
                    $customer = $this->customerService->find($state['customer_id']);
                    $customer->setBillingAddress($state['customer']->getBillingAddress());
                    $customer->setShippingAddress($state['customer']->getShippingAddress());
                    $state['customer'] = $customer;
                }
                */
            }
            $state['customer_note'] = $post->post_excerpt;
            $state['status'] = $post->post_status;
            $state['created_at'] = strtotime($post->post_date);
            $state['items'] = $this->getItems($post->ID);
            $state['discounts'] = $this->getDiscounts($post->ID);
            if (isset($state['shipping'])) {
                $shipping = unserialize($state['shipping']);
                if (!empty($shipping['method'])) {
                    $state['shipping'] = [
                        'method' => $this->shippingService->findForState($shipping['method']),
                        'price' => $shipping['price'],
                        'rate' => isset($shipping['rate']) ? $shipping['rate'] : null,
                    ];
                }
            }
            if (isset($state['payment'])) {
                $state['payment'] = $this->paymentService->get($state['payment']);
            }

            $order = $this->fill($order, $state);
        }

        return $this->wp->applyFilters('jigoshop\find\order', $order, $state);
    }

    /**
     * @param $id int Order ID.
     *
     * @return array List of items assigned to the order.
     */
    private function getItems($id)
    {
        $wpdb = $this->wp->getWPDB();
        $query = $wpdb->prepare("
			SELECT * FROM {$wpdb->prefix}jigoshop_order_item joi
			LEFT JOIN {$wpdb->prefix}jigoshop_order_item_meta joim ON joim.item_id = joi.id
			WHERE joi.order_id = %d
			ORDER BY joi.id",
            [$id]);
        $results = $wpdb->get_results($query, ARRAY_A);
        $items = [];

        for ($i = 0, $endI = count($results); $i < $endI;) {
            $id = $results[$i]['id'];
            $item = new Entity\Item();
            $item->setId($results[$i]['item_id']);
            $item->setType($results[$i]['product_type']);
            $item->setName($results[$i]['title']);
            $item->setTaxClasses($results[$i]['tax_classes']);
            $item->setQuantity($results[$i]['quantity']);
            $item->setPrice($results[$i]['price']);
            $item->setTax($results[$i]['tax']);

            $product = $this->productService->find($results[$i]['product_id']);
            $product = $this->wp->applyFilters('jigoshop\factory\order\find_product', $product, $item);
            if ($product == null || !$product instanceof ProductEntity) {
                $product = new ProductEntity\Simple();
                $product->setId($results[$i]['product_id']);
            }

            while ($i < $endI && $results[$i]['id'] == $id) {
//				Securing against empty meta's, but still no piece of code does not add the meta.
                if ($results[$i]['meta_key']) {
                    $meta = new Entity\Item\Meta();
                    $meta->setKey($results[$i]['meta_key']);
                    $meta->setValue($results[$i]['meta_value']);
                    $item->addMeta($meta);
                }
                $i++;
            }
            $item->setProduct($product);
            $item->setKey($this->productService->generateItemKey($item));
            $items[] = $item;
        }

        return $items;
    }

    /**
     * Updates order properties based on array data.
     *
     * @param $order \Jigoshop\Entity\Order for update.
     * @param $data array of data for update.
     *
     * @return \Jigoshop\Entity\Order
     */
    public function update(Entity $order, $data)
    {
        if (!empty($data)) {
            $helpers = $this->wp->getHelpers();

            $order = $this->fill($order, $data);
            $order->restoreState($data['jigoshop_order']);
        }

        return $order;
    }

    /**
     * @param $id int Order ID.
     *
     * @return array List of items assigned to the order.
     */
    private function getDiscounts($id)
    {
        $wpdb = $this->wp->getWPDB();
        $query = $wpdb->prepare("
			SELECT * FROM {$wpdb->prefix}jigoshop_order_discount jod
			LEFT JOIN {$wpdb->prefix}jigoshop_order_discount_meta jodm ON jodm.discount_id = jod.id
			WHERE jod.order_id = %d
			ORDER BY jod.id",
            [$id]);
        $results = $wpdb->get_results($query, ARRAY_A);
        $discounts = [];

        for ($i = 0, $endI = count($results); $i < $endI;) {
            $id = $results[$i]['id'];
            $discount = new Entity\Discount();
            $discount->setId($results[$i]['id']);
            $discount->setType($results[$i]['type']);
            $discount->setCode($results[$i]['code']);
            $discount->setAmount($results[$i]['amount']);

            while ($i < $endI && $results[$i]['id'] == $id) {
//				Securing against empty meta's, but still no piece of code does not add the meta.
                if ($results[$i]['meta_key']) {
                    $meta = new Entity\Discount\Meta();
                    $meta->setKey($results[$i]['meta_key']);
                    $meta->setValue($results[$i]['meta_value']);
                    $discount->addMeta($meta);
                }
                $i++;
            }
            $discounts[] = $discount;
        }

        return $discounts;
    }

    public function fill(OrderInterface $order, array $data)
    {
        if (!empty($data['customer']) && is_numeric($data['customer'])) {
            $data['customer'] = $this->customerService->find($data['customer']);
        }

        if (isset($data['customer'])) {

            if (!empty($data['customer'])) {
                $data['customer'] = $this->wp->getHelpers()->maybeUnserialize($data['customer']);
            } else {
                $data['customer'] = new CustomerEntity\Guest();
            }

            if (isset($data['billing_address'])) {
                $data['billing_address'] = array_merge(
                    array_flip(array_keys(ProductHelper::getBasicBillingFields())),
                    $data['billing_address']
                );
                /** @var CustomerEntity $customer */
                $customer = $data['customer'];
                $customer->setBillingAddress($this->createAddress($data['billing_address']));
            }
            if (isset($data['shipping_address'])) {
                $data['shipping_address'] = array_merge(
                    array_flip(array_keys(ProductHelper::getBasicShippingFields())),
                    $data['shipping_address']
                );

                /** @var CustomerEntity $customer */
                $customer = $data['customer'];
                $customer->setShippingAddress($this->createAddress($data['shipping_address']));
            }

            $order->setCustomer($data['customer']);
            unset($data['customer']);
        }
        /** @var OrderInterface $order */
        $order = $this->wp->applyFilters('jigoshop\factory\order\fetch\after_customer', $order);

        if (isset($data['items'])) {
            $order->removeItems();
        }

        if (isset($data['discounts'])) {
            $order->removeDiscounts();
        }

        $order->restoreState($data);

        // Process tax removal for new orders if we have Vat number.
        if($order->getCustomer()->getBillingAddress() instanceof CompanyAddress && $this->options->get('tax.euVat.enabled') && Country::isEU($order->getCustomer()->getBillingAddress()->getCountry())) {
            if($order->getCustomer()->getBillingAddress()->getVatNumber() && $order->getEuVatValidationStatus() === '') {
                $euVatNumberValidationResult = Tax::validateEUVatNumber($order->getCustomer()->getBillingAddress()->getVatNumber(), $order->getCustomer()->getBillingAddress()->getCountry());

                $order->setTaxRemovalState(false);
                if($euVatNumberValidationResult == Tax::EU_VAT_VALIDATION_RESULT_VALID) {
                    if($this->options->get('general.country') == $order->getCustomer()->getBillingAddress()->getCountry()) {
                        if($this->options->get('tax.euVat.removeVatIfCustomerIsLocatedInShopCountry')) {
                            $order->setTaxRemovalState(true);
                        }
                    }
                    else {
                        $order->setTaxRemovalState(true);
                    }
                }
                elseif($euVatNumberValidationResult == Tax::EU_VAT_VALIDATION_RESULT_INVALID || $euVatNumberValidationResult == Tax::EU_VAT_VALIDATION_RESULT_ERROR) {
                    if($this->options->get('tax.euVat.failedValidationHandling') == 'acceptRemoveVat') {
                        $order->setTaxRemovalState(true);
                    }
                }

                $order->setIPAddress($_SERVER['REMOTE_ADDR']);
                $order->setEUVatValidationStatus($euVatNumberValidationResult);

                try {
                    $ipAddressCountry = Geolocation::getCountryOfIP($_SERVER['REMOTE_ADDR']);
                    if($ipAddressCountry !== null) {
                        $order->setIPAddressCountry($ipAddressCountry);
                    }
                }
                catch(Exception $e) {}                
            }
        }

        return $this->wp->applyFilters('jigoshop\factory\order\fill', $order);
    }

    /**
     * @param OrderInterface $order
     * @param $productId
     * @param array $data
     * @return Entity\Item
     */
    public function updateOrderItemByProductId(OrderInterface $order, $productId, array $data){
        /** @var ProductEntity $product */
        $product = $this->productService->find($productId);
        $item = new Entity\Item();
        $item->setProduct($product);
        $key = $this->productService->generateItemKey($item);
        $orderItem = $order->getItem($key);
        if($orderItem){
            $order->removeItem($key);
        }
        $item->setKey($key);
        $item->setName($product->getName());
        $item->setQuantity((int)$data['quantity']);
        if (isset($data['price']) && is_numeric($data['price'])) {
            $item->setPrice((float)$data['price']);
        }
        if ($item->getQuantity() > 0) {
            $item = $this->wp->applyFilters('jigoshop\admin\order\update_product', $item, $order);
        }
        $order->addItem($item);

        return $item;

    }

    private function createAddress($data)
    {
        if (!empty($data['company']) || !empty($data['euvatno'])) {
            $address = new CustomerEntity\CompanyAddress();
            if(isset($data['company'])) {
                $address->setCompany($data['company']);
            }
            if (isset($data['euvatno'])) {
                $address->setVatNumber($data['euvatno']);
            }
        } else {
            $address = new CustomerEntity\Address();
        }

        $address->setFirstName($data['first_name']);
        $address->setLastName($data['last_name']);
        $address->setAddress($data['address']);
        $address->setCountry($data['country']);
        $address->setState($data['state']);
        $address->setCity($data['city']);
        $address->setPostcode($data['postcode']);

        if (isset($data['phone'])) {
            $address->setPhone($data['phone']);
        }

        if (isset($data['email'])) {
            $address->setEmail($data['email']);
        }

        return $address;
    }

    public function fromCart(\Jigoshop\Entity\Cart $cart)
    {
        $order = new \Jigoshop\Entity\Order($this->options->get('tax.classes'));
        $state = $cart->getStateToSave();
        $state['items'] = unserialize($state['items']);
        $state['customer'] = unserialize($state['customer']);
        unset($state['shipping'], $state['payment']);

        $order->setTaxDefinitions($cart->getTaxDefinitions());
        $order->restoreState($state);

        $shipping = $cart->getShippingMethod();
        if ($shipping && $shipping instanceof ShippingMethod) {
            $order->setShippingMethod($shipping);
            $order->setShippingTax($cart->getShippingTax());
        }

        $payment = $cart->getPaymentMethod();
        if ($payment && $payment instanceof PaymentMethod) {
            $order->setPaymentMethod($payment);
        }

        return $order;
    }
}
