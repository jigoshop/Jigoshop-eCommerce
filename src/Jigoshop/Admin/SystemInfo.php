<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin;
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Admin\Settings\TabInterface;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Styles;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

/**
 * Jigoshop system info page.
 *
 * @package Jigoshop\Admin
 * @author  Amadeusz Starzykiewicz
 */
class SystemInfo implements PageInterface
{
	const NAME = 'system_information';

	/** @var \WPAL\Wordpress */
	private $wp;

	private $tabs = [];
	private $currentTab;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
		$wp->addAction('current_screen', [$this, 'register']);
		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), ['admin.php',])) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, ['jigoshop_page_'.self::NAME])) {
				return;
			}

			Styles::add('jigoshop.admin.system_info', \JigoshopInit::getUrl().'/assets/css/admin/system_info.css', ['jigoshop.admin']);
		});
	}

	/**
	 * Adds new tab to settings screen.
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
		return __('System Information', 'jigoshop-ecommerce');
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
		return 'manage_jigoshop';
	}

	/**
	 * @return string Menu slug.
	 */
	public function getMenuSlug()
	{
		return self::NAME;
	}

	/**
	 * Registers setting item.
	 */
	public function register()
	{
		// Weed out all admin pages except the Jigoshop Settings page hits
		if (!in_array($this->wp->getPageNow(), ['admin.php'])) {
			return;
		}

		$screen = $this->wp->getCurrentScreen();
		if (!in_array($screen->base, ['jigoshop_page_'.self::NAME])) {
			return;
		}

		$tab = $this->getCurrentTab();
		$tab = $this->tabs[$tab];

		// Workaround for PHP pre-5.4
		$that = $this;
		/** @var TabInterface $tab */
		foreach ($tab->getSections() as $section) {
			$this->wp->addSettingsSection($section['id'], $section['title'], function () use ($tab, $section, $that){
				$that->displaySection($tab, $section);
			}, self::NAME);

			foreach ($section['fields'] as $field) {
				$this->wp->addSettingsField($field['id'], $field['title'], [$this, 'displayField'], self::NAME, $section['id'], $field);
			}
		}
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		Render::output('admin/system_info', [
			'tabs' => $this->tabs,
			'current_tab' => $this->getCurrentTab(),
        ]);
	}

	/**
	 * Displays the tab.
	 *
	 * @param TabInterface $tab     Tab to display.
	 * @param array        $section Section to display.
	 */
	public function displaySection(TabInterface $tab, array $section)
	{
		Render::output('admin/system_info/section', [
				'tab' => $tab,
				'section' => $section,
        ]);
	}


	/**
	 * Displays field according to definition.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return string Field output to display.
	 */
	public function displayField(array $field)
	{
		switch ($field['type']) {
			case 'user_defined':
				// Workaround for PHP pre-5.4
				echo call_user_func($field['display'], $field);
				break;
			case 'text':
			case 'number':
				Forms::text($field);
				break;
			case 'select':
				Forms::select($field);
				break;
			case 'checkbox':
				Forms::checkbox($field);
				break;
			case 'constant':
				Forms::constant($field);
				break;
			case 'textarea':
				Forms::textarea($field);
				break;
			default:
				$this->wp->doAction('jigoshop\admin\settings\form_field\\'.$field['type'], $field);
		}
	}

	/**
	 * @return string Slug of currently displayed tab
	 */
	protected function getCurrentTab()
	{
		if ($this->currentTab === null) {
			$this->currentTab = Admin\SystemInfo\SystemStatusTab::SLUG;
			// Use REQUEST to work with both GET and POST parameters
			if (isset($_REQUEST['tab']) && in_array($_REQUEST['tab'], array_keys($this->tabs))) {
				$this->currentTab = $_REQUEST['tab'];
			}
		}

		return $this->currentTab;
	}
}
