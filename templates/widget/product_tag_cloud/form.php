<?php
/**
 * @var $title_id string Title field ID.
 * @var $title_name string Title field name.
 * @var $title string The title.
 */
?>
<p>
	<label for="<?= $title_id; ?>"><?php _e('Title:', 'jigoshop-ecommerce'); ?></label>
	<input class="widefat" id="<?= $title_id; ?>"  name="<?= $title_name; ?>" type="text" value="<?= $title; ?>" />
</p>
