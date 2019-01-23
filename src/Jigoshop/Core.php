<?php

namespace Jigoshop;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Template;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Helper\Tax;
use WPAL\Wordpress;

class Core
{
	const VERSION = '2.1.18';
	const WIDGET_CACHE = 'jigoshop_widget_cache';
	const TERMS = 'jigoshop_term';

	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Core\Messages */
	private $messages;
	/** @var \Jigoshop\Core\Template */
	private $template;
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var array */
	private $widgets;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, Template $template, $widgets = [])
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->template = $template;
		$this->widgets = $widgets;

		// Register main Jigoshop scripts
		$wp->wpEnqueueScript('jquery');
		Styles::register('prettyphoto', \JigoshopInit::getUrl().'/assets/css/prettyPhoto.css');
		Styles::register('tokenfield', \JigoshopInit::getUrl().'/assets/css/vendors/tokenfield.css');
		Styles::register('impromptu', \JigoshopInit::getUrl().'/assets/css/vendors/impromptu.css');
		Scripts::register('jigoshop.helpers', \JigoshopInit::getUrl().'/assets/js/helpers.js', ['jquery']);
		Scripts::register('jigoshop.helpers.ajax_search', \JigoshopInit::getUrl().'/assets/js/helpers/ajax_search.js', ['jigoshop.helpers']);
		Scripts::register('jigoshop.helpers.payment', \JigoshopInit::getUrl().'/assets/js/helpers/payment.js', ['jigoshop.helpers', 'jquery-blockui']);
		Scripts::register('jigoshop.api', \JigoshopInit::getUrl().'/assets/js/api.js', ['jigoshop.helpers']);
		Scripts::register('jigoshop.media', \JigoshopInit::getUrl().'/assets/js/media.js', ['jquery']);
		Scripts::register('jigoshop.shop', \JigoshopInit::getUrl().'/assets/js/shop.js', [
			'jquery',
			'jigoshop.helpers'
        ]);
		Scripts::register('jquery-blockui', '//cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.66.0-2013.10.09/jquery.blockUI.min.js', ['jquery']);
		Scripts::register('prettyphoto', \JigoshopInit::getUrl().'/assets/js/jquery.prettyPhoto.js');
		Scripts::register('tokenfield', \JigoshopInit::getUrl().'/assets/js/vendors/tokenfield.js', ['jquery']);
		Scripts::register('impromptu', \JigoshopInit::getUrl().'/assets/js/vendors/impromptu.js', ['jquery']);
		Scripts::localize('jigoshop.helpers', 'jigoshop_helpers', [
			'assets' => \JigoshopInit::getUrl().'/assets',
			'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
	}

	/**
	 * Starts Jigoshop extensions and Jigoshop itself.
	 *
	 * @param Container $container
	 */
	public function run(Container $container)
	{
		$wp = $this->wp;

		// Add table to benefit from WordPress metadata API
		$wpdb = $wp->getWPDB();
		/** @noinspection PhpUndefinedFieldInspection */
		$wpdb->jigoshop_termmeta = "{$wpdb->prefix}jigoshop_term_meta";

		$wp->addFilter('template_include', [$this->template, 'process']);
		$wp->addFilter('template_redirect', [$this->template, 'redirect']);
		$wp->addFilter('jigoshop\get_fields', function ($fields){
			// Post type
			if (isset($_GET['post_type'])) {
				$fields['post_type'] = $_GET['post_type'];
			}

			return $fields;
		});
		$wp->addAction('jigoshop\shop\content\before', [$this, 'displayCustomMessage']);
		$wp->addAction('wp_head', [$this, 'googleAnalyticsTracking'], 9990);
		// Action for limiting WordPress feed from using order notes.
		$wp->addAction('comment_feed_where', function ($where){
			return $where." AND comment_type <> 'order_note'";
		});

		$container->get('jigoshop.permalinks');

        /** @var \Jigoshop\Service\TaxServiceInterface $tax */
        $tax = $container->get('jigoshop.service.tax');
        $tax->register();
        Tax::setTaxService($tax);

		/** @var \Jigoshop\Endpoint $api */
		$endpoint = $container->get('jigoshop.endpoint');
		$endpoint->run();

		if($this->options->get('advanced.api.enable', false)) {
			/** @var \Jigoshop\Api $api */
			$api = $container->get('jigoshop.api');
			$api->run();
		}
		if(defined('DOING_AJAX') && DOING_AJAX) {
		    /** @var Ajax $ajax */
		    $ajax = $container->get('jigoshop.ajax');
		    $ajax->run();
        }

		$container->get('jigoshop.emails');

		$widget = $container->get('jigoshop.widget');
		$widget->init($container, $wp);

		// TODO: Why this is required? :/
		//$this->wp->flushRewriteRules(false);
		$this->wp->doAction('jigoshop\run', $container);
	}

	/**
	 * Displays Google Analytics tracking code in the header as the LAST item before closing </head> tag
	 */
	public function googleAnalyticsTracking()
	{
		// Do not track admin pages
		if ($this->wp->isAdmin()) {
			return;
		}

		// Do not track shop owners
		if ($this->wp->currentUserCan('manage_jigoshop')) {
			return;
		}

		$trackingId = $this->options->get('advanced.integration.google_analytics');

		if (empty($trackingId)) {
			return;
		}

		$userId = '';
		if ($this->wp->isUserLoggedIn()) {
			$userId = $this->wp->getCurrentUserId();
		}
		?>
		<script type="text/javascript">
			(function(i, s, o, g, r, a, m) {
				i['GoogleAnalyticsObject'] = r;
				i[r] = i[r] || function() {
						(i[r].q = i[r].q || []).push(arguments)
					}, i[r].l = 1 * new Date();
				a = s.createElement(o),
					m = s.getElementsByTagName(o)[0];
				a.async = 1;
				a.src = g;
				m.parentNode.insertBefore(a, m)
			})(window, document, 'script', '//www.google-analytics.com/analytics.js', 'jigoshopGoogleAnalytics');
			jigoshopGoogleAnalytics('create', '<?php echo $trackingId; ?>', {'userId': '<?php echo $userId; ?>'});
			jigoshopGoogleAnalytics('send', 'pageview');
		</script>
		<?php
	}

	/**
	 * Adds a custom store banner to the site.
	 */
	public function displayCustomMessage()
	{
		if ($this->options->get('general.show_message') && Frontend\Pages::isJigoshop()) {
			Render::output('shop/custom_message', [
				'message' => $this->options->get('general.message'),
            ]);
		}

		if ($this->options->get('general.demo_store') && Frontend\Pages::isJigoshop()) {
			Render::output('shop/custom_message', [
				'message' => __('This is a demo store for testing purposes &mdash; no orders shall be fulfilled.', 'jigoshop-ecommerce'),
            ]);
		}
	}
}
