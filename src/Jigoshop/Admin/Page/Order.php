<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Entity\Product as ProductEntity;
use Jigoshop\Entity\Product;
use Jigoshop\Exception;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Helper\Tax;
use Jigoshop\Helper\Validation;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Shipping;
use WPAL\Wordpress;

class Order
{
    /** @var \WPAL\Wordpress */
    private $wp;
    /** @var \Jigoshop\Core\Options */
    private $options;
    /** @var OrderServiceInterface */
    private $orderService;
    /** @var ProductServiceInterface */
    private $productService;
    /** @var CustomerServiceInterface */
    private $customerService;
    /** @var ShippingServiceInterface */
    private $shippingService;

    public function __construct(
        Wordpress $wp,
        Options $options,
        OrderServiceInterface $orderService,
        ProductServiceInterface $productService,
        CustomerServiceInterface $customerService,
        ShippingServiceInterface $shippingService
    ) {
        $this->wp = $wp;
        $this->options = $options;
        $this->orderService = $orderService;
        $this->productService = $productService;
        $this->customerService = $customerService;
        $this->shippingService = $shippingService;

        $wp->addAction('admin_enqueue_scripts', function () use ($wp, $options) {
            if ($wp->getPostType() == Types::ORDER) {
                Styles::add('jigoshop.admin.order', \JigoshopInit::getUrl() . '/assets/css/admin/order.css');
                Scripts::add('jigoshop.admin.order', \JigoshopInit::getUrl() . '/assets/js/admin/order.js', [
                    'jquery',
                    'jigoshop.helpers'
                ]);
                Styles::add('jigoshop.vendors.datetimepicker',
                    \JigoshopInit::getUrl() . '/assets/css/vendors/datetimepicker.css');
                Scripts::add('jigoshop.vendors.datetimepicker',
                    \JigoshopInit::getUrl() . '/assets/js/vendors/datetimepicker.js');
                Scripts::localize('jigoshop.admin.order', 'jigoshop_admin_order', [
                    'tax_shipping' => $options->get('tax.shipping'),
                    'ship_to_billing' => $options->get('shipping.only_to_billing'),
                ]);
            }
        });

        $wp->addAction('wp_ajax_jigoshop.admin.order.add_product', [$this, 'ajaxAddProduct'], 10, 0);
        $wp->addAction('wp_ajax_jigoshop.admin.order.update_product', [$this, 'ajaxUpdateProduct'], 10, 0);
        $wp->addAction('wp_ajax_jigoshop.admin.order.remove_product', [$this, 'ajaxRemoveProduct'], 10, 0);
        $wp->addAction('wp_ajax_jigoshop.admin.order.change_country', [$this, 'ajaxChangeCountry'], 10, 0);
        $wp->addAction('wp_ajax_jigoshop.admin.order.change_state', [$this, 'ajaxChangeState'], 10, 0);
        $wp->addAction('wp_ajax_jigoshop.admin.order.change_postcode', [$this, 'ajaxChangePostcode'], 10, 0);
        $wp->addAction('wp_ajax_jigoshop.admin.order.change_shipping_method', [$this, 'ajaxChangeShippingMethod'],
            10, 0);

        $that = $this;
        $wp->addAction('add_meta_boxes_' . Types::ORDER, function () use ($wp, $orderService, $that) {
            $post = $wp->getGlobalPost();
            /** @var \Jigoshop\Entity\Order $order */
            $order = $orderService->findForPost($post);
            $wp->addMetaBox('jigoshop-order-data', $order->getTitle(), [$that, 'dataBox'], Types::ORDER, 'normal',
                'high');
            $wp->addMetaBox('jigoshop-order-items', __('Order Items', 'jigoshop-ecommerce'), [$that, 'itemsBox'],
                Types::ORDER, 'normal', 'high');
            $wp->addMetaBox('jigoshop-order-totals', __('Order Totals', 'jigoshop-ecommerce'), [$that, 'totalsBox'],
                Types::ORDER, 'normal', 'high');
            $wp->removeMetaBox('commentstatusdiv', null, 'normal');
            $wp->removeMetaBox('submitdiv', Types::ORDER, 'side');
            $wp->addMetaBox('commentsdiv', __('Comments'), 'post_comment_meta_box', null, 'normal', 'core');
            $wp->addMetaBox('submitdiv', __('Order Actions', 'jigoshop-ecommerce'), [$that, 'actionsBox'], Types::ORDER,
                'side', 'default');

            if($order->getCustomer()->getBillingAddress() instanceof Customer\CompanyAddress && $order->getCustomer()->getBillingAddress()->getVatNumber() && Country::isEU($order->getCustomer()->getBillingAddress()->getCountry())) {
                $wp->addMetaBox('euvatno', __('EU Vat', 'jigoshop-ecommerce'), [$that, 'euVatNumberBox'], Types::ORDER, 'side', 'default');
            }
        });
    }

