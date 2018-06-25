<?php
namespace Jigoshop\Admin;

use Jigoshop\Admin;

class ThemeOptions implements PageInterface {
	const NAME = 'jigoshop_theme_options';

	private $wp;
	private $options;

	public function __construct($wp, $options) {
		$this->wp = $wp;
		$this->options = $options;
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

	public function display() {
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

		echo 'registered';exit;
	}
}