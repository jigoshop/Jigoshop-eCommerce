<?php

namespace Jigoshop\Core;

use Jigoshop\Entity\Customer\CompanyAddress;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Order as OrderHelper;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Payment\BankTransfer;
use Jigoshop\Payment\Cheque;
use Jigoshop\Payment\Method;
use Jigoshop\Payment\OnDelivery;
use Jigoshop\Service\EmailServiceInterface;
use Jigoshop\Shipping\LocalPickup;
use WPAL\Wordpress;

class Emails
{
    /** @var Wordpress */
    private $wp;
    /** @var Options */
    private $options;
    /** @var EmailServiceInterface */
    private $emailService;

    public function __construct(Wordpress $wp, Options $options, EmailServiceInterface $emailService)
    {
        $this->wp = $wp;
        $this->options = $options;
        $this->emailService = $emailService;

        $wp->addAction('init', [$this, 'registerMails'], 100);
        $wp->addAction('jigoshop\order\\' . Order\Status::PENDING . '_to_' . Order\Status::PROCESSING,
            [$this, 'orderPendingToProcessing']);
        $wp->addAction('jigoshop\order\\' . Order\Status::PENDING . '_to_' . Order\Status::COMPLETED,
            [$this, 'orderPendingToCompleted']);
        $wp->addAction('jigoshop\order\\' . Order\Status::PENDING . '_to_' . Order\Status::ON_HOLD,
            [$this, 'orderPendingToOnHold']);
        $wp->addAction('jigoshop\order\\' . Order\Status::ON_HOLD . '_to_' . Order\Status::PROCESSING,
            [$this, 'orderOnHoldToProcessing']);
        $wp->addAction('jigoshop\order\\' . Order\Status::COMPLETED, [$this, 'orderCompleted']);
        $wp->addAction('jigoshop\order\\' . Order\Status::REFUNDED, [$this, 'orderRefunded']);
        $wp->addAction('jigoshop_low_stock_notification', [$this, 'productLowStock']);
        $wp->addAction('jigoshop_no_stock_notification', [$this, 'productOutOfStock']);
        $wp->addAction('jigoshop_product_on_backorders_notification', [$this, 'productBackorders']);

        foreach(Order\Status::getStatuses() as $status => $name) {
            $wp->addAction('jigoshop\order\\'. $status, [$this, 'orderStatusChanged']);
        }

        $this->addOrderActions();
        $this->addProductActions();
    }

    public function registerMails()
    {
        $orderArguments = $this->getOrderEmailArgumentsDescription();
        $stockArguments = $this->getStockEmailArgumentsDescription();

        $this->emailService->register('admin_order_status_changed', __('Order Status Changed for admin'), $orderArguments);
        $this->emailService->register('admin_order_status_pending_to_processing', __('Order Pending to Processing for admin'), $orderArguments);
        $this->emailService->register('admin_order_status_pending_to_completed', __('Order Pending to Completed for admin'), $orderArguments);
        $this->emailService->register('admin_order_status_pending_to_on_hold', __('Order Pending to On-Hold for admin'), $orderArguments);
        $this->emailService->register('customer_order_status_pending_to_on_hold', __('Order Pending to On-Hold for customer'), $orderArguments);
        $this->emailService->register('customer_order_status_pending_to_processing', __('Order Pending to Processing for customer'), $orderArguments);
        $this->emailService->register('customer_order_status_on_hold_to_processing', __('Order On-Hold to Processing for customer'), $orderArguments);
        $this->emailService->register('customer_order_status_completed', __('Order Completed for customer'), $orderArguments);
        $this->emailService->register('customer_order_status_refunded', __('Order Refunded for customer'), $orderArguments);
        $this->emailService->register('low_stock_notification', __('Low Stock Notification'), $stockArguments);
        $this->emailService->register('no_stock_notification', __('No Stock Notification'), $stockArguments);
        $this->emailService->register('product_on_backorders_notification', __('Backorders Notification'), array_merge(
            $stockArguments, $orderArguments, ['amount' => __('Amount', 'jigoshop-ecommerce')]
        ));
        $this->emailService->register('send_customer_invoice', __('Send Customer Invoice'), $orderArguments);
    }

