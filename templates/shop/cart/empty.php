<?php
/**
 * @var $shopUrl string Url to shop (product list).
 */
?>
<div class="alert alert-info text-center" id="cart">
	<p><?php _e('Your cart is empty.', 'jigoshop-ecommerce'); ?></p>
	<a href="<?= $shopUrl; ?>" class="btn btn-primary"><?php _e('Return to shop', 'jigoshop-ecommerce'); ?></a>
</div>
