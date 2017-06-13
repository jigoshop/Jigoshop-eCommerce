<?php
/**
 * @var $cart \Jigoshop\Entity\Cart
 */
?>
<ul id="mobile" class="list-group">
    <?php foreach ($cart->getItems() as $key => $item): ?>
        <?php \Jigoshop\Helper\Render::output('shop/cart/mobile/'.$item->getType(), [
            'cart' => $cart,
            'key' => $key,
            'item' => $item
        ]); ?>
    <?php endforeach; ?>
</ul>
