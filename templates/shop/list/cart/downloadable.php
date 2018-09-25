<?php
/**
 * @var $product \Jigoshop\Entity\Product\Downloadable Product to add.
 */
?>
<form action="" method="post" class="form-inline cart" role="form">
	<input type="hidden" name="action" value="add-to-cart" />
	<input type="hidden" name="item" value="<?= $product->getId(); ?>" />
	<button class="btn btn-primary js-btn" type="submit"><i class="fas fa-shopping-cart"></i> <?php _e('Add to cart', 'jigoshop-ecommerce'); ?></button>
</form>
