<?php

namespace Jigoshop\Admin\Reports;

interface TableInterface
{
	public function getSlug();
	public function getTitle();
	public function getSearch();
	public function getColumns();
	public function getItems($columns);
	public function noItems();
	public function display();
}