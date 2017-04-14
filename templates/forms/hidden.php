<?php
/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $value mixed Current value.
 */
?>
<div class="form-group <?= $id; ?>_field clearfix">
	<input type="hidden" id="<?= $id; ?>" name="<?= $name; ?>" class="form-control <?= join(' ', $classes); ?>" value="<?= $value; ?>" />
</div>