    public function ajaxAddProduct()
    {
        try {
            /** @var \Jigoshop\Entity\Order $order */
            $order = $this->orderService->find((int)$_POST['order']);

            if ($order->getId() === null) {
                throw new Exception(__('Order not found.', 'jigoshop-ecommerce'));
            }

            /** @var ProductEntity|ProductEntity\Purchasable $product */
            $post = $this->wp->getPost((int)$_POST['product']);
            if ($post->post_type == 'product_variation' && $post->post_parent > 0) {
                $post = $this->wp->getPost($post->post_parent);
                //TODO: change this!!!
                $_POST['variation_id'] = (int)$_POST['product'];
                $_POST['quantity'] = 1;
            }
            /** @var Product\* $product */
            $product = $this->productService->findforPost($post);

            if ($product->getId() === null) {
                throw new Exception(__('Product not found.', 'jigoshop-ecommerce'));
            }

            /** @var Item $item */
            $item = $this->wp->applyFilters('jigoshop\cart\add', null, $product);

            if ($item === null) {
                throw new Exception(__('Product cannot be added to the order.', 'jigoshop-ecommerce'));
            }

            $key = $this->productService->generateItemKey($item);
            $item->setKey($key);

            $order->addItem($item);
            $this->orderService->save($order);

            $row = Render::get('admin/order/item/' . $item->getType(), [
                'item' => $item,
                'order' => $order
            ]);

            $result = $this->getAjaxResponse($order);
            $result['html']['row'] = $row;
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

        echo json_encode($result);
        exit;
    }

    /**
     * @param $order OrderInterface Order to get values from.
     *
     * @return array Ajax response array.
     */
    private function getAjaxResponse($order)
    {
        $tax = $order->getTax();
        $shippingTax = $order->getShippingTax();

        foreach ($order->getTax() as $class => $value) {
            if (isset($shippingTax[$class])) {
                $tax[$class] = $value + $shippingTax[$class];
            }
        }

        $shipping = [];
        $shippingHtml = [];
        foreach ($this->shippingService->getAvailable() as $method) {
            /** @var $method Shipping\Method */
            if ($method instanceof Shipping\MultipleMethod) {
                /** @var $method Shipping\MultipleMethod */
                foreach ($method->getRates($order) as $rate) {
                    /** @var $rate Shipping\Rate */
                    $shipping[$method->getId() . '-' . $rate->getId()] = $method->isEnabled() ? $rate->calculate($order) : -1;

                    if ($method->isEnabled()) {
                        $shippingHtml[$method->getId() . '-' . $rate->getId()] = [
                            'price' => ProductHelper::formatPrice($rate->calculate($order)),
                            'html' => Render::get('admin/order/totals/shipping/rate', [
                                'method' => $method,
                                'rate' => $rate,
                                'order' => $order
                            ]),
                        ];
                    }
                }
            } else {
                $shipping[$method->getId()] = $method->isEnabled() ? $method->calculate($order) : -1;

                if ($method->isEnabled()) {
                    $shippingHtml[$method->getId()] = [
                        'price' => ProductHelper::formatPrice($method->calculate($order)),
                        'html' => Render::get('admin/order/totals/shipping/method', [
                            'method' => $method,
                            'order' => $order
                        ]),
                    ];
                }
            }
        }

        return [
            'success' => true,
            'shipping' => $shipping,
            'product_subtotal' => $order->getProductSubtotal(),
            'subtotal' => $order->getSubtotal(),
            'total' => $order->getTotal(),
            'tax' => $tax,
            'html' => [
                'shipping' => $shippingHtml,
                'product_subtotal' => ProductHelper::formatPrice($order->getProductSubtotal()),
                'subtotal' => ProductHelper::formatPrice($order->getSubtotal()),
                'total' => ProductHelper::formatPrice($order->getTotal()),
                'tax' => $this->getTaxes($order),
            ],
        ];
    }

    /**
     * @param $order OrderInterface Order to get taxes for.
     *
     * @return array Taxes with labels array.
     */
    private function getTaxes($order)
    {
        $result = [];
        foreach ($order->getCombinedTax() as $class => $value) {
            $result[$class] = [
                'label' => Tax::getLabel($class, $order),
                'value' => ProductHelper::formatPrice($value, '', $order->getCurrency()),
            ];
        }

        return $result;
    }

    public function ajaxUpdateProduct()
    {
        try {
            if (!is_numeric($_POST['quantity']) || $_POST['quantity'] < 0) {
                throw new Exception(__('Invalid quantity value.', 'jigoshop-ecommerce'));
            }
            if (!is_numeric($_POST['price']) || $_POST['price'] < 0) {
                throw new Exception(__('Invalid product price.', 'jigoshop-ecommerce'));
            }

            /** @var \Jigoshop\Entity\Order $order */
            $order = $this->orderService->find((int)$_POST['order']);

            if ($order->getId() === null) {
                throw new Exception(__('Order not found.', 'jigoshop-ecommerce'));
            }

            $item = $order->removeItem($_POST['product']);

            if ($item === null) {
                throw new Exception(__('Item not found.', 'jigoshop-ecommerce'));
            }

            $item->setQuantity((int)$_POST['quantity']);
            $item->setPrice((float)$_POST['price']);

            if ($item->getQuantity() > 0) {
                $item = $this->wp->applyFilters('jigoshop\admin\order\update_product', $item, $order);
                $order->addItem($item);
            }

            $this->orderService->save($order);

            $result = $this->getAjaxResponse($order);
            $result['item_cost'] = $item->getCost();
            $result['html']['item_cost'] = ProductHelper::formatPrice($item->getCost());
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

        echo json_encode($result);
        exit;
    }

    public function ajaxRemoveProduct()
    {
        try {
            /** @var \Jigoshop\Entity\Order $order */
            $order = $this->orderService->find((int)$_POST['order']);

            if ($order->getId() === null) {
                throw new Exception(__('Order not found.', 'jigoshop-ecommerce'));
            }

            $order->removeItem($_POST['product']);
            $this->orderService->save($order);
            $result = $this->getAjaxResponse($order);
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

        echo json_encode($result);
        exit;
    }

    public function ajaxChangeShippingMethod()
    {
        try {
            /** @var \Jigoshop\Entity\Order $order */
            $order = $this->orderService->find((int)$_POST['order']);

            if ($order->getId() === null) {
                throw new Exception(__('Order not found.', 'jigoshop-ecommerce'));
            }

            $shippingMethod = $this->shippingService->get($_POST['method']);

            if ($shippingMethod instanceof Shipping\MultipleMethod) {
                if (!isset($_POST['rate'])) {
                    throw new Exception(__('Method rate is required.', 'jigoshop-ecommerce'));
                }

                $shippingMethod->setShippingRate((int)$_POST['rate']);
            }

            $order->setShippingMethod($shippingMethod);
            $order = $this->rebuildOrder($order);
            $this->orderService->save($order);
            $result = $this->getAjaxResponse($order);
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

        echo json_encode($result);
        exit;
    }

    /**
     * @param $order \Jigoshop\Entity\Order The order.
     *
     * @return \Jigoshop\Entity\Order Updated order.
     */
    private function rebuildOrder($order)
    {
        // Recalculate values
        $items = $order->getItems();
        $method = $order->getShippingMethod();
        $order->removeItems();

        foreach ($items as $item) {
            /** @var $item Item */
            $item = $this->wp->applyFilters('jigoshop\admin\order\update_product', $item, $order);
            $order->addItem($item);
        }

        if ($method !== null) {
            $order->setShippingMethod($method);
        }

        return $order;
    }

    public function ajaxChangeCountry()
    {
        try {
            if (!in_array($_POST['value'], array_keys(Country::getAllowed()))) {
                throw new Exception(__('Invalid country.', 'jigoshop-ecommerce'));
            }

            $post = $this->wp->getPost((int)$_POST['order']);
            $this->wp->updateGlobalPost($post);
            /** @var \Jigoshop\Entity\Order $order */
            $order = $this->orderService->findForPost($post);

            if ($order->getId() === null) {
                throw new Exception(__('Order not found.', 'jigoshop-ecommerce'));
            }

            switch ($_POST['type']) {
                case 'shipping':
                    $address = $order->getCustomer()->getShippingAddress();
                    break;
                case 'billing':
                default:
                    $address = $order->getCustomer()->getBillingAddress();
            }

            $address->setCountry($_POST['value']);
            $order = $this->rebuildOrder($order);
            $this->orderService->save($order);

            $result = $this->getAjaxResponse($order);
            $result['has_states'] = Country::hasStates($address->getCountry());
            $result['states'] = Country::getStates($address->getCountry());
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

        echo json_encode($result);
        exit;
    }

    /**
     * Ajax action for changing state.
     */
    public function ajaxChangeState()
    {
        try {
            $post = $this->wp->getPost((int)$_POST['order']);
            $this->wp->updateGlobalPost($post);
            /** @var \Jigoshop\Entity\Order $order */
            $order = $this->orderService->findForPost($post);

            if ($order->getId() === null) {
                throw new Exception(__('Order not found.', 'jigoshop-ecommerce'));
            }

            switch ($_POST['type']) {
                case 'shipping':
                    $address = $order->getCustomer()->getShippingAddress();
                    break;
                case 'billing':
                default:
                    $address = $order->getCustomer()->getBillingAddress();
            }

            if (Country::hasStates($address->getCountry()) && !Country::hasState($address->getCountry(),
                    $_POST['value'])
            ) {
                throw new Exception(__('Invalid state.', 'jigoshop-ecommerce'));
            }

            $address->setState($_POST['value']);
            $order = $this->rebuildOrder($order);
            $this->orderService->save($order);

            $result = $this->getAjaxResponse($order);
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

        echo json_encode($result);
        exit;
    }

    /**
     * Ajax action for changing postcode.
     */
    public function ajaxChangePostcode()
    {
        try {
            $post = $this->wp->getPost((int)$_POST['order']);
            $this->wp->updateGlobalPost($post);
            /** @var \Jigoshop\Entity\Order $order */
            $order = $this->orderService->findForPost($post);

            if ($order->getId() === null) {
                throw new Exception(__('Order not found.', 'jigoshop-ecommerce'));
            }

            switch ($_POST['type']) {
                case 'shipping':
                    $address = $order->getCustomer()->getShippingAddress();
                    break;
                case 'billing':
                default:
                    $address = $order->getCustomer()->getBillingAddress();
            }

            if ($this->options->get('shopping.validate_zip') && !Validation::isPostcode($_POST['value'],
                    $address->getCountry())
            ) {
                throw new Exception(__('Invalid postcode.', 'jigoshop-ecommerce'));
            }

            $address->setPostcode($_POST['value']);
            $order = $this->rebuildOrder($order);
            $this->orderService->save($order);

            $result = $this->getAjaxResponse($order);
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

        echo json_encode($result);
        exit;
    }

    public function dataBox()
    {
        $post = $this->wp->getGlobalPost();
        /** @var \Jigoshop\Entity\Order $order */
        $order = $this->orderService->findForPost($post);
        $billingOnly = $this->options->get('shipping.only_to_billing');

        $address = $order->getCustomer()->getBillingAddress();

        $billingFields = $this->wp->applyFilters('jigoshop\admin\order\billing_fields',
            ProductHelper::getBasicBillingFields([
                'first_name' => [
                    'value' => $address->getFirstName(),
                ],
                'last_name' => [
                    'value' => $address->getLastName(),
                ],
                'company' => [
                    'value' => $address instanceof Customer\CompanyAddress ? $address->getCompany() : '',
                ],
                'euvatno' => [
                    'value' => $address instanceof Customer\CompanyAddress ? $address->getVatNumber() : '',
                ],
                'address' => [
                    'value' => $address->getAddress(),
                ],
                'city' => [
                    'value' => $address->getCity(),
                ],
                'postcode' => [
                    'value' => $address->getPostcode(),
                ],
                'country' => [
                    'value' => $address->getCountry(),
                    'options' => Country::getAllowed(),
                ],
                'state' => [
                    'type' => Country::hasStates($address->getCountry()) ? 'select' : 'text',
                    'value' => $address->getState(),
                    'options' => Country::getStates($address->getCountry()),
                ],
                'phone' => [
                    'value' => $address->getPhone(),
                ],
                'email' => [
                    'value' => $address->getEmail(),
                ],
            ], $order));

        $address = $order->getCustomer()->getShippingAddress();

        $shippingFields = $this->wp->applyFilters('jigoshop\admin\order\shipping_fields',
            ProductHelper::getBasicShippingFields([
                'first_name' => [
                    'value' => $address->getFirstName(),
                ],
                'last_name' => [
                    'value' => $address->getLastName(),
                ],
                'company' => [
                    'value' => $address instanceof Customer\CompanyAddress ? $address->getCompany() : '',
                ],
                'address' => [
                    'value' => $address->getAddress(),
                ],
                'city' => [
                    'value' => $address->getCity(),
                ],
                'postcode' => [
                    'value' => $address->getPostcode(),
                ],
                'country' => [
                    'value' => $address->getCountry(),
                    'options' => Country::getAllowed(),
                ],
                'state' => [
                    'type' => Country::hasStates($address->getCountry()) ? 'select' : 'text',
                    'value' => $address->getState(),
                    'options' => Country::getStates($address->getCountry()),
                ],
            ], $order));
        $customers = $this->customerService->findAll();

        Render::output('admin/order/dataBox', [
            'order' => $order,
            'billingFields' => $billingFields,
            'shippingFields' => $shippingFields,
            'customers' => $customers,
            'billingOnly' => $billingOnly,
        ]);
    }

    public function itemsBox()
    {
        $post = $this->wp->getGlobalPost();
        $order = $this->orderService->findForPost($post);

        Render::output('admin/order/itemsBox', [
            'order' => $order,
        ]);
    }

    public function totalsBox()
    {
        $post = $this->wp->getGlobalPost();
        /** @var \Jigoshop\Entity\Order $order */
        $order = $this->orderService->findForPost($post);

        Render::output('admin/order/totalsBox', [
            'order' => $order,
            'shippingMethods' => $this->shippingService->getEnabled(),
            'tax' => $this->getTaxes($order),
        ]);
    }

    public function actionsBox()
    {
        $post = $this->wp->getGlobalPost();
        /** @var \Jigoshop\Entity\Order $order */
        $order = $this->orderService->findForPost($post);

        $delete_text = '';
        if (current_user_can("delete_post", $post->ID)) {
            if (!EMPTY_TRASH_DAYS) {
                $delete_text = __('Delete Permanently');
            } else {
                $delete_text = __('Move to Trash');
            }
        }

        $this->renderModifiedPublishBoxContent($post);

        Render::output('admin/order/actionsBox', [
            'order' => $order,
            'delete_text' => $delete_text,
        ]);
    }

    public function euVatNumberBox() {
        $post = $this->wp->getGlobalPost();
        $order = $this->orderService->findForPost($post);

        $euVatNumberValidationStatus = '-';
        if($order->getEuVatValidationStatus() == Tax::EU_VAT_VALIDATION_RESULT_VALID) {
            $euVatNumberValidationStatus = __('Valid', 'jigoshop-ecommerce');
        }
        elseif($order->getEuVatValidationStatus() == Tax::EU_VAT_VALIDATION_RESULT_INVALID) {
            $euVatNumberValidationStatus = __('Invalid', 'jigoshop-ecommerce');
        }
        elseif($order->getEuVatValidationStatus() == Tax::EU_VAT_VALIDATION_RESULT_ERROR) {
            $euVatNumberValidationStatus = __('Error', 'jigoshop-ecommerce');
        }

        if($order->getIpAddressCountry()) {
            $ipAddressCountry = Country::getName($order->getIpAddressCountry());
        }
        else {
            $ipAddressCountry = 'unknown';
        }

        Render::output('admin/order/euVatNumberBox', [
            'euVatNumberValidationStatus' => $euVatNumberValidationStatus,
            'ipAddress' => $order->getIpAddress(),
            'ipAddressCountry' => $ipAddressCountry
        ]);
    }

    private function renderModifiedPublishBoxContent($post)
    {
        post_submit_meta_box($post);
        //TODO: move it to CSS file.
        echo '<style>#major-publishing-actions, #minor-publishing-actions, .misc-pub-post-status, #misc-publishing-actions #visibility {display:none;}</style>';
    }
}
