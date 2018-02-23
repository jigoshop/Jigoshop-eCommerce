<?php
use Jigoshop\Admin\Helper\Forms;
?>

<tr>
	<td>
		<?php 
		Forms::select([
			'name' => sprintf('processingFeeRules[%s][methods]', $id),
			'options' => $methods,
			'multiple' => true,
			'value' => isset($methodsSelected)?$methodsSelected:[]
		]);
		?>
	</td>
	<td>
		<?php
		Forms::text([
			'name' => sprintf('processingFeeRules[%s][value]', $id),
			'placeholder' => __('Absolute value or percentage of order value.', 'jigoshop-ecommerce'),
			'value' => isset($value)?$value:''
		]);
		?>
	</td>
	<td>
		<a class="btn btn-default processing-fee-remove-rule">
			<span class="glyphicon glyphicon-remove"></span>
		</a>
	</td>
</tr>