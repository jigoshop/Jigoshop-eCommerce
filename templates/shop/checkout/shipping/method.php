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
    <?php 
    try { 
    	$price = $method->calculate($cart);

    	$price = apply_filters('jigoshop\shipping\get_price', $price);
    	?>
		<span class="pull-right"><?= Product::formatPrice($price); ?></span>
    	<?php 
	} 
	catch (Exception $e) {} 
	?>
</li>
