<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Options;
use Jigoshop\Payment\Method;
use Jigoshop\Service\PaymentServiceInterface;

/**
 * Payment tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class PaymentTab implements TabInterface
{
	const SLUG = 'payment';

	/** @var array */
	private $options;
	/** @var PaymentServiceInterface */
	private $paymentService;

	public function __construct(Options $options, PaymentServiceInterface $paymentService)
	{
		$this->options = $options->get(self::SLUG);
		$this->paymentService = $paymentService;
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Payment', 'jigoshop');
	}

	/**
	 * @return string Tab slug.
	 */
	public function getSlug()
	{
		return self::SLUG;
	}

	/**
	 * Extract title and id from all availble gateway
	 *
	 * @return array
	 */
	protected function getTitles()
	{
	    $gateways = $this->paymentService->getEnabled();
	    if(count($gateways)) {
            $options = [
                '' => __('Please select a gateway', 'jigoshop'),
            ];
            foreach ($gateways as $gateway) {
                /** @var $gateway Method */
                $options[$gateway->getId()] = trim(strip_tags($gateway->getName()));
            }
        } else {
	        $options['no_default_gateway'] = __('All gateways are disabled. Please turn on a gateway.', 'jigoshop');
        }

        return $options;
	}

	/**
	 * Add section with defaults gateway
	 *
	 * @param array $options all gateway
	 *
	 * @return array
	 */
	protected function getSectionGateway($options)
	{
		return array(
			array(
				'title'  => __('Default Gateway', 'jigoshop'),
				'id'     => 'default_gateway',
				'fields' => array(
					array(
						'name'    => "[default_gateway]",
						'title'   => __('Set default gataway', 'jigoshop'),
						'type'    => "select",
						'value'   => $this->options['default_gateway'],
						'options' => $this->getTitles($options),
						'classes' => array(),
					)
				),
			)
		);
	}

	/**
	 * Get avaible gateway from the system
	 *
	 * @return array
	 */
	protected function getAvailableGateway()
	{
		$options = array();

		foreach ($this->paymentService->getAvailable() as $method)
		{
			/** @var $method Method */
			$options[] = array(
				'title' => $method->getName(),
                'description' => apply_filters('jigoshop\admin\settings\payment\method\description', '', $method),
				'id' => $method->getId(),
				'fields' => $method->getOptions(),
				'enabled' => $method->isEnabled(),
			);
		}

		return $options;
	}

	/**
	 * @return array List of items to display.
	 */
	public function getSections()
	{
		$options = $this->getAvailableGateway();
		return array_merge($this->getSectionGateway($options), $options);
	}

	/**
	 * Validate and sanitize input values.
	 *
	 * @param array $settings Input fields.
	 *
	 * @return array Sanitized and validated output.
	 * @throws ValidationException When some items are not valid.
	 */
	public function validate($settings)
	{
		$activeGatewayFromPost = array();

		foreach ($this->paymentService->getAvailable() as $method)
		{
			/** @var $method Method */
			$methodId = $method->getId();
			$settings[$methodId] = $method->validateOptions($settings[$methodId]);

//			$_POST 'enabled' need be used by all as payment gateways
			if ($_POST['jigoshop'][$methodId]['enabled'] == 'on')
			{
				$activeGatewayFromPost[] = $methodId;
			}
		}

		if (count($activeGatewayFromPost) == 0)
		{
			$settings['default_gateway'] = 'no_default_gateway';

			return $settings;
		}

		if ($_POST['jigoshop'][$this->options['default_gateway']]['enabled'] == 'off')
		{
			$settings['default_gateway'] = $activeGatewayFromPost[0];

		}

		return $settings;
	}
}