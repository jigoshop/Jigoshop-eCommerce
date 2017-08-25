<?php
namespace Jigoshop\Service;

interface CronServiceInterface {
	public function create();

	public function get($key);

	public function save($cronjob);

	public function hasJob($key);

	public function getAllJobs();

	public function getJobsToBeExecuteInThisRequest();

	public function remove($key);
}