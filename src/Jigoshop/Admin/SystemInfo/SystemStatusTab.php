<?php
namespace Jigoshop\Admin\SystemInfo;

use Jigoshop\Admin\Settings\TabInterface;
use Jigoshop\Admin\SystemInfo;
use Jigoshop\Core;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

class SystemStatusTab implements TabInterface
{
	const SLUG = 'system_status';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var array */
	private $serverLocale = array();
	/** @var  @var array  */
	private $sections = array();

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), array('admin.php'))) {
				return;
			}

			if (isset($_REQUEST['tab']) && $_REQUEST['tab'] != self::SLUG){
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, array('jigoshop_page_'.SystemInfo::NAME))) {
				return;
			}

			Scripts::add('jigoshop.admin.system_info.system_status', JIGOSHOP_URL.'/assets/js/admin/system_info/system_status.js', array('jquery'));
			Scripts::localize('jigoshop.admin.system_info.system_status', 'system_data', $this->getSystemData());
		});
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('System Status', 'jigoshop');
	}

	/**
	 * @return string Tab slug.
	 */
	public function getSlug()
	{
		return self::SLUG;
	}

	/**
	 * @return array List of items to display.
	 */
	public function getSections()
	{
		if(empty($this->sections)){
			$this->sections = array(
				array(
					'title' => __('Get System Information', 'jigoshop'),
					'id' => 'get-system-information',
					'description' => __('Please copy and paste this information in your ticket when contacting support', 'jigoshop'),
					'fields' => array(
						array(
							'id' => 'debug_report',
							'title' => '',
							'generate_button_id' => 'generate-report',
							'debug_textarea_id' => 'report-for-support',
							'generate_button_label' => __('Generate Report', 'jigoshop'),
							'type' => 'user_defined',
							'display' => function($field){
								return Render::output('admin/system_info/debug_report', $field);
							}
						),
					)
				),
				array(
					'title' => __('WordPress Environment', 'jigoshop'),
					'id' => 'wordpress-environment',
					'fields' => array(
						array(
							'id' => 'home-url',
							'name' => 'home-url',
							'title' => __('Home URL', 'jigoshop'),
							'tip' => __('The URL of your site\'s homepage.', 'jigoshop'),
							'type' => 'constant',
							'value' => home_url(),
						),
						array(
							'id' => 'site-url',
							'name' => 'site-url',
							'title' => __('Site URL', 'jigoshop'),
							'tip' => __('The root URL of your site.', 'jigoshop'),
							'type' => 'constant',
							'value' => site_url(),
						),
						array(
							'id' => 'jigoshop-version',
							'name' => 'jigoshop-version',
							'title' => __('Jigoshop Version', 'jigoshop'),
							'tip' => __('The version of Jigoshop installed on your site.', 'jigoshop'),
							'type' => 'constant',
							'value' => Core::VERSION,
						),
						array(
							'id' => 'jigoshop-database-version',
							'name' => 'jigoshop-database-version',
							'title' => __('Jigoshop Database Version', 'jigoshop'),
							'tip' => __('The version of jigoshop that the database is formatted for. This should be the same as your jigoshop Version.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->wp->getOption('jigoshop-database-version'),
						),
						array(
							'id' => 'log-directory-writable',
							'name' => 'log-directory-writable',
							'title' => __('Log Directory Writable', 'jigoshop'),
							'tip' => __('Several Jigoshop extensions can write logs which makes debugging problems easier. The directory must be writable for this to happen.', 'jigoshop'),
							'description' => sprintf(__('To allow logging, make <code>%s</code> writable or define a custom <code>JIGOSHOP_LOG_DIR</code>.', 'jigoshop'), JIGOSHOP_LOG_DIR),
							'type' => 'constant',
							'value' => $this->checkLogDirectory() ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'wp-version',
							'name' => 'wp-version',
							'title' => __('WP Version', 'jigoshop'),
							'tip' => __('The version of WordPress installed on your site.', 'jigoshop'),
							'type' => 'constant',
							'value' => get_bloginfo('version'),
						),
						array(
							'id' => 'wp-multisite',
							'name' => 'wp-multisite',
							'title' => __('WP Multisite', 'jigoshop'),
							'tip' => __('The maximum amount of memory (RAM) that your site can use at one time.', 'jigoshop'),
							'type' => 'constant',
							'value' => is_multisite() ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'wp-memory-limit',
							'name' => 'wp-memory-limit',
							'title' => __('WP Memory Limit', 'jigoshop'),
							'tip' => __('The maximum amount of memory (RAM) that your site can use at one time.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->checkMemoryLimit(WP_MEMORY_LIMIT, JIGOSHOP_REQUIRED_WP_MEMORY),
						),
						array(
							'id' => 'wp-debug-mode',
							'name' => 'wp-debug-mode',
							'title' => __('WP Debug Mode', 'jigoshop'),
							'tip' => __('Displays whether or not WordPress is in Debug Mode.', 'jigoshop'),
							'type' => 'constant',
							'value' => defined('WP_DEBUG') && WP_DEBUG ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'language',
							'name' => 'language',
							'title' => __('Language', 'jigoshop'),
							'tip' => __('The current language used by WordPress. Default = English.', 'jigoshop'),
							'type' => 'constant',
							'value' => get_locale(),
						),
					),
				),
				array(
					'title' => __('Server Environment', 'jigoshop'),
					'id' => 'srever-environment',
					'fields' => array(
						array(
							'id' => 'server-info',
							'name' => 'server-info',
							'title' => __('Server Info', 'jigoshop'),
							'tip' => __('Information about the web server that is currently hosting your site.', 'jigoshop'),
							'type' => 'constant',
							'value' => esc_html($_SERVER['SERVER_SOFTWARE']),
						),
						array(
							'id' => 'php-version',
							'name' => 'php-version',
							'title' => __('PHP Version', 'jigoshop'),
							'tip' => __('The version of PHP installed on your hosting server.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->checkPhpVersion(PHP_VERSION, JIGOSHOP_PHP_VERSION),
						),
						array(
							'id' => 'php-post-max-size',
							'name' => 'php-post-max-size',
							'title' => __('PHP Post Max Size', 'jigoshop'),
							'tip' => __('The largest filesize that can be contained in one post.', 'jigoshop'),
							'type' => 'constant',
							'value' => size_format($this->letterToNumber($this->iniGet('post_max_size'))),
						),
						array(
							'id' => 'php-time-limit',
							'name' => 'php-time-limit',
							'title' => __('PHP Time Limit', 'jigoshop'),
							'tip' => __('The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups).', 'jigoshop'),
							'type' => 'constant',
							'value' => size_format($this->letterToNumber($this->iniGet('post_max_size'))),
						),
						array(
							'id' => 'php-time-limit',
							'name' => 'php-time-limit',
							'title' => __('PHP Max Input Vars', 'jigoshop'),
							'tip' => __('The maximum number of variables your server can use for a single function to avoid overloads.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->iniGet('max_input_vars'),
						),
						array(
							'id' => 'suhosin-installed',
							'name' => 'suhosin-installed',
							'title' => __('SUHOSIN Installed', 'jigoshop'),
							'tip' => __('Suhosin is an advanced protection system for PHP installations. It was designed to protect your servers on the one hand against a number of well known problems in PHP applications and on the other hand against potential unknown vulnerabilities within these applications or the PHP core itself. If enabled on your server, Suhosin may need to be configured to increase its data submission limits.', 'jigoshop'),
							'type' => 'constant',
							'value' => extension_loaded('suhosin') ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'eaccelerator',
							'name' => 'eaccelerator',
							'title' => __('eAccelerator', 'jigoshop'),
							'classes' => array('system-data'),
							'tip' => __('eAccelerator is deprecated and causes problems with Jigoshop.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->iniGet('eaccelerator.enable') == 1 ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'apc',
							'name' => 'apc',
							'title' => __('APC', 'jigoshop'),
							'tip' => __('APC is deprecated and causes problems with Jigoshop.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->iniGet('apc.enable') == 1 ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'apc',
							'name' => 'apc',
							'title' => __('OpCache', 'jigoshop'),
							'tip' => __('OpCache is new PHP optimizer and it is recommended to use with Jigoshop.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->iniGet('opcache.enable') == 1 ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'short-open-tag',
							'name' => 'short-open-tag',
							'title' => __('Short Open Tag', 'jigoshop'),
							'tip' => __('Whether short tags are enabled, they are used by some older extensions.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->iniGet('short-open-tag') != '' ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'allow-url-fopen',
							'name' => 'allow-url-fopen',
							'title' => __('Allow URL fopen', 'jigoshop'),
							'tip' => __('Whether fetching remote files is allowed. This option is used by many Jigoshop extensions.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->iniGet('allow_url_fopen') != '' ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'session',
							'name' => 'session',
							'title' => __('Session', 'jigoshop'),
							'tip' => __('Whether fetching remote files is allowed. This option is used by many Jigoshop extensions.', 'jigoshop'),
							'type' => 'constant',
							'value' => (session_id() != null && isset($_SESSION)) ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'cookie-path',
							'name' => 'cookie-path',
							'title' => __('Cookie Path', 'jigoshop'),
							'tip' => __('Path for which cookies are saved. This is important for sessions and session security.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->iniGet('session.cookie_path'),
						),
						array(
							'id' => 'save-path',
							'name' => 'save-path',
							'title' => __('Save Path', 'jigoshop'),
							'tip' => __('Path where sessions are stored on the server. This is sometimes cause of login/logout problems.', 'jigoshop'),
							'type' => 'constant',
							'value' => esc_html($this->iniGet('session.save_path')),
						),
						array(
							'id' => 'use-cookies',
							'name' => 'use-cookies',
							'title' => __('Use Cookies', 'jigoshop'),
							'tip' => __('Whether cookies are used to store PHP session on user\'s computer. Recommended.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->iniGet('session.use_cookies') != '' ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'use-only-cookies',
							'name' => 'use-only-cookies',
							'title' => __('Use Only Cookies', 'jigoshop'),
							'tip' => __('Whether PHP uses only cookies to handle user sessions. This is important for security reasons.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->iniGet('session.use_only_cookies') != '' ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'max-upload-size',
							'name' => 'max-upload-size',
							'title' => __('Max Upload Size', 'jigoshop'),
							'tip' => __('The largest filesize that can be uploaded to your WordPress installation.', 'jigoshop'),
							'type' => 'constant',
							'value' => size_format(wp_max_upload_size()),
						),
						array(
							'id' => 'default-timezone',
							'name' => 'default-timezone',
							'title' => __('Default Timezone', 'jigoshop'),
							'tip' => __('The default timezone for your server. We recommend to set it as UTC.', 'jigoshop'),
							'type' => 'constant',
							'value' => date_default_timezone_get(),
						),
						array(
							'id' => 'fsockopen-curl',
							'name' => 'fsockopen-curl',
							'title' => __('fsockopen/cURL', 'jigoshop'),
							'tip' => __('Payment gateways can use cURL to communicate with remote servers to authorize payments, other plugins may also use it when communicating with remote services.', 'jigoshop'),
							'type' => 'constant',
							'value' => function_exists('fsockopen') || function_exists('curl_init') ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'soap-client',
							'name' => 'soap-client',
							'title' => __('SoapClient', 'jigoshop'),
							'tip' => __('Some webservices like shipping use SOAP to get information from remote servers, for example, live shipping quotes from FedEx require SOAP to be installed.', 'jigoshop'),
							'type' => 'constant',
							'value' => class_exists('SoapClient') ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'wp-remote-post',
							'name' => 'wp-remote-post',
							'title' => __('Remote Post', 'jigoshop'),
							'tip' => __('PayPal uses this method of communicating when sending back transaction information.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->checkRemoteRequest('post') ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'wp-remote-get',
							'name' => 'wp-remote-get',
							'title' => __('Remote Get', 'jigoshop'),
							'tip' => __('PayJigoshop plugins may use this method of communication when checking for plugin updates.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->checkRemoteRequest('get') ? '&#10004;' : '&#10005;',
						),
					),
				),
				array(
					'title' => __('Server Locale', 'jigoshop'),
					'id' => 'srever-locale',
					'fields' => array(
						array(
							'id' => 'decimal-point',
							'name' => 'decimal-point',
							'title' => __('Decimal Point', 'jigoshop'),
							'tip' => __('The character used for decimal points.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->getServerLocale('decimal_point') ? $this->getServerLocale('decimal_point') : '&#10005;',
						),
						array(
							'id' => 'thousands-sep',
							'name' => 'thousands-sep',
							'title' => __('Thousands Separator', 'jigoshop'),
							'tip' => __('The character used for a thousands separator.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->getServerLocale('thousands_sep') ? $this->getServerLocale('thousands_sep') : '&#10005;',
						),
						array(
							'id' => 'mon-decimal-point',
							'name' => 'mon-decimal-point',
							'title' => __('Monetary Decimal Point', 'jigoshop'),
							'tip' => __('The character used for decimal points in monetary values.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->getServerLocale('mon_decimal_point') ? $this->getServerLocale('mon_decimal_point') : '&#10005;',
						),
						array(
							'id' => 'mon-thousands-sep',
							'name' => 'mon-thousands-sep',
							'title' => __('Monetary Thousands Separator', 'jigoshop'),
							'tip' => __('The character used for a thousands separator in monetary values.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->getServerLocale('mon_thousands_sep') ? $this->getServerLocale('mon_thousands_sep') : '&#10005;',
						),
					),
				),
				array(
					'title' => sprintf(__('Active Plugins (%s)', 'jigoshop'), count((array)$this->wp->getOption('active_plugins'))),
					'id' => 'active-plugins',
					'fields' => $this->getActivePlugins()
				),
				array(
					'title' => __('Settings', 'jigoshop'),
					'id' => 'settings',
					'fields' => array(
						array(
							'id' => 'force-ssl',
							'name' => 'force-ssl',
							'title' => __('Force SSL', 'jigoshop'),
							'tip' => __('Does your site force a SSL Certificate for transactions?', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->options->get('shopping.force_ssl') ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'shipping-enabled',
							'name' => 'shipping-enabled',
							'title' => __('Shipping Enabled', 'jigoshop'),
							'tip' => __('Does your site have shipping enabled?', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->options->get('shipping.enabled') ? '&#10004;' : '&#10005;',
						),
						array(
							'id' => 'currency',
							'name' => 'currency',
							'title' => __('Shipping Enabled', 'jigoshop'),
							'tip' => __('What currency prices are listed at in the catalog and which currency gateways will take payments in?', 'jigoshop'),
							'type' => 'constant',
							'value' => Currency::code().'('.Currency::symbol().')',
						),
						array(
							'id' => 'currency-position',
							'name' => 'currency-position',
							'title' => __('Currency Position', 'jigoshop'),
							'tip' => __('The position of the currency symbol.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->getCurrencyPosition(),
						),
						array(
							'id' => 'thousand-separator',
							'name' => 'thousand-separator',
							'title' => __('Thousand Separator', 'jigoshop'),
							'tip' => __('The thousand separator of displayed prices.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->options->get('general.currency_thousand_separator'),
						),
						array(
							'id' => 'decimal-separator',
							'name' => 'decimal-separator',
							'title' => __('Decimal Separator', 'jigoshop'),
							'tip' => __('The decimal separator of displayed prices.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->options->get('general.currency_decimal_separator'),
						),
						array(
							'id' => 'number-of-decimals',
							'name' => 'number-of-decimals',
							'title' => __('Number of Decimals', 'jigoshop'),
							'tip' => __('The number of decimal points shown in displayed prices.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->options->get('general.currency_decimals'),
						),
					)
				),
				array(
					'title' => __('Jigoshop Pages', 'jigoshop'),
					'id' => 'jigoshop-pages',
					'fields' => array(
						array(
							'id' => 'shop-base',
							'name' => 'shop-base',
							'title' => __('Shop Base', 'jigoshop'),
							'tip' => __('The ID of your Jigoshop shop\'s homepage.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->options->get('advanced.pages.shop') ? '#'.$this->options->get('advanced.pages.shop') : '&#10005;',
						),
						array(
							'id' => 'cart',
							'name' => 'cart',
							'title' => __('Cart', 'jigoshop'),
							'tip' => __('The ID of your Jigoshop shop\'s cart page.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->options->get('advanced.pages.cart') ? '#'.$this->options->get('advanced.pages.cart') : '&#10005;',
						),
						array(
							'id' => 'checkout',
							'name' => 'checkout',
							'title' => __('Checkout', 'jigoshop'),
							'tip' => __('The ID of your Jigoshop shop\'s checkout page.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->options->get('advanced.pages.checkout') ? '#'.$this->options->get('advanced.pages.checkout') : '&#10005;',
						),
						array(
							'id' => 'thank-you',
							'name' => 'thank-you',
							'title' => __('Thank You', 'jigoshop'),
							'tip' => __('The ID of your Jigoshop shop\'s thank you page.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->options->get('advanced.pages.checkout_thank_you') ? '#'.$this->options->get('advanced.pages.checkout_thank_you') : '&#10005;',
						),
						array(
							'id' => 'my-account',
							'name' => 'my-account',
							'title' => __('My account', 'jigoshop'),
							'tip' => __('The ID of your Jigoshop shop\'s my account page.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->options->get('advanced.pages.account') ? '#'.$this->options->get('advanced.pages.account') : '&#10005;',
						),
						array(
							'id' => 'terms',
							'name' => 'terms',
							'title' => __('Terms', 'jigoshop'),
							'tip' => __('The ID of your Jigoshop shop\'s terms page.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->options->get('advanced.pages.terms') ? '#'.$this->options->get('advanced.pages.terms') : '&#10005;',
						),
					)
				),
				array(
					'title' => __('Theme', 'jigoshop'),
					'id' => 'theme',
					'fields' => array(
						array(
							'id' => 'name',
							'name' => 'name',
							'title' => __('Name', 'jigoshop'),
							'tip' => __('The name of the current active theme.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->wp->wpGetTheme()->display('Name'),
						),
						array(
							'id' => 'version',
							'name' => 'version',
							'title' => __('Version', 'jigoshop'),
							'tip' => __('The installed version of the current active theme.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->wp->wpGetTheme()->display('Version')
						),
						array(
							'id' => 'author-url',
							'name' => 'author-url',
							'title' => __('Author URL', 'jigoshop'),
							'tip' => __('The theme developers URL.', 'jigoshop'),
							'type' => 'constant',
							'value' => $this->wp->wpGetTheme()->display('AuthorURI')
						),
						array(
							'id' => 'child-theme',
							'name' => 'child-theme',
							'title' => __('Child Theme', 'jigoshop'),
							'tip' => __('Displays whether or not the current theme is a child theme', 'jigoshop'),
							'description' => sprintf(__('If you\'re modifying Jigoshop or a parent theme you didn\'t build personally we recommend using a child theme. See: <a href="%s" target="-blank">How to create a child theme</a>', 'jigoshop'), 'https://codex.wordpress.org/Child_Themes'),
							'type' => 'constant',
							'value' => is_child_theme() ? '&#10004;' : '&#10005;'
						),
						array(
							'id' => 'parent-theme-name',
							'name' => 'parent-theme-name',
							'title' => __('Parent Theme Name', 'jigoshop'),
							'tip' => __('The name of the parent theme.', 'jigoshop'),
							'type' => 'constant',
							'value' => is_child_theme() ? wp_get_theme($this->wp->wpGetTheme()->display('Template'))->display('Name') : '&#10005;'
						),
						array(
							'id' => 'parent-theme-version',
							'name' => 'parent-theme-version',
							'title' => __('Parent Theme Version', 'jigoshop'),
							'tip' => __('The installed version of the parent theme.', 'jigoshop'),
							'type' => 'constant',
							'value' => is_child_theme() ? wp_get_theme($this->wp->wpGetTheme()->display('Template'))->display('Version') : '&#10005;'
						),
						array(
							'id' => 'parent-theme-author-url',
							'name' => 'parent-theme-author-url',
							'title' => __('Parent Theme Author URL', 'jigoshop'),
							'tip' => __('The installed version of the parent theme.', 'jigoshop'),
							'type' => 'constant',
							'value' => is_child_theme() ? wp_get_theme($this->wp->wpGetTheme()->display('Template'))->display('AuthorURI') : '&#10005;'
						),
					)
				),
				array(
					'title' => __('Templates', 'jigoshop'),
					'id' => 'templates',
					'description' => __('This section shows any files that are overriding the default jigoshop template pages', 'jigoshop'),
					'fields' => $this->getOverrides()
				),
			);
		}

		return $this->sections;
	}

	/**
	 * Validate and sanitize input values.
	 *
	 * @param array $settings Input fields.
	 *
	 * @return array Sanitized and validated output.
	 * @throws ValidationException When some items are not valid.
	 */
	public function validate($settings)
	{
		return $settings;
	}

	private function getSystemData()
	{
		$data = array();
		$sections = $this->getSections();
		$ignoredSections = array('get-system-information');
		foreach($sections as $section){
			if(!in_array($section['id'], $ignoredSections)){
				$data[] = PHP_EOL.'### '.$section['title'].' ###'.PHP_EOL;
				foreach($section['fields'] as $field){
					$data[] = strip_tags($field['title'] .': '.str_replace(array('&#10004;', '&#10005;'), array('Yes', 'No'), $field['value']));
				}
			}
		}

		return $data;
	}

	/**
	 * Checks if log directory is writable
	 *
	 * @return string
	 */
	private function checkLogDirectory()
	{
		if (@fopen(JIGOSHOP_LOG_DIR.'/jigoshop.log', 'a')) {
			return true;
		}
		return false;
	}

	/**
	 * @param string $definedMemory
	 * @param string $requiredMemory
	 *
	 * @return string
	 */
	private function checkMemoryLimit($definedMemory, $requiredMemory)
	{

		$memory_limit = $this->letterToNumber($definedMemory);

		if ($memory_limit < $requiredMemory * 1024 * 1024) {
			return sprintf(__('%s - We recommend setting memory to at least %dMB. See: <a href="%s" target="_blank">Increasing memory allocated to PHP</a>', 'jigoshop'), size_format($memory_limit), $requiredMemory, 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP');
		} else {
			return size_format($memory_limit);
		}
	}

	/**
	 * @param string $actualVersion
	 * @param string $requiredVersion
	 *
	 * @return string
	 */
	private function checkPhpVersion($actualVersion, $requiredVersion)
	{
		if (version_compare($actualVersion, $requiredVersion, '<')) {
			return sprintf(__('%s - We recommend a minimum PHP version of %s. See: <a href="%s" target="_blank">How to update your PHP version</a>', 'jigoshop'), esc_html($actualVersion), $requiredVersion, 'http://docs.woothemes.com/document/how-to-update-your-php-version/');
		} else {
			return esc_html($actualVersion);
		}
	}

	/**
	 *
	 *
	 * @param string $v
	 *
	 * @return int
	 */
	private function letterToNumber($v)
	{
		$l = substr($v, -1);
		$ret = substr($v, 0, -1);
		switch (strtoupper($l)) {
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
			case 'm':
				$ret *= 1024;
			case 'K':
			case 'k':
				$ret *= 1024;
		}

		return $ret;
	}

	/**
	 * Returns single value from php configuration
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	private function iniGet($field)
	{
		if (function_exists('ini_get')) {
			$value = ini_get($field);

			return $value === false ? '' : $value;
		}

		return '';
	}

	/**
	 * Checks availability wp_remote_post or wp_remote_get
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	private function checkRemoteRequest($type)
	{
		switch ($type) {
			case 'post':
				$response = $this->wp->wpRemotePost('http://wordpress.org', array());

				if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300) {
					return true;
				} else {
					return false;
				}
			case 'get':
				$response = $this->wp->wpRemoteGet('http://wordpress.org');
				if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300) {
					return true;
				} else {
					return false;
				}
		}

		return false;
	}

	/**
	 * Returns single value from associative array containing localized numeric and monetary formatting information.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	private function getServerLocale($type)
	{
		if (empty($this->serverLocale)) {
			$this->serverLocale = localeconv();
		}

		return $this->serverLocale[$type];
	}

	/**
	 * Returns fields with active plugin data.
	 *
	 * @return array
	 */
	private function getActivePlugins()
	{
		$activePlugins = (array)$this->wp->getOption('active_plugins', array());

		if (is_multisite()) {
			$activePlugins = array_merge($activePlugins, $this->wp->getSiteOption('active_sitewide_plugins', array()));
		}

		$fields = array();
		foreach ($activePlugins as $plugin) {
			$pluginData = @get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin);
			$versionString = '';
			$networkString = '';

			if (!empty($pluginData['Name'])) {
				// link the plugin name to the plugin url if available
				$pluginName = esc_html($pluginData['Name']);

				if (!empty($pluginData['PluginURI'])) {
					$pluginName = '<a href="'.esc_url($pluginData['PluginURI']).'" title="'.__('Visit plugin homepage', 'jigoshop').'" target="_blank">'.$pluginName.'</a>';
				}

				$fields[] = array(
					'id' => $plugin,
					'name' => $plugin,
					'title' => $pluginName,
					'tip' => '',
					'type' => 'constant',
					'value' => sprintf(_x('by %s', 'by author', 'jigoshop'), $pluginData['Author']).' &ndash; '.esc_html($pluginData['Version']).$versionString.$networkString,
				);
			}
		}

		return $fields;
	}

	/**
	 * Returns formatted currency position
	 *
	 * @return string
	 */
	private function getCurrencyPosition()
	{
		$pattern = $this->options->get('general.currency_position');
		$positions = Currency::positions();

		return $positions[$pattern];
	}

	/**
	 * Return fields with all template overrides.
	 *
	 * @return array
	 */
	private function getOverrides()
	{
		$templatePaths = $this->wp->applyFilters('jigoshop\admin\system_info\system_status\overrides_scan_paths', array('jigoshop' => JIGOSHOP_DIR.'/templates/'));
		$scannedFiles = array();
		$foundFiles = array();

		foreach ($templatePaths as $pluginName => $templatePath) {
			$scannedFiles[$pluginName] = $this->scanTemplateFiles($templatePath);
		}

		foreach ($scannedFiles as $pluginName => $files) {
			foreach ($files as $file) {
				$themeFile = $this->getTemplateFile($file);

				if (!empty($themeFile)) {
					$coreVersion = $this->getFileVersion(JIGOSHOP_DIR.'/templates/'.$file);
					$themeVersion = $this->getFileVersion($themeFile);

					if ($coreVersion && (empty($themeVersion) || version_compare($themeVersion, $coreVersion, '<'))) {
						$foundFiles[$pluginName][] = sprintf(__('<code>%s</code> version %s is out of date.', 'jigoshop'), str_replace(WP_CONTENT_DIR.'/themes/', '', $themeFile), $themeVersion ? $themeVersion : '-');
					} else {
						$foundFiles[$pluginName][] = sprintf('<code>%s</code>', str_replace(WP_CONTENT_DIR.'/themes/', '', $themeFile));
					}
				}
			}
		}

		$fields = array();
		if ($foundFiles) {
			foreach ($foundFiles as $pluginName => $foundPluginFiles) {
				$fields[] = array(
					'id' => strtolower($pluginName),
					'name' => strtolower($pluginName),
					'title' => sprintf(__('%s Overrides', 'jigoshop'), $pluginName),
					'tip' => '',
					'type' => 'constant',
					'value' => implode(', <br/>', $foundPluginFiles)
				);
			}
		} else {
			$fields[] = array(
				'id' => 'no_overrides',
				'name' => 'no_overrides',
				'title' => __('No Overrides', 'jigoshop'),
				'tip' => '',
				'type' => 'constant',
				'value' => ''
			);
		}

		return $fields;
	}

	/**
	 * Returns array of all plugin templates.
	 *
	 * @param string $templatePath
	 *
	 * @return array
	 */
	private function scanTemplateFiles($templatePath)
	{
		$files = scandir($templatePath);
		$result = array();
		if ($files) {
			foreach ($files as $key => $value) {
				if (!in_array($value, array(".", ".."))) {
					if (is_dir($templatePath.DIRECTORY_SEPARATOR.$value)) {
						$subFiles = $this->scanTemplateFiles($templatePath.DIRECTORY_SEPARATOR.$value);
						foreach ($subFiles as $subFile) {
							$result[] = $value.DIRECTORY_SEPARATOR.$subFile;
						}
					} else {
						$result[] = $value;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Returns version of file.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	private function getFileVersion($file)
	{
		// Avoid notices if file does not exist
		if (!file_exists($file)) {
			return '';
		}
		// We don't need to write to the file, so just open for reading.
		$fp = fopen($file, 'r');
		// Pull only the first 8kiB of the file in.
		$fileData = fread($fp, 8192);
		// PHP will close file handle, but we are good citizens.
		fclose($fp);
		// Make sure we catch CR-only line endings.
		$fileData = str_replace("\r", "\n", $fileData);
		$version = '';
		if (preg_match('/^[ \t\/*#@]*'.preg_quote('@version', '/').'(.*)$/mi', $fileData, $match) && $match[1]) {
			$version = _cleanup_header_comment($match[1]);
		}

		return $version;
	}

	/**
	 * Returns a full path to file
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	private function getTemplateFile($file)
	{
		if(file_exists(get_stylesheet_directory().'/'.$file)) {
			return get_stylesheet_directory().'/'.$file;
		} elseif (file_exists(get_stylesheet_directory().'/jigoshop/'.$file)) {
			return get_stylesheet_directory().'/jigoshop/'.$file;
		} elseif (file_exists(get_template_directory().'/'.$file)) {
			return get_template_directory().'/'.$file;
		} elseif (file_exists(get_template_directory().'/jigoshop/'.$file)) {
			return get_template_directory().'/jigoshop/'.$file;
		}

		return '';
	}
}