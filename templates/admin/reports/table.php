<?php
/**
 *
 */
?>
<div class="row" style="margin-left:0px;margin-right:0px;">

	<div class="col-xs-12" style="border-bottom:1px solid black">
		<?php foreach($columns as $columnKey => $columnName) :?>
			<?php if(in_array($columnKey, array('email','orders','spent'))) : ?>
				<div class="col-xs-1 hidden-xs" style="text-align:center"><?php echo $columnName; ?></div>
			<?php else :?>
				<div class="col-xs-2 hidden-xs" style="text-align:center"><?php echo $columnName; ?></div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<?php foreach($items as $item) :?>
		<div class=col-xs-12" style="border-bottom:1px solid black">
			<?php foreach($columns as $columnKey => $columnName) :?>
				<?php if(in_array($columnKey, array('email','orders','spent'))) : ?>
					<div class="col-sm-1">
						<div class="hidden-md hidden-lg hidden-sm col-xs-6">
							<?php echo $columnName; ?>
						</div>
						<div class="col-sm-12 col-xs-6" style="overflow:hidden">
							<?php print_r($item[$columnKey]); ?>
						</div>
					</div>
				<?php else :?>
					<div class="col-sm-2">
						<div class="hidden-md hidden-lg hidden-sm col-xs-6">
							<?php echo $columnName; ?>
						</div>
						<div class="col-sm-12 col-xs-6" style="overflow:hidden">
							<?php print_r($item[$columnKey]); ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
			<div class="clear"></div>
		</div>
	<?php endforeach; ?>

	<div class="col-xs-12" style="border-bottom:1px solid black">
		<?php foreach($columns as $columnKey => $columnName) :?>
			<?php if(in_array($columnKey, array('email','orders','spent'))) : ?>
				<div class="col-xs-1 hidden-xs" style="text-align:center"><?php echo $columnName; ?></div>
			<?php else :?>
				<div class="col-xs-2 hidden-xs" style="text-align:center"><?php echo $columnName; ?></div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<div class="clear"></div>
</div>

