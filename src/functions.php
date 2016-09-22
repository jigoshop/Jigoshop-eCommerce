<?php

namespace Jigoshop;

/**
 * Checks if current Jigoshop version is at least a specified one.
 *
 * @param $version string Version string (i.e. 1.10.1)
 *
 * @return bool
 */
function isMinimumVersion($version)
{
	if (version_compare(Core::VERSION, $version, '<')) {
		return false;
	}

	return true;
}

/**
 * Adds notice for specified source (i.e. plugin name) that current Jigoshop version is not matched.
 *
 * Notice is added only if version is not at it's minimum.
 *
 * @param $source  string Source name (used in message).
 * @param $version string Version string (i.e. 1.10.1).
 *
 * @return bool Whether notice was added.
 */
function addRequiredVersionNotice($source, $version)
{
	if (!isMinimumVersion($version)) {
		add_action('admin_notices', function () use ($source, $version){
			$message = sprintf(__('<strong>%s</strong>: required Jigoshop version: %s. Current version: %s. Please upgrade.', 'jigoshop'), $source, $version, Core::VERSION);
			echo '<div class="error"><p>'.$message.'</p></div>';
		});

		return true;
	}

	return false;
}


//Remove this
if(!function_exists('__toString')) {
    function __toString(){}
}
if(!function_exists('_install_jigoshop')) {
    function _install_jigoshop(){}
}
if(!function_exists('add_bank_transfer_gateway')) {
    function add_bank_transfer_gateway(){}
}
if(!function_exists('add_cheque_gateway')) {
    function add_cheque_gateway(){}
}
if(!function_exists('add_cod_gateway')) {
    function add_cod_gateway(){}
}
if(!function_exists('add_futurepay_gateway')) {
    function add_futurepay_gateway(){}
}
if(!function_exists('array_compare')) {
    function array_compare(){}
}
if(!function_exists('array_find')) {
    function array_find(){}
}
if(!function_exists('attributes_display')) {
    function attributes_display(){}
}
if(!function_exists('base_country_notice')) {
    function base_country_notice(){}
}
if(!function_exists('boolval')) {
    function boolval(){}
}
if(!function_exists('Browser')) {
    function Browser(){}
}
if(!function_exists('changeVisibliltity')) {
    function changeVisibliltity(){}
}
if(!function_exists('check_ipn_request_is_valid')) {
    function check_ipn_request_is_valid(){}
}
if(!function_exists('check_ipn_response')) {
    function check_ipn_response(){}
}
if(!function_exists('checkBrowserAmaya')) {
    function checkBrowserAmaya(){}
}
if(!function_exists('checkBrowserAndroid')) {
    function checkBrowserAndroid(){}
}
if(!function_exists('checkBrowserBlackBerry')) {
    function checkBrowserBlackBerry(){}
}
if(!function_exists('checkBrowserChrome')) {
    function checkBrowserChrome(){}
}
if(!function_exists('checkBrowserFirebird')) {
    function checkBrowserFirebird(){}
}
if(!function_exists('checkBrowserFirefox')) {
    function checkBrowserFirefox(){}
}
if(!function_exists('checkBrowserGaleon')) {
    function checkBrowserGaleon(){}
}
if(!function_exists('checkBrowserGoogleBot')) {
    function checkBrowserGoogleBot(){}
}
if(!function_exists('checkBrowserIcab')) {
    function checkBrowserIcab(){}
}
if(!function_exists('checkBrowserIceCat')) {
    function checkBrowserIceCat(){}
}
if(!function_exists('checkBrowserIceweasel')) {
    function checkBrowserIceweasel(){}
}
if(!function_exists('checkBrowserInternetExplorer')) {
    function checkBrowserInternetExplorer(){}
}
if(!function_exists('checkBrowseriPad')) {
    function checkBrowseriPad(){}
}
if(!function_exists('checkBrowseriPhone')) {
    function checkBrowseriPhone(){}
}
if(!function_exists('checkBrowseriPod')) {
    function checkBrowseriPod(){}
}
if(!function_exists('checkBrowserKonqueror')) {
    function checkBrowserKonqueror(){}
}
if(!function_exists('checkBrowserLynx')) {
    function checkBrowserLynx(){}
}
if(!function_exists('checkBrowserMozilla')) {
    function checkBrowserMozilla(){}
}
if(!function_exists('checkBrowserMSNBot')) {
    function checkBrowserMSNBot(){}
}
if(!function_exists('checkBrowserNetPositive')) {
    function checkBrowserNetPositive(){}
}
if(!function_exists('checkBrowserNetscapeNavigator9Plus')) {
    function checkBrowserNetscapeNavigator9Plus(){}
}
if(!function_exists('checkBrowserNokia')) {
    function checkBrowserNokia(){}
}
if(!function_exists('checkBrowserOmniWeb')) {
    function checkBrowserOmniWeb(){}
}
if(!function_exists('checkBrowserOpera')) {
    function checkBrowserOpera(){}
}
if(!function_exists('checkBrowserPhoenix')) {
    function checkBrowserPhoenix(){}
}
if(!function_exists('checkBrowsers')) {
    function checkBrowsers(){}
}
if(!function_exists('checkBrowserSafari')) {
    function checkBrowserSafari(){}
}
if(!function_exists('checkBrowserShiretoko')) {
    function checkBrowserShiretoko(){}
}
if(!function_exists('checkBrowserSlurp')) {
    function checkBrowserSlurp(){}
}
if(!function_exists('checkBrowserW3CValidator')) {
    function checkBrowserW3CValidator(){}
}
if(!function_exists('checkBrowserWebTv')) {
    function checkBrowserWebTv(){}
}
if(!function_exists('checkForAol')) {
    function checkForAol(){}
}
if(!function_exists('checkout_form_shipping')) {
    function checkout_form_shipping(){}
}
if(!function_exists('checkPlatform')) {
    function checkPlatform(){}
}
if(!function_exists('column_default')) {
    function column_default(){}
}
if(!function_exists('csort_tax_rates')) {
    function csort_tax_rates(){}
}
if(!function_exists('determine')) {
    function determine(){}
}
if(!function_exists('display_attribute')) {
    function display_attribute(){}
}
if(!function_exists('form')) {
    function form(){}
}
if(!function_exists('form')) {
    function form(){}
}
if(!function_exists('format_tax_rates_for_display')) {
    function format_tax_rates_for_display(){}
}
if(!function_exists('FuturePayResponseHandler')) {
    function FuturePayResponseHandler(){}
}
if(!function_exists('get_available_attributes_variations')) {
    function get_available_attributes_variations(){}
}
if(!function_exists('get_called_class')) {
    function get_called_class(){}
}
if(!function_exists('get_customer_orders')) {
    function get_customer_orders(){}
}
if(!function_exists('get_default_attributes')) {
    function get_default_attributes(){}
}
if(!function_exists('get_jigoshop_cart')) {
    function get_jigoshop_cart(){}
}
if(!function_exists('get_jigoshop_change_password')) {
    function get_jigoshop_change_password(){}
}
if(!function_exists('get_jigoshop_checkout')) {
    function get_jigoshop_checkout(){}
}
if(!function_exists('get_jigoshop_currency_symbol')) {
    function get_jigoshop_currency_symbol(){}
}
if(!function_exists('get_jigoshop_edit_address')) {
    function get_jigoshop_edit_address(){}
}
if(!function_exists('get_jigoshop_my_account')) {
    function get_jigoshop_my_account(){}
}
if(!function_exists('get_jigoshop_order_tracking')) {
    function get_jigoshop_order_tracking(){}
}
if(!function_exists('get_jigoshop_pay')) {
    function get_jigoshop_pay(){}
}
if(!function_exists('get_jigoshop_thankyou')) {
    function get_jigoshop_thankyou(){}
}
if(!function_exists('get_jigoshop_view_order')) {
    function get_jigoshop_view_order(){}
}
if(!function_exists('get_order_email_arguments')) {
    function get_order_email_arguments(){}
}
if(!function_exists('get_order_email_arguments_description')) {
    function get_order_email_arguments_description(){}
}
if(!function_exists('get_shipping_tax_rate')) {
    function get_shipping_tax_rate(){}
}
if(!function_exists('get_stock_email_arguments')) {
    function get_stock_email_arguments(){}
}
if(!function_exists('get_stock_email_arguments_description')) {
    function get_stock_email_arguments_description(){}
}
if(!function_exists('get_tax_classes')) {
    function get_tax_classes(){}
}
if(!function_exists('get_tax_rates')) {
    function get_tax_rates(){}
}
if(!function_exists('get_updated_tax_classes')) {
    function get_updated_tax_classes(){}
}
if(!function_exists('get_value')) {
    function get_value(){}
}
if(!function_exists('getAolVersion')) {
    function getAolVersion(){}
}
if(!function_exists('getBrowser')) {
    function getBrowser(){}
}
if(!function_exists('getPlatform')) {
    function getPlatform(){}
}
if(!function_exists('getUserAgent')) {
    function getUserAgent(){}
}
if(!function_exists('getVersion')) {
    function getVersion(){}
}
if(!function_exists('in_plugin_update_message')) {
    function in_plugin_update_message(){}
}
if(!function_exists('init_cart_favicon')) {
    function init_cart_favicon(){}
}
if(!function_exists('install_jigoshop')) {
    function install_jigoshop(){}
}
if(!function_exists('is_account')) {
    function is_account(){}
}
if(!function_exists('is_ajax')) {
    function is_ajax(){}
}
if(!function_exists('is_cart')) {
    function is_cart(){}
}
if(!function_exists('is_checkout')) {
    function is_checkout(){}
}
if(!function_exists('is_content_wrapped')) {
    function is_content_wrapped(){}
}
if(!function_exists('is_jigoshop')) {
    function is_jigoshop(){}
}
if(!function_exists('is_jigoshop_page')) {
    function is_jigoshop_page(){}
}
if(!function_exists('is_jigoshop_single_page')) {
    function is_jigoshop_single_page(){}
}
if(!function_exists('is_order_tracker')) {
    function is_order_tracker(){}
}
if(!function_exists('is_product')) {
    function is_product(){}
}
if(!function_exists('is_product_category')) {
    function is_product_category(){}
}
if(!function_exists('is_product_list')) {
    function is_product_list(){}
}
if(!function_exists('is_product_tag')) {
    function is_product_tag(){}
}
if(!function_exists('is_shop')) {
    function is_shop(){}
}
if(!function_exists('isAol')) {
    function isAol(){}
}
if(!function_exists('isBrowser')) {
    function isBrowser(){}
}
if(!function_exists('isChromeFrame')) {
    function isChromeFrame(){}
}
if(!function_exists('isMobile')) {
    function isMobile(){}
}
if(!function_exists('isRobot')) {
    function isRobot(){}
}
if(!function_exists('jigoshop_add_attribute')) {
    function jigoshop_add_attribute(){}
}
if(!function_exists('jigoshop_add_body_class')) {
    function jigoshop_add_body_class(){}
}
if(!function_exists('jigoshop_add_category_thumbnail_field')) {
    function jigoshop_add_category_thumbnail_field(){}
}
if(!function_exists('jigoshop_add_comment_rating')) {
    function jigoshop_add_comment_rating(){}
}
if(!function_exists('jigoshop_add_order_item')) {
    function jigoshop_add_order_item(){}
}
if(!function_exists('jigoshop_add_required_version_notice')) {
    function jigoshop_add_required_version_notice(){}
}
if(!function_exists('jigoshop_add_script')) {
    function jigoshop_add_script(){}
}
if(!function_exists('jigoshop_add_style')) {
    function jigoshop_add_style(){}
}
if(!function_exists('jigoshop_add_to_bulk_quick_edit_custom_box')) {
    function jigoshop_add_to_bulk_quick_edit_custom_box(){}
}
if(!function_exists('jigoshop_add_to_cart_action')) {
    function jigoshop_add_to_cart_action(){}
}
if(!function_exists('jigoshop_add_to_cart_form_nonce')) {
    function jigoshop_add_to_cart_form_nonce(){}
}
if(!function_exists('jigoshop_admin_bar_edit')) {
    function jigoshop_admin_bar_edit(){}
}
if(!function_exists('jigoshop_admin_bar_links')) {
    function jigoshop_admin_bar_links(){}
}
if(!function_exists('jigoshop_admin_footer')) {
    function jigoshop_admin_footer(){}
}
if(!function_exists('jigoshop_admin_head')) {
    function jigoshop_admin_head(){}
}
if(!function_exists('jigoshop_admin_product_search')) {
    function jigoshop_admin_product_search(){}
}
if(!function_exists('jigoshop_admin_product_search_label')) {
    function jigoshop_admin_product_search_label(){}
}
if(!function_exists('jigoshop_admin_scripts')) {
    function jigoshop_admin_scripts(){}
}
if(!function_exists('jigoshop_admin_styles')) {
    function jigoshop_admin_styles(){}
}
if(!function_exists('jigoshop_admin_toolbar')) {
    function jigoshop_admin_toolbar(){}
}
if(!function_exists('jigoshop_admin_user_profile')) {
    function jigoshop_admin_user_profile(){}
}
if(!function_exists('jigoshop_admin_user_profile_update')) {
    function jigoshop_admin_user_profile_update(){}
}
if(!function_exists('jigoshop_after_admin_menu')) {
    function jigoshop_after_admin_menu(){}
}
if(!function_exists('jigoshop_ajax_get_product_stock_price')) {
    function jigoshop_ajax_get_product_stock_price(){}
}
if(!function_exists('jigoshop_ajax_update_item_quantity')) {
    function jigoshop_ajax_update_item_quantity(){}
}
if(!function_exists('jigoshop_ajax_update_order_review')) {
    function jigoshop_ajax_update_order_review(){}
}
if(!function_exists('jigoshop_attributes')) {
    function jigoshop_attributes(){}
}
if(!function_exists('jigoshop_before_admin_menu')) {
    function jigoshop_before_admin_menu(){}
}
if(!function_exists('jigoshop_body_class')) {
    function jigoshop_body_class(){}
}
if(!function_exists('jigoshop_body_classes')) {
    function jigoshop_body_classes(){}
}
if(!function_exists('jigoshop_body_classes_check')) {
    function jigoshop_body_classes_check(){}
}
if(!function_exists('jigoshop_breadcrumb')) {
    function jigoshop_breadcrumb(){}
}
if(!function_exists('jigoshop_bulk_actions')) {
    function jigoshop_bulk_actions(){}
}
if(!function_exists('jigoshop_cancel_order')) {
    function jigoshop_cancel_order(){}
}
if(!function_exists('jigoshop_cart')) {
    function jigoshop_cart(){}
}
if(!function_exists('jigoshop_cart_get_post_thumbnail')) {
    function jigoshop_cart_get_post_thumbnail(){}
}
if(!function_exists('jigoshop_cart_has_post_thumbnail')) {
    function jigoshop_cart_has_post_thumbnail(){}
}
if(!function_exists('jigoshop_categories_ordering')) {
    function jigoshop_categories_ordering(){}
}
if(!function_exists('jigoshop_categories_scripts')) {
    function jigoshop_categories_scripts(){}
}
if(!function_exists('jigoshop_category_thumbnail_field_save')) {
    function jigoshop_category_thumbnail_field_save(){}
}
if(!function_exists('jigoshop_change_insert_into_post')) {
    function jigoshop_change_insert_into_post(){}
}
if(!function_exists('jigoshop_change_password')) {
    function jigoshop_change_password(){}
}
if(!function_exists('jigoshop_check_comment_rating')) {
    function jigoshop_check_comment_rating(){}
}
if(!function_exists('jigoshop_check_required_css')) {
    function jigoshop_check_required_css(){}
}
if(!function_exists('jigoshop_check_thumbnail_support')) {
    function jigoshop_check_thumbnail_support(){}
}
if(!function_exists('jigoshop_checkout')) {
    function jigoshop_checkout(){}
}
if(!function_exists('jigoshop_checkout_login_form')) {
    function jigoshop_checkout_login_form(){}
}
if(!function_exists('jigoshop_clear_cart_after_payment')) {
    function jigoshop_clear_cart_after_payment(){}
}
if(!function_exists('jigoshop_clear_cart_on_return')) {
    function jigoshop_clear_cart_on_return(){}
}
if(!function_exists('jigoshop_comment_feed_where')) {
    function jigoshop_comment_feed_where(){}
}
if(!function_exists('jigoshop_comments')) {
    function jigoshop_comments(){}
}
if(!function_exists('jigoshop_comments_template')) {
    function jigoshop_comments_template(){}
}
if(!function_exists('jigoshop_complete_processing_orders')) {
    function jigoshop_complete_processing_orders(){}
}
if(!function_exists('jigoshop_coupon_data_box')) {
    function jigoshop_coupon_data_box(){}
}
if(!function_exists('jigoshop_create_emails')) {
    function jigoshop_create_emails(){}
}
if(!function_exists('jigoshop_create_pages')) {
    function jigoshop_create_pages(){}
}
if(!function_exists('jigoshop_create_product_cat')) {
    function jigoshop_create_product_cat(){}
}
if(!function_exists('jigoshop_create_single_page')) {
    function jigoshop_create_single_page(){}
}
if(!function_exists('jigoshop_custom_coupon_columns')) {
    function jigoshop_custom_coupon_columns(){}
}
if(!function_exists('jigoshop_custom_order_columns')) {
    function jigoshop_custom_order_columns(){}
}
if(!function_exists('jigoshop_custom_order_views')) {
    function jigoshop_custom_order_views(){}
}
if(!function_exists('jigoshop_custom_product_columns')) {
    function jigoshop_custom_product_columns(){}
}
if(!function_exists('jigoshop_custom_product_orderby')) {
    function jigoshop_custom_product_orderby(){}
}
if(!function_exists('jigoshop_custom_product_sort')) {
    function jigoshop_custom_product_sort(){}
}
if(!function_exists('jigoshop_dash_latest_news')) {
    function jigoshop_dash_latest_news(){}
}
if(!function_exists('jigoshop_dash_monthly_report')) {
    function jigoshop_dash_monthly_report(){}
}
if(!function_exists('jigoshop_dash_recent_orders')) {
    function jigoshop_dash_recent_orders(){}
}
if(!function_exists('jigoshop_dash_recent_reviews')) {
    function jigoshop_dash_recent_reviews(){}
}
if(!function_exists('jigoshop_dash_right_now')) {
    function jigoshop_dash_right_now(){}
}
if(!function_exists('jigoshop_dash_stock_report')) {
    function jigoshop_dash_stock_report(){}
}
if(!function_exists('jigoshop_dash_useful_links')) {
    function jigoshop_dash_useful_links(){}
}
if(!function_exists('jigoshop_dashboard')) {
    function jigoshop_dashboard(){}
}
if(!function_exists('jigoshop_default_options')) {
    function jigoshop_default_options(){}
}
if(!function_exists('jigoshop_default_taxonomies')) {
    function jigoshop_default_taxonomies(){}
}
if(!function_exists('jigoshop_delete_product_cat')) {
    function jigoshop_delete_product_cat(){}
}
if(!function_exists('jigoshop_demo_store')) {
    function jigoshop_demo_store(){}
}
if(!function_exists('jigoshop_disable_autosave')) {
    function jigoshop_disable_autosave(){}
}
if(!function_exists('jigoshop_download_product')) {
    function jigoshop_download_product(){}
}
if(!function_exists('jigoshop_downloadable_add_to_cart')) {
    function jigoshop_downloadable_add_to_cart(){}
}
if(!function_exists('jigoshop_downloadable_product_permissions')) {
    function jigoshop_downloadable_product_permissions(){}
}
if(!function_exists('jigoshop_edit_address')) {
    function jigoshop_edit_address(){}
}
if(!function_exists('jigoshop_edit_attribute')) {
    function jigoshop_edit_attribute(){}
}
if(!function_exists('jigoshop_edit_category_thumbnail_field')) {
    function jigoshop_edit_category_thumbnail_field(){}
}
if(!function_exists('jigoshop_edit_coupon_columns')) {
    function jigoshop_edit_coupon_columns(){}
}
if(!function_exists('jigoshop_edit_order_columns')) {
    function jigoshop_edit_order_columns(){}
}
if(!function_exists('jigoshop_edit_product_columns')) {
    function jigoshop_edit_product_columns(){}
}
if(!function_exists('jigoshop_email_data_box')) {
    function jigoshop_email_data_box(){}
}
if(!function_exists('jigoshop_email_variable_box')) {
    function jigoshop_email_variable_box(){}
}
if(!function_exists('jigoshop_enqueue_product_quick_scripts')) {
    function jigoshop_enqueue_product_quick_scripts(){}
}
if(!function_exists('jigoshop_enter_title_here')) {
    function jigoshop_enter_title_here(){}
}
if(!function_exists('jigoshop_eu_b2b_vat_message')) {
    function jigoshop_eu_b2b_vat_message(){}
}
if(!function_exists('jigoshop_exclude_order_admin_comments')) {
    function jigoshop_exclude_order_admin_comments(){}
}
if(!function_exists('jigoshop_external_add_to_cart')) {
    function jigoshop_external_add_to_cart(){}
}
if(!function_exists('jigoshop_feature_product')) {
    function jigoshop_feature_product(){}
}
if(!function_exists('jigoshop_featured_products')) {
    function jigoshop_featured_products(){}
}
if(!function_exists('jigoshop_filter_products_type')) {
    function jigoshop_filter_products_type(){}
}
if(!function_exists('jigoshop_filter_request')) {
    function jigoshop_filter_request(){}
}
if(!function_exists('jigoshop_force_ssl')) {
    function jigoshop_force_ssl(){}
}
if(!function_exists('jigoshop_force_ssl_images')) {
    function jigoshop_force_ssl_images(){}
}
if(!function_exists('jigoshop_force_ssl_urls')) {
    function jigoshop_force_ssl_urls(){}
}
if(!function_exists('jigoshop_format_decimal')) {
    function jigoshop_format_decimal(){}
}
if(!function_exists('jigoshop_front_page_archive')) {
    function jigoshop_front_page_archive(){}
}
if(!function_exists('jigoshop_frontend_scripts')) {
    function jigoshop_frontend_scripts(){}
}
if(!function_exists('jigoshop_ga_ecommerce_tracking')) {
    function jigoshop_ga_ecommerce_tracking(){}
}
if(!function_exists('jigoshop_ga_tracking')) {
    function jigoshop_ga_tracking(){}
}
if(!function_exists('jigoshop_get_address_fields')) {
    function jigoshop_get_address_fields(){}
}
if(!function_exists('jigoshop_get_address_to_edit')) {
    function jigoshop_get_address_to_edit(){}
}
if(!function_exists('jigoshop_get_available_pages')) {
    function jigoshop_get_available_pages(){}
}
if(!function_exists('jigoshop_get_category_field_for_product')) {
    function jigoshop_get_category_field_for_product(){}
}
if(!function_exists('jigoshop_get_core_capabilities')) {
    function jigoshop_get_core_capabilities(){}
}
if(!function_exists('jigoshop_get_core_supported_themes')) {
    function jigoshop_get_core_supported_themes(){}
}
if(!function_exists('jigoshop_get_current_post_type')) {
    function jigoshop_get_current_post_type(){}
}
if(!function_exists('jigoshop_get_customer_order_count')) {
    function jigoshop_get_customer_order_count(){}
}
if(!function_exists('jigoshop_get_customer_total_spent')) {
    function jigoshop_get_customer_total_spent(){}
}
if(!function_exists('jigoshop_get_formatted_variation')) {
    function jigoshop_get_formatted_variation(){}
}
if(!function_exists('jigoshop_get_image_placeholder')) {
    function jigoshop_get_image_placeholder(){}
}
if(!function_exists('jigoshop_get_image_size')) {
    function jigoshop_get_image_size(){}
}
if(!function_exists('jigoshop_get_order_coupon_list')) {
    function jigoshop_get_order_coupon_list(){}
}
if(!function_exists('jigoshop_get_order_items_table')) {
    function jigoshop_get_order_items_table(){}
}
if(!function_exists('jigoshop_get_order_taxes_list')) {
    function jigoshop_get_order_taxes_list(){}
}
if(!function_exists('jigoshop_get_page_id')) {
    function jigoshop_get_page_id(){}
}
if(!function_exists('jigoshop_get_plugin_data')) {
    function jigoshop_get_plugin_data(){}
}
if(!function_exists('jigoshop_get_product_ids_in_view')) {
    function jigoshop_get_product_ids_in_view(){}
}
if(!function_exists('jigoshop_get_product_thumbnail')) {
    function jigoshop_get_product_thumbnail(){}
}
if(!function_exists('jigoshop_get_sidebar')) {
    function jigoshop_get_sidebar(){}
}
if(!function_exists('jigoshop_get_sidebar_end')) {
    function jigoshop_get_sidebar_end(){}
}
if(!function_exists('jigoshop_get_template')) {
    function jigoshop_get_template(){}
}
if(!function_exists('jigoshop_get_template_part')) {
    function jigoshop_get_template_part(){}
}
if(!function_exists('jigoshop_get_user_address_data')) {
    function jigoshop_get_user_address_data(){}
}
if(!function_exists('jigoshop_grouped_add_to_cart')) {
    function jigoshop_grouped_add_to_cart(){}
}
if(!function_exists('jigoshop_head_version')) {
    function jigoshop_head_version(){}
}
if(!function_exists('jigoshop_hide_euvat_field')) {
    function jigoshop_hide_euvat_field(){}
}
if(!function_exists('jigoshop_import_start')) {
    function jigoshop_import_start(){}
}
if(!function_exists('jigoshop_init')) {
    function jigoshop_init(){}
}
if(!function_exists('jigoshop_install_emails')) {
    function jigoshop_install_emails(){}
}
if(!function_exists('jigoshop_is_admin_page')) {
    function jigoshop_is_admin_page(){}
}
if(!function_exists('jigoshop_is_minumum_version')) {
    function jigoshop_is_minumum_version(){}
}
if(!function_exists('jigoshop_json_search_products')) {
    function jigoshop_json_search_products(){}
}
if(!function_exists('jigoshop_json_search_products_and_variations')) {
    function jigoshop_json_search_products_and_variations(){}
}
if(!function_exists('jigoshop_layered_nav_init')) {
    function jigoshop_layered_nav_init(){}
}
if(!function_exists('jigoshop_layered_nav_query')) {
    function jigoshop_layered_nav_query(){}
}
if(!function_exists('jigoshop_let_to_num')) {
    function jigoshop_let_to_num(){}
}
if(!function_exists('jigoshop_localize_script')) {
    function jigoshop_localize_script(){}
}
if(!function_exists('jigoshop_locate_template')) {
    function jigoshop_locate_template(){}
}
if(!function_exists('jigoshop_log')) {
    function jigoshop_log(){}
}
if(!function_exists('jigoshop_login_form')) {
    function jigoshop_login_form(){}
}
if(!function_exists('jigoshop_mail_from_name')) {
    function jigoshop_mail_from_name(){}
}
if(!function_exists('jigoshop_meta_boxes')) {
    function jigoshop_meta_boxes(){}
}
if(!function_exists('jigoshop_meta_boxes_save')) {
    function jigoshop_meta_boxes_save(){}
}
if(!function_exists('jigoshop_meta_boxes_save_errors')) {
    function jigoshop_meta_boxes_save_errors(){}
}
if(!function_exists('jigoshop_meta_scripts')) {
    function jigoshop_meta_scripts(){}
}
if(!function_exists('jigoshop_my_account')) {
    function jigoshop_my_account(){}
}
if(!function_exists('jigoshop_nav_menu_items_classes')) {
    function jigoshop_nav_menu_items_classes(){}
}
if(!function_exists('jigoshop_order_actions_meta_box')) {
    function jigoshop_order_actions_meta_box(){}
}
if(!function_exists('jigoshop_order_attributes_meta_box')) {
    function jigoshop_order_attributes_meta_box(){}
}
if(!function_exists('jigoshop_order_categories')) {
    function jigoshop_order_categories(){}
}
if(!function_exists('jigoshop_order_data')) {
    function jigoshop_order_data(){}
}
if(!function_exists('jigoshop_order_data_meta_box')) {
    function jigoshop_order_data_meta_box(){}
}
if(!function_exists('jigoshop_order_items_meta_box')) {
    function jigoshop_order_items_meta_box(){}
}
if(!function_exists('jigoshop_order_review')) {
    function jigoshop_order_review(){}
}
if(!function_exists('jigoshop_order_status_field')) {
    function jigoshop_order_status_field(){}
}
if(!function_exists('jigoshop_order_totals_meta_box')) {
    function jigoshop_order_totals_meta_box(){}
}
if(!function_exists('jigoshop_order_tracking')) {
    function jigoshop_order_tracking(){}
}
if(!function_exists('jigoshop_output_content_wrapper')) {
    function jigoshop_output_content_wrapper(){}
}
if(!function_exists('jigoshop_output_content_wrapper_end')) {
    function jigoshop_output_content_wrapper_end(){}
}
if(!function_exists('jigoshop_output_product_data_tabs')) {
    function jigoshop_output_product_data_tabs(){}
}
if(!function_exists('jigoshop_output_related_products')) {
    function jigoshop_output_related_products(){}
}
if(!function_exists('jigoshop_page_body_classes')) {
    function jigoshop_page_body_classes(){}
}
if(!function_exists('jigoshop_pagination')) {
    function jigoshop_pagination(){}
}
if(!function_exists('jigoshop_pay')) {
    function jigoshop_pay(){}
}
if(!function_exists('jigoshop_pay_action')) {
    function jigoshop_pay_action(){}
}
if(!function_exists('jigoshop_pay_for_existing_order')) {
    function jigoshop_pay_for_existing_order(){}
}
if(!function_exists('jigoshop_populate_options')) {
    function jigoshop_populate_options(){}
}
if(!function_exists('jigoshop_post_type')) {
    function jigoshop_post_type(){}
}
if(!function_exists('jigoshop_post_updated_messages')) {
    function jigoshop_post_updated_messages(){}
}
if(!function_exists('jigoshop_prepare_dashboard_title')) {
    function jigoshop_prepare_dashboard_title(){}
}
if(!function_exists('jigoshop_price')) {
    function jigoshop_price(){}
}
if(!function_exists('jigoshop_price_filter')) {
    function jigoshop_price_filter(){}
}
if(!function_exists('jigoshop_process_ajax_checkout')) {
    function jigoshop_process_ajax_checkout(){}
}
if(!function_exists('jigoshop_process_checkout')) {
    function jigoshop_process_checkout(){}
}
if(!function_exists('jigoshop_process_login')) {
    function jigoshop_process_login(){}
}
if(!function_exists('jigoshop_process_shop_coupon_meta')) {
    function jigoshop_process_shop_coupon_meta(){}
}
if(!function_exists('jigoshop_process_shop_email_meta')) {
    function jigoshop_process_shop_email_meta(){}
}
if(!function_exists('jigoshop_process_shop_order_meta')) {
    function jigoshop_process_shop_order_meta(){}
}
if(!function_exists('jigoshop_product')) {
    function jigoshop_product(){}
}
if(!function_exists('jigoshop_product_add_to_cart')) {
    function jigoshop_product_add_to_cart(){}
}
if(!function_exists('jigoshop_product_add_to_cart_url')) {
    function jigoshop_product_add_to_cart_url(){}
}
if(!function_exists('jigoshop_product_attributes_help')) {
    function jigoshop_product_attributes_help(){}
}
if(!function_exists('jigoshop_product_attributes_panel')) {
    function jigoshop_product_attributes_panel(){}
}
if(!function_exists('jigoshop_product_attributes_tab')) {
    function jigoshop_product_attributes_tab(){}
}
if(!function_exists('jigoshop_product_cat_column')) {
    function jigoshop_product_cat_column(){}
}
if(!function_exists('jigoshop_product_cat_columns')) {
    function jigoshop_product_cat_columns(){}
}
if(!function_exists('jigoshop_product_cat_filter_post_link')) {
    function jigoshop_product_cat_filter_post_link(){}
}
if(!function_exists('jigoshop_product_cat_image')) {
    function jigoshop_product_cat_image(){}
}
if(!function_exists('jigoshop_product_category')) {
    function jigoshop_product_category(){}
}
if(!function_exists('jigoshop_product_category_help')) {
    function jigoshop_product_category_help(){}
}
if(!function_exists('jigoshop_product_customize_panel')) {
    function jigoshop_product_customize_panel(){}
}
if(!function_exists('jigoshop_product_customize_tab')) {
    function jigoshop_product_customize_tab(){}
}
if(!function_exists('jigoshop_product_data')) {
    function jigoshop_product_data(){}
}
if(!function_exists('jigoshop_product_data_box')) {
    function jigoshop_product_data_box(){}
}
if(!function_exists('jigoshop_product_data_help')) {
    function jigoshop_product_data_help(){}
}
if(!function_exists('jigoshop_product_description_panel')) {
    function jigoshop_product_description_panel(){}
}
if(!function_exists('jigoshop_product_description_tab')) {
    function jigoshop_product_description_tab(){}
}
if(!function_exists('jigoshop_product_dropdown_categories')) {
    function jigoshop_product_dropdown_categories(){}
}
if(!function_exists('jigoshop_product_list')) {
    function jigoshop_product_list(){}
}
if(!function_exists('jigoshop_product_list_help')) {
    function jigoshop_product_list_help(){}
}
if(!function_exists('jigoshop_product_reviews_panel')) {
    function jigoshop_product_reviews_panel(){}
}
if(!function_exists('jigoshop_product_reviews_tab')) {
    function jigoshop_product_reviews_tab(){}
}
if(!function_exists('jigoshop_product_sku')) {
    function jigoshop_product_sku(){}
}
if(!function_exists('jigoshop_product_tag')) {
    function jigoshop_product_tag(){}
}
if(!function_exists('jigoshop_product_tag_help')) {
    function jigoshop_product_tag_help(){}
}
if(!function_exists('jigoshop_product_thumbnail')) {
    function jigoshop_product_thumbnail(){}
}
if(!function_exists('jigoshop_product_updated_messages')) {
    function jigoshop_product_updated_messages(){}
}
if(!function_exists('jigoshop_products')) {
    function jigoshop_products(){}
}
if(!function_exists('jigoshop_products_by_category')) {
    function jigoshop_products_by_category(){}
}
if(!function_exists('jigoshop_recent_products')) {
    function jigoshop_recent_products(){}
}
if(!function_exists('jigoshop_register_shortcode_buttons')) {
    function jigoshop_register_shortcode_buttons(){}
}
if(!function_exists('jigoshop_register_shortcode_editor')) {
    function jigoshop_register_shortcode_editor(){}
}
if(!function_exists('jigoshop_register_widgets')) {
    function jigoshop_register_widgets(){}
}
if(!function_exists('jigoshop_related_products')) {
    function jigoshop_related_products(){}
}
if(!function_exists('jigoshop_remove_row_actions')) {
    function jigoshop_remove_row_actions(){}
}
if(!function_exists('jigoshop_remove_script')) {
    function jigoshop_remove_script(){}
}
if(!function_exists('jigoshop_remove_style')) {
    function jigoshop_remove_style(){}
}
if(!function_exists('jigoshop_render')) {
    function jigoshop_render(){}
}
if(!function_exists('jigoshop_render_result')) {
    function jigoshop_render_result(){}
}
if(!function_exists('jigoshop_reports')) {
    function jigoshop_reports(){}
}
if(!function_exists('jigoshop_required_memory_warning')) {
    function jigoshop_required_memory_warning(){}
}
if(!function_exists('jigoshop_required_version')) {
    function jigoshop_required_version(){}
}
if(!function_exists('jigoshop_required_wordpress_version')) {
    function jigoshop_required_wordpress_version(){}
}
if(!function_exists('jigoshop_required_wp_memory_warning')) {
    function jigoshop_required_wp_memory_warning(){}
}
if(!function_exists('jigoshop_return_template')) {
    function jigoshop_return_template(){}
}
if(!function_exists('jigoshop_roles_init')) {
    function jigoshop_roles_init(){}
}
if(!function_exists('jigoshop_sale_products')) {
    function jigoshop_sale_products(){}
}
if(!function_exists('jigoshop_sanitize_num')) {
    function jigoshop_sanitize_num(){}
}
if(!function_exists('jigoshop_sanitize_user')) {
    function jigoshop_sanitize_user(){}
}
if(!function_exists('jigoshop_save_attributes')) {
    function jigoshop_save_attributes(){}
}
if(!function_exists('jigoshop_save_bulk_edit')) {
    function jigoshop_save_bulk_edit(){}
}
if(!function_exists('jigoshop_save_quick_edit')) {
    function jigoshop_save_quick_edit(){}
}
if(!function_exists('jigoshop_schedule_events')) {
    function jigoshop_schedule_events(){}
}
if(!function_exists('jigoshop_search_shortcode')) {
    function jigoshop_search_shortcode(){}
}
if(!function_exists('jigoshop_send_customer_invoice')) {
    function jigoshop_send_customer_invoice(){}
}
if(!function_exists('jigoshop_set_category_order')) {
    function jigoshop_set_category_order(){}
}
if(!function_exists('jigoshop_set_image_sizes')) {
    function jigoshop_set_image_sizes(){}
}
if(!function_exists('jigoshop_sharethis')) {
    function jigoshop_sharethis(){}
}
if(!function_exists('jigoshop_shipping_calculator')) {
    function jigoshop_shipping_calculator(){}
}
if(!function_exists('jigoshop_shop_page_archive_redirect')) {
    function jigoshop_shop_page_archive_redirect(){}
}
if(!function_exists('jigoshop_shortcode_wrapper')) {
    function jigoshop_shortcode_wrapper(){}
}
if(!function_exists('jigoshop_show_product_images')) {
    function jigoshop_show_product_images(){}
}
if(!function_exists('jigoshop_show_product_sale_flash')) {
    function jigoshop_show_product_sale_flash(){}
}
if(!function_exists('jigoshop_show_product_thumbnails')) {
    function jigoshop_show_product_thumbnails(){}
}
if(!function_exists('jigoshop_simple_add_to_cart')) {
    function jigoshop_simple_add_to_cart(){}
}
if(!function_exists('jigoshop_tables_install')) {
    function jigoshop_tables_install(){}
}
if(!function_exists('jigoshop_template_loader')) {
    function jigoshop_template_loader(){}
}
if(!function_exists('jigoshop_template_loop_add_to_cart')) {
    function jigoshop_template_loop_add_to_cart(){}
}
if(!function_exists('jigoshop_template_loop_price')) {
    function jigoshop_template_loop_price(){}
}
if(!function_exists('jigoshop_template_loop_product_thumbnail')) {
    function jigoshop_template_loop_product_thumbnail(){}
}
if(!function_exists('jigoshop_template_single_add_to_cart')) {
    function jigoshop_template_single_add_to_cart(){}
}
if(!function_exists('jigoshop_template_single_excerpt')) {
    function jigoshop_template_single_excerpt(){}
}
if(!function_exists('jigoshop_template_single_meta')) {
    function jigoshop_template_single_meta(){}
}
if(!function_exists('jigoshop_template_single_price')) {
    function jigoshop_template_single_price(){}
}
if(!function_exists('jigoshop_template_single_sharing')) {
    function jigoshop_template_single_sharing(){}
}
if(!function_exists('jigoshop_template_single_title')) {
    function jigoshop_template_single_title(){}
}
if(!function_exists('jigoshop_terms_clauses')) {
    function jigoshop_terms_clauses(){}
}
if(!function_exists('jigoshop_thankyou')) {
    function jigoshop_thankyou(){}
}
if(!function_exists('jigoshop_update')) {
    function jigoshop_update(){}
}
if(!function_exists('jigoshop_update_cart_action')) {
    function jigoshop_update_cart_action(){}
}
if(!function_exists('jigoshop_update_pending_orders')) {
    function jigoshop_update_pending_orders(){}
}
if(!function_exists('jigoshop_upgrade')) {
    function jigoshop_upgrade(){}
}
if(!function_exists('jigoshop_upgrade_1_10_0')) {
    function jigoshop_upgrade_1_10_0(){}
}
if(!function_exists('jigoshop_upgrade_1_10_3')) {
    function jigoshop_upgrade_1_10_3(){}
}
if(!function_exists('jigoshop_upgrade_1_10_6')) {
    function jigoshop_upgrade_1_10_6(){}
}
if(!function_exists('jigoshop_upgrade_1_13_3')) {
    function jigoshop_upgrade_1_13_3(){}
}
if(!function_exists('jigoshop_upgrade_1_16_0')) {
    function jigoshop_upgrade_1_16_0(){}
}
if(!function_exists('jigoshop_upgrade_1_16_1')) {
    function jigoshop_upgrade_1_16_1(){}
}
if(!function_exists('jigoshop_upgrade_1_8_0')) {
    function jigoshop_upgrade_1_8_0(){}
}
if(!function_exists('jigoshop_validate_postcode')) {
    function jigoshop_validate_postcode(){}
}
if(!function_exists('jigoshop_variable_add_to_cart')) {
    function jigoshop_variable_add_to_cart(){}
}
if(!function_exists('jigoshop_verify_checkout_states_for_countries_message')) {
    function jigoshop_verify_checkout_states_for_countries_message(){}
}
if(!function_exists('jigoshop_view_order')) {
    function jigoshop_view_order(){}
}
if(!function_exists('jigoshop_virtual_add_to_cart')) {
    function jigoshop_virtual_add_to_cart(){}
}
if(!function_exists('jigoshop_walk_category_dropdown_tree')) {
    function jigoshop_walk_category_dropdown_tree(){}
}
if(!function_exists('jigoshop_write_panel_scripts')) {
    function jigoshop_write_panel_scripts(){}
}
if(!function_exists('jigowatt_clean')) {
    function jigowatt_clean(){}
}
if(!function_exists('jigowatt_clean')) {
    function jigowatt_clean(){}
}
if(!function_exists('jrto_deregister_script')) {
    function jrto_deregister_script(){}
}
if(!function_exists('jrto_deregister_style')) {
    function jrto_deregister_style(){}
}
if(!function_exists('jrto_enqueue_script')) {
    function jrto_enqueue_script(){}
}
if(!function_exists('jrto_enqueue_style')) {
    function jrto_enqueue_style(){}
}
if(!function_exists('jrto_localize_script')) {
    function jrto_localize_script(){}
}
if(!function_exists('jrto_register_script')) {
    function jrto_register_script(){}
}
if(!function_exists('jrto_register_style')) {
    function jrto_register_style(){}
}
if(!function_exists('legacy_ipn_response')) {
    function legacy_ipn_response(){}
}
if(!function_exists('my_action_row')) {
    function my_action_row(){}
}
if(!function_exists('on_load_page')) {
    function on_load_page(){}
}
if(!function_exists('on_show_page')) {
    function on_show_page(){}
}
if(!function_exists('onCatChange')) {
    function onCatChange(){}
}
if(!function_exists('orders_filter_when')) {
    function orders_filter_when(){}
}
if(!function_exists('orders_this_month')) {
    function orders_this_month(){}
}
if(!function_exists('payment_fields')) {
    function payment_fields(){}
}
if(!function_exists('process_payment')) {
    function process_payment(){}
}
if(!function_exists('receipt_page')) {
    function receipt_page(){}
}
if(!function_exists('reset')) {
    function reset(){}
}
if(!function_exists('set_variation_attributes')) {
    function set_variation_attributes(){}
}
if(!function_exists('setAol')) {
    function setAol(){}
}
if(!function_exists('setAolVersion')) {
    function setAolVersion(){}
}
if(!function_exists('setBrowser')) {
    function setBrowser(){}
}
if(!function_exists('setMobile')) {
    function setMobile(){}
}
if(!function_exists('setPlatform')) {
    function setPlatform(){}
}
if(!function_exists('setRobot')) {
    function setRobot(){}
}
if(!function_exists('setUserAgent')) {
    function setUserAgent(){}
}
if(!function_exists('setVersion')) {
    function setVersion(){}
}
if(!function_exists('showTooltip')) {
    function showTooltip(){}
}
if(!function_exists('start_el')) {
    function start_el(){}
}
if(!function_exists('successful_request')) {
    function successful_request(){}
}
if(!function_exists('taxonomy_metadata_wpdbfix')) {
    function taxonomy_metadata_wpdbfix(){}
}
if(!function_exists('thankyou_page')) {
    function thankyou_page(){}
}
if(!function_exists('update')) {
    function update(){}
}
if(!function_exists('update_variable_list')) {
    function update_variable_list(){}
}
if(!function_exists('weekendAreas')) {
    function weekendAreas(){}
}
if(!function_exists('widget')) {
    function widget(){}
}