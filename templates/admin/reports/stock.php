<?php
/**
 * @var $types          array List of all sales report types.
 * @var $current_type   current selected type.
 * @var $content
 */
use Jigoshop\Admin\Reports;

?>
<div class="stats thumbnail main-graph">
	<nav>
		<ul class="nav nav-tabs nav-justified second-level">
		<?php foreach ($types as $slug => $title) : ?>
			<li <?php echo $slug == $current_type ? 'class="active"' : '' ?>>
				<a	href="?page=<?php echo Reports::NAME; ?>&tab=<?php echo Reports\StockTab::SLUG; ?>&type=<?php echo $slug; ?>"><?php echo $title; ?></a>
			</li>
		<?php endforeach; ?>
		</ul>
	</nav>
	<div class="tab-content">
		<?php $content->display(); ?>
	</div>
</div>

