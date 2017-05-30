<?php
use Jigoshop\Admin\Helper\Forms;

/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $value mixed Current value.
 * @var $checked boolean Whether checkbox is checked.
 * @var $multiple boolean Whether checkbox is with multiple values.
 * @var $disabled bool Is field disabled?
 * @var $tip string Tip to show to the user.
 * @var $description string Field description.
 * @var $hidden boolean Whether the field is hidden.
 * @var $size int Size of form widget.
 * @var $data array Key-value pairs for data attributes.
 */
$hasLabel = !empty($label);
?>
<div class="form-group <?= $id; ?>_field <?= join(' ', $classes); ?><?php $hidden and print ' not-active'; ?>">
	<div class="row">
		<div class="col-sm-<?= $size; ?>">
			<?php if ($hasLabel): $size -= 2; ?>
				<label for="<?= $id; ?>" class="col-xs-12 col-sm-2 margin-top-bottom-9">
					<?= $label; ?>
				</label>
			<?php endif; ?>
			<div class="col-xs-12 col-sm-<?= $size ?> clearfix">
				<div class="tooltip-inline-badge">
					<?php if (!empty($tip)): ?>
						<span data-toggle="tooltip" class="badge margin-top-bottom-9" data-placement="top" title="<?= $tip; ?>">?</span>
					<?php endif; ?>
				</div>
				<div class="tooltip-inline-input">
					<?php if (!$multiple): ?>
						<input type="hidden" name="<?= $name; ?>" value="off"/>
					<?php endif; ?>
					<input type="checkbox" id="<?= $id; ?>" name="<?= $name; ?>" class="<?= join(' ', $classes); ?>" <?= Forms::checked($checked, true); ?> value="<?= $value; ?>"<?= Forms::disabled($disabled); ?>
					<?php 
					if(isset($data) && is_array($data)) {
						foreach($data as $dataKey => $dataValue) {
							echo sprintf(' data-%s="%s"', $dataKey, $dataValue);
						}
					}
					?>
					/>
					<?php if (!empty($description)): ?>
						<label for="<?= $id; ?>"><span class="help"><?= $description; ?></span></label>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
