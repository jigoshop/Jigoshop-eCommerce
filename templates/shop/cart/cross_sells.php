<?php
/**
 * @var array $products List of related products.
 */
?>
<?php if(count($products) > 0): ?>
    <div class="cross-sells">
        <h4><?php _e('You may be interested in&hellip;', 'jigoshop-ecommerce'); ?></h4>
        <ul id="cross-sells" class="product-list list-inline">
            <?php foreach ($products as $product): ?>
                <?php \Jigoshop\Helper\Render::output('shop/list/product', ['product' => $product, 'show_add_to_cart_form' => false]); ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
