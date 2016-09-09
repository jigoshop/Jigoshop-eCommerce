<?php

namespace Jigoshop\Admin\Reports\Chart;

interface WidgetInterface
{
	public function getSlug();
	public function getTitle();
	public function getArgs();
	public function display();
}