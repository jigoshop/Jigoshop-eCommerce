<?php
namespace Jigoshop\Service;

use Jigoshop\Entity\Cronjob as Entity;
use Jigoshop\Factory\Cronjob as Factory;
use WPAL\Wordpress;

class CronService implements CronServiceInterface {
	private $wp;
	private $factory;

	public function __construct(Wordpress $wp, Factory $factory) {
		$this->wp = $wp;
		$this->factory = $factory;

		$this->wp->addAction('init', function() {
			$this->processJobs();
		});
	}

	/**
	 * Fires up all cronjobs.
	 */
	private function processJobs() {
		$lock = $this->wp->getTransient('jigoshop_cron_lock');
		if($lock !== false) {
			return;
		}
		$this->wp->setTransient('jigoshop_cron_lock', 1, 60);

		$cronjobs = $this->getJobsToBeExecuteInThisRequest();
		foreach($cronjobs as $cronjob) {
			try {
				$cronjob->executeNow();

				$this->save($cronjob);
			}
			catch(\Exception $e) {
				return false;
			}
		}

		$this->wp->deleteTransient('jigoshop_cron_lock');
	}

	/**
	 * Creates empty (and properly initialized) cronjob.
	 */
	public function create() {
		return $this->factory->create();
	}

	/**
	 * Fetches cronjob from the database.
	 * 
	 * @param string $key Key of the cronjob to fetch.
	 * @throws Exception If no cronjob was found matching specified key.
	 */
	public function get($key) {
		$cronjob = $this->factory->fetch($key);
		if(is_null($cronjob)) {
			throw new \Exception('No cronjob exists with specified key.');
		}

		return $cronjob;
	}

	/**
	 * Saves cronjob to the database.
	 * 
	 * @param Jigoshop\Entity\Cronjob $cronjob Cronjob to save.
	 * @throws Exception If invalid (or not properly set) cronjob was specified.
	 * @return bool True if cronjob was saved successfully.
	 */
	public function save($cronjob) {
		if(!$cronjob instanceof Entity) {
			throw new \Exception('Invalid cronjob specified.');
		}

		if($cronjob->getKey() == '') {
			throw new \Exception('The job key is invalid.');
		}

		if($cronjob->getInterval() <= 0) {
			throw new \Exception('The interval is invalid.');
		}

		$state = $cronjob->getState();
		if(!$state['callback'] || !is_callable($state['callback'])) {
			throw new \Exception('There is no callback or it is invalid.');
		}

		$wpdb = $this->wp->getWpdb();
		$table = sprintf('%sjigoshop_cronjobs', $wpdb->prefix);
		$row = $wpdb->get_row($wpdb->prepare('SELECT id FROM ' . $table . ' WHERE jobKey = \'%s\' LIMIT 1', $cronjob->getKey()));
		if(is_null($row)) {
			$wpdb->insert($table, [
				'id' => 'NULL',
				'jobKey' => $cronjob->getKey()
			], [
				'%d',
				'%s'
			]);
		}

		$wpdb->update($table, [
			'executeAt' => $state['executeAt'],
			'executeEvery' => $state['executeEvery'],
			'lastExecutedAt' => $state['lastExecutedAt'],
			'callback' => serialize($state['callback'])
		], [
			'jobKey' => $cronjob->getKey()
		], [
			'%d',
			'%d',
			'%d',
			'%s'
		]);

		return true;
	}

	/**
	 * Checks if specified cronjob exists in the database.
	 * 
	 * @param string $key Cronjob key.
	 * @return bool True if found, false otherwise.
	 */
	public function hasJob($key) {
		$wpdb = $this->wp->getWpdb();
		$table = sprintf('%sjigoshop_cronjobs', $wpdb->prefix);
		$row = $wpdb->get_row($wpdb->prepare('SELECT id FROM ' . $table . ' WHERE jobKey = \'%s\' LIMIT 1', $key));
		if(is_null($row)) {
			return false;
		}

		return true;
	}

	/**
	 * Returns an array of all cronjobs.
	 * 
	 * @return array Array of cronjobs.
	 */
	public function getAllJobs() {
		$cronjobs = [];

		$wpdb = $this->wp->getWpdb();
		$table = sprintf('%sjigoshop_cronjobs', $wpdb->prefix);
		$rows = $wpdb->get_results('SELECT id, jobKey FROM ' . $table . ' ORDER BY id ASC');
		foreach($rows as $row) {
			if(is_object($row) && $row->id) {
				$cronjobs[] = $this->get($row->jobKey);
			}
		}

		return $cronjobs;
	}

	/**
	 * Returns an array of all cronjobs which are scheduled to execute in this request.
	 * 
	 * @return array Array of cronjobs.
	 */
	public function getJobsToBeExecuteInThisRequest() {
		$cronjobs = [];

		$wpdb = $this->wp->getWpdb();
		$table = sprintf('%sjigoshop_cronjobs', $wpdb->prefix);
		$rows = $wpdb->get_results($wpdb->prepare('SELECT id, jobKey FROM ' . $table . ' WHERE executeAt <= %d ORDER BY id ASC', time()));
		foreach($rows as $row) {
			if(is_object($row) && $row->id) {
				$cronjobs[] = $this->get($row->jobKey);
			}
		}

		return $cronjobs;		
	}

	/**
	 * Removes cronjob by it`s job key.
	 */
	public function remove($key) {
		$wpdb = $this->wp->getWpdb();
		$table = sprintf('%sjigoshop_cronjobs', $wpdb->prefix);

		$wpdb->delete($table, [
			'jobKey' => $key
		]);		
	}

	/**
	 * Removes the lock. To be used primarly during development when called function stopped execution of entire script, so lock could not be removed properly.
	 */
	public function forceLockOff() {
		$this->wp->deleteTransient('jigoshop_cron_lock');
	}
}