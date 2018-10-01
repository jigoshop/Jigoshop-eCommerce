<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin;
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Admin\Settings\GeneralTab;
use Jigoshop\Admin\Settings\TabInterface;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

/**
 * Jigoshop settings.
 *
 * @package Jigoshop\Admin
 * @author  Amadeusz Starzykiewicz
 */
class Settings implements PageInterface
{
	const NAME = 'jigoshop_settings';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var Messages */
	private $messages;
	private $tabs = [];
	private $currentTab;

	public function __construct(Wordpress $wp, Options $options, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;


		$wp->addAction('current_screen', [$this, 'register']);
		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), ['admin.php', 'options.php'])) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, ['jigoshop_page_'.Settings::NAME, 'options'])) {
				return;
			}
            Styles::add('jigoshop.admin.settings', \JigoshopInit::getUrl().'/assets/css/admin/settings.css', ['jigoshop.admin']);
			Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css', ['jigoshop.admin']);
			Styles::add('jigoshop.vendors.datepicker', \JigoshopInit::getUrl().'/assets/css/vendors/datepicker.css', ['jigoshop.admin']);
			Styles::add('jigoshop.vendors.bs_switch', \JigoshopInit::getUrl().'/assets/css/vendors/bs_switch.css', ['jigoshop.admin']);

			Scripts::add('jigoshop.admin.settings', \JigoshopInit::getUrl() . '/assets/js/admin/settings.js', ['jigoshop.admin'], ['page' => 'jigoshop_page_jigoshop_settings', 'in_footer' => true]);
			Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl() . '/assets/js/vendors/select2.js', [
				'jigoshop.admin.settings',
            ], ['page' => 'jigoshop_page_jigoshop_settings', 'in_footer' => true]);
			Scripts::add('jigoshop.vendors.bs_tab_trans_tooltip_collapse', \JigoshopInit::getUrl() . '/assets/js/vendors/bs_tab_trans_tooltip_collapse.js', [
				'jigoshop.admin.settings',
            ], ['page' => 'jigoshop_page_jigoshop_settings', 'in_footer' => true]);
			Scripts::add('jigoshop.vendors.bs_switch', \JigoshopInit::getUrl() . '/assets/js/vendors/bs_switch.js', [
				'jigoshop.admin.settings',
            ], ['page' => 'jigoshop_page_jigoshop_settings', 'in_footer' => true]);
            Scripts::localize('jigoshop.admin.settings', 'jigoshop_settings', [
                'i18n' => [
                    'yes' => __('Yes', 'jigoshop-ecommerce'),
                    'no' => __('No', 'jigoshop-ecommerce'),
                ],
            ]);
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
		return __('Settings', 'jigoshop-ecommerce');
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
		if (!in_array($this->wp->getPageNow(), ['admin.php', 'options.php'])) {
			return;
		}

		$screen = $this->wp->getCurrentScreen();
		if (!in_array($screen->base, ['jigoshop_page_'.self::NAME, 'options'])) {
			return;
		}

		$this->wp->registerSetting(self::NAME, Options::NAME, [$this, 'validate']);

		$tab = $this->getCurrentTab();
		$tab = $this->tabs[$tab];

		// Workaround for PHP pre-5.4
		$that = $this;
		/** @var TabInterface $tab */
		$sections = $this->wp->applyFilters('jigoshop/admin/settings/tab/' . $tab->getSlug(), $tab->getSections(), $tab);
		foreach ($sections as $section) {
			$this->wp->addSettingsSection($section['id'], $section['title'], function () use ($tab, $section, $that){
				$that->displaySection($tab, $section);
				if(isset($section['display']) && is_callable($section['display'])) {
					echo call_user_func($section['display']);
				}				
			}, self::NAME);

			if(isset($section['fields']) && is_array($section['fields'])) {
				foreach ($section['fields'] as $field) {
					$field = $this->validateField($field);
					$this->wp->addSettingsField($field['id'], $field['title'], [$this, 'displayField'], self::NAME, $section['id'], $field);
				}
			}
		}
	}

	/**
	 * @return string Slug of currently displayed tab
	 */
	protected function getCurrentTab()
	{
		if ($this->currentTab === null) {
			$this->currentTab = GeneralTab::SLUG;
			// Use REQUEST to work with both GET and POST parameters
			if (isset($_REQUEST['tab']) && in_array($_REQUEST['tab'], array_keys($this->tabs))) {
				$this->currentTab = $_REQUEST['tab'];
			}
		}

		return $this->currentTab;
	}

	/**
	 * Displays the tab.
	 *
	 * @param TabInterface $tab     Tab to display.
	 * @param array        $section Section to display.
	 */
	public function displaySection(TabInterface $tab, array $section)
	{
		Render::output('admin/settings/section', [
			'tab' => $tab,
			'section' => $section,
        ]);
	}

	protected function validateField(array $field)
	{
		if (!isset($field['id']) || $field['id'] === null) {
			$field['id'] = Forms::prepareIdFromName($field['name']);
		}
		$field['label_for'] = $field['id'];

		// TODO: Think on how to improve this name hacking
		$field['name'] = Options::NAME.$field['name'];

		return $field;
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		Render::output('admin/settings', [
			'tabs' => $this->tabs,
			'current_tab' => $this->getCurrentTab(),
			'messages' => $this->messages,
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
				Forms::userDefined($field);
				break;
			case 'text':
				Forms::text($field);
				break;
			case 'number':
				Forms::number($field);
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
	 * Validates settings for WordPress to save.
	 *
	 * @param array $input Input data to validate.
	 *
	 * @return array Sanitized output for saving.
	 */
	public function validate($input)
	{
	    try {
            $currentTab = $this->getCurrentTab();
            /** @var TabInterface $tab */
            $tab = $this->tabs[$currentTab];
            $this->options->update($currentTab, $tab->validate($input));
        } catch(Admin\Settings\ValidationException $e) {
            $this->messages->addError($e->getMessage(), true);
            $this->wp->wpSafeRedirect(admin_url(sprintf('admin.php?page=%s&tab=%s', self::NAME, $tab->getSlug())));
            exit;
        }
		return $this->options->getAll();
	}
}
