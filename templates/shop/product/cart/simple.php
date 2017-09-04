<?php
/**
 * @var $product \Jigoshop\Entity\Product\Simple Product to add.
 */
?>
<form action="" method="post" class="form-inline" role="form">
	<input type="hidden" name="action" value="add-to-cart" />
	<?php do_action('jigoshop\template\product\before_cart', $product); ?>
	<div class="form-group">
		<label class="sr-only" for="product-quantity"><?php _e('Quantity', 'jigoshop-ecommerce'); ?></label>
		<input type="number" class="form-control" name="quantity" min="1" id="product-quantity" value="1" />
	</div>
	<button class="btn btn-primary" type="submit"><?php _e('Add to cart', 'jigoshop-ecommerce'); ?></button>
</form>
