<?php
/**
 * @var $product \Jigoshop\Entity\Product\Variable Product to add.
 */
?>
<form action="<?= $product->getLink(); ?>" method="get" class="form-inline cart" role="form">
	<button class="btn btn-primary js-btn" type="submit"><i class="fas fa-shopping-cart"></i> <?php _e('Select', 'jigoshop-ecommerce'); ?></button>
</form>
