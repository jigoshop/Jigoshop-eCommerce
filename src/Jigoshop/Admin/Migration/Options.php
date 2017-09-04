<?php

namespace Jigoshop\Admin\Migration;

use Jigoshop\Admin\Helper\Migration;
use Jigoshop\Helper\Render;
use Jigoshop\Service\TaxServiceInterface;
use WPAL\Wordpress;

class Options implements Tool
{
    const ID = 'jigoshop_options_migration';

    /** @var Wordpress */
    private $wp;
    /** @var Options */
    private $options;
    /** @var TaxServiceInterface */
    private $taxService;

    public function __construct(Wordpress $wp, \Jigoshop\Core\Options $options, TaxServiceInterface $taxService)
    {
        $this->wp = $wp;
        $this->options = $options;
        $this->taxService = $taxService;

        $wp->addAction('wp_ajax_jigoshop.admin.migration.options', [$this, 'ajaxMigrationOptions'], 10, 0);
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
        $countAll = 93;
        $countRemain = 93;

        if (($itemsFromBase = $this->wp->getOption('jigoshop_options_migrate_id')) !== false) {
            if ($itemsFromBase === '1') {
                $countRemain = 0;
            }
        }

        Render::output('admin/migration/options',
            ['countAll' => $countAll, 'countDone' => ($countAll - $countRemain)]);
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
     * @param mixed $options
     * @return bool migration options status: success or not
     */
    public function migrate($options = null)
    {
        $wpdb = $this->wp->getWPDB();

//		Open transaction for save migration emails
        $var_autocommit_sql = $wpdb->get_var("SELECT @@AUTOCOMMIT");

        try {
            $this->checkSql();
            $wpdb->query("SET AUTOCOMMIT=0");
            $this->checkSql();
            $wpdb->query("START TRANSACTION");
            $this->checkSql();
            $options = $this->wp->getOption('jigoshop_options');
            $this->checkSql();
            $transformations = $this->_getTransformations();
            $transformations = $this->_addShippingTransformations($transformations);
            $transformations = $this->_addPaymentTransformations($transformations);

            foreach ($transformations as $old => $new) {
                if (array_key_exists($old, $options)) {
                    $value = $this->_transform($old, $options[$old]);

                    if($old == 'jigoshop_default_country') {
                        $tmp = explode(':', $value);
                        if(count($tmp) > 1) {
                            $this->options->update('general.state', $tmp[1]);
                            $this->checkSql();
                            $value = $tmp[0];
                        }
                    }
                }

                if ($value !== null) {
                    $this->options->update($new, $value);
                    $this->checkSql();
                }
            }

            // Migrate tax rates
            if (!is_array($options['jigoshop_tax_rates'])) {
                $options['jigoshop_tax_rates'] = [];
            }

            $options['jigoshop_tax_rates'] = array_values($options['jigoshop_tax_rates']);
            for ($i = 0, $endI = count($options['jigoshop_tax_rates']); $i < $endI;) {
                $tax = $options['jigoshop_tax_rates'][$i];
                $rateDate = [
                    'id' => '',
                    'rate' => $tax['rate'],
                    'label' => empty($tax['label']) ? __('Tax', 'jigoshop-ecommerce') : $tax['label'],
                    // TODO: Check how other classes are used
                    'class' => $tax['class'] == '*' ? 'standard' : $tax['class'],
                    'country' => $tax['country'],
                    'states' => isset($tax['is_all_states']) && $tax['is_all_states'] ? '' : $tax['state'],
                    'is_compound' => ($tax['compound'] == 'yes' ? 1 : 0),
                    'postcodes' => '',
                ];
                $i++;

                $tax = isset($options['jigoshop_tax_rates'][$i]) ?  $options['jigoshop_tax_rates'][$i] : '';
                while ($i < $endI && $tax['rate'] == $rateDate['rate'] && $tax['country'] == $rateDate['country']) {
                    if (isset($tax['is_all_states']) && $tax['is_all_states']) {
                        $rateDate['states'] = '';
                    } else {
                        $rateDate['states'] .= empty($tax['state']) ? '' : ',' . $options['jigoshop_tax_rates'][$i]['state'];
                    }

                    $i++;
                    $tax = isset($options['jigoshop_tax_rates'][$i]) ?  $options['jigoshop_tax_rates'][$i] : '';
                }

                $this->taxService->save($rateDate);
                $this->checkSql();
            }

            $this->options->saveOptions();
            $this->checkSql();

//			commit sql transation and restore value of autocommit
            $wpdb->query("COMMIT");
            $wpdb->query("SET AUTOCOMMIT=" . $var_autocommit_sql);
            return true;

        } catch (Exception $e) {
//          rollback sql transation and restore value of autocommit
            if (WP_DEBUG) {
                \Monolog\Registry::getInstance(JIGOSHOP_LOGGER)->addDebug($e);
            }
            $wpdb->query("ROLLBACK");
            $wpdb->query("SET AUTOCOMMIT=" . $var_autocommit_sql);

            Migration::saveLog(__('Migration options end with error: ', 'jigoshop-ecommerce') . $e);

            return false;
        }
    }

    private function _transform($key, $value)
    {
        switch ($key) {
            case 'jigoshop_allowed_countries':
                return $value !== 'all';
            case 'jigoshop_tax_classes':
                $value = explode("\n", $value);

                return array_merge($this->options->get('tax.classes', []), array_map(function ($label) {
                    return [
                        'class' => sanitize_title($label),
                        'label' => $label,
                    ];
                }, $value));
            case 'jigoshop_tax_rates':
                return null;
            case 'jigoshop_free_shipping_enabled':
                return $value == 'yes';
            case 'jigoshop_local_pickup_enabled':
                return $value == 'yes';
            case 'jigoshop_flat_rate_enabled':
                return $value == 'yes';
            case 'jigoshop_cheque_enabled':
                return $value == 'yes';
            case 'jigoshop_cod_enabled':
                return $value == 'yes';
            case 'jigoshop_paypal_enabled':
                return $value == 'yes';
            case 'jigoshop_paypal_force_payment':
                return $value == 'yes';
            case 'jigoshop_paypal_testmode':
                return $value == 'yes';
            case 'jigoshop_paypal_send_shipping':
                return $value == 'yes';
            case 'jigoshop_use_wordpress_tiny_crop':
                return $value == 'yes';
            case 'jigoshop_use_wordpress_thumbnail_crop':
                return $value == 'yes';
            case 'jigoshop_use_wordpress_catalog_crop':
                return $value == 'yes';
            case 'jigoshop_use_wordpress_featured_crop':
                return $value == 'yes';
            case 'jigoshop_force_ssl_checkout':
                return $value == 'yes';
            case 'jigoshop_enable_guest_checkout':
                return $value == 'yes';
            case 'jigoshop_enable_guest_login':
                return $value == 'yes';
            case 'jigoshop_enable_signup_form':
                return $value == 'yes';
            case 'jigoshop_reset_pending_orders':
                return $value == 'yes';
            case 'jigoshop_complete_processing_orders':
                return $value == 'yes';
            case 'jigoshop_downloads_require_login':
                return $value == 'yes';
            case 'jigoshop_flat_rate_tax_status':
                return $value == 'taxable';
            case 'jigoshop_currency_pos':
                switch ($value) {
                    case 'left':
                        return '%1$s%3$s';
                    case 'left_space':
                        return '%1$s %3$s';
                    case 'right':
                        return '%3$s%1$s';
                    case 'right_space':
                        return '%3$s %1$s';
                    case 'left_code':
                        return '%2$s%3$s';
                    case 'left_code_space':
                        return '%2$s %3$s';
                    case 'right_code':
                        return '%3$s%2$s';
                    case 'right_code_space':
                        return '%3$s %2$s';
                    case 'symbol_code':
                        return '%1$s%3$s%2$s';
                    case 'symbol_code_space':
                        return '%1$s %3$s %2$s';
                    case 'code_symbol':
                        return '%2$s%3$s%1$s';
                    case 'code_symbol_space':
                        return '%2$s %3$s %1$s';
                }
            default:
                return $value;
        }
    }

    private function _addShippingTransformations($transformations)
    {
        return array_merge($transformations, [
            'jigoshop_free_shipping_enabled' => 'shipping.free_shipping.enabled',
            'jigoshop_free_shipping_title' => 'shipping.free_shipping.title',
            'jigoshop_free_shipping_minimum_amount' => 'shipping.free_shipping.minimum',
            'jigoshop_free_shipping_availability' => 'shipping.free_shipping.available_for',
            'jigoshop_free_shipping_countries' => 'shipping.free_shipping.countries',
            'jigoshop_local_pickup_enabled' => 'shipping.local_pickup.enabled',
            'jigoshop_flat_rate_enabled' => 'shipping.flat_rate.enabled',
            'jigoshop_flat_rate_title' => 'shipping.flat_rate.title',
            'jigoshop_flat_rate_availability' => 'shipping.flat_rate.available_for',
            'jigoshop_flat_rate_countries' => 'shipping.flat_rate.countries',
            'jigoshop_flat_rate_type' => 'shipping.flat_rate.type',
            'jigoshop_flat_rate_tax_status' => 'shipping.flat_rate.is_taxable',
            'jigoshop_flat_rate_cost' => 'shipping.flat_rate.cost',
            'jigoshop_flat_rate_handling_fee' => 'shipping.flat_rate.fee',
        ]);
    }

    private function _addPaymentTransformations($transformations)
    {
        return array_merge($transformations, [
            'jigoshop_cheque_enabled' => 'payment.cheque.enabled',
            'jigoshop_cheque_title' => 'payment.cheque.title',
            'jigoshop_cheque_description' => 'payment.cheque.description',
            'jigoshop_cod_enabled' => 'payment.on_delivery.enabled',
            'jigoshop_cod_title' => 'payment.on_delivery.title',
            'jigoshop_cod_description' => 'payment.on_delivery.description',
            'jigoshop_paypal_enabled' => 'payment.paypal.enabled',
            'jigoshop_paypal_title' => 'payment.paypal.title',
            'jigoshop_paypal_description' => 'payment.paypal.description',
            'jigoshop_paypal_email' => 'payment.paypal.email',
            'jigoshop_paypal_force_payment' => 'payment.paypal.force_payment',
            'jigoshop_paypal_testmode' => 'payment.paypal.test_mode',
            'jigoshop_sandbox_email' => 'payment.paypal.test_email',
            'jigoshop_paypal_send_shipping' => 'payment.paypal.send_shipping',
        ]);
    }

    private function _getTransformations()
    {
        return [
            'jigoshop_default_country' => 'general.country',
            'jigoshop_currency' => 'general.currency',
            'jigoshop_allowed_countries' => 'shopping.restrict_selling_locations',
            'jigoshop_specific_allowed_countries' => 'shopping.selling_locations',
            'jigoshop_demo_store' => 'general.demo_store',
            'jigoshop_company_name' => 'general.company_name',
            'jigoshop_tax_number' => 'general.company_tax_number',
            'jigoshop_address_1' => 'general.company_address_1',
            'jigoshop_address_2' => 'general.company_address_2',
            'jigoshop_company_phone' => 'general.company_phone',
            'jigoshop_company_email' => 'general.company_email',
            'jigoshop_product_category_slug' => 'permalinks.category',
            'jigoshop_product_tag_slug' => 'permalinks.tag',
            'jigoshop_email' => 'general.email',
//			'jigoshop_cart_shows_shop_button' => 'yes',
            'jigoshop_redirect_add_to_cart' => 'shopping.redirect_add_to_cart',
            'jigoshop_reset_pending_orders' => 'advanced.automatic_reset',
            'jigoshop_complete_processing_orders' => 'advanced.automatic_complete',
            'jigoshop_downloads_require_login' => 'shopping.login_for_downloads',
//			'jigoshop_disable_css' => 'no',
//			'jigoshop_frontend_with_theme_css' => 'no',
//			'jigoshop_disable_fancybox' => 'no',
            'jigoshop_enable_postcode_validating' => 'shopping.validate_zip',
//			'jigoshop_verify_checkout_info_message' => 'yes',
//			'jigoshop_eu_vat_reduction_message' => 'yes',
            'jigoshop_enable_guest_checkout' => 'shopping.guest_purchases',
            'jigoshop_enable_guest_login' => 'shopping.show_login_form',
            'jigoshop_enable_signup_form' => 'shopping.allow_registration',
            'jigoshop_force_ssl_checkout' => 'shopping.force_ssl',
            'jigoshop_sharethis' => 'advanced.integration.share_this',
            'jigoshop_ga_id' => 'advanced.integration.google_analytics',
//			'jigoshop_ga_ecommerce_tracking_enabled' => 'no',
//			'jigoshop_catalog_product_button' => 'add',
            'jigoshop_catalog_sort_orderby' => 'shopping.catalog_order_by',
            'jigoshop_catalog_sort_direction' => 'shopping.catalog_order',
            'jigoshop_catalog_per_page' => 'shopping.catalog_per_page',
            'jigoshop_currency_pos' => 'general.currency_position',
            'jigoshop_price_thousand_sep' => 'general.currency_thousand_separator',
            'jigoshop_price_decimal_sep' => 'general.currency_decimal_separator',
            'jigoshop_price_num_decimals' => 'general.currency_decimals',
            'jigoshop_use_wordpress_tiny_crop' => 'products.images.tiny.crop',
            'jigoshop_use_wordpress_thumbnail_crop' => 'products.images.thumbnail.crop',
            'jigoshop_use_wordpress_catalog_crop' => 'products.images.small.crop',
            'jigoshop_use_wordpress_featured_crop' => 'products.images.large.crop',
            'jigoshop_shop_tiny_w' => 'products.images.tiny.width',
            'jigoshop_shop_tiny_h' => 'products.images.tiny.height',
            'jigoshop_shop_thumbnail_w' => 'products.images.thumbnail.width',
            'jigoshop_shop_thumbnail_h' => 'products.images.thumbnail.height',
            'jigoshop_shop_small_w' => 'products.images.small.width',
            'jigoshop_shop_small_h' => 'products.images.small.height',
            'jigoshop_shop_large_w' => 'products.images.large.width',
            'jigoshop_shop_large_h' => 'products.images.large.height',
            'jigoshop_weight_unit' => 'products.weight_unit',
            'jigoshop_dimension_unit' => 'products.dimensions_unit',
//			'jigoshop_product_thumbnail_columns' => '3',
//			'jigoshop_enable_related_products' => 'yes',
            'jigoshop_manage_stock' => 'products.manage_stock',
            'jigoshop_show_stock' => 'products.show_stock',
            'jigoshop_notify_low_stock' => 'products.notify_low_stock',
            'jigoshop_notify_low_stock_amount' => 'products.low_stock_threshold',
            'jigoshop_notify_no_stock' => 'products.notify_out_of_stock',
            'jigoshop_hide_no_stock_product' => 'shopping.hide_out_of_stock',
            'jigoshop_prices_include_tax' => 'tax.included',
            'jigoshop_tax_classes' => 'tax.classes',
            'jigoshop_tax_rates' => '',
            'jigoshop_calc_shipping' => 'shipping.enabled',
            'jigoshop_enable_shipping_calc' => 'shipping.calculator',
            'jigoshop_ship_to_billing_address_only' => 'shipping.only_to_billing',
            'jigoshop_show_checkout_shipping_fields' => 'shipping.always_show_shipping',
//			'jigoshop_default_gateway' => 'cheque',
//			'jigoshop_error_disappear_time' => 8000,
//			'jigoshop_message_disappear_time' => 4000,
            'jigoshop_shop_page_id' => 'advanced.pages.shop',
            'jigoshop_cart_page_id' => 'advanced.pages.cart',
            'jigoshop_checkout_page_id' => 'advanced.pages.checkout',
            'jigoshop_myaccount_page_id' => 'advanced.pages.account',
            'jigoshop_thanks_page_id' => 'advanced.pages.checkout_thank_you',
            'jigoshop_terms_page_id' => 'advanced.pages.terms',
        ];
    }

    public function ajaxMigrationOptions()
    {
        try {
//			1 - if first time ajax request
            if ($_POST['msgLog'] == 1) {
                Migration::saveLog(__('Migration options START.', 'jigoshop-ecommerce'), true);
            }

            $countAll = 93;
            $countRemain = 93;

            if (($itemsFromBase = $this->wp->getOption('jigoshop_options_migrate_id')) !== false) {
                if ($itemsFromBase === '1') {
                    $countRemain = 0;
                }
            }

            $ajax_response = [
                'success' => true,
                'percent' => floor(($countAll - $countRemain) / $countAll * 100),
                'processed' => $countAll - $countRemain,
                'remain' => $countRemain,
                'total' => $countAll,
            ];

            if ($countRemain > 0) {
                if ($this->migrate()) {
                    $this->wp->updateOption('jigoshop_options_migrate_id', '1');
                } else {
                    $ajax_response['success'] = false;
                    Migration::saveLog(__('Migration coupons end with error.', 'jigoshop-ecommerce'));
                }
            } elseif ($countRemain == 0) {
                Migration::saveLog(__('Migration coupons END.', 'jigoshop-ecommerce'));
            }

            echo json_encode($ajax_response);

        } catch (Exception $e) {
            if (WP_DEBUG) {
                \Monolog\Registry::getInstance(JIGOSHOP_LOGGER)->addDebug($e);
            }
            echo json_encode([
                'success' => false,
            ]);

            Migration::saveLog(__('Migration options end with error: ', 'jigoshop-ecommerce') . $e);
        }

        exit;
    }
}
