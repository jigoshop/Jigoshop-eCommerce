<?php 
use Jigoshop\Helper\Forms;
?>

<tr id="<?php echo $method['id']; ?>">
	<td>
		<?php echo $method['name']; ?>
	</td>
	<td>
		<div class="form-group switch-medium clearfix">
			<div class="col-sm-12 checkbox-inline col-no-padding">
				<input type="checkbox" class="switch-medium payment-method-enable" value="on" <?php echo ($method['active']?'checked="checked"':''); ?> />
			</div>
		</div>
	</td>
	<td>
		<?php 
		if($method['hasTestMode']):
		?>	
			<div class="form-group switch-medium clearfix">
				<div class="col-sm-12 checkbox-inline col-no-padding">
					<input type="checkbox" class="switch-medium payment-method-testMode" value="on" <?php echo ($method['testModeActive']?'checked="checked"':''); ?> />
				</div>
			</div>
		<?php 
		else:
			echo '-';
		endif;
		?>
	</td>
	<td>
		<button type="button" class="btn btn-default text-left shipping-payment-method-configure" value="<?php echo $method['id']; ?>"><span class="glyphicon glyphicon-plus"></span> <?php echo __('Configure', 'jigoshop-ecommerce'); ?></button>
	</td>
	<td>
		<?php echo $method['status']; ?>
	</td>
</tr>