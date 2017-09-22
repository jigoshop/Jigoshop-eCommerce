<?php
use Jigoshop\Core\Types;
use Jigoshop\Helper\Forms;

/**
 * @var $terms array List of available categories.
 * @var $current string Currently selected type.
 * @var $walker \Jigoshop\Web\CategoryWalker Walker for displaying categories.
 * @var $query array Query to fetch categories.
 */
?>
<?php //Forms::select(array(
//	'name' => Types::PRODUCT_CATEGORY,
//	'id' => 'dropdown_'.Types::PRODUCT_CATEGORY,
//	'value' => $current,
//	'options'
//));
?>
<select name="<?= Types::PRODUCT_CATEGORY; ?>" id="dropdown_<?= Types::PRODUCT_CATEGORY; ?>">
	<option value="" <?= Forms::selected($current, ''); ?>><?php _e('View all categories', 'jigoshop-ecommerce'); ?></option>
	<?= $walker->walk($terms, 0, $query); ?>
</select>
