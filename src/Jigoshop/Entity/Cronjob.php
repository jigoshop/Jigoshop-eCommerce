<?php
namespace Jigoshop\Entity;

use Jigoshop\Service\Cron as Service;

class Cronjob {
	private $key = '';
	private $executeAt = 0;
	private $executeEvery = 3600;
	private $lastExecutedAt = 0;
	private $callback = null;

	/**
	 * Returns job key of this cronjob.
	 * 
	 * @return string Job key. 
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Sets the job key for this cronjob.
	 * 
	 * @param string $key Job key.
	 */
	public function setKey($key) {
		$this->key = $key;
	}

	/**
	 * Returns time of next execution (timestamp).
	 * 
	 * @return int Timestamp of next execution.
	 */
	public function getNextExecutionTime() {
		return $this->executeAt;
	}

	/**
	 * Sets job to be executed "now" (or at next visit to the site).
	 * 
	 * @throws Exception If callback is invalid or not reachable.
	 */
	public function executeNow() {
		if(!isset($this->callback[0]) || $this->callback[0] instanceof \__PHP_Incomplete_Class || !is_callable($this->callback)) {
			throw new \Exception('Unable to call specified callback.');
		}

		$className = get_class($this->callback[0]);
		$object = new $className;
		if(!is_object($object)) {
			throw new \Exception('Unable to create object.');
		}

		$callback = [
			$object,
			$this->callback[1]
		];

		call_user_func($callback, $this);

		$this->lastExecutedAt = time();
		$this->recalculateNextScheduleTime();
	}

	/**
	 * Returns job interval (in seconds).
	 * 
	 * @return int Interval.
	 */
	public function getInterval() {
		return $this->executeEvery;
	}

	/**
	 * Sets the job interval (in seconds).
	 * 
	 * @param int $interval Interval
	 * @throws Exception If invalid interval was specified.
	 */
	public function setInterval($interval) {
		if(!is_int($interval)) {
			throw new \Exception('The interval has to be integer.');
		}

		if($interval <= 0) {
			throw new \Exception('The interval cannot be less or equal 0.');
		}

		$this->executeEvery = $interval;
		$this->recalculateNextScheduleTime();
	}

	/**
	 * Sets proper next execution time.
	 */
	private function recalculateNextScheduleTime() {
		$this->executeAt = (time() + $this->executeEvery);
	}

	/**
	 * Returns time of last job execution (timestamp).
	 * 
	 * @return int Time of last execution.
	 */
	public function getLastExecutionTime() {
		return $this->lastExecutedAt;
	}

	/**
	 * Sets callback to be called when job is executed.
	 * 
	 * @param callable $callback Callback.
	 * @throws Exception If invalid callback was specified.
	 */
	public function setCallback($callback) {
		if(!is_callable($callback)) {
			throw new \Exception('Invalid or unreachable callback specified.');
		}

		$this->callback = $callback;
	}

	/**
	 * Returns all parameters of this cronjob as array.
	 * 
	 * @return array Cronjob parameters.
	 */
	public function getState() {
		return [
			'key' => $this->key,
			'executeAt' => $this->executeAt,
			'executeEvery' => $this->executeEvery,
			'lastExecutedAt' => $this->lastExecutedAt,
			'callback' => $this->callback
		];
	}

	/**
	 * Sets all parameters of this cronjob from array.
	 * 
	 * @param array $state Cronjob parameters.
	 */
	public function restoreState($state) {
		$this->key = $state['key'];
		$this->executeAt = $state['executeAt'];
		$this->executeEvery = $state['executeEvery'];
		$this->lastExecutedAt = $state['lastExecutedAt'];
		$this->callback = $state['callback'];
	}
}