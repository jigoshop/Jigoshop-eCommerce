<?php
/**
 * @var $title_id string Title field ID.
 * @var $title_name string Title field name.
 * @var $title string The title.
 * @var $view_cart_button_id string Number field ID.
 * @var $view_cart_button_name string Number field name.
 * @var $view_cart_button string Number of products in widget.
 * @var $checkout_button_id string Number field ID.
 * @var $checkout_button_name string Number field name.
 * @var $checkout_button string Number of products in widget.
 */
?>
<p>
	<label for="<?= $title_id; ?>"><?php _e('Title:', 'jigoshop-ecommerce'); ?></label>
	<input class="widefat" id="<?= $title_id; ?>"  name="<?= $title_name; ?>" type="text" value="<?= $title; ?>" />
</p>
<p>
	<label for="<?= $view_cart_button_id; ?>"><?php _e('View cart button:', 'jigoshop-ecommerce'); ?></label>
	<input class="widefat" id="<?= $view_cart_button_id; ?>"  name="<?= $view_cart_button_name; ?>" type="text" value="<?= $view_cart_button; ?>" />
</p>
<p>
	<label for="<?= $checkout_button_id; ?>"><?php _e('Checkout button:', 'jigoshop-ecommerce'); ?></label>
	<input class="widefat" id="<?= $checkout_button_id; ?>"  name="<?= $checkout_button_name; ?>" type="text" value="<?= $checkout_button; ?>" />
</p>
