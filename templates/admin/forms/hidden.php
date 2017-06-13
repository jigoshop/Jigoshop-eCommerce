<?php
use Jigoshop\Admin\Helper\Forms;

/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $placeholder string Field's placeholder.
 * @var $value mixed Current value.
 * @var $data array Key-value pairs for data attributes.
 */
?>
<div class="form-group <?= $id; ?>_field">
	<div>
		<input type="hidden" id="<?= $id; ?>" name="<?= $name; ?>" class="form-control <?= join(' ', $classes); ?>" value="<?= $value; ?>" 
		<?php 
		if(isset($data) && is_array($data)) {
			foreach($data as $dataKey => $dataValue) {
				echo sprintf(' data-%s="%s"', $dataKey, $dataValue);
			}
		}
		?>
		/>
	</div>
</div>
