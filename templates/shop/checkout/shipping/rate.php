<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $method \Jigoshop\Shipping\MultipleMethod Method to display.
 * @var $rate \Jigoshop\Shipping\Rate Rate to display.
 * @var $cart \Jigoshop\Entity\Cart Current cart.
 */
?>
<li
	class="list-group-item shipping-<?= $method->getId(); ?>-<?= $rate->getId(); ?> clearfix">
	<label>
		<input type="radio" name="jigoshop_order[shipping_method]" value="<?= $method->getId(); ?>" <?= Forms::checked($cart->hasShippingMethod($method, $rate), true); ?> />
		<input type="hidden" class="shipping-method-rate" name="jigoshop_order[shipping_method_rate][<?= $method->getId(); ?>]" value="<?= $rate->getId(); ?>" />
		<?= $rate->getName(); ?>
	</label>
	<span class="pull-right">
		<?php
		$price = $rate->calculate($cart);
		$price = apply_filters('jigoshop\shipping\get_price', $price);

		echo Product::formatPrice($price);
		?>
	</span>
</li>
