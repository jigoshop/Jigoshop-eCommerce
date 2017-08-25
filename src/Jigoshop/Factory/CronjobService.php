<?php
namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Service\CronService as Service;
use WPAL\Wordpress;

class CronjobService {
	private $wp;
	private $factory;

	public function __construct(Wordpress $wp, Cronjob $factory)
	{
		$this->wp = $wp;
		$this->factory = $factory;
	}

	/**
	 * @return CronServiceInterface Cron service.
	 * @since 2.0
	 */
	public function getService()
	{
		/** @var \WPAL\Wordpress $wp */
		$service = new Service($this->wp, $this->factory);

		$service = $this->wp->applyFilters('jigoshop\core\get_cronjob_service', $service);

		return $service;
	}	
}