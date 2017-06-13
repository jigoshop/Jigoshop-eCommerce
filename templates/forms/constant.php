<?php
/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $placeholder string Field's placeholder.
 * @var $value mixed Current value.
 * @var $tip string Tip to show to the user.
 * @var $description string Field description.
 * @var $hidden boolean Whether the field is hidden.
 * @var $size int Size of form widget.
 * @var $data array Key-value pairs for data attributes.
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
		<p class="form-control-static <?= join(' ', $classes); ?>" id="<?= $id; ?>"
		<?php 
		if(isset($data) && is_array($data)) {
			foreach($data as $dataKey => $dataValue) {
				echo sprintf(' data-%s="%s"', $dataKey, $dataValue);
			}
		}
		?>
		><?= $value; ?></p>
		<?php if(!empty($description)): ?>
			<span class="help-block"><?= $description; ?></span>
		<?php endif; ?>
	</div>
</div>
