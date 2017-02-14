<?php
/**
 * @var $cart \Jigoshop\Entity\Cart
 * @var $showWithTax
 * @var $suffix
 */
?>
<ul id="mobile" class="list-group">
    <?php foreach ($cart->getItems() as $key => $item): ?>
        <?php \Jigoshop\Helper\Render::output('shop/cart/mobile/'.$item->getType(), [
            'cart' => $cart,
            'key' => $key,
            'item' => $item,
            'showWithTax' => $showWithTax,
            'suffix' => $suffix,
        ]); ?>
    <?php endforeach; ?>
</ul>
