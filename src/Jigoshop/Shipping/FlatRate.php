<?php

namespace Jigoshop\Shipping;

use Jigoshop\Admin\Settings;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Entity\Product\Shippable;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Scripts;
use Jigoshop\Service\CartServiceInterface;
use WPAL\Wordpress;

class FlatRate implements Method3
{
	const NAME = 'flat_rate';

	/** @var Wordpress */
	private $wp;
	/** @var array */
	private $options;
	/** @var CartServiceInterface */
	private $cartService;
	/** @var Messages */
	private $messages;
	/** @var array */
	private $types;
	/** @var array */
	private $availability;

	public function __construct(Wordpress $wp, Options $options, CartServiceInterface $cartService, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options->get('shipping.'.self::NAME);
		$this->cartService = $cartService;
		$this->messages = $messages;
		$this->types = [
			'per_order' => __('Per order', 'jigoshop-ecommerce'),
			'per_item' => __('Per item', 'jigoshop-ecommerce'),
        ];
		$this->availability = [
			'all' => __('All allowed countries', 'jigoshop-ecommerce'),
			'specific' => __('Selected countries', 'jigoshop-ecommerce'),
        ];

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), ['admin.php', 'options.php'])) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, ['jigoshop_page_'.Settings::NAME, 'options'])) {
				return;
			}

			if (!isset($_GET['tab']) || $_GET['tab'] !== 'shipping') {
				return;
			}

			Scripts::add('jigoshop.admin.shipping.flat_rate', \JigoshopInit::getUrl().'/assets/js/admin/shipping/flat_rate.js', [
				'jquery',
				'jigoshop.admin'
            ]);
		});
	}

	/**
	 * @return string Name of method.
	 */
	public function getName()
	{
		return __('Flat rate', 'jigoshop-ecommerce');
	}

	/**
	 * @return string Human readable (customizable) title of method.
	 */
	public function getTitle()
	{
		return $this->options['title'];
	}

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		$post = $this->wp->getGlobalPost();
		if ($post === null || $post->post_type != Types::ORDER) {
            $cart = $this->cartService->getCurrent();
            $customer = $cart->getCustomer();
		} else {
			// TODO: Get rid of this hack for customer fetching
			$customer = unserialize($this->wp->getPostMeta($post->ID, 'customer', true));
		}

		return $this->options['enabled'] && ($this->options['available_for'] === 'all' || in_array($customer->getShippingAddress()->getCountry(), $this->options['countries']));
	}

	/**
	 * @return bool Whether current method is taxable.
	 */
	public function isTaxable()
	{
		return $this->options['is_taxable'];
	}

	/**
	 * @return array List of options to display on Shipping settings page.
	 */
	public function getOptions()
	{
		return [
			[
				'name' => sprintf('[%s][enabled]', self::NAME),
				'title' => __('Is enabled?', 'jigoshop-ecommerce'),
				'type' => 'checkbox',
				'checked' => $this->options['enabled'],
				'classes' => ['switch-medium'],
            ],
			[
				'name' => sprintf('[%s][title]', self::NAME),
				'title' => __('Method title', 'jigoshop-ecommerce'),
				'type' => 'text',
				'value' => $this->getTitle(),
            ],
			[
				'name' => sprintf('[%s][type]', self::NAME),
				'title' => __('Type', 'jigoshop-ecommerce'),
				'type' => 'select',
				'value' => $this->options['type'],
				'options' => $this->types,
            ],
			[
				'name' => sprintf('[%s][is_taxable]', self::NAME),
				'title' => __('Is taxable?', 'jigoshop-ecommerce'),
				'type' => 'checkbox',
				'checked' => $this->options['is_taxable'],
				'classes' => ['switch-medium'],
            ],
			[
				'name' => sprintf('[%s][cost]', self::NAME),
				'title' => __('Cost', 'jigoshop-ecommerce'),
				'type' => 'text',
				'value' => $this->options['cost'],
            ],
			[
				'name' => sprintf('[%s][fee]', self::NAME),
				'title' => __('Handling fee', 'jigoshop-ecommerce'),
				'type' => 'text',
				'value' => $this->options['fee'],
            ],
            [
                'title' => __('Enable Only for Admin', 'jigoshop-ecommerce'),
                'description' => __('Enable this if you would like to test it only for Site Admin', 'jigoshop-ecommerce'),
                'name' => sprintf('[%s][adminOnly]', self::NAME),
                'type' => 'checkbox',
                'checked' => $this->options['adminOnly'],
                'classes' => ['switch-medium'],
            ],
			[
				'name' => sprintf('[%s][available_for]', self::NAME),
				'id' => 'flat_rate_available_for',
				'title' => __('Available for', 'jigoshop-ecommerce'),
				'type' => 'select',
				'value' => $this->options['available_for'],
				'options' => $this->availability,
            ],
			[
				'name' => sprintf('[%s][countries]', self::NAME),
				'id' => 'flat_rate_countries',
				'title' => __('Select countries', 'jigoshop-ecommerce'),
				'type' => 'select',
				'value' => $this->options['countries'],
				'options' => Country::getAllowed(),
				'multiple' => true,
				'hidden' => $this->options['available_for'] == 'all',
            ],
        ];
	}

	/**
	 * Validates and returns properly sanitized options.
	 *
	 * @param $settings array Input options.
	 *
	 * @return array Sanitized result.
	 */
	public function validateOptions($settings)
	{
		$settings['enabled'] = $settings['enabled'] == 'on';
		$settings['is_taxable'] = $settings['is_taxable'] == 'on';
        $settings['adminOnly'] = $settings['adminOnly'] == 'on';

        if (!in_array($settings['type'], array_keys($this->types))) {
			$settings['type'] = $this->options['type'];
			$this->messages->addWarning(__('Type is invalid - value is left unchanged.', 'jigoshop-ecommerce'));
		}

		if (!is_numeric($settings['cost'])) {
			$settings['cost'] = $this->options['cost'];
			$this->messages->addWarning(__('Cost was invalid - value is left unchanged.', 'jigoshop-ecommerce'));
		}
		if ($settings['cost'] >= 0) {
			$settings['cost'] = (float)$settings['cost'];
		} else {
			$settings['cost'] = $this->options['cost'];
			$this->messages->addWarning(__('Cost was below 0 - value is left unchanged.', 'jigoshop-ecommerce'));
		}

		if (!is_numeric($settings['fee'])) {
			$settings['fee'] = $this->options['fee'];
			$this->messages->addWarning(__('Fee was invalid - value is left unchanged.', 'jigoshop-ecommerce'));
		}
		if ($settings['fee'] >= 0) {
			$settings['fee'] = (float)$settings['fee'];
		} else {
			$settings['fee'] = $this->options['fee'];
			$this->messages->addWarning(__('Fee was below 0 - value is left unchanged.', 'jigoshop-ecommerce'));
		}

		if (!in_array($settings['available_for'], array_keys($this->availability))) {
			$settings['available_for'] = $this->options['available_for'];
			$this->messages->addWarning(__('Availability is invalid - value is left unchanged.', 'jigoshop-ecommerce'));
		}

		if ($settings['available_for'] === 'specific') {
			$settings['countries'] = array_filter($settings['countries'], function ($item){
				return Country::exists($item);
			});
		} else {
			$settings['countries'] = [];
		}

		return $settings;
	}

	/**
	 * @param OrderInterface $order Order to calculate shipping for.
	 *
	 * @return float Calculated value of shipping for the order.
	 */
	public function calculate(OrderInterface $order)
	{
        if($this->options['type'] == 'per_item') {
            $quantity = array_sum(array_map(function($item) {
                $product = $item->getProduct();
                return $product && $product instanceof Shippable && $product->isShippable() ? $item->getQuantity() : 0;
            }, $order->getItems()));
            return (float)(($this->options['cost'] * $quantity) + $this->options['fee']);
        }
		return (float)($this->options['cost'] + $this->options['fee']);
	}

	/**
	 * @return array List of applicable tax classes.
	 */
	public function getTaxClasses()
	{
		return ['standard'];
	}

	/**
	 * @return array Minimal state to fully identify shipping method.
	 */
	public function getState()
	{
		return [
			'id' => $this->getId(),
        ];
	}

	/**
	 * @return string ID of shipping method.
	 */
	public function getId()
	{
		return self::NAME;
	}

	/**
	 * Restores shipping method state.
	 *
	 * @param array $state State to restore.
	 */
	public function restoreState(array $state)
	{
		// Empty
	}

	/**
	 * Checks whether current method is the one specified with selected rule.
	 *
	 * @param Method $method Method to check.
	 * @param int    $rate   Rate to check.
	 *
	 * @return boolean Is this the method?
	 */
	public function is(Method $method, $rate = null)
	{
		return $method->getId() == $this->getId();
	}


    /**
     * Whenever method was enabled by the user.
     *
     * @return boolean Method enable state.
     */
    public function isActive()
    {
        return isset($this->options['enabled']) && $this->options['enabled'];
    }

    /**
     * Set method enable state.
     *
     * @param boolean $state Method enable state.
     *
     * @return array Method current settings (after enable state change).
     */
    public function setActive($state)
    {
        $this->options['enabled'] = $state;

        return $this->options;
    }

    /**
     * Whenever method was configured by the user (all required data was filled for current scenario).
     *
     * @return boolean Method config state.
     */
    public function isConfigured()
    {
        return true;
    }

    /**
     * Whenever method has some sort of test mode.
     *
     * @return boolean Method test mode presence.
     */
    public function hasTestMode()
    {
        return false;
    }

    /**
     * Whenever method test mode was enabled by the user.
     *
     * @return boolean Method test mode state.
     */
    public function isTestModeEnabled()
    {
        return false;
    }

    /**
     * Set Method test mode state.
     *
     * @param boolean $state Method test mode state.
     *
     * @return array Method current settings (after test mode state change).
     */
    public function setTestMode($state)
    {
        return $this->options;
    }

    /**
     * Whenever method requires SSL to be enabled to function properly.
     *
     * @return boolean Method SSL requirment.
     */
    public function isSSLRequired()
    {
        return false;
    }

    /**
     * Whenever method is set to enabled for admin only.
     *
     * @return boolean Method admin only state.
     */
    public function isAdminOnly()
    {
        return isset($this->options['adminOnly']) && $this->options['adminOnly'];
    }

    /**
     * Sets admin only state for the method and returns complete method options.
     *
     * @param boolean $state Method admin only state.
     *
     * @return array Complete method options after change was applied.
     */
    public function setAdminOnly($state)
    {
        $this->options['adminOnly'] = $state;

        return $this->options;
    }
}
