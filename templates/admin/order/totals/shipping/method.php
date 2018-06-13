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
    <?php 
    try { 
    	$price = $method->calculate($order);

    	$price = apply_filters('jigoshop\shipping\get_price', $price);
    	?>
		<span class="pull-right"><?= Product::formatPrice($price, '', $order->getCurrency()); ?></span>
    	<?php 
	} 
	catch (Exception $e) {} 
	?>
</li>
