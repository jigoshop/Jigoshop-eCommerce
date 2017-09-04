<?php
/**
 *
 */
?>
<div class="row">
	<div class="col-sm-8">
		<div class="search form-group">
			<form action="#" method="GET">
				<?php foreach($_GET as $key => $value) : ?>
					<input type="hidden" name="<?= $key; ?>" value="<?= $value; ?>">
				<?php endforeach; ?>
				<div class="row">
					<div class="col-xs-6"><input class="form-control" type="text" name="search" value="<?= $search; ?>" placeholder="<?= __('Search', 'jigoshop-ecommerce'); ?>"></div>
					<div class="col-xs-3"><button class="btn btn-default" type="submit"><?= $search_title; ?></button></div>
					<?php if(!empty($csv_download_link)): ?>
						<a href="<?= $csv_download_link; ?>" class="btn btn-default export-csv" aria-label="Left Align">
							<span class="glyphicon glyphicon-export" aria-hidden="true"></span><?php _e('Export CSV', 'jigoshop-ecommerce'); ?>
						</a>
					<?php endif; ?>
				</div>
			</form>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="text-right row">
			<div class="col-xs-9"">
				<?php if(isset($total_pages) && $total_pages > 1) : ?>
					<nav>
						<ul class="pagination">
							<?php if($active_page > 1): ?>
								<li>
									<a href="<?= add_query_arg('paged', $active_page - 1); ?>" aria-label="Previous">
										<span aria-hidden="true">&laquo;</span>
									</a>
								</li>
							<?php endif; ?>
							<?php $limit = $active_page + 2 > $total_pages ? $total_pages : $active_page + 2; ?>
							<?php $start = $limit - 4 > 0 ? $limit - 4 : 1; ?>
							<?php for($i = $start; $i <= $limit ; $i++) : ?>
						        <li class="<?= $i == $active_page ? 'active' : ''; ?>"><a href="<?= add_query_arg('paged', $i); ?>"><?= $i; ?></a></li>
							<?php endfor; ?>
							<?php if($active_page < $total_pages): ?>
							    <li>
									<a href="<?= add_query_arg('paged', $active_page + 1); ?>" aria-label="Next">
										<span aria-hidden="true">&raquo;</span>
									</a>
							    </li>
							<?php endif; ?>
						</ul>
					</nav>
				<?php endif; ?>
			</div>
			<div class="col-xs-3 item-count"><?php printf('%s: %d', __('Items', 'jigoshop-ecommerce'), $total_items); ?></div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>
</div>
<div class="list">
	<div class="row">
		<?php foreach ($columns as $columnKey => $columnData) : ?>
			<div class="col-sm-<?= $columnData['size'] ?> fix-padding visible-lg visible-md"><?= $columnData['name']; ?></div>
		<?php endforeach; ?>
		<div class="clear"></div>
	</div>
	<?php if(empty($items)) : ?>
		<div class="row">
			<div class="col-xs-12">
				<div><?= $no_items; ?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php else : ?>
		<?php foreach ($items as $item) : ?>
			<div class="row">
				<?php foreach ($columns as $columnKey => $columnData) : ?>
					<div class="col-md-<?= $columnData['size'] ?> fix-padding">
						<div class="col-sm-6 col-xs-6 fix-padding visible-sm visible-xs">
							<?= $columnData['name']; ?>
						</div>
						<div class="col-md-12 col-sm-6 col-xs-6 fix-padding">
							<?php if ($columnKey == 'user_actions') : ?>
								<?php foreach ($item[$columnKey] as $action): ?>
									<a href="<?= $action['url']; ?>" class="btn btn-sm btn-default <?= $action['action']; ?>"><?= $action['name']; ?></a>
								<?php endforeach; ?>
							<?php else : ?>
								<?= $item[$columnKey] ?>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
	<div class="row">
		<?php foreach ($columns as $columnKey => $columnData) : ?>
			<div class="col-sm-<?= $columnData['size'] ?> fix-padding visible-lg visible-md"><?= $columnData['name']; ?></div>
		<?php endforeach; ?>
		<div class="clear"></div>
	</div>
</div>
<div class="row">
	<div class="col-xs-11" style="text-align:center">
		<?php if(isset($total_pages) && $total_pages > 1) : ?>
			<nav>
				<ul class="pagination">
					<?php if($active_page > 1): ?>
						<li>
							<a href="<?= add_query_arg('paged', $active_page - 1); ?>" aria-label="Previous">
								<span aria-hidden="true">&laquo;</span>
							</a>
						</li>
					<?php endif; ?>
					<?php $limit = $active_page + 2 > $total_pages ? $total_pages : $active_page + 2; ?>
					<?php $start = $limit - 4 > 0 ? $limit - 4 : 1; ?>
					<?php for($i = $start; $i <= $limit ; $i++) : ?>
						<li class="<?= $i == $active_page ? 'active' : ''; ?>"><a href="<?= add_query_arg('paged', $i); ?>"><?= $i; ?></a></li>
					<?php endfor; ?>
					<?php if($active_page < $total_pages): ?>
						<li>
							<a href="<?= add_query_arg('paged', $active_page + 1); ?>" aria-label="Next">
								<span aria-hidden="true">&raquo;</span>
							</a>
					    </li>
					<?php endif; ?>
				</ul>
			</nav>
		<?php endif; ?>
	</div>
	<div class="clear"></div>
</div>