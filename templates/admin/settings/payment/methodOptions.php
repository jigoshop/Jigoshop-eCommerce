<?php 
use Jigoshop\Admin\Helper\Forms;
?>

<div id="shipping-payment-method-options-<?php echo $id; ?>" style="display: none">
	<div class="payment-method-options-container">
		<div class="row clearfix"><h2><?php echo $name; ?></h2></div>
		<table class="form-table">
		<?php  
		foreach($options as $field):
			$field['label'] = $field['title'];
			if($field['type'] == 'checkbox') {
				$field['classes'] = [];
			}
			
			$field['name'] = 'jigoshop' . $field['name'];
			?>
			<tr>
				<td>
					<?php Forms::field($field['type'], $field); ?>
				</td>
			</tr>
		<?php
		endforeach;
		?>
		</table>

		<button type="submit" class="btn btn-primary pull-right shipping-payment-method-options-save margin-left-right-5">
			<?php echo __('Close & save changes', 'jigoshop-ecommerce'); ?>
		</button>

		<button type="submit" class="btn btn-danger pull-right shipping-payment-method-options-discard">
			<?php echo __('Close & discard changes', 'jigoshop-ecommerce'); ?>	
		</button>

		<div class="clearfix"></div>
	</div>
</div>