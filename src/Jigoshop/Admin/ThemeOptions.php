<?php
namespace Jigoshop\Admin;

use Jigoshop\Admin;
use Jigoshop\Admin\ThemeOptions\ThemeInterface;
use Jigoshop\Admin\ThemeOptions\ThemeTabInterface;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;

class ThemeOptions implements PageInterface {
	const NAME = 'jigoshop_theme_options';

	private $wp;
	private $options;
	private $messages;

	private static $theme;

	private $currentTab;

	public function __construct($wp, $options, $messages) {
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;

		$wp->addAction('current_screen', [$this, 'registerScreen']);
		$wp->addAction('admin_enqueue_scripts', function () use ($wp) {
			// Weed out all admin pages except the Jigoshop Theme Options page hits
			if (!in_array($wp->getPageNow(), ['admin.php', 'options.php'])) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, ['jigoshop_page_' . self::NAME, 'options'])) {
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
		});		
	}

	public function registerScreen() {
		// Weed out all admin pages except the Jigoshop Settings page hits
		if (!in_array($this->wp->getPageNow(), ['admin.php', 'options.php'])) {
			return;
		}

		$screen = $this->wp->getCurrentScreen();
		if (!in_array($screen->base, ['jigoshop_page_' . self::NAME, 'options'])) {
			return;
		}

		$this->wp->registerSetting(self::NAME, self::NAME, [$this, 'validate']);

		$tab = $this->getCurrentTab();
		foreach(self::$theme->getTabs() as $currentTab) {
			if($currentTab->getSlug() == $tab) {
				$tab = $currentTab;

				break;
			}
		}

		// Workaround for PHP pre-5.4
		$that = $this;
		/** @var TabInterface $tab */
		$sections = $tab->getSections();
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

	public function getTitle() {
		return __('Theme options', 'jigoshop-ecommerce');
	}

	public function getParent() {
		return Admin::MENU;
	}

	public function getCapability() {
		return 'manage_jigoshop';
	}

	public function getMenuSlug() {
		return self::NAME;
	}

	protected function getCurrentTab() {
		if ($this->currentTab === null) {
			$this->currentTab = self::$theme->getTabs()[0]->getSlug();
			// Use REQUEST to work with both GET and POST parameters
			if (isset($_REQUEST['tab']) && in_array($_REQUEST['tab'], array_keys(self::$theme->getTabs()))) {
				$this->currentTab = $_REQUEST['tab'];
			}
		}

		return $this->currentTab;
	}	

	public function display() {
		Render::output('admin/theme_options', [
			'tabs' => self::$theme->getTabs(),
			'messages' => $this->messages,
			'current_tab' => $this->getCurrentTab()
        ]);		
	}

	/**
	 * Registers theme object.
	 * 
	 * @param \Jigoshop\Admin\ThemeOptions\ThemeInterface $theme Theme object to register.
	 * 
	 * @throws \Exception On invalid object specified.
	 */
	public static function register($theme) {
		if(!$theme instanceof ThemeInterface) {
			throw new \Exception('Specified theme does not implement ThemeInterface.');
		}

		foreach($theme->getTabs() as $tab) {
			if(!$tab instanceof ThemeTabInterface) {
				throw new \Exception('Specified tab does not implement ThemeTabInterface.');
			}
		}

		self::$theme = $theme;
	}
}