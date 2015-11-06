<?php
/**
 * @var $types          array List of all sales report types.
 * @var $current_type   current selected type.
 * @var $orderAmounts   array Order amounts for each day to display in chart.
 * @var $chart          \Jigoshop\Admin\Reports\Chart
 */
use Jigoshop\Admin\Reports;

?>
<div class="stats thumbnail main-graph">
	<nav>
		<ul class="nav nav-tabs nav-justified">
		<?php foreach ($types as $slug => $title) : ?>
			<li <?php echo $slug == $current_type ? 'class="active"' : '' ?>><a
					href="?page=<?php echo Reports::NAME; ?>&tab=<?php echo Reports\SalesTab::SLUG; ?>&type=<?php echo $slug; ?>"><?php echo $title; ?></a></li>
		<?php endforeach; ?>
		</ul>
	</nav>
	<div class="tab-content">
		<div class="chart-sidebar">
		<?php if ($legends = $chart->getChartLegend()) : ?>
			<ul class="chart-legend">
							<?php foreach ($legends as $legend) : ?>
								<li style="border-color: <?php echo $legend['color']; ?>" <?php
								if (isset($legend['highlight_series']))
									echo 'class="highlight_series '.(isset($legend['placeholder']) ? 'help_tip' : '').'" data-series="'.esc_attr($legend['highlight_series']).'"';
								?> data-tip="<?php echo isset($legend['placeholder']) ? $legend['placeholder'] : ''; ?>">
									<?php echo $legend['title']; ?>
								</li>
							<?php endforeach; ?>
						</ul>
		<?php endif; ?>
		</div>
		<?php $chart->display(); ?>
	</div>
</div>