    public function orderStatusChanged($order)
    {
        $arguments = $this->getOrderEmailArguments($order);
        $this->send('admin_order_status_changed', $arguments, $this->options->get('general.email'), ['order' => $order]);
    }

    /**
     * @param $order Order
     */
    public function orderPendingToProcessing($order)
    {
        $arguments = $this->getOrderEmailArguments($order);
        $this->send('admin_order_status_pending_to_processing', $arguments, $this->options->get('general.email'), ['order' => $order]);
        $this->send('customer_order_status_pending_to_processing', $arguments, $order->getCustomer()->getBillingAddress()->getEmail(), ['order' => $order]);
    }

    /**
     * @param $order Order
     */
    public function orderPendingToCompleted($order)
    {
        $this->send('admin_order_status_pending_to_completed', $this->getOrderEmailArguments($order), $this->options->get('general.email'), ['order' => $order]);
    }

    /**
     * @param $order Order
     */
    public function orderPendingToOnHold($order)
    {
        $arguments = $this->getOrderEmailArguments($order);
        $this->send('admin_order_status_pending_to_on_hold', $arguments, $this->options->get('general.email'), ['order' => $order]);
        $this->send('customer_order_status_pending_to_on_hold', $arguments, $order->getCustomer()->getBillingAddress()->getEmail(), ['order' => $order]);
    }

    /**
     * @param $order Order
     */
    public function orderOnHoldToProcessing($order)
    {
        $this->send('customer_order_status_on_hold_to_processing', $this->getOrderEmailArguments($order), $order->getCustomer()->getBillingAddress()->getEmail(), ['order' => $order]);
    }

    /**
     * @param $order Order
     */
    public function orderCompleted($order)
    {
        $this->send('customer_order_status_completed', $this->getOrderEmailArguments($order), $order->getCustomer()->getBillingAddress()->getEmail(), ['order' => $order]);
    }

    /**
     * @param $order Order
     */
    public function orderRefunded($order)
    {
        $this->send('customer_order_status_refunded', $this->getOrderEmailArguments($order), $order->getCustomer()->getBillingAddress()->getEmail(), ['order' => $order]);
    }

    /**
     * @param $product Product
     */
    public function productLowStock($product)
    {
        $this->send('low_stock_notification', $this->getStockEmailArguments($product), $this->options->get('general.email'), ['product' => $product]);
    }

    /**
     * @param $product Product
     */
    public function productOutOfStock($product)
    {
        $this->send('no_stock_notification', $this->getStockEmailArguments($product), $this->options->get('general.email'), ['product' => $product]);
    }

    /**
     * @param $order   Order
     * @param $product Product
     * @param $amount  int
     */
    public function productBackorders($order, $product, $amount)
    {
        $arguments = array_merge(
            $this->getOrderEmailArguments($order),
            $this->getStockEmailArguments($product),
            ['amount' => $amount]
        );

        if ($product instanceof Product\Purchasable) {
            $this->send('product_on_backorders_notification', $arguments, $this->options->get('general.email'), ['order' => $order, 'product' => $product]);
            if ($product->getStock()->getAllowBackorders() == Product\Attributes\StockStatus::BACKORDERS_NOTIFY) {
                $this->send('product_on_backorders_notification', $arguments, $order->getCustomer()->getBillingAddress()->getEmail(), ['order' => $order, 'product' => $product]);
            }
        }
    }

    /**
     * @param string $hook
     * @param $args
     * @param $email
     * @param array $objects
     */
    public function send($hook, $args, $email, array $objects = [])
    {
        $wp = $this->wp;
        $closure = function ($phpMailer) use ($wp, $hook, $objects) {
            $wp->doAction('jigoshop\core\emails\phpmailer_init', $phpMailer, $hook, $objects);
        };
        $wp->addAction('phpmailer_init', $closure);
        $this->emailService->send($hook, $args, $email);
        $wp->removeAction('phpmailer_init', $closure);
    }

