<?php

namespace Jigoshop\Core;

use Jigoshop\Admin\Settings\LayoutTab;
use Jigoshop\Exception;
use Jigoshop\Frontend\Page\PageInterface;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Render;
use Monolog\Registry;
use WPAL\Wordpress;

/**
 * Class binding all basic templates.
 *
 * @package Jigoshop\Core
 */
class Template
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var PageInterface */
	private $page;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
	}

	/**
	 * Sets current page object.
	 *
	 * @param PageInterface $page
	 */
	public function setPage($page)
	{
		$this->page = $page;
	}

	/**
	 * Redirect Jigoshop pages to proper types.
	 */
	public function redirect()
	{
		if ($this->page !== null) {
			$this->page->action();
		}
	}

	/**
	 * Loads proper template based on current page.
	 *
	 * @param $template string Template chain.
	 *
	 * @return string Template to load.
	 */
	public function process($template)
	{
		if (!Pages::isJigoshop()) {
			return $template;
		}

		if ($this->page === null) {
			if (WP_DEBUG) {
				throw new Exception('Page object should already be set for Jigoshop pages, but none found.');
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addCritical('Page object should already be set for Jigoshop pages, but none found.');

			return false;
		}

		$content = $this->page->render();
		if($this->options->get(LayoutTab::SLUG.'.enabled', false)) {
		    Render::output('layout/custom', [
		        'content' => $content,
                'options' => $this->getCustomLayoutOptions()
            ]);
        } else {
            $template = $this->wp->getOption('template');
            $theme = $this->wp->wpGetTheme();
            if ($theme->get('Author') === 'WooThemes') {
                $template = 'woothemes';
            }

            if (!file_exists(\JigoshopInit::getDir() . '/templates/layout/' . $template . '.php')) {
                $template = 'default';
            }

            Render::output('layout/' . $template, [
                'content' => $content,
            ]);
        }

		return false;
	}

    /**
     * @return array
     */
	private function getCustomLayoutOptions()
    {
        $settings = $this->options->get(LayoutTab::SLUG, false);

        $options = $settings['default'];
        if(Pages::isProductList() && $settings[Pages::PRODUCT_LIST]['enabled']) {
            $options = $settings[Pages::PRODUCT_LIST];
        } elseif (Pages::isProduct() && $settings[Pages::PRODUCT]['enabled']) {
            $options = $settings[Pages::PRODUCT];
        } elseif (Pages::isCart() && $settings[Pages::CART]['enabled']) {
            $options = $settings[Pages::CART];
        } elseif (Pages::isCheckout() && $settings[Pages::CHECKOUT]['enabled']) {
            $options = $settings[Pages::CHECKOUT];
        } elseif (Pages::isProductCategory() && $settings[Pages::PRODUCT_CATEGORY]['enabled']) {
            $options = $settings[Pages::PRODUCT_CATEGORY];
        } elseif (Pages::isProductTag() && $settings[Pages::PRODUCT_TAG]['enabled']) {
            $options = $settings[Pages::PRODUCT_TAG];
        } elseif (Pages::isAccount() && $settings[Pages::ACCOUNT]['enabled']) {
            $options = $settings[Pages::ACCOUNT];
        } elseif (Pages::isCheckoutThankYou() && $settings[Pages::THANK_YOU]['enabled']) {
            $options = $settings[Pages::THANK_YOU];
        }

        $options['page_width'] = $settings['page_width'];
        $options['global_css'] = $settings['global_css'];
        if($options['proportions'] == 'custom') {
            $options['proportions'] = $options['custom_proportions'];
        } else {
            $proportions = explode('-', $options['proportions']);
            $options['proportions'] = [
                'content' => $proportions[0],
                'sidebar' => $proportions[1],
            ];
        }

        return $this->wp->applyFilters('jigoshop\template\custom_layout\options', $options);
    }
}
