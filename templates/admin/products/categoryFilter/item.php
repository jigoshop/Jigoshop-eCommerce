<?php
use Jigoshop\Admin\Helper\Forms;
/**
 * @var $depth int Current category depth.
 * @var $value string Current category value.
 * @var $name string Category name.
 * @var $selected string Currently selected item.
 * @var $show_count bool Whether to show count of products in the category.
 * @var $count int Count of items in category.
 */
?>
<option class="level-<?= $depth; ?>" value="<?= $value; ?>" <?= Forms::selected($value, $selected); ?>>
	<?= str_repeat('&nbsp;', $depth*3).$name; ?>
	<?php if ($show_count): ?>
		(<?= $count; ?>)
	<?php endif; ?>
</option>
