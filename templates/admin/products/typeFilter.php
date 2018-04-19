<?php
use Jigoshop\Helper\Forms;

/**
 * @var $types array List of available types.
 * @var $current string Currently selected type.
 */
?>
<select name="product_type" id="dropdown_product_type">
	<option value='0'><?= __('Show all types', 'jigoshop-ecommerce'); ?></option>
	<?php foreach($types as $type => $options): ?>
	<option value="<?= $type; ?>" <?= Forms::selected($type, $current); ?>><?= $options['label']; ?> (<?= absint($options['count']); ?>)</option>
	<?php endforeach; ?>
</select>
