<?php 
use Jigoshop\Admin\Helper\Forms;
?>

<div id="shipping-method-options-<?php echo $method['id']; ?>" style="display: none">
	<div class="shipping-method-options-container">
		<div class="row clearfix"><h2><?php echo $method['name']; ?></h2></div>

		<?php  
		foreach($method['options'] as $field) {
			$field['label'] = $field['title'];
			if($field['type'] == 'checkbox') {
				$field['classes'] = [];
			}

			$field['name'] = 'jigoshop' . $field['name'];

			Forms::field($field['type'], $field);
		}
		?>

		<button type="submit" class="btn btn-primary pull-right shipping-method-options-save"><?php echo __('Close & save changes', 'jigoshop'); ?></button>

		<div class="clearfix"></div>
	</div>
</div>