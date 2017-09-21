<?php

namespace Jigoshop;

use Jigoshop\Admin\Dashboard;
use Jigoshop\Admin\DashboardInterface;
use Jigoshop\Admin\PageInterface;
use Jigoshop\Admin\Permalinks;
use Jigoshop\Admin\Settings;
use Jigoshop\Core\Types;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Monolog\Registry;
use WPAL\Wordpress;

/**
 * Class for handling administration panel.
 *
 * @package Jigoshop
 * @author  Amadeusz Starzykiewicz
 */
class Admin
{
	const MENU = 'jigoshop';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var array */
	private $pages = [
		'jigoshop' => [],
		'products' => [],
		'orders' => [],
    ];
	private $dashboard;
	/** @var  DashboardInterface[] */
	private $dashboards = [];

	public function __construct(Wordpress $wp, Dashboard $dashboard, Permalinks $permalinks)
	{
		$this->wp = $wp;
		$this->dashboard = $dashboard;

        if ( get_transient( 'jigoshop_activation_redirect' ) ) {
            delete_transient('jigoshop_activation_redirect');

            wp_redirect(admin_url('admin.php?page=' . Admin\Setup::SLUG));
            exit;
        }

		$wp->addAction('admin_menu', [$this, 'beforeMenu'], 9);
		$wp->addAction('admin_menu', [$this, 'afterMenu'], 50);
        $wp->addAction('admin_menu', [$this, 'initDashboards']);

		//TODO do wyrzucenia, przeniesienia do osobnych widokow
		Scripts::add('jigoshop.vendors.bs_tab_trans_tooltip_collapse', \JigoshopInit::getUrl().'/assets/js/vendors/bs_tab_trans_tooltip_collapse.js', ['jquery']);
		Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css');
		Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/js/vendors/select2.js', ['jquery']);


		Styles::add('jigoshop.admin', \JigoshopInit::getUrl() . '/assets/css/admin.css');

		Scripts::add('jigoshop.admin', \JigoshopInit::getUrl().'/assets/js/admin.js', [
			'jquery',
			'jigoshop.helpers',
			'jigoshop.vendors.bs_tab_trans_tooltip_collapse'
        ]);
		Scripts::add('jigoshop.vendors.bs_tab_trans_tooltip_collapse', \JigoshopInit::getUrl() . '/assets/js/vendors/bs_tab_trans_tooltip_collapse.js', [
			'jquery',
        ], ['in_footer' => true]);
	}

	/**
	 * Adds new page to Jigoshop admin panel.
	 * Available parents:
	 *   * jigoshop - main Jigoshop menu,
	 *   * products - Jigoshop products menu
	 *   * orders - Jigoshop orders menu
	 *
	 * @param $page PageInterface Page to add.
	 *
	 * @throws Exception When trying to add page not in Jigoshop menus.
	 */
	public function addPage(PageInterface $page)
	{
		$parent = $page->getParent();
		if (!isset($this->pages[$parent])) {
			if (WP_DEBUG) {
				throw new Exception(sprintf('Trying to add page to invalid parent (%s). Available ones are: %s', $parent, join(', ', array_keys($this->pages))));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addDebug(sprintf('Trying to add page to invalid parent (%s).', $parent), ['parents' => $this->pages]);

			return;
		}

		$this->pages[$parent][] = $page;
	}

	/**
	 * Adds Jigoshop menus.
	 */
	public function beforeMenu()
	{
		$menu = $this->wp->getMenu();

		if ($this->wp->currentUserCan('manage_jigoshop')) {
			$menu[54] = ['', 'read', 'separator-jigoshop', '', 'wp-menu-separator jigoshop'];
		}

        $this->wp->doAction('jigoshop\admin\before_menu');

		$this->wp->addMenuPage(__('Jigoshop'), __('Jigoshop'), 'manage_jigoshop', 'jigoshop', [$this->dashboard, 'display'], null, 55);
		foreach ($this->pages['jigoshop'] as $page) {
			/** @var $page PageInterface */
			$this->wp->addSubmenuPage(self::MENU, $page->getTitle(), $page->getTitle(), $page->getCapability(), $page->getMenuSlug(), [$page, 'display']);
		}

		foreach ($this->pages['products'] as $page) {
			/** @var $page PageInterface */
			$this->wp->addSubmenuPage('edit.php?post_type='.Types::PRODUCT, $page->getTitle(), $page->getTitle(), $page->getCapability(), $page->getMenuSlug(), [
				$page,
				'display'
            ]);
		}

		foreach ($this->pages['orders'] as $page) {
			/** @var $page PageInterface */
			$this->wp->addSubmenuPage('edit.php?post_type='.Types::ORDER, $page->getTitle(), $page->getTitle(), $page->getCapability(), $page->getMenuSlug(), [
				$page,
				'display'
            ]);
		}
	}

	/**
	 * Adds Jigoshop settings and system information menus (at the end of Jigoshop sub-menu).
	 */
	public function afterMenu()
	{
//		$this->wp->addSubmenuPage(self::MENU, $this->settings->getTitle(), $this->settings->getTitle(), $this->settings->getCapability(),
//			$this->settings->getMenuSlug(), array($this->settings, 'display'));
//		$this->wp->addSubmenuPage(self::MENU, $this->systemInfo->getTitle(), $this->systemInfo->getTitle(), $this->systemInfo->getCapability(),
//			$this->systemInfo->getMenuSlug(), array($this->systemInfo, 'display'));

		$this->wp->doAction('jigoshop\admin\after_menu');
	}

    /**
     * @return DashboardInterface[]
     */
    public function getDashboards()
    {
        return $this->dashboards;
    }

    /**
     * @param DashboardInterface $dashboard
     */
    public function addDashboard($dashboard)
    {
        $this->dashboards[] = $dashboard;
    }

    public function initDashboards()
    {
        $di = Integration::getContainer();
        $dashboards = $di->tags->get('jigoshop.admin.dashboard');
        foreach ($dashboards as $dashboard) {
            $class = $di->getServices()->getClassName($dashboard);
            if(isset($_GET['page']) && $class::SLUG == $_GET['page']) {
                $dashboard = $di->get($dashboard);
                add_dashboard_page($dashboard->getTitle(), $dashboard->getTitle(), $dashboard->getCapability(), $dashboard->getMenuSlug());
            }
        }
    }
}
