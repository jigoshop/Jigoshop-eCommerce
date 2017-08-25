<?php
namespace Jigoshop\Factory;

use Jigoshop\Entity\Cronjob as Entity;
use WPAL\Wordpress;

class Cronjob {
	private $wp;

	public function __construct(Wordpress $wp) {
		$this->wp = $wp;
	}

	public function create() {
		return new Entity;
	}

	public function fetch($key) {
		$wpdb = $this->wp->getWpdb();
		$table = sprintf('%sjigoshop_cronjobs', $wpdb->prefix);
		$row = $wpdb->get_row($wpdb->prepare('SELECT id, jobKey, executeAt, executeEvery, lastExecutedAt, callback FROM ' . $table . ' WHERE jobKey = %d LIMIT 1', $key));
		if(is_null($row)) {
			return null;
		}

		$cronjob = $this->create();
		$cronjob->restoreState([
			'key' => $row->jobKey,
			'executeAt' => $row->executeAt,
			'executeEvery' => $row->executeEvery,
			'lastExecutedAt' => $row->lastExecutedAt,
			'callback' => unserialize($row->callback)
		]);

		return $cronjob;
	}
}