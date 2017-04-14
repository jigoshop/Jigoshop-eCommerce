<?php
use Jigoshop\Helper\Render;

/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $placeholder string Field's placeholder.
 * @var $multiple boolean Is field supposed to accept multiple values?
 * @var $value mixed Currently selected value(s).
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
		<select id="<?= $id; ?>" name="<?= $name; ?>" class="form-control <?= join(' ', $classes); ?>" placeholder="<?= $placeholder; ?>"<?php $multiple and print ' multiple="multiple"'; ?><?php $disabled and print ' disabled'; ?>>
			<?php foreach($options as $option => $item): ?>
				<?php if(isset($item['items'])): ?>
					<optgroup label="<?= $option; ?>">
						<?php foreach($item['items'] as $subvalue => $subitem): $subitem['disabled'] = isset($subitem['disabled']) && $subitem['disabled'] ? true : false; ?>
							<?php Render::output('forms/select/option', ['label' => $subitem['label'], 'disabled' => $subitem['disabled'], 'value' => $subvalue, 'current' => $value]); ?>
						<?php endforeach; ?>
					</optgroup>
				<?php else: $item['disabled'] = isset($item['disabled']) && $item['disabled'] ? true : false; ?>
					<?php Render::output('forms/select/option', ['label' => $item['label'], 'disabled' => $item['disabled'], 'value' => $option, 'current' => $value]); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
		<?php if(!empty($description)): ?>
			<span class="help-block"><?= $description; ?></span>
		<?php endif; ?>
	</div>
</div>
<!-- TODO: Get rid of this and use better asset script. -->
<script type="text/javascript">
	/*<![CDATA[*/
	jQuery(function($){
		$("select#<?= $id; ?>").select2(<?= json_encode($args); ?>);
	});
	/*]]>*/
</script>
