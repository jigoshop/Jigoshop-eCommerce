<?php

namespace Jigoshop\Admin\Reports;

interface TableInterface
{
	public function getSlug();
	public function getTite();
	public function getSearch();
	public function getColumns();
	public function getItems();
	public function noItems();
	public function display();

}