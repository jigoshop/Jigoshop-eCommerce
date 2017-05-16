<div class="form-group">
	<div class="row">
		<div class="col-sm-12">
			<?php 
			if($title):
			?>
				<label class="col-xs-12 col-sm-2 margin-top-bottom-9"><?php echo $title; ?></label>
			<?php
			endif;
			?>

			<div class="col-xs-12 col-sm-10 clearfix">
				<div class="tooltip-inline-badge">
					<?php 
					if($tip):
					?>
						<span data-toggle="tooltip" class="badge margin-top-bottom-9" data-placement="top" title data-original-title="<?php echo $tip; ?>">?</span>
					<?php
					endif;
					?>
				</div>

				<div class="tooltip-inline-input">
					<?php
					echo $display;
					?>
				</div>
			</div>
		</div>
	</div>
</div>