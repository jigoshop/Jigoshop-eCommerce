<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Helper\Render;

/**
 * @var $attribute Attribute Attribute to display.
 * @var $id int ID of the attribute.
 * @var $types array List of available attribute types.
 */
?>
<tr class="attribute" data-id="<?= $id; ?>">
	<td>
		<?php Forms::text([
			'name' => 'attributes['.$id.'][label]',
			'classes' => ['attribute-label'],
			'value' => $attribute->getLabel(),
        ]); ?>
	</td>
	<td>
		<?php Forms::text([
			'name' => 'attributes['.$id.'][slug]',
			'classes' => ['attribute-slug'],
			'value' => $attribute->getSlug(),
        ]); ?>
	</td>
	<td>
		<?php Forms::select([
			'name' => 'attributes['.$id.'][type]',
			'classes' => ['attribute-type'],
			'value' => $attribute->getType(),
			'options' => $types,
        ]); ?>
	</td>
	<td>
		<?php if ($attribute->getType() != Attribute\Text::TYPE): ?>
		<button type="button" class="configure-attribute btn btn-default"><?php _e('Configure', 'jigoshop-ecommerce'); ?></button>
		<?php endif; ?>
		<button type="button" class="remove-attribute btn btn-default" title="<?php _e('Remove', 'jigoshop-ecommerce'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
	</td>
</tr>
<?php if ($attribute->getType() != Attribute\Text::TYPE): ?>
<tr class="options not-active" data-id="<?= $id; ?>">
	<td colspan="4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h5 class="panel-title">
					<?php _e('Attribute options', 'jigoshop-ecommerce'); ?>
					<button type="button" class="btn btn-default pull-right"><?php _e('Close', 'jigoshop-ecommerce'); ?></button>
				</h5>
			</div>
			<table class="table table-condensed">
				<thead>
				<tr>
					<th scope="col" style="width: 30px"></th>
					<th scope="col"><?php _e('Label', 'jigoshop-ecommerce'); ?></th>
					<th scope="col"><?php _e('Value', 'jigoshop-ecommerce'); ?></th>
					<th scope="col"></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach($attribute->getOptions() as $option): ?>
					<?php Render::output('admin/product_attributes/option', ['id' => $id, 'option_id' => $option->getId(), 'option' => $option]); ?>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
				<tr>
					<td></td>
					<td>
						<?php Forms::text([
							'name' => 'option_label',
							'classes' => ['new-option-label'],
							'placeholder' => __('New option label', 'jigoshop-ecommerce'),
                        ]); ?>
					</td>
					<td>
						<?php Forms::text([
							'name' => 'option_value',
							'classes' => ['new-option-value'],
							'placeholder' => __('New option value', 'jigoshop-ecommerce'),
                        ]); ?>
					</td>
					<td>
						<button type="button" class="btn btn-default add-option"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop-ecommerce'); ?></button>
					</td>
				</tr>
				</tfoot>
			</table>
		</div>
	</td>
</tr>
<?php endif; ?>