    /**
     * @param $order Order The order.
     *
     * @return array Available arguments with proper values.
     */
    private function getOrderEmailArguments($order)
    {
        $billingAddress = $order->getCustomer()->getBillingAddress();
        $shippingAddress = $order->getCustomer()->getShippingAddress();

        $items = $this->formatItems($order);

        return $this->wp->applyFilters('jigoshop\emails\order_variables', [
            'blog_name' => $this->wp->getBloginfo('name'),
            'order_number' => $order->getNumber(),
            'order_status' => Order\Status::getName($order->getStatus()),
            'order_date' => $this->wp->getHelpers()->dateI18n($this->wp->getOption('date_format')),
            'shop_name' => $this->options->get('general.company_name'),
            'shop_address_1' => $this->options->get('general.company_address_1'),
            'shop_address_2' => $this->options->get('general.company_address_2'),
            'shop_tax_number' => $this->options->get('general.company_tax_number'),
            'shop_phone' => $this->options->get('general.company_phone'),
            'shop_email' => $this->options->get('general.company_email'),
            'customer_note' => $order->getCustomerNote(),
            'order_items' => $items,
            'order_items_table' => $items,
            'subtotal' => ProductHelper::formatPrice($order->getSubtotal()),
            'shipping' => ProductHelper::formatPrice($order->getShippingPrice()),
            'shipping_cost' => ProductHelper::formatPrice($order->getShippingPrice()),
            'shipping_cost_raw' => $order->getShippingPrice(),
            'shipping_method' => $order->getShippingMethod() ? $order->getShippingMethod()->getTitle() : '',
            'discount' => ProductHelper::formatPrice($order->getDiscount()),
            'total_tax' => ProductHelper::formatPrice($order->getTotalTax()),
            'processing_fee' => ProductHelper::formatPrice($order->getProcessingFee()),
            'total' => ProductHelper::formatPrice($order->getTotal()),
            'is_local_pickup' => $order->getShippingMethod() && $order->getShippingMethod()->getId() == LocalPickup::NAME ? true : null,
            'checkout_url' => $order->getStatus() == Order\Status::PENDING ? OrderHelper::getPayLink($order) : null,
            'payment_method' => $order->getPaymentMethod() ? $order->getPaymentMethod()->getName() : '',
            'billing_first_name' => $billingAddress->getFirstName(),
            'billing_last_name' => $billingAddress->getLastName(),
            'billing_company' => $billingAddress instanceof CompanyAddress ? $billingAddress->getCompany() : '',
            'billing_euvatno' => $billingAddress instanceof CompanyAddress ? $billingAddress->getVatNumber() : '',
            'billing_address_1' => $billingAddress->getAddress(),
            'billing_address_2' => '', // TODO: Remove address_2
            'billing_postcode' => $billingAddress->getPostcode(),
            'billing_city' => $billingAddress->getCity(),
            'billing_country' => Country::getName($billingAddress->getCountry()),
            'billing_country_raw' => $billingAddress->getCountry(),
            'billing_state' => Country::hasStates($billingAddress->getCountry()) ? Country::getStateName($billingAddress->getCountry(),
                $billingAddress->getState()) : $billingAddress->getState(),
            'billing_state_raw' => $billingAddress->getState(),
            'billing_email' => $billingAddress->getEmail(),
            'billing_phone' => $billingAddress->getPhone(),
            'shipping_first_name' => $shippingAddress->getFirstName(),
            'shipping_last_name' => $shippingAddress->getLastName(),
            'shipping_company' => $shippingAddress instanceof CompanyAddress ? $shippingAddress->getCompany() : '',
            'shipping_address_1' => $shippingAddress->getAddress(),
            'shipping_address_2' => '', // TODO: Remove address_2
            'shipping_postcode' => $shippingAddress->getPostcode(),
            'shipping_city' => $shippingAddress->getCity(),
            'shipping_country' => Country::getName($shippingAddress->getCountry()),
            'shipping_country_raw' => $shippingAddress->getCountry(),
            'shipping_state' => Country::hasStates($shippingAddress->getCountry()) ? Country::getStateName($shippingAddress->getCountry(),
                $shippingAddress->getState()) : $shippingAddress->getState(),
            'shipping_state_raw' => $shippingAddress->getState(),
            'is_cheque' => $order->getPaymentMethod() instanceof Method && $order->getPaymentMethod()->getId() == Cheque::ID,
            'cheque_info' => str_replace(PHP_EOL, '', $this->options->get('payment.cheque.description', '')),
            'is_bank_transfer' => $order->getPaymentMethod() instanceof Method && $order->getPaymentMethod()->getId() == BankTransfer::ID,
            'bank_info' => $this->getBankInfo(),
            'is_cash_on_delivery' => $order->getPaymentMethod() instanceof Method && $order->getPaymentMethod()->getId() == OnDelivery::ID,
            'is_local_pickup' => $order->getShippingMethod() instanceof \Jigoshop\Shipping\Method && $order->getShippingMethod()->getId() == LocalPickup::NAME,
        ], $order);
    }

