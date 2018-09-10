<?php
/**
 * @var $product \Jigoshop\Entity\Product Product to view.
 */
?>
<form action="<?= $product->getLink(); ?>" method="get" class="form-inline cart" role="form">
    <button class="btn btn-primary js-btn" type="submit"><?php _e('View Product', 'jigoshop-ecommerce'); ?></button>
</form>
