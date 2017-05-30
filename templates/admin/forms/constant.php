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
		</div>
	</div>
</div>