    /**
     * @internal Do not use this method, it will disappear with integration layer!
     *
     * @param $order Order The order.
     *
     * @return string Items formatted for email.
     */
    public function __formatItems($order)
    {
        return $this->formatItems($order);
    }

    /**
     * @param $order Order The order.
     *
     * @return string Items formatted for email.
     */
    private function formatItems($order)
    {

        $result = '';

        foreach ($order->getItems() as $item) {
            /** @var $item Order\Item */
            $itemResult = '';
            $product = $item->getProduct();
            $itemResult .= $item->getQuantity() . ' x ' . html_entity_decode($this->wp->applyFilters('jigoshop\emails\product_title',
                    $item->getName(), $product, $item), ENT_QUOTES, 'UTF-8');

            $sku = '';
            if ( $product instanceof Product\Variable) {
                $sku = $product->getVariation($item->getMeta('variation_id')->getValue())->getProduct()->getSku();
            } else {
                $sku = $product->getSku();
            }

            if($sku) {
                $itemResult .= ' (#' . $sku . ')';
            }

            $itemResult .= ' - ' . ProductHelper::formatPrice($item->getCost());

            if ($product instanceof Product\Variable) {
                $variation = $product->getVariation($item->getMeta('variation_id')->getValue());
                $itemResult .= PHP_EOL;

                foreach ($variation->getAttributes() as $attribute) {
                    /** @var $attribute \Jigoshop\Entity\Product\Variable\Attribute */
                    $itemResult .= $attribute->getAttribute()->getLabel() . ': ' . $attribute->getItemValue($item) . ', ';
                }

                $itemResult = rtrim($itemResult, ',');
            }

            $itemResult = $this->wp->applyFilters('jigoshop\emails\order_item', $itemResult, $item, $order);
            $result .= $itemResult . PHP_EOL;
        }

        return $result;
    }

