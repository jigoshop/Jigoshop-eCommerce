<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin;
use Jigoshop\Admin\Reports\TabInterface;
use Jigoshop\Core\Messages;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

/**
 * Jigoshop reports admin page.
 *
 * @package Jigoshop\Admin
 */
class Reports implements PageInterface
{
	const NAME = 'jigoshop_reports';

	/** @var Wordpress */
	private $wp;
	/** @var Messages */
	private $messages;
	/** @var OrderServiceInterface */
	private $tabs = [];
	/** @var  string */
	private $currentTab;

	public function __construct(Wordpress $wp, Messages $messages)
	{
		$this->wp = $wp;
		$this->messages = $messages;

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), ['admin.php', 'options.php'])) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if ($screen->base != 'jigoshop_page_'.Reports::NAME) {
				return;
			}

			Styles::add('jigoshop.admin.reports', \JigoshopInit::getUrl().'/assets/css/admin/reports.css', ['jigoshop.admin']);
			Styles::add('jigoshop.vendors.datepicker', \JigoshopInit::getUrl().'/assets/css/vendors/datepicker.css', ['jigoshop.admin.reports']);
			Scripts::add('jigoshop.admin.reports', \JigoshopInit::getUrl().'/assets/js/admin/reports.js', ['jigoshop.admin', 'jigoshop.vendors.datepicker']);
			Scripts::add('jigoshop.vendors.datepicker', \JigoshopInit::getUrl().'/assets/js/vendors/datepicker.js', ['jquery']);
			Scripts::add('jigoshop.vendors.bs_tab_trans_tooltip_collapse', \JigoshopInit::getUrl() . '/assets/js/vendors/bs_tab_trans_tooltip_collapse.js', ['jigoshop.admin.reports'], ['in_footer' => true]);
		});
	}

	/**
	 * Adds new tab to reports screen.
	 *
	 * @param TabInterface $tab The tab.
	 */
	public function addTab(TabInterface $tab)
	{
		$this->tabs[$tab->getSlug()] = $tab;
	}


	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return __('Reports', 'jigoshop-ecommerce');
	}

	/** @return string Parent of the page string. */
	public function getParent()
	{
		return Admin::MENU;
	}

	/**
	 * @return string Required capability to view the page.
	 */
	public function getCapability()
	{
		return 'view_jigoshop_reports';
	}

	/**
	 * @return string Menu slug.
	 */
	public function getMenuSlug()
	{
		return self::NAME;
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		Render::output('admin/reports', [
			'tabs' => $this->tabs,
			'current_tab' => $this->getCurrentTab(),
			'messages' => $this->messages
        ]);
	}

	/**
	 * @return string Slug of currently displayed tab
	 */
	protected function getCurrentTab()
	{
		if ($this->currentTab === null) {
			$this->currentTab = Admin\Reports\SalesTab::SLUG;
			// Use REQUEST to work with both GET and POST parameters
			if (isset($_REQUEST['tab']) && in_array($_REQUEST['tab'], array_keys($this->tabs))) {
				$this->currentTab = $_REQUEST['tab'];
			}
		}

		return $this->currentTab;
	}
}
