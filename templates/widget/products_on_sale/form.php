<?php
/**
 * @var $title_id string Title field ID.
 * @var $title_name string Title field name.
 * @var $title string The title.
 * @var $number_id string Number field ID.
 * @var $number_name string Number field name.
 * @var $number string Number of products in widget.
 */
?>
<p>
	<label for="<?= $title_id; ?>"><?php _e('Title:', 'jigoshop-ecommerce'); ?></label>
	<input class="widefat" id="<?= $title_id; ?>"  name="<?= $title_name; ?>" type="text" value="<?= $title; ?>" />
</p>
<p>
	<label for="<?= $number_id; ?>"><?php _e('Number of products to show:', 'jigoshop-ecommerce'); ?></label>
	<input class="widefat" id="<?= $number_id; ?>"  name="<?= $number_name; ?>" type="number" min="1" value="<?= $number; ?>" />
</p>
