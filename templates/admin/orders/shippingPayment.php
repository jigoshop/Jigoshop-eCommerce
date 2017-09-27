<?php
/**
 * @var $order \Jigoshop\Entity\Order The order.
 */
$hasShipping = $order->getShippingMethod() !== null;
$hasPayment = $order->getPaymentMethod() !== null;
?>
<?php if ($hasPayment || $hasShipping): ?>
	<div>
		<?php if ($hasShipping): ?>
			<div class="font-bold shipping pull-left"><?php printf(__('Shipping%s', 'jigoshop-ecommerce'), ':&nbsp;'); ?></div>
			<div class="shipping pull-left"><?= strip_tags($order->getShippingMethod()
			                                                           ->getName()); ?></div>
			<div class="clear"></div>
		<?php endif; ?>
		<?php if ($hasPayment): ?>
			<div class="font-bold payment pull-left"><?php printf(__('Payment%s', 'jigoshop-ecommerce'), ':&nbsp;'); ?></div>
			<div class="payment pull-left"><?= strip_tags($order->getPaymentMethod()
			                                                          ->getName()); ?></div>
		<?php endif; ?>
	</div>
<?php endif; ?>
