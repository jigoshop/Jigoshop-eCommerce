<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $method \Jigoshop\Shipping\Method Method to display.
 * @var $cart \Jigoshop\Entity\Cart Current cart.
 */
?>
<li class="list-group-item shipping-<?= $method->getId(); ?> clearfix">
	<label>
		<input type="radio" name="jigoshop_order[shipping_method]" value="<?= $method->getId(); ?>" <?= Forms::checked($cart->hasShippingMethod($method), true); ?> />
		<?= $method->getTitle(); ?>
	</label>
	<span class="pull-right"><?= Product::formatPrice($method->calculate($cart)); ?></span>
</li>
