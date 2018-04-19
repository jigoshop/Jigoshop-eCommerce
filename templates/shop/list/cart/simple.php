<?php
/**
 * @var $product \Jigoshop\Entity\Product\Simple Product to add.
 */
?>
<form action="" method="post" class="form-inline cart" role="form">
	<input type="hidden" name="action" value="add-to-cart" />
	<input type="hidden" name="item" value="<?= $product->getId(); ?>" />
	<button class="btn btn-primary js-btn btn-block" type="submit"><?php _e(' <i class="fas fa-shopping-cart"></i> Add to cart', 'jigoshop'); ?></button>
</form>
