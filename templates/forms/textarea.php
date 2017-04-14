<?php
/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $rows int Number of rows to display.
 * @var $value mixed Current value.
 * @var $disabled bool Is field disabled?
 * @var $tip string Tip to show to the user.
 * @var $description string Field description.
 * @var $hidden boolean Whether the field is hidden.
 * @var $size int Size of form widget.
 */
?>
<div class="form-group <?= $id; ?>_field <?= join(' ', $classes); ?> clearfix<?php $hidden and print ' not-active'; ?>">
	<label for="<?= $id; ?>" class="col-sm-<?= $size == 12 ? 12 : 12 - $size; ?> control-label">
		<?= $label; ?>
		<?php if(!empty($tip)): ?>
			<a href="#" data-toggle="tooltip" class="badge" data-placement="top" title="<?= $tip; ?>">?</a>
		<?php endif; ?>
	</label>
	<div class="col-sm-<?= $size; ?>">
		<textarea rows="<?= $rows; ?>" id="<?= $id; ?>" name="<?= $name; ?>" class="form-control <?= join(' ', $classes); ?>"<?php $disabled and print ' disabled'; ?>><?= $value; ?></textarea>
		<?php if(!empty($description)): ?>
			<span class="help-block"><?= $description; ?></span>
		<?php endif; ?>
	</div>
</div>
