<?php

namespace Jigoshop\Shipping;

use Jigoshop\Admin\Settings;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Discount;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Scripts;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

class FreeShipping implements Method3
{
	const NAME = 'free_shipping';

	/** @var Wordpress */
	private $wp;
	/** @var array */
	private $options;
	/** @var CartServiceInterface */
	private $cartService;
    /** @var OrderServiceInterface */
    private $orderService;
	/** @var Messages */
	private $messages;
	/** @var array */
	private $availability;

	public function __construct(Wordpress $wp, Options $options, CartServiceInterface $cartService, OrderServiceInterface $orderService, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options->get('shipping.'.self::NAME);
		$this->cartService = $cartService;
		$this->orderService = $orderService;
		$this->messages = $messages;

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

			Scripts::add('jigoshop.admin.shipping.free_shipping', \JigoshopInit::getUrl().'/assets/js/admin/shipping/free_shipping.js', [
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
		return __('Free shipping', 'jigoshop-ecommerce');
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
        $freeShippingDiscount = false;

		if ($post === null || $post->post_type != Types::ORDER) {
            $order = $this->cartService->getCurrent();
			$customer = $order->getCustomer();
            $freeShippingDiscount = array_reduce($order->getDiscounts(), function ($value, $discount){
                /** @var $discount Discount */
                return $value || $discount->getMeta('free_shipping') ? $discount->getMeta('free_shipping')->getValue() : 0;
            }, false);
		} else {
			// TODO: Get rid of this hack for customer fetching
            $order = $this->orderService->findForPost($post);
			$customer = $order->getCustomer();
		}

		return $this->options['enabled'] && ($freeShippingDiscount || (
		    ($order instanceof OrderInterface && $order->getProductSubtotal() >= $this->options['minimum']) &&
            ($this->options['available_for'] === 'all' || in_array($customer->getShippingAddress()->getCountry(), $this->options['countries']))));
	}

	/**
	 * @return bool Whether current method is taxable.
	 */
	public function isTaxable()
	{
		return false;
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
				'name' => sprintf('[%s][minimum]', self::NAME),
				'title' => __('Minimum cart value', 'jigoshop-ecommerce'),
				'description' => __('Minimum cart value above which the Free Shipping method should be available.', 'jigoshop-ecommerce'),
				'type' => 'text',
				'value' => $this->options['minimum'],
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
				'id' => 'free_shipping_available_for',
				'title' => __('Available for', 'jigoshop-ecommerce'),
				'type' => 'select',
				'value' => $this->options['available_for'],
				'options' => $this->availability,
            ],
			[
				'name' => sprintf('[%s][countries]', self::NAME),
				'id' => 'free_shipping_countries',
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
        $settings['adminOnly'] = $settings['adminOnly'] == 'on';

		if (!is_numeric($settings['minimum'])) {
			$settings['minimum'] = $this->options['minimum'];
			$this->messages->addWarning(__('Minimum cart value was invalid - value is left unchanged.', 'jigoshop-ecommerce'));
		}

		if ($settings['minimum'] >= 0) {
			$settings['minimum'] = (int)$settings['minimum'];
		} else {
			$settings['minimum'] = $this->options['minimum'];
			$this->messages->addWarning(__('Minimum cart value was below 0 - value is left unchanged.', 'jigoshop-ecommerce'));
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
		return 0.0;
	}

	/**
	 * @return array List of applicable tax classes.
	 */
	public function getTaxClasses()
	{
		return [];
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
