<?php
namespace Jigoshop\Admin;

use Jigoshop\Admin;
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Admin\ThemeOptions\ThemeInterface;
use Jigoshop\Admin\ThemeOptions\ThemeTabInterface;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Integration;

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

		$this->wp->registerSetting(self::NAME, Options::NAME, [$this, 'validate']);

		$tab = $this->getCurrentTab();
		$tab = $this->getTabBySlug($tab);

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

	private function validateField(array $field) {
		if(!isset($field['id']) || $field['id'] === null) {
			$field['id'] = Forms::prepareIdFromName($field['name']);
		}
		$field['label_for'] = $field['id'];

		// TODO: Think on how to improve this name hacking
		$field['name'] = sprintf('%s[%s]', Options::NAME, $field['name']);

		return $field;
	}

	public function displaySection(ThemeTabInterface $tab, array $section) {
		Render::output('admin/theme_options/section', [
			'tab' => $tab,
			'section' => $section,
        ]);
	}

	public function displayField(array $field) {
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
				$this->wp->doAction('jigoshop\admin\theme_options\form_field\\'.$field['type'], $field);
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

	/**
	 * Returns current tab slug.
	 * 
	 * @return string Tab slug.
	 */
	private function getCurrentTab() {
		if ($this->currentTab === null) {
			$this->currentTab = self::$theme->getTabs()[0]->getSlug();
			// Use REQUEST to work with both GET and POST parameters
			if (isset($_REQUEST['tab']) && in_array($_REQUEST['tab'], array_keys(self::$theme->getTabs()))) {
				$this->currentTab = $_REQUEST['tab'];
			}
		}

		return $this->currentTab;
	}	

	/**
	 * Returns TabInterface object based on provided slug.
	 * 
	 * @param string $slug Slug of tab to fetch.
	 * 
	 * @return \Jigoshop\Admin\ThemeOptions\TabInterface Fetched tab or null if not found.
	 */
	private function getTabBySlug($slug) {
		foreach(self::$theme->getTabs() as $tab) {
			if($tab->getSlug() == $slug) {
				return $tab;
			}
		}

		return null;
	}

	public function display() {
		Render::output('admin/theme_options', [
			'tabs' => self::$theme->getTabs(),
			'messages' => $this->messages,
			'current_tab' => $this->getCurrentTab()
        ]);		
	}

	public function validate($input) {
		if($_REQUEST['option_page'] != self::NAME) {
			return $input;
		}

		$tab = $this->getTabBySlug($this->getCurrentTab());

		try {
            $this->options->update(sprintf('jigoshop.theme_options.%s.%s', self::$theme->getSlug(), $tab->getSlug()), $tab->validate($input));
        } catch(Admin\Settings\ValidationException $e) {
            $this->messages->addError($e->getMessage(), true);
            $this->wp->wpSafeRedirect(admin_url(sprintf('admin.php?page=%s&tab=%s', self::NAME, $tab->getSlug())));
            exit;
        }

		return $this->options->getAll();		
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

		$options = Integration::getOptions();

		foreach($theme->getTabs() as $tab) {
			if(!$tab instanceof ThemeTabInterface) {
				throw new \Exception('Specified tab does not implement ThemeTabInterface.');
			}

			$tabOptions = array_merge($tab->getDefaultOptions(), $options->get(sprintf('jigoshop.theme_options.%s.%s', $theme->getSlug(), $tab->getSlug()), []));
			$tab->setOptions($tabOptions);
		}

		self::$theme = $theme;
	}
}