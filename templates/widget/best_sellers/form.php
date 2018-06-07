<?php
/**
 * @var $title_id string Title field ID.
 * @var $title_name string Title field name.
 * @var $title string The title.
 * @var $number_id string Number field ID.
 * @var $number_name string Number field name.
 * @var $number string Number of products in widget.
 * @var $style_id string Style field ID.
 * @var $style_name string
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
<p>
    <label for="<?= $style_id; ?>"><?php _e('Style:', 'jigoshop-ecommerce'); ?></label>
    <select class="widefat" id="<?= $style_id; ?>" name="<?= $style_name; ?>">
        <option value="compact" <?php selected('compact', $style); ?>><?= __('Compact', 'jigoshop-ecommerce'); ?></option>
        <option value="full" <?php selected('full', $style); ?>><?= __('Full', 'jigoshop-ecommerce'); ?></option>
    </select>
</p>

