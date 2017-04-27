<?php 
use Jigoshop\Admin\Helper\Forms;
?>

<div id="payment-method-options-<?php echo $id; ?>">
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

		<button type="submit" class="btn btn-primary pull-right payment-method-close"><?php echo __('Close', 'jigoshop'); ?></button>

		<div class="clearfix"></div>
	</div>
</div>