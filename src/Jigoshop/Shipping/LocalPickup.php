<?php

namespace Jigoshop\Shipping;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Service\CartServiceInterface;
use WPAL\Wordpress;

class LocalPickup implements Method3
{
	const NAME = 'local_pickup';

	/** @var Wordpress */
	private $wp;
	/** @var array */
	private $options;
	/** @var CartServiceInterface */
	private $cartService;
	/** @var string */
	private $baseCountry;

	public function __construct(Wordpress $wp, Options $options, CartServiceInterface $cartService)
	{
		$this->wp = $wp;
		$this->options = $options->get('shipping.'.self::NAME);
		$this->baseCountry = $options->get('general.country');
		$this->cartService = $cartService;
	}

	/**
	 * @return string Name of method.
	 */
	public function getName()
	{
		return __('Local pickup', 'jigoshop-ecommerce');
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
			$customer = maybe_unserialize($this->wp->getPostMeta($post->ID, 'customer', true));
		}

		if(empty($customer)) {
			return $this->options['enabled'];
		}

		return $this->options['enabled'] && $customer->getShippingAddress()->getCountry() == $this->baseCountry;
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
				'value' => $this->options['title'],
            ],
			[
				'name' => sprintf('[%s][fee]', self::NAME),
				'title' => __('Handling Fee', 'jigoshop-ecommerce'),
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

		return $settings;
	}

	/**
	 * @param OrderInterface $order Order to calculate shipping for.
	 *
	 * @return float Calculated value of shipping for the order.
	 */
	public function calculate(OrderInterface $order)
	{
		return(float)$this->options['fee'];
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
