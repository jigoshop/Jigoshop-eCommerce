<?php
/**
 * @var $product \Jigoshop\Entity\Product\External Product to add.
 */
?>
<?php do_action('jigoshop\template\product\before_cart', $product); ?>
<form action="<?= $product->getUrl(); ?>" method="get" class="form-inline cart" role="form">
    <button class="btn btn-primary btn-block" type="submit"><i class="fas fa-shopping-cart"></i> <?php _e('Buy product', 'jigoshop-ecommerce'); ?></button>
</form>
