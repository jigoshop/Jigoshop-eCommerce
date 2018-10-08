<?php
namespace Jigoshop\Admin\SystemInfo;

use Jigoshop\Admin\Settings\TabInterface;
use Jigoshop\Admin\Settings\ValidationException;
use Jigoshop\Admin\SystemInfo;
use Jigoshop\Core;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

class SystemStatusTab implements TabInterface
{
	const SLUG = 'system_status';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var array */
	private $serverLocale = [];
	/** @var  @var array  */
	private $sections = [];

	private $yes = '&#10004;';
	private $no = '&#10005;';

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), ['admin.php'])) {
				return;
			}

			if (isset($_REQUEST['tab']) && $_REQUEST['tab'] != self::SLUG){
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, ['jigoshop_page_'.SystemInfo::NAME])) {
				return;
			}

			Scripts::add('jigoshop.admin.system_info.system_status', \JigoshopInit::getUrl().'/assets/js/admin/system_info/system_status.js', ['jquery']);
			Scripts::localize('jigoshop.admin.system_info.system_status', 'system_data', $this->getSystemData());
		});
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('System Status', 'jigoshop-ecommerce');
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
			$this->sections = [
				[
					'title' => __('Get System Information', 'jigoshop-ecommerce'),
					'id' => 'get-system-information',
					'description' => __('Please copy and paste this information in your ticket when contacting support', 'jigoshop-ecommerce'),
					'fields' => [
						[
							'id' => 'debug_report',
							'title' => '',
							'generate_button_id' => 'generate-report',
							'debug_textarea_id' => 'report-for-support',
							'generate_button_label' => __('Generate Report', 'jigoshop-ecommerce'),
							'type' => 'user_defined',
							'display' => function($field){
								return Render::output('admin/system_info/debug_report', $field);
							}
                        ],
                    ]
                ],
				[
					'title' => __('WordPress Environment', 'jigoshop-ecommerce'),
					'id' => 'wordpress-environment',
					'fields' => [
						[
							'id' => 'home-url',
							'name' => 'home-url',
							'title' => __('Home URL', 'jigoshop-ecommerce'),
							'tip' => __('The URL of your site\'s homepage.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => home_url(),
                        ],
						[
							'id' => 'site-url',
							'name' => 'site-url',
							'title' => __('Site URL', 'jigoshop-ecommerce'),
							'tip' => __('The root URL of your site.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => site_url(),
                        ],
						[
							'id' => 'jigoshop-version',
							'name' => 'jigoshop-version',
							'title' => __('Jigoshop Version', 'jigoshop-ecommerce'),
							'tip' => __('The version of Jigoshop installed on your site.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => Core::VERSION,
                        ],
						[
							'id' => 'jigoshop-database-version',
							'name' => 'jigoshop-database-version',
							'title' => __('Jigoshop Database Version', 'jigoshop-ecommerce'),
							'tip' => __('The version of jigoshop that the database is formatted for. This should be the same as your jigoshop Version.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->wp->getOption('jigoshop-database-version'),
                        ],
						[
							'id' => 'log-directory-writable',
							'name' => 'log-directory-writable',
							'title' => __('Log Directory Writable', 'jigoshop-ecommerce'),
							'tip' => __('Several Jigoshop extensions can write logs which makes debugging problems easier. The directory must be writable for this to happen.', 'jigoshop-ecommerce'),
							'description' => sprintf(__('To allow logging, make <code>%s</code> writable or define a custom <code>JIGOSHOP_LOG_DIR</code>.', 'jigoshop-ecommerce'), JIGOSHOP_LOG_DIR),
							'type' => 'constant',
							'value' => $this->checkLogDirectory() ? $this->yes : $this->no,
                        ],
						[
							'id' => 'wp-version',
							'name' => 'wp-version',
							'title' => __('WP Version', 'jigoshop-ecommerce'),
							'tip' => __('The version of WordPress installed on your site.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => get_bloginfo('version'),
                        ],
						[
							'id' => 'wp-multisite',
							'name' => 'wp-multisite',
							'title' => __('WP Multisite', 'jigoshop-ecommerce'),
							'tip' => __('Whether or not you have WordPress Multisite enabled.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => is_multisite() ? $this->yes : $this->no,
                        ],
						[
							'id' => 'wp-memory-limit',
							'name' => 'wp-memory-limit',
							'title' => __('WP Memory Limit', 'jigoshop-ecommerce'),
							'tip' => __('The maximum amount of memory (RAM) that your site can use at one time.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->checkMemoryLimit(WP_MEMORY_LIMIT, JIGOSHOP_REQUIRED_WP_MEMORY),
                        ],
						[
							'id' => 'wp-debug-mode',
							'name' => 'wp-debug-mode',
							'title' => __('WP Debug Mode', 'jigoshop-ecommerce'),
							'tip' => __('Displays whether or not WordPress is in Debug Mode.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => defined('WP_DEBUG') && WP_DEBUG ? $this->yes : $this->no,
                        ],
						[
							'id' => 'language',
							'name' => 'language',
							'title' => __('Language', 'jigoshop-ecommerce'),
							'tip' => __('The current language used by WordPress. Default = English.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => get_locale(),
                        ],
                    ],
                ],
				[
					'title' => __('Server Environment', 'jigoshop-ecommerce'),
					'id' => 'srever-environment',
					'fields' => [
						[
							'id' => 'server-info',
							'name' => 'server-info',
							'title' => __('Server Info', 'jigoshop-ecommerce'),
							'tip' => __('Information about the web server that is currently hosting your site.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => esc_html($_SERVER['SERVER_SOFTWARE']),
                        ],
						[
							'id' => 'php-version',
							'name' => 'php-version',
							'title' => __('PHP Version', 'jigoshop-ecommerce'),
							'tip' => __('The version of PHP installed on your hosting server.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->checkPhpVersion(PHP_VERSION, JIGOSHOP_PHP_VERSION),
                        ],
						[
							'id' => 'php-post-max-size',
							'name' => 'php-post-max-size',
							'title' => __('PHP Post Max Size', 'jigoshop-ecommerce'),
							'tip' => __('The largest filesize that can be contained in one post.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => size_format($this->letterToNumber($this->iniGet('post_max_size'))),
                        ],
						[
							'id' => 'php-time-limit',
							'name' => 'php-time-limit',
							'title' => __('PHP Time Limit', 'jigoshop-ecommerce'),
							'tip' => __('The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups).', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => size_format($this->letterToNumber($this->iniGet('post_max_size'))),
                        ],
						[
							'id' => 'php-time-limit',
							'name' => 'php-time-limit',
							'title' => __('PHP Max Input Vars', 'jigoshop-ecommerce'),
							'tip' => __('The maximum number of variables your server can use for a single function to avoid overloads.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->iniGet('max_input_vars'),
                        ],
						[
							'id' => 'suhosin-installed',
							'name' => 'suhosin-installed',
							'title' => __('SUHOSIN Installed', 'jigoshop-ecommerce'),
							'tip' => __('Suhosin is an advanced protection system for PHP installations. It was designed to protect your servers on the one hand against a number of well known problems in PHP applications and on the other hand against potential unknown vulnerabilities within these applications or the PHP core itself. If enabled on your server, Suhosin may need to be configured to increase its data submission limits.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => extension_loaded('suhosin') ? $this->yes : $this->no,
                        ],
						[
							'id' => 'eaccelerator',
							'name' => 'eaccelerator',
							'title' => __('eAccelerator', 'jigoshop-ecommerce'),
							'classes' => ['system-data'],
							'tip' => __('eAccelerator is deprecated and causes problems with Jigoshop.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->iniGet('eaccelerator.enable') == 1 ? $this->yes : $this->no,
                        ],
						[
							'id' => 'apc',
							'name' => 'apc',
							'title' => __('APC', 'jigoshop-ecommerce'),
							'tip' => __('APC is deprecated and causes problems with Jigoshop.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->iniGet('apc.enable') == 1 ? $this->yes : $this->no,
                        ],
						[
							'id' => 'apc',
							'name' => 'apc',
							'title' => __('OpCache', 'jigoshop-ecommerce'),
							'tip' => __('OpCache is new PHP optimizer and it is recommended to use with Jigoshop.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->iniGet('opcache.enable') == 1 ? $this->yes : $this->no,
                        ],
						[
							'id' => 'short-open-tag',
							'name' => 'short-open-tag',
							'title' => __('Short Open Tag', 'jigoshop-ecommerce'),
							'tip' => __('Whether short tags are enabled, they are used by some older extensions.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->iniGet('short-open-tag') != '' ? $this->yes : $this->no,
                        ],
						[
							'id' => 'allow-url-fopen',
							'name' => 'allow-url-fopen',
							'title' => __('Allow URL fopen', 'jigoshop-ecommerce'),
							'tip' => __('Whether fetching remote files is allowed. This option is used by many Jigoshop extensions.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->iniGet('allow_url_fopen') != '' ? $this->yes : $this->no,
                        ],
						[
							'id' => 'session',
							'name' => 'session',
							'title' => __('Session', 'jigoshop-ecommerce'),
							'tip' => __('Whether PHP sessions are working properly.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => (session_id() != null && isset($_SESSION)) ? $this->yes : $this->no,
                        ],
						[
							'id' => 'cookie-path',
							'name' => 'cookie-path',
							'title' => __('Cookie Path', 'jigoshop-ecommerce'),
							'tip' => __('Path for which cookies are saved. This is important for sessions and session security.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->iniGet('session.cookie_path'),
                        ],
						[
							'id' => 'save-path',
							'name' => 'save-path',
							'title' => __('Save Path', 'jigoshop-ecommerce'),
							'tip' => __('Path where sessions are stored on the server. This is sometimes cause of login/logout problems.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => esc_html($this->iniGet('session.save_path')),
                        ],
						[
							'id' => 'use-cookies',
							'name' => 'use-cookies',
							'title' => __('Use Cookies', 'jigoshop-ecommerce'),
							'tip' => __('Whether cookies are used to store PHP session on user\'s computer. Recommended.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->iniGet('session.use_cookies') != '' ? $this->yes : $this->no,
                        ],
						[
							'id' => 'use-only-cookies',
							'name' => 'use-only-cookies',
							'title' => __('Use Only Cookies', 'jigoshop-ecommerce'),
							'tip' => __('Whether PHP uses only cookies to handle user sessions. This is important for security reasons.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->iniGet('session.use_only_cookies') != '' ? $this->yes : $this->no,
                        ],
						[
							'id' => 'max-upload-size',
							'name' => 'max-upload-size',
							'title' => __('Max Upload Size', 'jigoshop-ecommerce'),
							'tip' => __('The largest filesize that can be uploaded to your WordPress installation.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => size_format(wp_max_upload_size()),
                        ],
						[
							'id' => 'default-timezone',
							'name' => 'default-timezone',
							'title' => __('Default Timezone', 'jigoshop-ecommerce'),
							'tip' => __('The default timezone for your server. We recommend to set it as UTC.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => date_default_timezone_get(),
                        ],
						[
							'id' => 'fsockopen-curl',
							'name' => 'fsockopen-curl',
							'title' => __('fsockopen/cURL', 'jigoshop-ecommerce'),
							'tip' => __('Payment gateways can use cURL to communicate with remote servers to authorize payments, other plugins may also use it when communicating with remote services.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => function_exists('fsockopen') || function_exists('curl_init') ? $this->yes : $this->no,
                        ],
						[
							'id' => 'soap-client',
							'name' => 'soap-client',
							'title' => __('SoapClient', 'jigoshop-ecommerce'),
							'tip' => __('Some webservices like shipping use SOAP to get information from remote servers, for example, live shipping quotes from FedEx require SOAP to be installed.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => class_exists('SoapClient') ? $this->yes : $this->no,
                        ],
						[
							'id' => 'wp-remote-post',
							'name' => 'wp-remote-post',
							'title' => __('Remote Post', 'jigoshop-ecommerce'),
							'tip' => __('PayPal uses this method of communicating when sending back transaction information.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->checkRemoteRequest('post') ? $this->yes : $this->no,
                        ],
						[
							'id' => 'wp-remote-get',
							'name' => 'wp-remote-get',
							'title' => __('Remote Get', 'jigoshop-ecommerce'),
							'tip' => __('PayJigoshop plugins may use this method of communication when checking for plugin updates.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->checkRemoteRequest('get') ? $this->yes : $this->no,
                        ],
                    ],
                ],
				[
					'title' => __('Server Locale', 'jigoshop-ecommerce'),
					'id' => 'srever-locale',
					'fields' => [
						[
							'id' => 'decimal-point',
							'name' => 'decimal-point',
							'title' => __('Decimal Point', 'jigoshop-ecommerce'),
							'tip' => __('The character used for decimal points.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->getServerLocale('decimal_point') ? $this->getServerLocale('decimal_point') : $this->no,
                        ],
						[
							'id' => 'thousands-sep',
							'name' => 'thousands-sep',
							'title' => __('Thousands Separator', 'jigoshop-ecommerce'),
							'tip' => __('The character used for a thousands separator.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->getServerLocale('thousands_sep') ? $this->getServerLocale('thousands_sep') : $this->no,
                        ],
						[
							'id' => 'mon-decimal-point',
							'name' => 'mon-decimal-point',
							'title' => __('Monetary Decimal Point', 'jigoshop-ecommerce'),
							'tip' => __('The character used for decimal points in monetary values.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->getServerLocale('mon_decimal_point') ? $this->getServerLocale('mon_decimal_point') : $this->no,
                        ],
						[
							'id' => 'mon-thousands-sep',
							'name' => 'mon-thousands-sep',
							'title' => __('Monetary Thousands Separator', 'jigoshop-ecommerce'),
							'tip' => __('The character used for a thousands separator in monetary values.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->getServerLocale('mon_thousands_sep') ? $this->getServerLocale('mon_thousands_sep') : $this->no,
                        ],
                    ],
                ],
				[
					'title' => sprintf(__('Active Plugins (%s)', 'jigoshop-ecommerce'), count((array)$this->wp->getOption('active_plugins'))),
					'id' => 'active-plugins',
					'fields' => $this->getActivePlugins()
                ],
				[
					'title' => __('Settings', 'jigoshop-ecommerce'),
					'id' => 'settings',
					'fields' => [
						[
							'id' => 'force-ssl',
							'name' => 'force-ssl',
							'title' => __('Force SSL', 'jigoshop-ecommerce'),
							'tip' => __('Does your site force a SSL Certificate for transactions?', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->options->get('shopping.force_ssl') ? $this->yes : $this->no,
                        ],
						[
							'id' => 'shipping-enabled',
							'name' => 'shipping-enabled',
							'title' => __('Shipping Enabled', 'jigoshop-ecommerce'),
							'tip' => __('Does your site have shipping enabled?', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->options->get('shipping.enabled') ? $this->yes : $this->no,
                        ],
						[
							'id' => 'currency',
							'name' => 'currency',
							'title' => __('Shipping Enabled', 'jigoshop-ecommerce'),
							'tip' => __('What currency prices are listed at in the catalog and which currency gateways will take payments in?', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => Currency::code().'('.Currency::symbol().')',
                        ],
						[
							'id' => 'currency-position',
							'name' => 'currency-position',
							'title' => __('Currency Position', 'jigoshop-ecommerce'),
							'tip' => __('The position of the currency symbol.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->getCurrencyPosition(),
                        ],
						[
							'id' => 'thousand-separator',
							'name' => 'thousand-separator',
							'title' => __('Thousand Separator', 'jigoshop-ecommerce'),
							'tip' => __('The thousand separator of displayed prices.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->options->get('general.currency_thousand_separator'),
                        ],
						[
							'id' => 'decimal-separator',
							'name' => 'decimal-separator',
							'title' => __('Decimal Separator', 'jigoshop-ecommerce'),
							'tip' => __('The decimal separator of displayed prices.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->options->get('general.currency_decimal_separator'),
                        ],
						[
							'id' => 'number-of-decimals',
							'name' => 'number-of-decimals',
							'title' => __('Number of Decimals', 'jigoshop-ecommerce'),
							'tip' => __('The number of decimal points shown in displayed prices.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->options->get('general.currency_decimals'),
                        ],
                    ]
                ],
				[
					'title' => __('Jigoshop Pages', 'jigoshop-ecommerce'),
					'id' => 'jigoshop-pages',
					'fields' => [
						[
							'id' => 'shop-base',
							'name' => 'shop-base',
							'title' => __('Shop Base', 'jigoshop-ecommerce'),
							'tip' => __('The ID of your Jigoshop shop\'s homepage.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->options->get('advanced.pages.shop') ? '#'.$this->options->get('advanced.pages.shop') : $this->no,
                        ],
						[
							'id' => 'cart',
							'name' => 'cart',
							'title' => __('Cart', 'jigoshop-ecommerce'),
							'tip' => __('The ID of your Jigoshop shop\'s cart page.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->options->get('advanced.pages.cart') ? '#'.$this->options->get('advanced.pages.cart') : $this->no,
                        ],
						[
							'id' => 'checkout',
							'name' => 'checkout',
							'title' => __('Checkout', 'jigoshop-ecommerce'),
							'tip' => __('The ID of your Jigoshop shop\'s checkout page.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->options->get('advanced.pages.checkout') ? '#'.$this->options->get('advanced.pages.checkout') : $this->no,
                        ],
						[
							'id' => 'thank-you',
							'name' => 'thank-you',
							'title' => __('Thank You', 'jigoshop-ecommerce'),
							'tip' => __('The ID of your Jigoshop shop\'s thank you page.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->options->get('advanced.pages.checkout_thank_you') ? '#'.$this->options->get('advanced.pages.checkout_thank_you') : $this->no,
                        ],
						[
							'id' => 'my-account',
							'name' => 'my-account',
							'title' => __('My account', 'jigoshop-ecommerce'),
							'tip' => __('The ID of your Jigoshop shop\'s my account page.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->options->get('advanced.pages.account') ? '#'.$this->options->get('advanced.pages.account') : $this->no,
                        ],
						[
							'id' => 'terms',
							'name' => 'terms',
							'title' => __('Terms', 'jigoshop-ecommerce'),
							'tip' => __('The ID of your Jigoshop shop\'s terms page.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->options->get('advanced.pages.terms') ? '#'.$this->options->get('advanced.pages.terms') : $this->no,
                        ],
                    ]
                ],
				[
					'title' => __('Theme', 'jigoshop-ecommerce'),
					'id' => 'theme',
					'fields' => [
						[
							'id' => 'name',
							'name' => 'name',
							'title' => __('Name', 'jigoshop-ecommerce'),
							'tip' => __('The name of the current active theme.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->wp->wpGetTheme()->display('Name'),
                        ],
						[
							'id' => 'version',
							'name' => 'version',
							'title' => __('Version', 'jigoshop-ecommerce'),
							'tip' => __('The installed version of the current active theme.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->wp->wpGetTheme()->display('Version')
                        ],
						[
							'id' => 'author-url',
							'name' => 'author-url',
							'title' => __('Author URL', 'jigoshop-ecommerce'),
							'tip' => __('The theme developers URL.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => $this->wp->wpGetTheme()->display('AuthorURI')
                        ],
						[
							'id' => 'child-theme',
							'name' => 'child-theme',
							'title' => __('Child Theme', 'jigoshop-ecommerce'),
							'tip' => __('Displays whether or not the current theme is a child theme', 'jigoshop-ecommerce'),
							'description' => sprintf(__('If you\'re modifying Jigoshop or a parent theme you didn\'t build personally we recommend using a child theme. See: <a href="%s" target="-blank">How to create a child theme</a>', 'jigoshop-ecommerce'), 'https://codex.wordpress.org/Child_Themes'),
							'type' => 'constant',
							'value' => is_child_theme() ? $this->yes : $this->no
                        ],
						[
							'id' => 'parent-theme-name',
							'name' => 'parent-theme-name',
							'title' => __('Parent Theme Name', 'jigoshop-ecommerce'),
							'tip' => __('The name of the parent theme.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => is_child_theme() ? wp_get_theme($this->wp->wpGetTheme()->display('Template'))->display('Name') : $this->no
                        ],
						[
							'id' => 'parent-theme-version',
							'name' => 'parent-theme-version',
							'title' => __('Parent Theme Version', 'jigoshop-ecommerce'),
							'tip' => __('The installed version of the parent theme.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => is_child_theme() ? wp_get_theme($this->wp->wpGetTheme()->display('Template'))->display('Version') : $this->no
                        ],
						[
							'id' => 'parent-theme-author-url',
							'name' => 'parent-theme-author-url',
							'title' => __('Parent Theme Author URL', 'jigoshop-ecommerce'),
							'tip' => __('The parent theme developers URL.', 'jigoshop-ecommerce'),
							'type' => 'constant',
							'value' => is_child_theme() ? wp_get_theme($this->wp->wpGetTheme()->display('Template'))->display('AuthorURI') : $this->no
                        ],
                    ]
                ],
				[
					'title' => __('Templates', 'jigoshop-ecommerce'),
					'id' => 'templates',
					'description' => __('This section shows any files that are overriding the default jigoshop template pages', 'jigoshop-ecommerce'),
					'fields' => $this->getOverrides()
                ],
            ];
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
		$data = [];
		$sections = $this->getSections();
		$ignoredSections = ['get-system-information'];
		foreach($sections as $section){
			if(!in_array($section['id'], $ignoredSections)){
				$data[] = PHP_EOL.'### '.$section['title'].' ###'.PHP_EOL;
				foreach($section['fields'] as $field){
					$data[] = strip_tags($field['title'] .': '.str_replace([$this->yes, $this->no], ['Yes', 'No'], $field['value']));
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
		if (@fopen(JIGOSHOP_LOG_DIR.'/jigoshop.debug.log', 'a')) {
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
			return sprintf(__('%s - We recommend setting memory to at least %dMB. See: <a href="%s" target="_blank">Increasing memory allocated to PHP</a>', 'jigoshop-ecommerce'), size_format($memory_limit), $requiredMemory, 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP');
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
			return sprintf(__('%s - We recommend a minimum PHP version of %s. See: <a href="%s" target="_blank">How to update your PHP version</a>', 'jigoshop-ecommerce'), esc_html($actualVersion), $requiredVersion, 'http://docs.woothemes.com/document/how-to-update-your-php-version/');
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
				$response = $this->wp->wpRemotePost('http://wordpress.org', []);

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
		$activePlugins = (array)$this->wp->getOption('active_plugins', []);

		if (is_multisite()) {
			$activePlugins = array_merge($activePlugins, $this->wp->getSiteOption('active_sitewide_plugins', []));
		}

		$fields = [];
		foreach ($activePlugins as $plugin) {
			$pluginData = @get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin);
			$versionString = '';
			$networkString = '';

			if (!empty($pluginData['Name'])) {
				// link the plugin name to the plugin url if available
				$pluginName = esc_html($pluginData['Name']);

				if (!empty($pluginData['PluginURI'])) {
					$pluginName = '<a href="'.esc_url($pluginData['PluginURI']).'" title="'.__('Visit plugin homepage', 'jigoshop-ecommerce').'" target="_blank">'.$pluginName.'</a>';
				}

				$fields[] = [
					'id' => $plugin,
					'name' => $plugin,
					'title' => $pluginName,
					'tip' => '',
					'type' => 'constant',
					'value' => sprintf(_x('by %s', 'by author', 'jigoshop-ecommerce'), $pluginData['Author']).' &ndash; '.esc_html($pluginData['Version']).$versionString.$networkString,
                ];
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
		$templatePaths = $this->wp->applyFilters('jigoshop\admin\system_info\system_status\overrides_scan_paths', ['jigoshop' => \JigoshopInit::getDir().'/templates/']);
		$scannedFiles = [];
		$foundFiles = [];

		foreach ($templatePaths as $pluginName => $templatePath) {
			$scannedFiles[$pluginName] = $this->scanTemplateFiles($templatePath);
		}

		foreach ($scannedFiles as $pluginName => $files) {
			foreach ($files as $file) {
				$themeFile = $this->getTemplateFile($file);

				if (!empty($themeFile)) {
					$coreVersion = $this->getFileVersion(\JigoshopInit::getDir().'/templates/'.$file);
					$themeVersion = $this->getFileVersion($themeFile);

					if ($coreVersion && (empty($themeVersion) || version_compare($themeVersion, $coreVersion, '<'))) {
						$foundFiles[$pluginName][] = sprintf(__('<code>%s</code> version %s is out of date.', 'jigoshop-ecommerce'), str_replace(WP_CONTENT_DIR.'/themes/', '', $themeFile), $themeVersion ? $themeVersion : '-');
					} else {
						$foundFiles[$pluginName][] = sprintf('<code>%s</code>', str_replace(WP_CONTENT_DIR.'/themes/', '', $themeFile));
					}
				}
			}
		}

		$fields = [];
		if ($foundFiles) {
			foreach ($foundFiles as $pluginName => $foundPluginFiles) {
				$fields[] = [
					'id' => strtolower($pluginName),
					'name' => strtolower($pluginName),
					'title' => sprintf(__('%s Overrides', 'jigoshop-ecommerce'), $pluginName),
					'tip' => '',
					'type' => 'constant',
					'value' => implode(', <br/>', $foundPluginFiles)
                ];
			}
		} else {
			$fields[] = [
				'id' => 'no_overrides',
				'name' => 'no_overrides',
				'title' => __('No Overrides', 'jigoshop-ecommerce'),
				'tip' => '',
				'type' => 'constant',
				'value' => ''
            ];
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
		$result = [];
		if ($files) {
			foreach ($files as $key => $value) {
				if (!in_array($value, [".", ".."])) {
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