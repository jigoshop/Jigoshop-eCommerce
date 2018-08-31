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
 * @var $data array Key-value pairs for data attributes.
 */
?>
<div class="form-group <?= $id; ?>_field <?= join(' ', $classes); ?> clearfix<?php $hidden and print ' not-active'; ?>">
	<?php if ($label): ?>
	<label for="<?= $id; ?>" class="col-sm-<?= $size == 12 ? 12 : 12 - $size; ?> control-label">
		<?= $label; ?>
		<?php if(!empty($tip)): ?>
			<a href="#" data-toggle="tooltip" class="badge" data-placement="top" title="<?= $tip; ?>">?</a>
		<?php endif; ?>
	</label>
	<?php endif; ?>
	<div class="col-sm-<?= $size; ?>">
		<input type="<?= $type; ?>" id="<?= $id; ?>" name="<?= $name; ?>" class="form-control <?= join(' ', $classes); ?>" placeholder="<?= $placeholder; ?>" value="<?= $value; ?>"<?php $disabled and print ' disabled'; ?>
        <?php
        if($type == 'number') {
            if(!isset($step)) {
                $step = 0.01;
            }
            echo sprintf(' step="%s"', $step);
        }
        ?>  
		<?php 
		if(isset($data) && is_array($data)) {
			foreach($data as $dataKey => $dataValue) {
				echo sprintf(' data-%s="%s"', $dataKey, $dataValue);
			}
		}
		?>
		/>
		<?php if(!empty($description)): ?>
			<span class="help-block"><?= $description; ?></span>
		<?php endif; ?>
	</div>
</div>
