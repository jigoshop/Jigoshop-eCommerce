<?php 
use Jigoshop\Helper\Render;
?>

<table class="table table-striped table-valign" id="processing-fee-rules">
	<thead>
		<tr>
			<th scope="col"><?php echo __('Methods', 'jigoshop-ecommerce'); ?></th>
			<th scope="col"><?php echo __('Fee', 'jigoshop-ecommerce'); ?></th>
			<th scope="col"></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach($rules as $rule) {
			Render::output('admin/settings/payment/processingFeeRules/processingFeeRule', [
				'id' => $rule['id'],
				'methods' => $methods,
				'methodsSelected' => $rule['methods'],
				'value' => $rule['value']
			]);
		}
		?>
	</tbody>
</table>

<p class="help"><?php echo __('First rule which matches is taken into account.', 'jigoshop-ecommerce'); ?></p>

<a id="processing-fee-add-rule" class="btn btn-default">
	<span class="glyphicon glyphicon-plus"></span>
	<?php echo __('Add', 'jigoshop-ecommerce'); ?>
</a>