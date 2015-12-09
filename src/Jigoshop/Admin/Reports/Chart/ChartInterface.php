<?php
namespace Jigoshop\Admin\Reports\Chart;

interface ChartInterface
{
	/**
	 * Output the report
	 */
	public function output();

	/**
	 * Get the main chart
	 *
	 * @return array
	 */
	public function getMainChart();

	/**
	 * Get the legend for the main chart sidebar
	 *
	 * @return array
	 */
	public function getChartLegend();

	/**
	 * [get_chart_widgets description]
	 *
	 * @return array
	 */
	public function getChartWidgets();

	/**
	 * Get an export link if needed
	 */
	public function getExportButton();
}