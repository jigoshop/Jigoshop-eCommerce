<?php
/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $type string Field type.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $placeholder string Field's placeholder.
 * @var $value mixed Current value.
 * @var $disabled bool Is field disabled?
 * @var $tip string Tip to show to the user.
 * @var $description string Field description.
 * @var $hidden boolean Whether the field is hidden.
 * @var $size int Size of form widget.
 */
$hasLabel = !empty($label);
?>
<div class="form-group <?php echo $id; ?>_field <?php echo join(' ', $classes); ?><?php $hidden and print ' not-active'; ?>">
	<div class="row">
		<div class="col-sm-<?php echo $size; ?>">
			<div class="row">
				<div class="col-xs-2 col-sm-1 text-right">
					<?php if (!empty($tip)): ?>
						<span data-toggle="tooltip" class="badge" data-placement="top" title="<?php echo $tip; ?>">?</span>
					<?php endif; ?>
				</div>
				<div class="col-xs-<?php echo $size - 2 ?> col-sm-<?php echo $size - 1 ?>">
					<input type="<?php echo $type; ?>" id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="form-control <?php echo join(' ', $classes); ?>" placeholder="<?php echo $placeholder; ?>" value="<?php echo $value; ?>"/>
					<?php if (!empty($description)): ?>
						<span class="help-block"><?php echo $description; ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
