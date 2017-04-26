<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $method \Jigoshop\Shipping\Method Method to display.
 * @var $order \Jigoshop\Entity\Order Order to display.
 */
?>
<li class="list-group-item shipping-<?= $method->getId(); ?> clearfix">
	<label>
		<input type="radio" name="order[shipping]" value="<?= $method->getId(); ?>" <?= Forms::checked($order->hasShippingMethod($method), true); ?> />
		<?= $method->getName(); ?>
	</label>
	<span class="pull-right"><?= Product::formatPrice($method->calculate($order)); ?></span>
</li>
