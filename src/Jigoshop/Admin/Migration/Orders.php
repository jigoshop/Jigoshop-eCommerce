<?php

namespace Jigoshop\Admin\Migration;

use Jigoshop\Admin\Helper\Migration;
use Jigoshop\Core\Messages;
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Discount\Type;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Entity\Product;
use Jigoshop\Exception;
use Jigoshop\Helper\Render;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\PaymentServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use WPAL\Wordpress;

class Orders implements Tool
{
    const ID = 'jigoshop_orders_migration';

    /** @var Wordpress */
    private $wp;
    /** @var \Jigoshop\Core\Options */
    private $options;
    /** @var Messages */
    private $messages;
    /** @var OrderServiceInterface */
    private $orderService;
    /** @var ShippingServiceInterface */
    private $shippingService;
    /** @var PaymentServiceInterface */
    private $paymentService;
    /** @var ProductServiceInterface */
    private $productService;
    /** @var  array */
    private $customer;

    public function __construct(
        Wordpress $wp,
        \Jigoshop\Core\Options $options,
        Messages $messages,
        OrderServiceInterface $orderService,
        ShippingServiceInterface $shippingService,
        PaymentServiceInterface $paymentService,
        ProductServiceInterface $productService
    ) {
        $this->wp = $wp;
        $this->options = $options;
        $this->messages = $messages;
        $this->orderService = $orderService;
        $this->shippingService = $shippingService;
        $this->paymentService = $paymentService;
        $this->productService = $productService;

        $wp->addAction('wp_ajax_jigoshop.admin.migration.orders', [$this, 'ajaxMigrationOrders'], 10, 0);
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
				SELECT DISTINCT p.ID FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
					WHERE p.post_type = %s AND p.post_status <> %s
				ORDER BY p.ID",
            ['shop_order', 'auto-draft'])));

        $countRemain = 0;
        $countDone = 0;

        if (($itemsFromBase = $this->wp->getOption('jigoshop_orders_migrate_id')) !== false) {
            $countRemain = count(unserialize($itemsFromBase));
            $countDone = $countAll - $countRemain;
        }

        Render::output('admin/migration/orders', ['countAll' => $countAll, 'countDone' => $countDone]);
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
     * @param array $orders
     * @return bool migration product status: success or not
     */
    public function migrate($orders)
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

            // Register order status taxonomy to fetch old statuses
            $this->wp->registerTaxonomy('shop_order_status',
                ['shop_order'],
                [
                    'hierarchical' => true,
                    'update_count_callback' => '_update_post_term_count',
                    'labels' => [
                        'name' => __('Order statuses', 'jigoshop-ecommerce'),
                        'singular_name' => __('Order status', 'jigoshop-ecommerce'),
                        'search_items' => __('Search Order statuses', 'jigoshop-ecommerce'),
                        'all_items' => __('All  Order statuses', 'jigoshop-ecommerce'),
                        'parent_item' => __('Parent Order status', 'jigoshop-ecommerce'),
                        'parent_item_colon' => __('Parent Order status:', 'jigoshop-ecommerce'),
                        'edit_item' => __('Edit Order status', 'jigoshop-ecommerce'),
                        'update_item' => __('Update Order status', 'jigoshop-ecommerce'),
                        'add_new_item' => __('Add New Order status', 'jigoshop-ecommerce'),
                        'new_item_name' => __('New Order status Name', 'jigoshop-ecommerce')
                    ],
                    'public' => false,
                    'show_ui' => false,
                    'show_in_nav_menus' => false,
                    'query_var' => true,
                    'rewrite' => false,
                ]
            );

            for ($i = 0, $endI = count($orders); $i < $endI;) {
                $order = $orders[$i];
                // Update central order data
                $status = $this->wp->getTheTerms($order->ID, 'shop_order_status');
                $this->checkSql();

                if (!empty($status)) {
                    $status = $this->_transformStatus($status[0]->slug);
                } else {
                    $status = Status::PENDING;
                }

                $query = $wpdb->prepare("UPDATE {$wpdb->posts} SET post_status = %s WHERE ID = %d", $status,
                    $order->ID);
                $wpdb->query($query);
                $this->checkSql();
                $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                    $order->ID, 'number', $order->ID));
                $this->checkSql();
                $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                    $order->ID, 'updated_at', time()));
                $this->checkSql();

                // Update columns
                do {
                    switch ($orders[$i]->meta_key) {
                        case '_js_completed_date':
                            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_key = %s, meta_value = %d WHERE meta_id = %d",
                                'completed_at',
                                strtotime($orders[$i]->meta_value),
                                $orders[$i]->meta_id
                            ));
                            $this->checkSql();
                            break;
                        case 'order_key':
                            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_id = %d",
                                'key', $orders[$i]->meta_id));
                            $this->checkSql();
                            break;
                        case 'order_data':
                            $data = unserialize($orders[$i]->meta_value);
                            $data = $this->_fetchOrderData($data);

                            // Migrate customer
                            if ($this->customer == null) {
                                $customer = $this->wp->getPostMeta($order->ID, 'customer', true);
                                $this->customer = $customer;
                            }
                            $this->customer = $this->_migrateCustomer($this->customer, $data);
                            $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                                $order->ID,
                                'customer',
                                serialize(serialize($this->customer))
                            ));
                            $this->checkSql();

                            // Migrate coupons
                            if(is_array($data['order_discount_coupons']) && count($data['order_discount_coupons']) && $data['order_discount']) {
                                $discounts = $this->convertCouponsToDiscounts($data);
                                foreach ($discounts as $discount) {
                                    $wpdb->insert($wpdb->prefix . 'jigoshop_order_discount', [
                                        'order_id' => $order->ID,
                                        'type' => $discount['type'],
                                        'code' => $discount['code'],
                                        'amount' => $discount['amount'],
                                    ]);
                                    $discountId = $wpdb->insert_id;
                                    if (isset($discount['meta']) && is_array($discount['meta'])) {
                                        foreach ($discount['meta'] as $key => $value) {
                                            $value = is_array($value) ? serialize($value) : $value;
                                            $wpdb->insert($wpdb->prefix . 'jigoshop_order_discount_meta', [
                                                'discount_id' => $discountId,
                                                'meta_key' => $key,
                                                'meta_value' => $value,
                                            ]);
                                        }
                                    }
                                }
                            }
                            $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                                $order->ID,
                                'coupons',
                                serialize($data['order_discount_coupons'])
                            )); // TODO: HERE
                            $this->checkSql();

                            // Migrate shipping method
                            try {
                                $method = $this->shippingService->get($data['shipping_method']);
                                $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                                    $order->ID,
                                    'shipping',
                                    serialize([
                                        'method' => $method->getState(),
                                        'price' => $data['order_shipping'],
                                        'rate' => '',
                                        // Rates are stored nowhere - so no rate here
                                    ])
                                ));
                                $this->checkSql();
                            } catch (Exception $e) {
                                $this->messages->addWarning(sprintf(__('Shipping method "%s" not found. Order with ID "%d" has no shipping method now.'),
                                    $data['shipping_method'], $order->ID));
                            }

                            // Migrate payment method
                            try {
                                $method = $this->paymentService->get($data['payment_method']);
                                $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                                    $order->ID,
                                    'payment',
                                    $method->getId()
                                ));
                                $this->checkSql();
                            } catch (Exception $e) {
                                $this->messages->addWarning(sprintf(__('Payment method "%s" not found. Order with ID "%d" has no payment method now.'),
                                    $data['payment_method'], $order->ID));
                            }

                            // Migrate order totals
                            $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                                $order->ID,
                                'subtotal',
                                $data['order_subtotal']
                            ));
                            $this->checkSql();
                            $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                                $order->ID,
                                'discount',
                                $data['order_discount']
                            ));
                            $this->checkSql();
                            $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                                $order->ID,
                                'total',
                                $data['order_total']
                            ));
                            $this->checkSql();
                            break;
                        case 'customer_user':
                            if ($this->customer == null) {
                                $customer = $this->wp->getPostMeta($order->ID, 'customer', true);
                                if ($customer !== false) {
                                    /** @var Customer $customer */
                                    $customer = maybe_unserialize(maybe_unserialize($customer));
                                    if (!$customer) {
                                        $customer = new Customer();
                                    }
                                } else {
                                    $customer = new Customer();
                                }

                                $this->customer = $customer;
                            }

                            /** @var \WP_User $user */
                            if (($user = $this->wp->getUserBy('id', $orders[$i]->meta_value)) !== false) {
                                $this->checkSql();
                                $this->customer->setId($user->ID);
                                $this->customer->setLogin($user->get('login'));
                                $this->customer->setEmail($user->get('user_email'));
                                $this->customer->setName($user->get('display_name'));
                                $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %d WHERE post_id = %d AND meta_key = %s",
                                    serialize(serialize($this->customer)), $orders[$i]->meta_id, 'customer'));
                                $this->checkSql();
                                $userId = $orders[$i]->meta_value;
                            } else {
                                $userId = 0;
                                $guest = new Customer\Guest();
                                $guest->setBillingAddress($this->customer->getBillingAddress());
                                $guest->setShippingAddress($this->customer->getShippingAddress());

                                $this->customer = $guest;
                            }

                            $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %d)",
                                $order->ID,
                                'customer_id',
                                $userId
                            ));
                            break;
                        case 'order_items':
                            $data = unserialize($orders[$i]->meta_value);
                            $globalTaxRate = 0.0;

                            foreach ($data as $itemData) {
                                /** @var Product $product */
                                $itemData = $this->_fetchItemData($itemData);
                                $product = null;
                                $productGetId = null;

                                if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE ID = %d",
                                        $itemData['id'])) > 0
                                ) {
                                    $product = $this->productService->find($itemData['id']);
                                    $productGetId = $product->getId();
                                }


                                $tax = 0.0;
                                $taxRate = 0;

                                if ($itemData['qty'] == 0) {
                                    $itemData['qty'] = 1;
                                }

                                $cost = $itemData['cost'] * $itemData['qty'];
                                if (!empty($itemData['taxrate']) && $itemData['taxrate'] > 0) {
                                    $tax = $cost * $itemData['taxrate'] / 100;
                                    $taxRate = $itemData['taxrate'];
                                } else {
                                    if (isset($itemData['cost_inc_tax']) && $itemData['cost'] < $itemData['cost_inc_tax']) {
                                        $tax = ($itemData['cost_inc_tax'] - $itemData['cost']) * $itemData['qty'];
                                        $taxRate = $tax / $itemData['cost'];
                                    }
                                }

                                $globalTaxRate += $taxRate;

                                $productGetType = false;
                                if ($productGetId == null) {
                                    if (isset($itemData['variation_id']) && !empty($itemData['variation_id'])) {
                                        $productGetType = Product\Variable::TYPE;
                                    } else {
                                        $productGetType = Product\Simple::TYPE;
                                    }
                                } else {
                                    $productGetType = $product->getType();
                                }

                                $insertOrderData = [
                                    'order_id' => $order->ID,
                                    'product_type' => $productGetType,
                                    'title' => $itemData['name'],
                                    'tax_classes' => '',
                                    'price' => $itemData['cost'],
                                    'tax' => $tax,
                                    'quantity' => $itemData['qty'],
                                    'cost' => $cost,
                                ];

                                if ($productGetId != null) {
                                    $insertOrderData['product_id'] = $productGetId;
                                }

                                $wpdb->insert($wpdb->prefix . 'jigoshop_order_item', $insertOrderData);
                                $this->checkSql();
                                $itemId = $wpdb->insert_id;

                                if($productGetId != null) {
                                    $this->_addDownloadableMeta($order->ID, $productGetId, $itemId);
                                }

                                if (isset($itemData['variation_id']) && !empty($itemData['variation_id']) && ($productGetId == null || $product instanceof Product\Variable)) {
                                    $wpdb->query($wpdb->prepare(
                                        "INSERT INTO {$wpdb->prefix}jigoshop_order_item_meta (item_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                                        $itemId, 'variation_id', $itemData['variation_id'] // TODO: HERE
                                    ));
                                    $this->checkSql();
                                    $this->_addDownloadableMeta($order->ID, $itemData['variation_id'], $itemId);

                                    if ($productGetId !== null) {
                                        /** @var Product\Variable\Variation $variationProduct */
                                        /** @var Product\Variable $product */
                                        $variationProduct = $product->getVariation($itemData['variation_id']);
                                        if (is_array($itemData['variation']) && $variationProduct && $variationProduct instanceof Product\Variable\Variation) {
                                            foreach ($itemData['variation'] as $variation => $variationValue) {
                                                $variation = str_replace('tax_', '', $variation);
                                                $attribute = $this->getAttribute($variationProduct, $variation);

                                                if ($attribute === null) {
                                                    $this->messages->addWarning(sprintf(__('Attribute "%s" not found for variation ID "%d".',
                                                        'jigoshop'), $variation, $variationProduct->getId()));
                                                    continue;
                                                }

                                                $option = $this->getAttributeOption($attribute, $variationValue);

                                                if ($option === null) {
                                                    $this->messages->addWarning(sprintf(__('Attribute "%s" option "%s" not found for variation ID "%d".',
                                                        'jigoshop'), $variation, $variationValue,
                                                        $variationProduct->getId()));
                                                    continue;
                                                }

                                                $wpdb->query($wpdb->prepare(
                                                    "INSERT INTO {$wpdb->prefix}jigoshop_order_item_meta (item_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                                                    $itemId, $attribute->getAttribute()->getId(), $option->getId()
                                                ));
                                                $this->checkSql();
                                            }
                                        }
                                    }
                                }
                            }
                            $wpdb->query($wpdb->prepare(
                                "INSERT INTO {$wpdb->prefix}jigoshop_order_tax (order_id, label, tax_class, rate, is_compound) VALUES (%d, %s, %s, %d, %d) ON DUPLICATE KEY UPDATE order_id = %d, label = %s ,tax_class = %s, rate = %d , is_compound= %d",
                                $order->ID, __('Standard', 'jigoshop-ecommerce'), 'standard',
                                $globalTaxRate / (count($data) == 0 ? 1 : count($data)), false,
                                $order->ID, __('Standard', 'jigoshop-ecommerce'), 'standard',
                                $globalTaxRate / (count($data) == 0 ? 1 : count($data)), false
                            ));
                            $this->checkSql();
                            break;
                    }

                    $i++;
                } while ($i < $endI && $orders[$i]->ID == $order->ID);
            }

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

            Migration::saveLog(__('Migration orders end with error: ', 'jigoshop-ecommerce') . $e);

            return false;
        }
    }

    private function _transformStatus($status)
    {
        switch ($status) {
            case 'pending':
            case 'waiting-for-payment':
                return Status::PENDING;
            case 'processing':
                return Status::PROCESSING;
            case 'completed':
                return Status::COMPLETED;
            case 'cancelled':
                return Status::CANCELLED;
            case 'refunded':
                return Status::REFUNDED;
            case 'on-hold':
            default:
                return Status::ON_HOLD;
        }
    }

    private function _migrateCustomer($customer, $data)
    {
        $data = $this->_fetchCustomerData($data);
        if (!$customer) {
            $customer = new Customer();
        } else {
            $customer = maybe_unserialize(maybe_unserialize($customer));
        }

        if (!($customer instanceof Customer)) {
            $customer = new Customer();
        }

        if (!empty($data['billing_company']) && !empty($data['billing_euvatno'])) {
            $address = new Customer\CompanyAddress();
            $address->setCompany($data['billing_company']);
            $address->setVatNumber($data['billing_euvatno']);
        } else {
            $address = new Customer\Address();
        }

        $address->setFirstName($data['billing_first_name']);
        $address->setLastName($data['billing_last_name']);
        $address->setAddress($data['billing_address_1'] . ' ' . $data['billing_address_2']);
        $address->setCountry($data['billing_country']);
        $address->setState($data['billing_state']);
        $address->setPostcode($data['billing_postcode']);
        $address->setPhone($data['billing_phone']);
        $address->setEmail($data['billing_email']);
        $customer->setBillingAddress($address);

        if (!empty($data['shipping_company'])) {
            $address = new Customer\CompanyAddress();
            $address->setCompany($data['shipping_company']);
        } else {
            $address = new Customer\Address();
        }
        $address->setFirstName($data['shipping_first_name']);
        $address->setLastName($data['shipping_last_name']);
        $address->setAddress($data['shipping_address_1'] . ' ' . $data['shipping_address_2']);
        $address->setCountry($data['shipping_country']);
        $address->setState($data['shipping_state']);
        $address->setPostcode($data['shipping_postcode']);

        $customer->setShippingAddress($address);

        return $customer;
    }

    /**
     * @param $variationProduct Product\Variable\Variation Variation to search.
     * @param $variation        string Attribute slug to find.
     *
     * @return Product\Variable\Attribute|null Attribute found.
     */
    private function getAttribute($variationProduct, $variation)
    {
        foreach ($variationProduct->getAttributes() as $attribute) {
            /** @var $attribute Product\Variable\Attribute */
            if ($attribute->getAttribute()->getSlug() == $variation) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * @param $attribute Product\Variable\Attribute Attribute to search.
     * @param $value     string Option to find.
     *
     * @return \Jigoshop\Entity\Product\Attribute\Option|null Option found.
     */
    private function getAttributeOption($attribute, $value)
    {
        foreach ($attribute->getAttribute()->getOptions() as $option) {
            /** @var $option Product\Attribute\Option */
            if ($option->getValue() == $value) {
                return $option;
            }
        }

        return null;
    }

    public function ajaxMigrationOrders()
    {
        try {
//			1 - if first time ajax request
            if ($_POST['msgLog'] == 1) {
                Migration::saveLog(__('Migration orders START.', 'jigoshop-ecommerce'), true);
            }

            $wpdb = $this->wp->getWPDB();

            $ordersIdsMigration = [];

            if (($TMP_ordersIdsMigration = $this->wp->getOption('jigoshop_orders_migrate_id')) === false) {
                $query = $wpdb->prepare("
				SELECT DISTINCT p.ID FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
					WHERE p.post_type = %s AND p.post_status <> %s
					ORDER BY p.ID",
                    'shop_order', 'auto-draft');
                $orders = $wpdb->get_results($query);

                $countMeta = count($orders);

                for ($aa = 0; $aa < $countMeta; $aa++) {
                    $ordersIdsMigration[] = $orders[$aa]->ID;
                }

                $ordersIdsMigration = array_unique($ordersIdsMigration);
                $this->wp->updateOption('jigoshop_orders_migrate_id', serialize($ordersIdsMigration));
                $this->wp->updateOption('jigoshop_orders_migrate_count', count($ordersIdsMigration));
            } else {
                $ordersIdsMigration = unserialize($TMP_ordersIdsMigration);
            }

            $countAll = $this->wp->getOption('jigoshop_orders_migrate_count');
            $singleOrdersId = array_shift($ordersIdsMigration);
            $countRemain = count($ordersIdsMigration);

            $query = $wpdb->prepare("
				SELECT DISTINCT p.ID, pm.* FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
					WHERE p.post_type = %s AND p.post_status <> %s AND p.ID = %d",
                'shop_order', 'auto-draft', $singleOrdersId);
            $order = $wpdb->get_results($query);

            $ajax_response = [
                'success' => true,
                'percent' => floor(($countAll - $countRemain) / $countAll * 100),
                'processed' => $countAll - $countRemain,
                'remain' => $countRemain,
                'total' => $countAll,
            ];

            if ($singleOrdersId) {
                if ($this->migrate($order)) {
                    $this->wp->updateOption('jigoshop_orders_migrate_id', serialize($ordersIdsMigration));
                } else {
                    $ajax_response['success'] = false;
                    Migration::saveLog(__('Migration orders end with error.', 'jigoshop-ecommerce'));
                }
            } elseif ($countRemain == 0) {
                $this->wp->updateOption('jigoshop_orders_migrate_id', serialize($ordersIdsMigration));
                Migration::saveLog(__('Migration orders END.', 'jigoshop-ecommerce'));
            }

            echo json_encode($ajax_response);

        } catch (Exception $e) {
            if (WP_DEBUG) {
                \Monolog\Registry::getInstance(JIGOSHOP_LOGGER)->addDebug($e);
            }
            echo json_encode([
                'success' => false,
            ]);

            Migration::saveLog(__('Migration orders end with error: ', 'jigoshop-ecommerce') . $e);
        }
        exit;
    }

    protected function _fetchData($defaults, $args)
    {
        return array_merge($defaults, $args);
    }

    protected function _fetchCustomerData($args)
    {
        $defaults = [
            'billing_company' => '',
            'billing_euvatno' => '',
            'billing_first_name' => '',
            'billing_last_name' => '',
            'billing_address_1' => '',
            'billing_address_2' => '',
            'billing_country' => '',
            'billing_state' => '',
            'billing_postcode' => '',
            'billing_phone' => '',
            'billing_email' => '',
            'shipping_company' => '',
            'shipping_first_name' => '',
            'shipping_last_name' => '',
            'shipping_address_1' => '',
            'shipping_address_2' => '',
            'shipping_country' => '',
            'shipping_state' => '',
            'shipping_postcode' => '',
        ];

        return $this->_fetchData($defaults, $args);
    }

    protected function _fetchOrderData($args)
    {
        $defaults = [
            'shipping_method' => '',
            'shipping_service' => '',
            'payment_method' => '',
            'payment_method_title' => '',
            'order_subtotal' => 0,
            'order_discount_subtotal' => 0,
            'order_shipping' => 0,
            'order_discount' => 0,
            'order_tax' => '',
            'order_tax_no_shipping_tax' => 0,
            'order_tax_divisor' => 0,
            'order_shipping_tax' => 0,
            'order_total' => 0,
            'order_total_prices_per_tax_class_ex_tax' => [],
            'order_discount_coupons' => [],
        ];

        return $this->_fetchData($defaults, $args);
    }

    protected function _fetchItemData($args)
    {
        $defaults = [
            'id' => 0,
            'variation_id' => 0,
            'variation' => [],
            'cost_inc_tax' => 0,
            'name' => '',
            'qty' => 1,
            'cost' => 0,
            'taxrate' => 0,
        ];

        if ($args['variation_id'] > 0) {
            $post = $this->wp->getPost($args['variation_id']);
            if ($post) {
                /** @var Product\Variable $product */
                $product = $this->productService->find($post->post_parent);
                if ($product && $product->getId() && $product instanceof Product\Variable) {
                    $args['name'] = $product->getVariation($post->ID)->getTitle();
                }
            }
        }

        return $this->_fetchData($defaults, $args);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function convertCouponsToDiscounts($data)
    {
        $discounts = [];
        $percentProductsCoupons = [];
        foreach ($data['order_discount_coupons'] as $coupon) {
            $discountAmount = 0;
            if ($coupon['type'] == 'fixed_cart' || $coupon['type'] == 'fixed_product') {
                $discountAmount = $coupon['amount'];
            } else if ($coupon['type'] == 'percent') {
                $discountAmount = $data['order_subtotal'] * $coupon['amount'] / 100;
            } else {
                $percentProductsCoupons[] = $coupon['amount'];
                continue;
            }

            $discount = [
                'type' => Type::COUPON,
                'code' => $coupon['code'],
                'amount' => $discountAmount,
                'meta' => ['js1_coupon' => $coupon]
            ];
            $discounts[] = $discount;

            $data['order_discount'] -= $discountAmount;
        }

        if ($data['order_discount'] < 0) {
            for ($i = 0; $i < count($discounts); $i++) {
                $discounts[$i]['amount'] -= abs($data['order_discount']) / count($discounts);
            }
        }

        if ($data['order_discount'] > 0 && count($percentProductsCoupons)) {
            foreach ($percentProductsCoupons as $coupon) {
                $discount = [
                    'type' => Type::COUPON,
                    'code' => $coupon['code'],
                    'amount' => $data['order_discount'] / count($percentProductsCoupons),
                    'meta' => ['js1_coupon' => $coupon]
                ];
                $discounts[] = $discount;
            }
        } elseif ($data['order_discount'] > 0) {
            $discount = [
                'type' => Type::USER_DEFINED,
                'code' => 'manually_added',
                'amount' => $data['order_discount'] / count($percentProductsCoupons),
                'meta' => ['js1_coupon' => $coupon]
            ];
            $discounts[] = $discount;
        }

        return $discounts;
    }

    /*
     * @param $orderId
     * @param $productId
     * @param $itemId
     */
    private function _addDownloadableMeta($orderId, $productId, $itemId) {
        $wpdb = $this->wp->getWPDB();
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT meta1.post_id as order_id, product.downloads_remaining as downloads, meta2.meta_value as file 
                    FROM {$wpdb->prefix}jigoshop_downloadable_product_permissions as product
                    LEFT JOIN {$wpdb->postmeta} as meta1 ON (meta1.meta_key = 'order_key' AND product.order_key = meta1.meta_value)
                    LEFT JOIN {$wpdb->postmeta} as meta2 ON (meta2.post_id = product.product_id AND meta2.meta_key = 'file_path')
                    WHERE product.product_id = %d", $productId), ARRAY_A);
        foreach ($results as $result) {
            if ($result['order_id'] == $orderId) {
                $wpdb->query($wpdb->prepare(
                    "INSERT INTO {$wpdb->prefix}jigoshop_order_item_meta (item_id, meta_key, meta_value) VALUES (%d, %s, %s) , (%d, %s, %s)",
                    $itemId, 'downloads', $result['downloads'], $itemId, 'file', $result['file']
                ));
                $this->checkSql();
            }
        }
    }
}
