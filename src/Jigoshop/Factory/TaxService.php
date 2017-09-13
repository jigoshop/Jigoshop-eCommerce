<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\TaxService as Service;
use WPAL\Wordpress;

class TaxService
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	private $customerService;

	public function __construct(Wordpress $wp, Options $options, CustomerServiceInterface $customerService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->customerService = $customerService;
	}

	/**
	 * @return Service Tax service.
	 * @since 2.0
	 */
	public function getService()
	{
		$optionClasses = $this->options->get('tax.classes');
		if(!is_array($optionClasses)) {
			$optionClasses = [];
		}

		$classes = array_map(function ($item){
			return $item['class'];
		}, $optionClasses);
		$service = new Service($this->wp, $classes, $this->customerService);

		switch ($this->options->get('advanced.cache')) {
			// TODO: Add caching mechanisms
//			case 'simple':
//				$service = new SimpleCache($service);
//				break;
			default:
				$service = $this->wp->applyFilters('jigoshop\core\get_tax_service', $service);
		}

		return $service;
	}
}