    private function getOrderEmailArgumentsDescription()
    {
        return apply_filters('jigoshop\email\order_variables_description', [
            'blog_name' => __('Blog Name', 'jigoshop-ecommerce'),
            'order_number' => __('Order Number', 'jigoshop-ecommerce'),
            'order_status' => __('Order Status', 'jigoshop-ecommerce'),
            'order_date' => __('Order Date', 'jigoshop-ecommerce'),
            'shop_name' => __('Shop Name', 'jigoshop-ecommerce'),
            'shop_address_1' => __('Shop Address part 1', 'jigoshop-ecommerce'),
            'shop_address_2' => __('Shop Address part 2', 'jigoshop-ecommerce'),
            'shop_tax_number' => __('Shop TaxNumber', 'jigoshop-ecommerce'),
            'shop_phone' => __('Shop_Phone', 'jigoshop-ecommerce'),
            'shop_email' => __('Shop Email', 'jigoshop-ecommerce'),
            'customer_note' => __('Customer Note', 'jigoshop-ecommerce'),
            'order_items' => __('Ordered Items', 'jigoshop-ecommerce'),
            'subtotal' => __('Subtotal', 'jigoshop-ecommerce'),
            'shipping' => __('Shipping Price and Method', 'jigoshop-ecommerce'),
            'shipping_cost' => __('Shipping Cost', 'jigoshop-ecommerce'),
            'shipping_cost_raw' => __('Raw Shipping Cost', 'jigoshop-ecommerce'),
            'shipping_method' => __('Shipping Method', 'jigoshop-ecommerce'),
            'discount' => __('Discount Price', 'jigoshop-ecommerce'),
            'total_tax' => __('Total Tax', 'jigoshop-ecommerce'),
            'processing_fee' => __('Processing fee', 'jigoshop-ecommerce'),
            'total' => __('Total Price', 'jigoshop-ecommerce'),
            'payment_method' => __('Payment Method Title', 'jigoshop-ecommerce'),
            'is_local_pickup' => __('Is Local Pickup?', 'jigoshop-ecommerce'),
            'checkout_url' => __('If order is pending, show checkout url', 'jigoshop-ecommerce'),
            'billing_first_name' => __('Billing First Name', 'jigoshop-ecommerce'),
            'billing_last_name' => __('Billing Last Name', 'jigoshop-ecommerce'),
            'billing_company' => __('Billing Company', 'jigoshop-ecommerce'),
            'billing_euvatno' => __('Billing Vat Number', 'jigoshop-ecommerce'),
            'billing_address_1' => __('Billing Address part 1', 'jigoshop-ecommerce'),
            'billing_address_2' => __('Billing Address part 2', 'jigoshop-ecommerce'),
            'billing_postcode' => __('Billing Postcode', 'jigoshop-ecommerce'),
            'billing_city' => __('Billing City', 'jigoshop-ecommerce'),
            'billing_country' => __('Billing Country', 'jigoshop-ecommerce'),
            'billing_country_raw' => __('Raw Billing Country', 'jigoshop-ecommerce'),
            'billing_state' => __('Billing State', 'jigoshop-ecommerce'),
            'billing state_raw' => __('Raw Billing State', 'jigoshop-ecommerce'),
            'billing_email' => __('Billing Email', 'jigoshop-ecommerce'),
            'billing_phone' => __('Billing Phone    ', 'jigoshop-ecommerce'),
            'shipping_first_name' => __('Shipping First Name', 'jigoshop-ecommerce'),
            'shipping_last_name' => __('Shipping Last Name', 'jigoshop-ecommerce'),
            'shipping_company' => __('Shipping Company', 'jigoshop-ecommerce'),
            'shipping_address_1' => __('Shipping Address part 1', 'jigoshop-ecommerce'),
            'shipping_address_2' => __('Shipping_Address part 2', 'jigoshop-ecommerce'),
            'shipping_postcode' => __('Shipping Postcode', 'jigoshop-ecommerce'),
            'shipping_city' => __('Shipping City', 'jigoshop-ecommerce'),
            'shipping_country' => __('Shipping Country', 'jigoshop-ecommerce'),
            'shipping_country_raw' => __('Raw Shipping Country', 'jigoshop-ecommerce'),
            'shipping_state' => __('Shipping State', 'jigoshop-ecommerce'),
            'shipping state_raw' => __('Raw Shipping State', 'jigoshop-ecommerce'),
            'is_bank_transfer' => __('Is payment method Bank Transfer?', 'jigoshop-ecommerce'),
            'is_cash_on_delivery' => __('Is payment method Cash on Delivery?', 'jigoshop-ecommerce'),
            'is_cheque' => __('Is payment method Cheque?', 'jigoshop-ecommerce'),
            'is_local_pickup' => __('Is Local Pickup?', 'jigoshop-ecommerce'),
            'bank_info' => __('Company bank transfer details', 'jigoshop-ecommerce'),
            'cheque_info' => __('Company cheque details', 'jigoshop-ecommerce'),
        ]);
    }

    /**
     * @param $product Product
     *
     * @return array
     */
    private function getStockEmailArguments($product)
    {
        return [
            'blog_name' => $this->wp->getBloginfo('name'),
            'shop_name' => $this->options->get('general.company_name'),
            'shop_address_1' => $this->options->get('general.company_address_1'),
            'shop_address_2' => $this->options->get('general.company_address_2'),
            'shop_tax_number' => $this->options->get('general.company_tax_number'),
            'shop_phone' => $this->options->get('general.company_phone'),
            'shop_email' => $this->options->get('general.company_email'),
            'product_id' => $product->getId(),
            'product_name' => $product->getName(),
            'sku' => $product->getSku(),
        ];
    }

    private function getStockEmailArgumentsDescription()
    {
        return [
            'blog_name' => __('Blog Name', 'jigoshop-ecommerce'),
            'shop_name' => __('Shop Name', 'jigoshop-ecommerce'),
            'shop_address_1' => __('Shop Address part 1', 'jigoshop-ecommerce'),
            'shop_address_2' => __('Shop Address part 2', 'jigoshop-ecommerce'),
            'shop_tax_number' => __('Shop TaxNumber', 'jigoshop-ecommerce'),
            'shop_phone' => __('Shop_Phone', 'jigoshop-ecommerce'),
            'shop_email' => __('Shop Email', 'jigoshop-ecommerce'),
            'product_id' => __('Product ID', 'jigoshop-ecommerce'),
            'product_name' => __('Product Name', 'jigoshop-ecommerce'),
            'sku' => __('SKU', 'jigoshop-ecommerce'),
        ];
    }

