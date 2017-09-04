<?php
/**
 * @var array $products List of related products.
 */
?>
<?php if(count($products) > 0): ?>
    <h4><?php _e('Related products', 'jigoshop-ecommerce'); ?></h4>
    <ul id="related_products" class="product-list list-inline">
        <?php foreach ($products as $product): ?>
            <?php \Jigoshop\Helper\Render::output('shop/list/product', ['product' => $product]); ?>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>