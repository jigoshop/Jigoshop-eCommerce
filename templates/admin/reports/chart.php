<?php
/**
 *
 */
use Jigoshop\Admin\Reports;

?>
<div class="col-sm-12">
	<div class="ranges col-sm-10">
		<div class="btn-group btn-group-justified" role="group" aria-label="Chart Ranges">
			<?php foreach($ranges as $key => $value) : ?>
				<div class="btn-group" role="group">
		            <a href="?page=<?php echo Reports::NAME ?>&tab=sales&type=<?php echo $current_type ?>&range=<?php echo $key; ?>" class="btn btn-default <?php echo $key == $current_range ? 'active' : ''; ?>">
			            <?php echo $value ?>
		            </a>
		        </div>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="col-sm-2">
		<button type="button" class="btn btn-default" aria-label="Left Align">
            <span class="glyphicon glyphicon-export" aria-hidden="true"></span><?php _e('Export CSV', 'jigoshop'); ?>
		</button>
	</div>
</div>
<div class="col-sm-3 chart-sidebar">
	<ul class="chart-legend">
		<?php foreach($legends as $legend): ?>
		<li <?php echo isset($legend['color']) ? 'style="border-color: '.$legend['color'].'"' : ''; ?> class="highlight_series <?php echo isset($legend['tip']) ? 'help-tip' : '' ?>" data-series="<?php echo $legend['highlight_series'] ?>" <?php echo isset($legend['tip']) ? 'data-tip="'.$legend['tip'].'"' : '' ?>>
			<?php echo $legend['title'] ?>
		</li>
		<?php endforeach; ?>
	</ul>
	<ul class="chart-widgets">
		<?php //TODO implement widgets ?>
	</ul>
</div>
<div class="col-sm-9 chart-container">
	<div class="main-chart" style="height:640px"></div>
	<div class="clear"></div>
</div>
<script>
	//TODO: Move this to JS file
	jQuery(function() {
		var drawGraph = function(highlight) {
			var series = JSON.parse('<?php echo json_encode($chart) ?>');
			console.log(series);
			if(highlight !== 'undefined' && series[highlight]){
				var highlight_series = series[highlight];
				highlight_series.color = '#98c242';
				if(highlight_series.bars)
					highlight_series.bars.fillColor = '#98c242';
				if(highlight_series.lines){
					highlight_series.lines.lineWidth = 5;
				}
			}
			jQuery.plot(
				jQuery('.main-chart'),
				series,
				{
					legend: {
						show: false
					},
					grid: {
						color: '#aaa',
						borderColor: 'transparent',
						borderWidth: 0,
						hoverable: true
					},
					xaxes: [{
						color: '#aaa',
						position: "bottom",
						tickColor: 'transparent',
						mode: "time",
						timeformat: "%d %b",
						monthNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
						tickLength: 1,
						minTickSize: [1, "day"],
						font: {
							color: "#aaa"
						}
					}],
					yaxes: [
						{
							min: 0,
							minTickSize: 1,
							tickDecimals: 0,
							color: '#d4d9dc',
							font: {color: "#aaa"}
						},
						{
							position: "right",
							min: 0,
							tickDecimals: 2,
							alignTicksWithAxis: 1,
							autoscaleMargin: 0,
							color: 'transparent',
							font: {color: "#aaa"}
						}
					]
				}
			);
			jQuery('.main-chart').resize();
		};
		drawGraph();
		jQuery('.highlight_series').hover(
			function() {
				drawGraph(jQuery(this).data('series'));
			},
			function() {
				drawGraph();
			}
		);
	});
</script>
<div class="clear"></div>