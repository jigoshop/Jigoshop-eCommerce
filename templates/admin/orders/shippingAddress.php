<?php

/**
 * @var $order \Jigoshop\Entity\Order The order.
 */

$address = $order->getCustomer()->getShippingAddress();
?>
<address>
	<?= $address; ?>
</address>
<?php $google_address = $address->getGoogleAddress(); ?>
<?php if (!empty($google_address)): ?>
	<a target="_blank" href="http://maps.google.com/maps?&amp;q=<?= $google_address; ?>&amp;z=16"><?php _e('Map' ,'jigoshop-ecommerce'); ?></a>
<?php endif; ?>
