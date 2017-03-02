<?php
/**
 * @var $product \Jigoshop\Entity\Product\External Product to add.
 */
?>
<?php do_action('jigoshop\template\product\before_cart', $product); ?>
<form action="<?php echo $product->getUrl(); ?>" method="get" class="form-inline cart" role="form">
    <button class="btn btn-primary btn-block" type="submit"><?php _e('Buy product', 'jigoshop'); ?></button>
</form>
