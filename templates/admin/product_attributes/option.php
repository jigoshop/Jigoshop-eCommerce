<?php
use Jigoshop\Admin\Helper\Forms;

/**
 * @var $option \Jigoshop\Entity\Product\Attribute\Option Option to display.
 * @var $id int ID of the attribute.
 * @var $option_id int ID of the option.
 */
?>
<tr data-id="<?= $option_id; ?>">
	<td>
		<span class="glyphicon glyphicon-sort"></span>
	</td>
	<td>
		<?php Forms::text([
			'name' => 'attributes['.$id.'][options]['.$option_id.'][label]',
			'classes' => ['option-label'],
			'value' => $option->getLabel(),
        ]); ?>
	</td>
	<td>
		<?php Forms::text([
			'name' => 'attributes['.$id.'][options]['.$option_id.'][value]',
			'classes' => ['option-value'],
			'value' => $option->getValue(),
        ]); ?>
	</td>
	<td>
		<button type="button" class="remove-attribute-option btn btn-default" title="<?php _e('Remove', 'jigoshop-ecommerce'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
	</td>
</tr>
