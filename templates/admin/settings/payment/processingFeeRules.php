<?php 
use Jigoshop\Helper\Render;
?>

<table class="table table-striped table-valign" id="processing-fee-rules">
	<thead>
		<tr>
			<th scope="col"></th>
			<th scope="col"><?php echo __('Methods', 'jigoshop-ecommerce'); ?></th>
			<th scope="col"><?php echo __('Min order value', 'jigoshop-ecommerce'); ?></th>
			<th scope="col"><?php echo __('Max order value', 'jigoshop-ecommerce'); ?></th>
			<th scope="col"><?php echo __('Fee', 'jigoshop-ecommerce'); ?></th>
			<th scope="col"><?php echo __('Alternate mode', 'jigoshop-ecommerce'); ?></th>
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
				'minValue' => $rule['minValue'],
				'maxValue' => $rule['maxValue'],
				'value' => $rule['value'],
				'alternateMode' => $rule['alternateMode']
			]);
		}
		?>
	</tbody>
</table>

<p class="help"><?php echo __('First rule which matches is taken into account.', 'jigoshop-ecommerce'); ?></p>
<p class="help"><?php echo __('Alternate mode: Fee is calculated based on final amount of payment, <strong>including the fee</strong>.', 'jigoshop-ecommerce'); ?></p>

<a id="processing-fee-add-rule" class="btn btn-default">
	<span class="glyphicon glyphicon-plus"></span>
	<?php echo __('Add', 'jigoshop-ecommerce'); ?>
</a>
