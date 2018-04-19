<?php
/**
 * @var $cart \Jigoshop\Entity\Cart
 */
?>
<ul id="mobile" class="list-group">
    <?php foreach ($cart->getItems() as $key => $item): ?>
        <?php
        	$template = null;
        	$template = apply_filters('jigoshop\template\shop\cart\mobile', $template, $cart, $key, $item);

        	if($template === null) {
        		\Jigoshop\Helper\Render::output('shop/cart/mobile/'.$item->getType(), [
            		'cart' => $cart,
            		'key' => $key,
            		'item' => $item
        		]);
        	}
        	else {
        		echo $template;
        	} 
        ?>
    <?php endforeach; ?>
</ul>
