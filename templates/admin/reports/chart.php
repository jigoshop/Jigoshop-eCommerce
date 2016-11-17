<?php
/**
 *
 */
use Jigoshop\Admin\Reports;

?>
<div class="col-sm-12 buttons">
	<div class="ranges col-sm-10">
		<div class="btn-group btn-group-justified" role="group" aria-label="Chart Ranges">
			<?php foreach($ranges as $key => $value) : ?>
				<div class="btn-group" role="group">
		            <a href="<?php echo esc_url(add_query_arg('range', $key, $url)); ?>" class="btn btn-default <?php echo $key == $current_range ? 'active' : ''; ?>">
			            <?php echo $value ?>
		            </a>
		        </div>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="col-sm-2">
		<a href="#" download="<?php echo $export['download'] ?>" class="btn btn-default export-csv" data-export="chart" data-xaxes="<?php echo $export['xaxes']; ?>" data-exclude_series="2" data-groupby="<?php echo $export['groupby']; ?>" aria-label="Left Align">
            <span class="glyphicon glyphicon-export" aria-hidden="true"></span><?php _e('Export CSV', 'jigoshop'); ?>
		</a>
	</div>
</div>
<div class="col-xs-3 chart-sidebar">
	<ul class="chart-legend">
		<?php foreach($legends as $legend): ?>
		<li <?php echo isset($legend['color']) ? 'style="border-color: '.$legend['color'].'"' : ''; ?> class="highlight_series" data-series="<?php echo $legend['highlight_series'] ?>" <?php echo isset($legend['tip']) ? 'data-toggle="tooltip" data-original-title="'.$legend['tip'].'"' : '' ?>>
			<?php echo $legend['title'] ?>
		</li>
		<?php endforeach; ?>
	</ul>
	<div class="chart-widgets">
		<form action="admin.php" method="get">
			<input type="hidden" name="page" value="<?php echo Reports::NAME ?>">
			<input type="hidden" name="tab" value="<?php echo $current_tab ?>">
			<input type="hidden" name="type" value="<?php echo $current_type ?>">
			<input type="hidden" name="range" value="<?php echo $current_range ?>">
			<?php foreach($widgets as $widget) : ?>
				<div class="chart-widget <?php echo $widget->getSlug(); ?>">
					<div class="title"><?php echo $widget->getTitle(); ?></div>
					<div class="content<?php $widget->isVisible() and print ' visible'; ?>"><?php $widget->display(); ?></div>
				</div>
			<?php endforeach; ?>
			<button type="submit" class="btn btn-primary btn-block"><?php _e('Filter Report', 'jigoshop') ?></button>
		</form>
	</div>
</div>
<div class="col-sm-9 chart-container">
	<div class="main-chart" style="height:640px"></div>
	<div class="clear"></div>
</div>
<div class="clear"></div>