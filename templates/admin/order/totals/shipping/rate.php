<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $method \Jigoshop\Shipping\MultipleMethod Method to display.
 * @var $rate \Jigoshop\Shipping\Rate Rate to display.
 * @var $order \Jigoshop\Entity\Order Order to display.
 */
?>
<li
	class="list-group-item shipping-<?= $method->getId(); ?>-<?= $rate->getId(); ?> clearfix">
	<label>
		<input type="radio" name="order[shipping]" value="<?= $method->getId(); ?>" <?= Forms::checked($order->hasShippingMethod($method, $rate), true); ?> />
		<input type="hidden" class="shipping-method-rate" name="order[shipping_rate][<?= $method->getId(); ?>]" value="<?= $rate->getId(); ?>" />
		<?= $rate->getName(); ?>
	</label>
	<span class="pull-right"><?= Product::formatPrice($rate->calculate($order), '', $order->getCurrency()); ?></span>
</li>