    /**
     * @param $order Order
     */
    public function sendCustomerInvoice($order)
    {
        $this->emailService->send('send_customer_invoice', $this->getOrderEmailArguments($order),
            $order->getCustomer()->getBillingAddress()->getEmail());
    }

    private function addOrderActions()
    {
        $this->wp->addAction(sprintf('jigoshop\order\%s_to_%s', Order\Status::PENDING, Order\Status::PROCESSING), [$this, 'orderPendingToProcessing']);
        $this->wp->addAction(sprintf('jigoshop\order\%s_to_%s', Order\Status::PENDING, Order\Status::COMPLETED), [$this, 'orderPendingToCompleted']);
        $this->wp->addAction(sprintf('jigoshop\order\%s_to_%s', Order\Status::PENDING, Order\Status::ON_HOLD), [$this, 'orderPendingToOnHold']);
        $this->wp->addAction(sprintf('jigoshop\order\%s_to_%s', Order\Status::ON_HOLD, Order\Status::PROCESSING), [$this, 'orderOnHoldToProcessing']);
        $this->wp->addAction(sprintf('jigoshop\order\%s_to_%s', Order\Status::PENDING, Order\Status::PROCESSING), [$this, 'orderPendingToProcessing']);
        $this->wp->addAction(sprintf('jigoshop\order\%s', Order\Status::COMPLETED), [$this, 'orderCompleted']);
        $this->wp->addAction(sprintf('jigoshop\order\%s', Order\Status::REFUNDED), [$this, 'orderRefunded']);
    }

    private function addProductActions()
    {
        $this->wp->addAction('jigoshop\product\low_stock', [$this, 'productLowStock']);
        $this->wp->addAction('jigoshop\product\out_of_stock', [$this, 'productOutOfStock']);
        $this->wp->addAction('jigoshop\product\backorders', [$this, 'productBackorders']);
    }

    private function getBankInfo()
    {
        $bankTransferOptions = $this->options->get('payment.' . BankTransfer::ID, []);
        $bank_info = '';
        if ($bankTransferOptions['description']) $bank_info .= '<strong>'.__('Description', 'jigoshop-ecommerce').'</strong>: ' . wptexturize($bankTransferOptions['description']) . '<br />';
        if ($bankTransferOptions['bank_name']) $bank_info .= '<strong>'.__('Bank Name', 'jigoshop-ecommerce').'</strong>: ' . wptexturize($bankTransferOptions['bank_name']) . '<br />';
        if ($bankTransferOptions['account_holder']) $bank_info .= '<strong>'.__('Account Holder', 'jigoshop-ecommerce').'</strong>: '.wptexturize($bankTransferOptions['account_holder']) . '<br />';
        if ($bankTransferOptions['account_number']) $bank_info .= '<strong>'.__('Account Number', 'jigoshop-ecommerce').'</strong>: '.wptexturize($bankTransferOptions['account_number']) . '<br />';
        if ($bankTransferOptions['sort_code']) $bank_info .= '<strong>'.__('Sort Code', 'jigoshop-ecommerce').'</strong>: '. wptexturize($bankTransferOptions['sort_code']) . '<br />';
        if ($bankTransferOptions['iban']) $bank_info .= '<strong>'.__('IBAN', 'jigoshop-ecommerce').'</strong>: '. wptexturize($bankTransferOptions['iban']) . '<br />';
        if ($bankTransferOptions['bic']) $bank_info .= '<strong>'.__('BIC Code', 'jigoshop-ecommerce').'</strong>: '. wptexturize($bankTransferOptions['bic']) . '<br />';
        if ($bankTransferOptions['additional_info']) $bank_info .= '<strong>'.__('Additional Info', 'jigoshop-ecommerce').'</strong>: '. wptexturize($bankTransferOptions['additional_info']) . '<br />';

        return $bank_info;
    }
}

