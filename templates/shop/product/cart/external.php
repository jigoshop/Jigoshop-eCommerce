<?php
/**
 * @var $product \Jigoshop\Entity\Product\External Product to add.
 */
?>
<?php do_action('jigoshop\template\product\before_cart', $product); ?>
<a class="btn btn-primary" target="_blank" href="<?= $product->getUrl(); ?>"><?php _e('Buy product', 'jigoshop-ecommerce'); ?></a>
