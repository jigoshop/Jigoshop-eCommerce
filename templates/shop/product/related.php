<?php
/**
 * @var array $products List of related products.
 */
?>
<?php if(count($products) > 0): ?>
    <h4><?php _e('Related products', 'jigoshop'); ?></h4>
    <ul id="related_products" class="list-inline">
        <?php foreach ($products as $product): ?>
            <?php \Jigoshop\Helper\Render::output('shop/list/product', array('product' => $product)); ?>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>